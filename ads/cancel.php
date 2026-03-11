<?php
require_once __DIR__ . '/shared/config.php';
require_once __DIR__ . '/shared/helpers.php';
require_once __DIR__ . '/shared/auth.php';

ads_session_start();
$__adv = current_advertiser();

$tx_ref     = $_GET['tx_ref'] ?? '';
$page_title = 'Payment Cancelled';
$active_nav = 'dashboard';

// Try to get booking from tx_ref
$booking_id = 0;
if ($tx_ref && preg_match('/^ADS-(\d+)-/', $tx_ref, $m)) {
    $booking_id = (int)$m[1];
}

require_once __DIR__ . '/shared/header.php';
?>

<div class="kn-result-page">
    <div style="width: 100%; max-width: 500px; margin: 0 auto;">

        <div class="kn-result-card">
            <div class="kn-result-icon error">
                <i class="fa-solid fa-circle-xmark"></i>
            </div>
            <h1 class="kn-result-title">Payment Cancelled</h1>
            <p class="kn-result-desc">
                Your payment was cancelled or did not complete. Your booking has been saved as pending — you can retry payment at any time from your dashboard.
            </p>

            <?php if ($tx_ref): ?>
            <div class="kn-booking-summary" style="text-align: left; margin-bottom: 20px;">
                <div class="kn-booking-summary-row">
                    <span class="label">Reference</span>
                    <span class="value" style="font-size: 0.8rem; font-family: monospace;"><?= h($tx_ref) ?></span>
                </div>
            </div>
            <?php endif; ?>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                <?php if ($booking_id && $__adv): ?>
                    <a href="/checkout.php?booking_id=<?= (int)$booking_id ?>" class="kn-btn kn-btn-primary kn-btn-full kn-btn-lg">
                        <i class="fa-solid fa-rotate-right"></i> Retry Payment
                    </a>
                <?php endif; ?>
                <a href="/dashboard.php" class="kn-btn kn-btn-outline-navy kn-btn-full">
                    <i class="fa-solid fa-gauge-high"></i> Back to Dashboard
                </a>
                <a href="/book.php" class="kn-btn kn-btn-ghost kn-btn-full" style="color: var(--kn-navy);">
                    <i class="fa-solid fa-plus"></i> Book a New Campaign
                </a>
            </div>

            <hr class="kn-divider">

            <p style="font-size: 0.8125rem; color: var(--kn-muted); text-align: center;">
                Need help?
                <a href="https://wa.me/256772253804" target="_blank" style="color: var(--kn-orange);">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp +256 772 253804
                </a>
                or email
                <a href="mailto:adverts@ug.kandanews.africa" style="color: var(--kn-orange);">adverts@ug.kandanews.africa</a>
            </p>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/shared/footer.php'; ?>
