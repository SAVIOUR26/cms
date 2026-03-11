<?php
/**
 * Flutterwave Webhook Handler — KandaNews Ads
 *
 * Set this URL in your Flutterwave dashboard → Settings → Webhooks:
 *   https://ads.kandanews.africa/api/pay-webhook.php
 *
 * Set "Secret Hash" in Flutterwave to match FW_WEBHOOK_HASH in your .env
 */
require_once __DIR__ . '/../shared/config.php';
require_once __DIR__ . '/../shared/db.php';

// 1. Verify Flutterwave signature
$signature = $_SERVER['HTTP_VERIF_HASH'] ?? '';
if (!$signature || !hash_equals(FW_HASH, $signature)) {
    http_response_code(401);
    exit('Unauthorized');
}

// 2. Parse body
$body  = file_get_contents('php://input');
$event = json_decode($body, true);

if (!$event || !isset($event['event'], $event['data'])) {
    http_response_code(400);
    exit('Bad Request');
}

$event_type = $event['event'];
$data       = $event['data'];

// 3. Only process successful charges
if ($event_type !== 'charge.completed' || ($data['status'] ?? '') !== 'successful') {
    http_response_code(200);
    exit('OK');
}

$tx_ref    = $data['tx_ref'] ?? '';
$flw_tx_id = (string) ($data['id'] ?? '');
$amount    = (float) ($data['amount'] ?? 0);
$currency  = $data['currency'] ?? 'UGX';

// 4. Extract booking_id from tx_ref: ADS-{booking_id}-{timestamp}
if (!preg_match('/^ADS-(\d+)-\d+$/', $tx_ref, $m)) {
    http_response_code(200);
    exit('OK');
}
$booking_id = (int) $m[1];

try {
    $db = get_db();

    // 5. Log the raw webhook event first
    $log = $db->prepare('
        INSERT INTO ads_payment_log
            (booking_id, flw_ref, flw_tx_id, event, amount, currency, raw_payload)
        VALUES (?, ?, ?, "webhook", ?, ?, ?)
    ');
    $log->execute([$booking_id, $tx_ref, $flw_tx_id, $amount, $currency, $body]);

    // 6. Verify the transaction with Flutterwave API (never trust webhook payload alone)
    $ch = curl_init("https://api.flutterwave.com/v3/transactions/{$flw_tx_id}/verify");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . FW_SECRET],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $verify_resp = curl_exec($ch);
    curl_close($ch);

    $verify = json_decode($verify_resp, true);

    $verified = (
        ($verify['status'] ?? '')         === 'success'     &&
        ($verify['data']['status'] ?? '') === 'successful'  &&
        ($verify['data']['tx_ref'] ?? '') === $tx_ref
    );

    if (!$verified) {
        error_log('ads webhook: verification failed for tx_ref=' . $tx_ref);
        http_response_code(200);
        exit('OK');
    }

    // 7. Mark booking as paid — idempotent, skips if already paid
    $upd = $db->prepare('
        UPDATE ads_bookings
        SET payment_status = "paid",
            status         = "confirmed",
            flw_ref        = ?,
            flw_tx_id      = ?,
            updated_at     = NOW()
        WHERE id = ? AND payment_status != "paid"
    ');
    $upd->execute([$tx_ref, $flw_tx_id, $booking_id]);

    // 8. Log the confirmed success
    $log2 = $db->prepare('
        INSERT INTO ads_payment_log
            (booking_id, flw_ref, flw_tx_id, event, amount, currency)
        VALUES (?, ?, ?, "success", ?, ?)
    ');
    $log2->execute([$booking_id, $tx_ref, $flw_tx_id, $amount, $currency]);

} catch (Exception $e) {
    error_log('ads webhook error: ' . $e->getMessage());
}

http_response_code(200);
echo 'OK';
