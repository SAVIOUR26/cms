<?php
require_once __DIR__ . '/shared/config.php';
require_once __DIR__ . '/shared/helpers.php';
require_once __DIR__ . '/shared/auth.php';

ads_session_start();
$__adv = require_ads_auth();

$status  = $_GET['status']  ?? '';
$tx_ref  = $_GET['tx_ref']  ?? '';
$tx_id   = $_GET['transaction_id'] ?? '';

$page_title = 'Payment Successful';
$active_nav = 'dashboard';

$booking    = null;
$verified   = false;
$errorMsg   = '';

// Extract booking ID from tx_ref (format: ADS-{id}-{time})
$booking_id = 0;
if ($tx_ref && preg_match('/^ADS-(\d+)-/', $tx_ref, $m)) {
    $booking_id = (int)$m[1];
}

if ($status === 'successful' && $tx_ref && FW_SECRET) {
    // Verify with Flutterwave
    try {
        $ch = curl_init('https://api.flutterwave.com/v3/transactions/' . urlencode($tx_id) . '/verify');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . FW_SECRET,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 15,
        ]);
        $resp = json_decode(curl_exec($ch), true);
        curl_close($ch);

        if (isset($resp['status']) && $resp['status'] === 'success'
            && isset($resp['data']['status']) && $resp['data']['status'] === 'successful'
            && isset($resp['data']['tx_ref']) && $resp['data']['tx_ref'] === $tx_ref) {

            $verified = true;
            $amount   = $resp['data']['amount'];
            $currency = $resp['data']['currency'];

            // Update booking status
            if ($booking_id) {
                $db = get_db();
                $db->prepare(
                    'UPDATE ads_bookings SET payment_status="paid", status="confirmed", flw_ref=? WHERE id=? AND advertiser_id=?'
                )->execute([$tx_ref, $booking_id, $__adv['id']]);

                $st = $db->prepare('SELECT * FROM ads_bookings WHERE id=? AND advertiser_id=?');
                $st->execute([$booking_id, $__adv['id']]);
                $booking = $st->fetch();
            }
        } else {
            $errorMsg = 'Payment verification failed. If you were charged, please contact support.';
        }
    } catch (Exception $e) {
        $errorMsg = 'Could not verify payment at this time. Please contact support with reference: ' . h($tx_ref);
    }
} elseif ($status !== 'successful') {
    redirect('/cancel.php?tx_ref=' . urlencode($tx_ref));
}

require_once __DIR__ . '/shared/header.php';
?>

<div class="kn-result-page">
    <div style="width: 100%; max-width: 540px; margin: 0 auto;">

        <?php if ($verified && $booking): ?>
        <!-- Success -->
        <div class="kn-result-card">
            <div class="kn-result-icon success">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h1 class="kn-result-title">Payment Confirmed!</h1>
            <p class="kn-result-desc">
                Your ad campaign has been booked and confirmed. Our team will review your booking and reach out for creative upload within 24 hours.
            </p>

            <!-- Booking confirmation box -->
            <div class="kn-booking-summary" style="text-align: left;">
                <div class="kn-booking-summary-row">
                    <span class="label">Booking Ref</span>
                    <span class="value"><strong>#<?= h($booking['id']) ?></strong></span>
                </div>
                <div class="kn-booking-summary-row">
                    <span class="label">Ad Format</span>
                    <span class="value"><?= h($booking['format_label']) ?></span>
                </div>
                <div class="kn-booking-summary-row">
                    <span class="label">Campaign Period</span>
                    <span class="value">
                        <?= h(date('d M Y', strtotime($booking['start_date']))) ?>
                        &rarr;
                        <?= h(date('d M Y', strtotime($booking['end_date']))) ?>
                    </span>
                </div>
                <div class="kn-booking-summary-row total-row">
                    <span class="label">Amount Paid</span>
                    <span class="value"><?= format_ugx($booking['total_price']) ?></span>
                </div>
                <div class="kn-booking-summary-row">
                    <span class="label">Payment Ref</span>
                    <span class="value" style="font-size: 0.8rem; font-family: monospace;"><?= h($tx_ref) ?></span>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 8px;">
                <a href="/dashboard.php" class="kn-btn kn-btn-primary kn-btn-full kn-btn-lg">
                    <i class="fa-solid fa-gauge-high"></i> Go to Dashboard
                </a>
                <a href="https://wa.me/256772253804?text=Hi%2C+I+just+paid+for+booking+%23<?= urlencode($booking['id']) ?>+on+KandaNews+Ads" target="_blank" rel="noopener" class="kn-btn kn-btn-outline-navy kn-btn-full">
                    <i class="fa-brands fa-whatsapp"></i> Send Creative via WhatsApp
                </a>
            </div>
        </div>

        <?php else: ?>
        <!-- Error / could not verify -->
        <div class="kn-result-card">
            <div class="kn-result-icon warning">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h1 class="kn-result-title">Verification Pending</h1>
            <p class="kn-result-desc">
                <?= $errorMsg ?: 'We could not automatically verify your payment. Please check your dashboard or contact support.' ?>
            </p>
            <?php if ($tx_ref): ?>
                <div class="kn-booking-summary" style="text-align: left;">
                    <div class="kn-booking-summary-row">
                        <span class="label">Payment Ref</span>
                        <span class="value" style="font-size: 0.8rem; font-family: monospace;"><?= h($tx_ref) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 16px;">
                <a href="/dashboard.php" class="kn-btn kn-btn-primary kn-btn-full">
                    <i class="fa-solid fa-gauge-high"></i> View Dashboard
                </a>
                <a href="https://wa.me/256772253804" target="_blank" rel="noopener" class="kn-btn kn-btn-outline-navy kn-btn-full">
                    <i class="fa-brands fa-whatsapp"></i> Contact Support
                </a>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/shared/footer.php'; ?>
