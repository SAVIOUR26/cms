<?php
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$advertiser = current_advertiser();
if (!$advertiser) {
    json_response(['error' => 'Not authenticated. Please log in.'], 401);
}

ads_session_start();

// CSRF check
$csrf = $_POST['csrf'] ?? '';
if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    json_response(['error' => 'Invalid request. Please refresh and try again.'], 403);
}

$booking_id = (int) ($_POST['booking_id'] ?? 0);
if (!$booking_id) {
    json_response(['error' => 'Invalid booking.']);
}

try {
    $db = get_db();

    // Fetch booking — must belong to this advertiser and not already paid
    $st = $db->prepare('
        SELECT * FROM ads_bookings
        WHERE id = ? AND advertiser_id = ? AND payment_status != "paid"
    ');
    $st->execute([$booking_id, $advertiser['id']]);
    $booking = $st->fetch();

    if (!$booking) {
        json_response(['error' => 'Booking not found or has already been paid.']);
    }

    $tx_ref = 'ADS-' . $booking_id . '-' . time();

    // Log payment initiation
    $log = $db->prepare('
        INSERT INTO ads_payment_log (booking_id, advertiser_id, flw_ref, event, amount, currency)
        VALUES (?, ?, ?, "init", ?, "UGX")
    ');
    $log->execute([$booking_id, $advertiser['id'], $tx_ref, $booking['total_price']]);

    // Build Flutterwave payment request
    $payload = [
        'tx_ref'       => $tx_ref,
        'amount'       => $booking['total_price'],
        'currency'     => 'UGX',
        'redirect_url' => SITE_URL . '/success.php',
        'customer'     => [
            'email'       => $advertiser['email'],
            'name'        => $advertiser['contact_name'],
            'phonenumber' => $advertiser['phone'],
        ],
        'meta' => [
            'booking_id'    => $booking_id,
            'advertiser_id' => $advertiser['id'],
            'company'       => $advertiser['company_name'],
        ],
        'customizations' => [
            'title'       => 'KandaNews Ads',
            'description' => $booking['format_label'] . ' — ' . $booking['days'] . ' day(s)',
            'logo'        => SITE_URL . '/assets/img/logo.png',
        ],
        'payment_options' => 'mobilemoney,card',
    ];

    $ch = curl_init('https://api.flutterwave.com/v3/payments');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . FW_SECRET,
        ],
        CURLOPT_TIMEOUT => 30,
    ]);
    $response   = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        error_log('Flutterwave curl error: ' . $curl_error);
        json_response(['error' => 'Could not connect to payment gateway. Please try again.'], 500);
    }

    $result = json_decode($response, true);

    if ($http_code !== 200 || ($result['status'] ?? '') !== 'success') {
        error_log('Flutterwave init failed (' . $http_code . '): ' . $response);
        json_response(['error' => 'Payment gateway rejected the request. Please try again.'], 500);
    }

    $payment_url = $result['data']['link'] ?? '';
    if (!$payment_url) {
        json_response(['error' => 'Could not retrieve payment link.'], 500);
    }

    json_response(['payment_url' => $payment_url]);

} catch (Exception $e) {
    error_log('ads pay-init error: ' . $e->getMessage());
    json_response(['error' => 'Payment initiation failed. Please try again.'], 500);
}
