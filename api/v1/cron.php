<?php
/**
 * KandaNews API v1 — Cron Tasks
 *
 * Run every hour via CyberPanel/cPanel cron:
 *   php /home/kandan/domains/api.kandanews.africa/public_html/cron.php
 *
 * Or via wget:
 *   wget -qO- "https://api.kandanews.africa/cron.php?key=YOUR_CRON_KEY"
 *
 * Tasks:
 *   1. Clean expired OTP codes (older than 1 day)
 *   2. Expire past-due subscriptions
 *   3. Log summary
 */

// Allow CLI or authenticated HTTP
$isCli = php_sapi_name() === 'cli';
if (!$isCli) {
    $config = require __DIR__ . '/config/app.php';
    $cronKey = $config['cron_key'] ?? '';
    if ($cronKey && ($_GET['key'] ?? '') !== $cronKey) {
        http_response_code(403);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    header('Content-Type: application/json');
} else {
    $config = require __DIR__ . '/config/app.php';
}

require __DIR__ . '/config/database.php';

$results = [];

// ── 1. Clean expired OTPs ──
try {
    $pdo = db();
    $stmt = $pdo->exec("DELETE FROM otp_codes WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY)");
    $results['otp_cleanup'] = "$stmt expired OTPs removed";
} catch (Exception $e) {
    $results['otp_cleanup'] = 'Error: ' . $e->getMessage();
}

// ── 2. Expire past-due subscriptions ──
try {
    $pdo = db();
    $stmt = $pdo->exec("
        UPDATE subscriptions
        SET status = 'expired'
        WHERE status = 'active' AND expires_at < NOW()
    ");
    $results['subscription_expiry'] = "$stmt subscriptions expired";
} catch (Exception $e) {
    $results['subscription_expiry'] = 'Error: ' . $e->getMessage();
}

// ── 3. Clean stale pending subscriptions (older than 24h) ──
try {
    $pdo = db();
    $stmt = $pdo->exec("
        UPDATE subscriptions
        SET status = 'cancelled'
        WHERE status = 'pending' AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $results['pending_cleanup'] = "$stmt stale pending subscriptions cancelled";
} catch (Exception $e) {
    $results['pending_cleanup'] = 'Error: ' . $e->getMessage();
}

// ── Output ──
$results['ran_at'] = date('Y-m-d H:i:s');

if ($isCli) {
    foreach ($results as $task => $msg) {
        echo "[$task] $msg\n";
    }
} else {
    echo json_encode(['ok' => true, 'results' => $results], JSON_PRETTY_PRINT);
}
