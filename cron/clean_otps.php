<?php
/**
 * KandaNews Africa — Cron: Clean Expired OTP Codes
 *
 * Purges rows from `otp_codes` that are:
 *   - already used (used = 1), OR
 *   - expired more than RETAIN_MINUTES ago (expires_at <= NOW() - INTERVAL X MINUTE)
 *
 * Keeps a short retention window so recent failures can still be debugged
 * from the DB without storing rows forever.
 *
 * Usage (cPanel / crontab):
 *   php /home/kandan/domains/cms.kandanews.africa/public_html/cron/clean_otps.php
 *
 * Or via HTTP (protected by CRON_KEY):
 *   GET https://cms.kandanews.africa/cron/clean_otps.php?key=YOUR_CRON_KEY
 *
 * Suggested schedule: daily (or every 6 hours on busy systems)
 *   0 3 * * * php /path/to/cron/clean_otps.php >> /tmp/kanda_cron.log 2>&1
 */

// ── Config ────────────────────────────────────────────────────────────────────

// Keep expired-but-unused rows for this many minutes before deleting.
// Useful for debugging "wrong code" reports. Set to 0 to purge immediately.
define('RETAIN_MINUTES', 60);

// ── Bootstrap ─────────────────────────────────────────────────────────────────

define('CRON_MODE', php_sapi_name() === 'cli');

if (!CRON_MODE) {
    $cronKey  = cron_read_env_key('CRON_KEY');
    $provided = $_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '';
    if (!$cronKey || !hash_equals($cronKey, $provided)) {
        http_response_code(401);
        exit("Unauthorized\n");
    }
    header('Content-Type: text/plain; charset=utf-8');
}

// ── Env loader ────────────────────────────────────────────────────────────────

function cron_read_env_key(string $key, string $default = ''): string {
    static $env = null;
    if ($env !== null) return $env[$key] ?? getenv($key) ?: $default;

    $env = [];
    $candidates = [
        dirname(__DIR__, 2) . '/.env',
        dirname(__DIR__, 1) . '/.env',
    ];
    foreach ($candidates as $f) {
        if (!is_file($f)) continue;
        foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if (!$line || $line[0] === '#' || strpos($line, '=') === false) continue;
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim($v);
        }
        break;
    }
    return $env[$key] ?? getenv($key) ?: $default;
}

// ── Database ──────────────────────────────────────────────────────────────────

function cron_db(): PDO {
    return new PDO(
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
}

// ── Main ──────────────────────────────────────────────────────────────────────

$startTime = microtime(true);
$timestamp = date('Y-m-d H:i:s');

cron_log("[$timestamp] clean_otps: starting (retain=" . RETAIN_MINUTES . "m)");

try {
    $pdo = cron_db();

    // Count before deletion for reporting
    $before = (int) $pdo->query("SELECT COUNT(*) FROM otp_codes")->fetchColumn();

    // Delete used OTPs (regardless of age)
    $stmtUsed = $pdo->query("DELETE FROM otp_codes WHERE used = 1");
    $deletedUsed = $stmtUsed->rowCount();

    // Delete expired-and-past-retention-window OTPs (unused ones, e.g. user never tried)
    $stmtExpired = $pdo->prepare("
        DELETE FROM otp_codes
        WHERE  used      = 0
          AND  expires_at <= DATE_SUB(NOW(), INTERVAL ? MINUTE)
    ");
    $stmtExpired->execute([RETAIN_MINUTES]);
    $deletedExpired = $stmtExpired->rowCount();

    $totalDeleted = $deletedUsed + $deletedExpired;
    $after        = (int) $pdo->query("SELECT COUNT(*) FROM otp_codes")->fetchColumn();

    cron_log("[$timestamp] clean_otps: deleted $totalDeleted row(s)"
           . " (used=$deletedUsed, expired=$deletedExpired)");
    cron_log("[$timestamp] clean_otps: table size before=$before, after=$after");

    $elapsed = round((microtime(true) - $startTime) * 1000);
    cron_log("[$timestamp] clean_otps: done in {$elapsed}ms");
    exit(0);

} catch (Throwable $e) {
    cron_log("[$timestamp] clean_otps: ERROR — " . $e->getMessage());
    exit(1);
}

// ── Helper ────────────────────────────────────────────────────────────────────

function cron_log(string $msg): void {
    echo $msg . "\n";
    error_log($msg);
}
