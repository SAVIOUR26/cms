<?php
/**
 * KandaNews Africa — Cron: Expire Subscriptions + Renewal Reminders
 *
 * Two jobs in one script:
 *
 *  1. EXPIRE  — Mark subscriptions as 'expired' where expires_at <= NOW() and
 *               status is still 'active'.
 *
 *  2. REMIND  — Send an SMS reminder to subscribers whose subscription expires
 *               within the next REMIND_HOURS hours and who haven't been
 *               reminded yet (reminder_sent_at IS NULL).
 *               Requires Africa's Talking credentials in .env.
 *               Set REMINDER_SMS=false in .env to disable without touching code.
 *
 * Database: requires a `reminder_sent_at` column on `subscriptions`.
 *   ALTER TABLE subscriptions ADD COLUMN reminder_sent_at DATETIME DEFAULT NULL;
 * This script adds it automatically if missing.
 *
 * Usage (cPanel / crontab):
 *   php /home/kandan/domains/cms.kandanews.africa/public_html/cron/expire_subscriptions.php
 *
 * Or via HTTP (protected by CRON_KEY):
 *   GET https://cms.kandanews.africa/cron/expire_subscriptions.php?key=YOUR_CRON_KEY
 *
 * Suggested schedule: every hour
 *   0 * * * * php /path/to/cron/expire_subscriptions.php >> /tmp/kanda_cron.log 2>&1
 */

// ── Bootstrap ─────────────────────────────────────────────────────────────────

define('CRON_MODE', php_sapi_name() === 'cli');

// How many hours before expiry to send the reminder
define('REMIND_HOURS', 24);

if (!CRON_MODE) {
    $cronKey  = cron_read_env_key('CRON_KEY');
    $provided = $_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '';
    if (!$cronKey || !hash_equals($cronKey, $provided)) {
        http_response_code(401);
        exit("Unauthorized\n");
    }
    header('Content-Type: text/plain; charset=utf-8');
}

// ── Env loader (standalone, no framework) ────────────────────────────────────

function cron_load_env(): array {
    $candidates = [
        dirname(__DIR__, 2) . '/.env',
        dirname(__DIR__, 1) . '/.env',
    ];
    $env = [];
    foreach ($candidates as $f) {
        if (!is_file($f)) continue;
        foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if (!$line || $line[0] === '#' || strpos($line, '=') === false) continue;
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim($v);
        }
        break; // use first found
    }
    return $env;
}

function cron_read_env_key(string $key, string $default = ''): string {
    static $env = null;
    if ($env === null) $env = cron_load_env();
    return $env[$key] ?? getenv($key) ?: $default;
}

// ── Database ──────────────────────────────────────────────────────────────────

function cron_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $pdo = new PDO(
        sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            cron_read_env_key('DB_HOST', 'localhost'),
            cron_read_env_key('DB_NAME', 'kandan_api'),
            cron_read_env_key('DB_CHARSET', 'utf8mb4')
        ),
        cron_read_env_key('DB_USER', 'kandan_api'),
        cron_read_env_key('DB_PASS', ''),
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Ensure reminder_sent_at column exists (idempotent)
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM subscriptions LIKE 'reminder_sent_at'")->fetchAll();
        if (empty($cols)) {
            $pdo->exec("ALTER TABLE subscriptions ADD COLUMN reminder_sent_at DATETIME DEFAULT NULL");
            cron_log("[schema] Added reminder_sent_at column to subscriptions");
        }
    } catch (PDOException $e) {
        cron_log("[schema] Could not check/add reminder_sent_at: " . $e->getMessage());
    }

    return $pdo;
}

// ── SMS (Africa's Talking) ────────────────────────────────────────────────────

function send_sms(string $phone, string $message): bool {
    $apiKey   = cron_read_env_key('AT_API_KEY');
    $username = cron_read_env_key('AT_USERNAME', 'sandbox');
    $senderId = cron_read_env_key('AT_SENDER_ID');

    if (!$apiKey || !$username) {
        cron_log("  [SMS] Not configured — skipping");
        return false;
    }

    $url = ($username === 'sandbox')
        ? 'https://api.sandbox.africastalking.com/version1/messaging'
        : 'https://api.africastalking.com/version1/messaging';

    $fields = ['username' => $username, 'to' => $phone, 'message' => $message];
    if ($senderId) $fields['from'] = $senderId;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields),
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'apiKey: ' . $apiKey,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        cron_log("  [SMS] cURL error: $curlErr");
        return false;
    }

    $body      = json_decode($response, true);
    $recipient = $body['SMSMessageData']['Recipients'][0] ?? [];
    $topMsg    = $body['SMSMessageData']['Message'] ?? '';

    // Empty recipients = AT error in top-level Message
    if (empty($recipient) && $topMsg) {
        cron_log("  [SMS] AT error: $topMsg");
        return false;
    }

    $atCode = (int)($recipient['statusCode'] ?? 0);
    $failCodes = [401, 402, 403, 404, 405, 406, 407, 500, 501, 502];
    if (in_array($atCode, $failCodes, true)) {
        cron_log("  [SMS] AT delivery failed (code $atCode): " . ($recipient['status'] ?? ''));
        return false;
    }

    return $httpCode >= 200 && $httpCode < 300;
}

/**
 * Format a phone number to E.164.
 * Mirrors normalize_phone() in api/v1/helpers/otp.php.
 */
function normalize_phone(string $phone, string $country = 'ug'): string {
    $phone = preg_replace('/[\s\-\(\)]/', '', $phone);
    $prefixes = ['ug' => '256', 'ke' => '254', 'ng' => '234', 'za' => '27'];
    $prefix = $prefixes[$country] ?? '256';
    if (str_starts_with($phone, '+')) return $phone;
    if (str_starts_with($phone, $prefix)) return '+' . $phone;
    if (str_starts_with($phone, '0'))     return '+' . $prefix . substr($phone, 1);
    return '+' . $prefix . $phone;
}

// ── Main ──────────────────────────────────────────────────────────────────────

$startTime  = microtime(true);
$timestamp  = date('Y-m-d H:i:s');
$smsEnabled = strtolower(cron_read_env_key('REMINDER_SMS', 'true')) !== 'false';

cron_log("[$timestamp] expire_subscriptions: starting (reminders=" . ($smsEnabled ? 'on' : 'off') . ")");

try {
    $pdo = cron_db();

    // ── 1. Expire overdue active subscriptions ────────────────────────────────
    $expireStmt = $pdo->query("
        SELECT s.id, s.user_id, s.plan, s.expires_at, u.full_name, u.phone, u.country
        FROM   subscriptions s
        JOIN   users u ON u.id = s.user_id
        WHERE  s.status   = 'active'
          AND  s.expires_at <= NOW()
    ");
    $toExpire = $expireStmt->fetchAll();

    if (empty($toExpire)) {
        cron_log("[$timestamp] expire_subscriptions: no subscriptions to expire");
    } else {
        $ids = array_column($toExpire, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $pdo->prepare("UPDATE subscriptions SET status = 'expired' WHERE id IN ($placeholders)")
            ->execute($ids);

        cron_log(sprintf("[$timestamp] expire_subscriptions: expired %d subscription(s)", count($toExpire)));
        foreach ($toExpire as $sub) {
            cron_log(sprintf(
                "  → Sub #%d user #%d (%s) %s plan — expired %s",
                $sub['id'], $sub['user_id'],
                mb_strimwidth($sub['full_name'] ?? 'unknown', 0, 30, '…'),
                strtoupper($sub['plan']),
                $sub['expires_at']
            ));
        }
    }

    // ── 2. Send renewal reminders ─────────────────────────────────────────────
    if ($smsEnabled) {
        $remindStmt = $pdo->prepare("
            SELECT s.id, s.plan, s.expires_at,
                   u.full_name, u.phone, u.country
            FROM   subscriptions s
            JOIN   users u ON u.id = s.user_id
            WHERE  s.status           = 'active'
              AND  s.reminder_sent_at IS NULL
              AND  s.expires_at       > NOW()
              AND  s.expires_at       <= DATE_ADD(NOW(), INTERVAL ? HOUR)
        ");
        $remindStmt->execute([REMIND_HOURS]);
        $toRemind = $remindStmt->fetchAll();

        if (empty($toRemind)) {
            cron_log("[$timestamp] expire_subscriptions: no reminders to send");
        } else {
            cron_log(sprintf("[$timestamp] expire_subscriptions: sending %d reminder(s)", count($toRemind)));

            foreach ($toRemind as $sub) {
                $phone = normalize_phone($sub['phone'] ?? '', $sub['country'] ?? 'ug');
                $name  = explode(' ', $sub['full_name'] ?? 'Reader')[0];
                $plan  = ucfirst($sub['plan']);
                $expiry = date('d M Y', strtotime($sub['expires_at']));

                $message = "Hi {$name}, your KandaNews {$plan} subscription expires on {$expiry}. "
                         . "Renew now to keep reading: https://kandanews.africa/subscribe";

                cron_log("  → Reminding sub #{$sub['id']} phone $phone");
                $sent = send_sms($phone, $message);

                // Record that reminder was sent (even on failure, to avoid spam retries)
                $pdo->prepare("UPDATE subscriptions SET reminder_sent_at = NOW() WHERE id = ?")
                    ->execute([$sub['id']]);

                cron_log("  " . ($sent ? "✓ sent" : "✗ failed (recorded to avoid retry)"));
            }
        }
    }

    $elapsed = round((microtime(true) - $startTime) * 1000);
    cron_log("[$timestamp] expire_subscriptions: done in {$elapsed}ms");
    exit(0);

} catch (Throwable $e) {
    cron_log("[$timestamp] expire_subscriptions: ERROR — " . $e->getMessage());
    cron_log($e->getTraceAsString());
    exit(1);
}

// ── Helper ────────────────────────────────────────────────────────────────────

function cron_log(string $msg): void {
    echo $msg . "\n";
    error_log($msg);
}
