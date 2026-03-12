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
        case 'GET banners':  misc_banners();  break;
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

    $testCode = otp_generate();
    $message  = "Your KandaNews verification code is: $testCode. Valid for 5 minutes. Do not share this code.";

    // Call AT directly here so we can expose the raw response
    $url    = $username === 'sandbox'
        ? 'https://api.sandbox.africastalking.com/version1/messaging'
        : 'https://api.africastalking.com/version1/messaging';
    $fields = ['username' => $username, 'to' => $phone, 'message' => $message];
    if ($senderId) $fields['from'] = $senderId;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($fields),
        CURLOPT_HTTPHEADER     => ['Accept: application/json', 'Content-Type: application/x-www-form-urlencoded', 'apiKey: ' . $apiKey],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $atResponse = curl_exec($ch);
    $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr    = curl_error($ch);
    curl_close($ch);

    $atBody        = json_decode($atResponse, true);
    $recipient     = $atBody['SMSMessageData']['Recipients'][0] ?? [];
    $topLevelMsg   = $atBody['SMSMessageData']['Message'] ?? '';
    $atStatus      = $recipient['status']     ?? ($topLevelMsg ?: 'unknown');
    $atCode        = $recipient['statusCode'] ?? 'unknown';
    $atCost        = $recipient['cost']       ?? 'unknown';

    // When Recipients is empty AT puts the error in the top-level Message field
    $isTopLevelError = empty($recipient) && $topLevelMsg;

    $diagnosis = match(true) {
        in_array((int)$atCode, [100, 101, 102], true)
            => 'SUCCESS — SMS queued/sent.',
        (int)$atCode === 405
            => 'FAILED — InsufficientBalance. Top up your AT wallet: africastalking.com → Billing.',
        (int)$atCode === 402 || ($isTopLevelError && str_contains($topLevelMsg, 'InvalidSenderId'))
            => 'FAILED — InvalidSenderId. "KandaNews" is not registered. '
             . 'Go to AT Dashboard → SMS → Sender IDs → Add "KandaNews". '
             . 'OR blank out AT_SENDER_ID= in your .env to use a shared shortcode immediately.',
        (int)$atCode === 401
            => 'FAILED — RiskHold. Contact Africa\'s Talking support.',
        (int)$atCode === 403
            => 'FAILED — InvalidPhoneNumber. Check the phone number format.',
        (int)$atCode === 407
            => 'FAILED — CouldNotRoute. Try blanking AT_SENDER_ID in .env.',
        default
            => 'Check at_status and at_raw above for details.',
    };

    json_success([
        'http_code'      => $httpCode,
        'curl_error'     => $curlErr ?: null,
        'at_status'      => $atStatus,
        'at_status_code' => $atCode,
        'at_cost'        => $atCost,
        'at_raw'         => $atBody,
        'phone'          => $phone,
        'test_code'      => $testCode,
        'config'         => $configStatus,
        'diagnosis'      => $diagnosis,
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

/**
 * GET /misc/banners?country=ug
 *
 * Returns active promotional banners for the home screen.
 * Respects starts_at / ends_at scheduling windows.
 */
function misc_banners(): void {
    $user    = optional_auth();
    $country = $_GET['country'] ?? ($user['country'] ?? 'ug');
    $country = strtolower(trim($country));

    $pdo  = db();
    $stmt = $pdo->prepare("
        SELECT id, title, subtitle, action_url, action_label,
               bg_color_hex, icon_name
        FROM   home_banners
        WHERE  is_active = 1
          AND  (country IS NULL OR country = ?)
          AND  (starts_at IS NULL OR starts_at <= NOW())
          AND  (ends_at   IS NULL OR ends_at   >= NOW())
        ORDER  BY sort_order ASC, id ASC
    ");
    $stmt->execute([$country]);
    $banners = $stmt->fetchAll();

    foreach ($banners as &$b) {
        $b['id'] = (int) $b['id'];
    }
    unset($b);

    json_success(['banners' => $banners]);
}
