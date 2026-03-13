<?php
/**
 * KandaNews Africa — Cron: Close Expired Polls
 *
 * Marks polls as 'closed' once their ends_at time has passed.
 *
 * Usage (cPanel / crontab):
 *   php /home/kandan/domains/cms.kandanews.africa/public_html/cron/close_polls.php
 *
 * Or via HTTP (protected by CRON_KEY):
 *   GET https://cms.kandanews.africa/cron/close_polls.php?key=YOUR_CRON_KEY
 *
 * Suggested schedule: every 15 minutes
 *   */15 * * * * php /path/to/cron/close_polls.php >> /tmp/kanda_cron.log 2>&1
 */

// ── Bootstrap ─────────────────────────────────────────────────────────────────

define('CRON_MODE', php_sapi_name() === 'cli');

if (!CRON_MODE) {
    // HTTP invocation — enforce key check
    $envFile = dirname(__DIR__) . '/.env';
    $cronKey = '';
    if (is_file($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (strpos($line, 'CRON_KEY=') === 0) {
                $cronKey = trim(substr($line, 9));
                break;
            }
        }
    }
    $provided = $_GET['key'] ?? $_SERVER['HTTP_X_CRON_KEY'] ?? '';
    if (!$cronKey || !hash_equals($cronKey, $provided)) {
        http_response_code(401);
        exit("Unauthorized\n");
    }
    header('Content-Type: text/plain; charset=utf-8');
}

// ── Database ──────────────────────────────────────────────────────────────────

function cron_db(): PDO {
    // Look for .env two levels up (above public_html) then one level up
    $candidates = [
        dirname(__DIR__, 2) . '/.env',
        dirname(__DIR__, 1) . '/.env',
    ];
    foreach ($candidates as $f) {
        if (is_file($f)) {
            foreach (file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if (!$line || $line[0] === '#' || strpos($line, '=') === false) continue;
                [$k, $v] = explode('=', $line, 2);
                $_ENV[trim($k)] = trim($v);
            }
            break;
        }
    }

    $host    = $_ENV['DB_HOST']    ?? getenv('DB_HOST')    ?: 'localhost';
    $dbname  = $_ENV['DB_NAME']    ?? getenv('DB_NAME')    ?: 'kandan_api';
    $user    = $_ENV['DB_USER']    ?? getenv('DB_USER')    ?: 'kandan_api';
    $pass    = $_ENV['DB_PASS']    ?? getenv('DB_PASS')    ?: '';
    $charset = $_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4';

    return new PDO("mysql:host={$host};dbname={$dbname};charset={$charset}", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

// ── Main logic ────────────────────────────────────────────────────────────────

$startTime = microtime(true);
$timestamp = date('Y-m-d H:i:s');

cron_log("[$timestamp] close_polls: starting");

try {
    $pdo = cron_db();

    // Find polls that have passed their end time and are still marked active
    $stmt = $pdo->query("
        SELECT id, question, country, ends_at
        FROM   polls
        WHERE  status = 'active'
          AND  ends_at IS NOT NULL
          AND  ends_at <= NOW()
    ");
    $expired = $stmt->fetchAll();

    if (empty($expired)) {
        cron_log("[$timestamp] close_polls: no polls to close");
    } else {
        // Bulk-close in one statement (safe even if list is empty)
        $ids = array_column($expired, 'id');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));

        $update = $pdo->prepare(
            "UPDATE polls SET status = 'closed' WHERE id IN ($placeholders)"
        );
        $update->execute($ids);

        $closed = $update->rowCount();
        cron_log("[$timestamp] close_polls: closed $closed poll(s)");

        foreach ($expired as $poll) {
            cron_log(sprintf(
                "  → Poll #%d [%s] \"%s\" — ended %s",
                $poll['id'],
                strtoupper($poll['country']),
                mb_strimwidth($poll['question'], 0, 60, '…'),
                $poll['ends_at']
            ));
        }
    }

    $elapsed = round((microtime(true) - $startTime) * 1000);
    cron_log("[$timestamp] close_polls: done in {$elapsed}ms");
    exit(0);

} catch (Throwable $e) {
    cron_log("[$timestamp] close_polls: ERROR — " . $e->getMessage());
    exit(1);
}

// ── Helper ────────────────────────────────────────────────────────────────────

function cron_log(string $msg): void {
    echo $msg . "\n";
    error_log($msg);
}
