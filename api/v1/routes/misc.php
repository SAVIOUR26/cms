<?php
/**
 * KandaNews API v1 — Miscellaneous Routes
 *
 * GET /misc/quote   — Quote of the day
 */

function route_misc(string $action, string $method): void {
    switch ("$method $action") {
        case 'GET quote':    misc_quote();    break;
        case 'GET sms-test': misc_sms_test(); break;
        default: json_error('Not found', 404);
    }
}

/**
 * GET /misc/sms-test?phone=+256...&key=CRON_KEY
 *
 * Diagnostic: sends a real OTP SMS to the given phone number.
 * Protected by the same CRON_KEY secret.
 * Use this to verify Africa's Talking credentials are working.
 * REMOVE or DISABLE after confirming SMS works.
 */
function misc_sms_test(): void {
    global $config;

    // Protect with cron key
    $key = $_GET['key'] ?? '';
    if (!$key || $key !== $config['cron_key']) {
        json_error('Unauthorized', 401);
    }

    $phone = trim($_GET['phone'] ?? '');
    if (!$phone) {
        json_error('phone parameter is required. E.g. ?phone=+256772000000&key=YOUR_CRON_KEY');
    }

    $phone = normalize_phone($phone, 'ug');

    // Report current config (masked)
    $apiKey   = $config['at_api_key'];
    $username = $config['at_username'];
    $senderId = $config['at_sender_id'];

    $configStatus = [
        'at_api_key'   => $apiKey   ? substr($apiKey, 0, 8) . '...' : 'NOT SET',
        'at_username'  => $username ?: 'NOT SET',
        'at_sender_id' => $senderId ?: 'NOT SET',
        'endpoint'     => $username === 'sandbox'
            ? 'https://api.sandbox.africastalking.com/version1/messaging (SANDBOX)'
            : 'https://api.africastalking.com/version1/messaging (LIVE)',
    ];

    if (!$apiKey || !$username) {
        json_error('Africa\'s Talking not configured — check AT_API_KEY and AT_USERNAME in .env', 500, [
            'config' => $configStatus,
        ]);
    }

    $testCode = (string) random_int(100000, 999999);
    $sent = otp_send_sms($phone, $testCode);

    json_success([
        'sms_sent'   => $sent,
        'phone'      => $phone,
        'test_code'  => $testCode, // visible here for verification
        'config'     => $configStatus,
        'note'       => $sent
            ? 'SMS dispatched to Africa\'s Talking. Check your phone and server error_log for the AT response.'
            : 'SMS FAILED. Check server error_log for details.',
    ]);
}

/**
 * GET /misc/quote
 *
 * Returns a random quote of the day, consistent for the same calendar day.
 * Uses the day-of-year as a seed to deterministically pick a quote.
 */
function misc_quote(): void {
    $pdo = db();

    // Fetch all active quotes
    $stmt = $pdo->query("SELECT id, quote, author FROM quotes WHERE active = 1 ORDER BY id ASC");
    $quotes = $stmt->fetchAll();

    if (empty($quotes)) {
        json_error('No quotes available', 404);
    }

    // Use day of year as seed for consistent daily selection
    $dayOfYear = (int) date('z'); // 0-365
    $index = $dayOfYear % count($quotes);

    $selected = $quotes[$index];

    json_success([
        'quote'  => $selected['quote'],
        'author' => $selected['author'],
    ]);
}
