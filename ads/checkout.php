<?php
require_once __DIR__ . '/shared/config.php';
require_once __DIR__ . '/shared/helpers.php';
require_once __DIR__ . '/shared/auth.php';

ads_session_start();
$__adv = require_ads_auth();

$booking_id = (int)($_GET['booking_id'] ?? 0);
if ($booking_id < 1) {
    flash('error', 'Invalid booking reference.');
    redirect('/dashboard.php');
}

// Fetch booking — must belong to this advertiser and be pending
$booking = null;
try {
    $db = get_db();
    $st = $db->prepare(
        'SELECT b.*, a.company_name, a.email, a.phone
         FROM ads_bookings b
         JOIN ads_advertisers a ON a.id = b.advertiser_id
         WHERE b.id = ? AND b.advertiser_id = ?'
    );
    $st->execute([$booking_id, $__adv['id']]);
    $booking = $st->fetch();
} catch (Exception $e) {
    // fall through
}

if (!$booking) {
    flash('error', 'Booking not found or access denied.');
    redirect('/dashboard.php');
}

if ($booking['payment_status'] === 'paid') {
    flash('success', 'This booking has already been paid for.');
    redirect('/dashboard.php');
}

// CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$page_title = 'Checkout — Booking #' . $booking['id'];
$active_nav = 'dashboard';

require_once __DIR__ . '/shared/header.php';
?>

<div class="kn-page-wrapper">
    <div class="container" style="max-width: 600px;">

        <div class="kn-page-header" style="text-align: center;">
            <h1 class="kn-page-title">Complete Your Payment</h1>
            <p class="kn-page-subtitle">Review your booking and pay securely via Flutterwave.</p>
        </div>

        <div id="checkout-alert"></div>

        <div class="kn-card">
            <div class="kn-card-header">
                <h2 class="kn-card-title">
                    <i class="fa-solid fa-file-invoice" style="color: var(--kn-orange);"></i>
                    Booking #<?= h($booking['id']) ?>
                </h2>
                <span class="kn-badge kn-badge-pending">
                    <i class="fa-solid fa-clock"></i> Awaiting Payment
                </span>
            </div>
            <div class="kn-card-body">
                <!-- Booking Summary -->
                <div class="kn-booking-summary">
                    <div class="kn-booking-summary-row">
                        <span class="label">Advertiser</span>
                        <span class="value"><?= h($booking['company_name']) ?></span>
                    </div>
                    <div class="kn-booking-summary-row">
                        <span class="label">Ad Format</span>
                        <span class="value"><strong><?= h($booking['format_label']) ?></strong></span>
                    </div>
                    <div class="kn-booking-summary-row">
                        <span class="label">Edition</span>
                        <span class="value">
                            <span class="kn-badge kn-badge-orange"><i class="fa-solid fa-flag"></i> Uganda</span>
                        </span>
                    </div>
                    <div class="kn-booking-summary-row">
                        <span class="label">Campaign Period</span>
                        <span class="value">
                            <?= h(date('d M Y', strtotime($booking['start_date']))) ?>
                            &rarr;
                            <?= h(date('d M Y', strtotime($booking['end_date']))) ?>
                        </span>
                    </div>
                    <div class="kn-booking-summary-row">
                        <span class="label">Duration</span>
                        <span class="value"><?= h($booking['days']) ?> day<?= $booking['days'] != 1 ? 's' : '' ?></span>
                    </div>
                    <div class="kn-booking-summary-row">
                        <span class="label">Unit Price</span>
                        <span class="value"><?= format_ugx($booking['unit_price']) ?>/day</span>
                    </div>
                    <div class="kn-booking-summary-row">
                        <span class="label">Subtotal</span>
                        <span class="value"><?= format_ugx($booking['subtotal']) ?></span>
                    </div>
                    <?php if ($booking['discount_pct'] > 0): ?>
                    <div class="kn-booking-summary-row">
                        <span class="label">Discount (<?= h($booking['discount_pct']) ?>%)</span>
                        <span class="value" style="color: var(--kn-success);">
                            -<?= format_ugx($booking['subtotal'] - $booking['total_price']) ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div class="kn-booking-summary-row total-row">
                        <span class="label"><strong>Total Payable</strong></span>
                        <span class="value"><strong><?= format_ugx($booking['total_price']) ?></strong></span>
                    </div>
                </div>

                <div class="kn-alert kn-alert-info">
                    <i class="fa-solid fa-shield-halved kn-alert-icon"></i>
                    <span>You will be redirected to <strong>Flutterwave</strong> — Africa's trusted payment platform. Payment is processed in <strong>UGX</strong> via Mobile Money or Card.</span>
                </div>

                <form id="checkout-form">
                    <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
                    <input type="hidden" name="booking_id" value="<?= h($booking['id']) ?>">

                    <button type="submit" id="pay-btn" class="kn-btn kn-btn-primary kn-btn-full kn-btn-xl" style="margin-top: 8px;">
                        <i class="fa-solid fa-credit-card"></i>
                        Pay <?= format_ugx($booking['total_price']) ?> via Flutterwave
                    </button>
                </form>

                <div style="display: flex; justify-content: center; gap: 24px; margin-top: 16px; font-size: 0.8rem; color: var(--kn-muted);">
                    <span><i class="fa-solid fa-lock" style="color: var(--kn-success);"></i> SSL Secured</span>
                    <span><i class="fa-solid fa-mobile-screen" style="color: var(--kn-success);"></i> Mobile Money</span>
                    <span><i class="fa-solid fa-credit-card" style="color: var(--kn-success);"></i> Card Payment</span>
                </div>

                <hr class="kn-divider">

                <p style="text-align: center;">
                    <a href="/dashboard.php" style="color: var(--kn-muted); font-size: 0.875rem;">
                        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                    </a>
                </p>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/shared/footer.php'; ?>
