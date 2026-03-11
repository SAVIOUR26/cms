<?php
require_once __DIR__ . '/shared/config.php';
require_once __DIR__ . '/shared/helpers.php';
require_once __DIR__ . '/shared/auth.php';

ads_session_start();
$__adv = require_ads_auth();

// CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$page_title = 'Book an Ad';
$active_nav = 'dashboard';

$formats = AD_FORMATS;
$format_icons = [
    'full_page'         => 'fa-newspaper',
    'half_page'         => 'fa-file-image',
    'video_60'          => 'fa-video',
    'video_30'          => 'fa-film',
    'audio_60'          => 'fa-microphone',
    'audio_30'          => 'fa-volume-high',
    'gif_insert'        => 'fa-images',
    'cart_ad'           => 'fa-cart-shopping',
    'market_listing'    => 'fa-store',
    'sponsored_content' => 'fa-star',
];

require_once __DIR__ . '/shared/header.php';
?>

<div class="kn-page-wrapper">
    <div class="container" style="max-width: 860px;">

        <!-- Page header -->
        <div class="kn-page-header" style="text-align: center;">
            <h1 class="kn-page-title">Book Your Ad</h1>
            <p class="kn-page-subtitle">Uganda Edition — complete the 3 steps below to book your campaign.</p>
        </div>

        <!-- Wizard container -->
        <div id="kn-booking-wizard">

            <!-- Step Indicators -->
            <div class="kn-wizard-steps">
                <div class="kn-wizard-step active" data-step="1">
                    <div class="kn-wizard-step-num">1</div>
                    <span class="kn-wizard-step-label">Choose Format</span>
                </div>
                <div class="kn-wizard-connector" id="connector-1"></div>
                <div class="kn-wizard-step" data-step="2">
                    <div class="kn-wizard-step-num">2</div>
                    <span class="kn-wizard-step-label">Choose Dates</span>
                </div>
                <div class="kn-wizard-connector" id="connector-2"></div>
                <div class="kn-wizard-step" data-step="3">
                    <div class="kn-wizard-step-num">3</div>
                    <span class="kn-wizard-step-label">Review &amp; Book</span>
                </div>
            </div>

            <!-- ===== STEP 1: FORMAT ===== -->
            <div class="kn-wizard-panel active" id="step-1">
                <div class="kn-card">
                    <div class="kn-card-header">
                        <h2 class="kn-card-title">
                            <i class="fa-solid fa-table-cells-large" style="color: var(--kn-orange);"></i>
                            Select Ad Format
                        </h2>
                        <span class="kn-pill">Step 1 of 3</span>
                    </div>
                    <div class="kn-card-body">
                        <div id="step1-alert"></div>
                        <div class="kn-format-grid">
                            <?php foreach ($formats as $key => $fmt): ?>
                            <div class="kn-format-card"
                                 data-format-key="<?= h($key) ?>"
                                 data-format-label="<?= h($fmt['label']) ?>"
                                 data-format-price="<?= (int)$fmt['price'] ?>">
                                <div class="kn-format-check"><i class="fa-solid fa-check"></i></div>
                                <div class="kn-format-icon">
                                    <i class="fa-solid <?= h($format_icons[$key] ?? 'fa-rectangle-ad') ?>"></i>
                                </div>
                                <div class="kn-format-name"><?= h($fmt['label']) ?></div>
                                <div class="kn-format-desc"><?= h($fmt['desc']) ?></div>
                                <div class="kn-format-price"><?= format_ugx($fmt['price']) ?>/day</div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div style="margin-top: 28px; display: flex; justify-content: flex-end;">
                            <button type="button" id="wizard-step1-next" class="kn-btn kn-btn-primary kn-btn-lg" disabled>
                                Continue <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== STEP 2: DATES ===== -->
            <div class="kn-wizard-panel" id="step-2">
                <div class="kn-card">
                    <div class="kn-card-header">
                        <h2 class="kn-card-title">
                            <i class="fa-solid fa-calendar-days" style="color: var(--kn-orange);"></i>
                            Choose Campaign Dates
                        </h2>
                        <span class="kn-pill">Step 2 of 3</span>
                    </div>
                    <div class="kn-card-body">
                        <div id="step2-alert"></div>

                        <div class="kn-form-row" style="margin-bottom: 24px;">
                            <div class="kn-form-group">
                                <label class="kn-label" for="booking-start-date">
                                    Start Date <span class="req">*</span>
                                </label>
                                <input type="date" id="booking-start-date" class="kn-input">
                                <div class="kn-form-hint">Campaigns start from the following day at earliest.</div>
                            </div>
                            <div class="kn-form-group">
                                <label class="kn-label" for="booking-days">
                                    Number of Days <span class="req">*</span>
                                </label>
                                <input type="number" id="booking-days" class="kn-input"
                                       min="1" max="365" value="1" placeholder="1">
                            </div>
                        </div>

                        <!-- Slider -->
                        <div class="kn-form-group">
                            <label class="kn-label">Duration: <strong id="price-days">1 day</strong></label>
                            <input type="range" id="booking-days-range" class="kn-range"
                                   min="1" max="60" value="1">
                            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--kn-muted); margin-top: 4px;">
                                <span>1 day</span>
                                <span>7 days <span class="kn-pill" style="font-size: 0.7rem; padding: 2px 8px;">10% off</span></span>
                                <span>30 days <span class="kn-pill" style="font-size: 0.7rem; padding: 2px 8px;">20% off</span></span>
                                <span>60 days</span>
                            </div>
                        </div>

                        <!-- Price calculator -->
                        <div class="kn-price-box" style="margin-top: 24px;">
                            <h3 style="font-size: 1rem; font-weight: 700; color: var(--kn-white); margin-bottom: 16px;">
                                <i class="fa-solid fa-calculator"></i> Price Estimate
                            </h3>
                            <div class="kn-price-row">
                                <span>Format</span>
                                <span class="kn-price-value" id="review-format-name">—</span>
                            </div>
                            <div class="kn-price-row">
                                <span>Unit Price</span>
                                <span class="kn-price-value" id="price-unit">—</span>
                            </div>
                            <div class="kn-price-row">
                                <span>Duration</span>
                                <span class="kn-price-value" id="price-days-label">—</span>
                            </div>
                            <div class="kn-price-row">
                                <span>End Date</span>
                                <span class="kn-price-value" id="price-end-date">—</span>
                            </div>
                            <div class="kn-price-row">
                                <span>Subtotal</span>
                                <span class="kn-price-value" id="price-subtotal">—</span>
                            </div>
                            <div class="kn-price-row" id="price-discount-row" style="display: none;">
                                <span>Discount</span>
                                <span class="kn-price-value discount" id="price-discount">—</span>
                            </div>
                            <div class="kn-price-row total">
                                <span>Total</span>
                                <span class="kn-price-total-amount" id="price-total">—</span>
                            </div>
                        </div>

                        <div style="margin-top: 28px; display: flex; justify-content: space-between;">
                            <button type="button" id="wizard-step2-back" class="kn-btn kn-btn-outline-navy">
                                <i class="fa-solid fa-arrow-left"></i> Back
                            </button>
                            <button type="button" id="wizard-step2-next" class="kn-btn kn-btn-primary kn-btn-lg">
                                Review Booking <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== STEP 3: REVIEW ===== -->
            <div class="kn-wizard-panel" id="step-3">
                <div class="kn-card">
                    <div class="kn-card-header">
                        <h2 class="kn-card-title">
                            <i class="fa-solid fa-clipboard-check" style="color: var(--kn-orange);"></i>
                            Review &amp; Confirm
                        </h2>
                        <span class="kn-pill">Step 3 of 3</span>
                    </div>
                    <div class="kn-card-body">
                        <div id="step3-alert"></div>

                        <!-- Booking summary -->
                        <div class="kn-booking-summary">
                            <h3 style="font-size: 1rem; font-weight: 700; color: var(--kn-navy); margin-bottom: 16px;">
                                Booking Summary
                            </h3>
                            <div class="kn-booking-summary-row">
                                <span class="label">Advertiser</span>
                                <span class="value"><?= h($__adv['company_name']) ?></span>
                            </div>
                            <div class="kn-booking-summary-row">
                                <span class="label">Ad Format</span>
                                <span class="value" id="review-format">—</span>
                            </div>
                            <div class="kn-booking-summary-row">
                                <span class="label">Edition</span>
                                <span class="value">
                                    <span class="kn-badge kn-badge-orange"><i class="fa-solid fa-flag"></i> Uganda</span>
                                </span>
                            </div>
                            <div class="kn-booking-summary-row">
                                <span class="label">Start Date</span>
                                <span class="value" id="review-start-date">—</span>
                            </div>
                            <div class="kn-booking-summary-row">
                                <span class="label">End Date</span>
                                <span class="value" id="review-end-date">—</span>
                            </div>
                            <div class="kn-booking-summary-row">
                                <span class="label">Duration</span>
                                <span class="value" id="review-days">—</span>
                            </div>
                            <div class="kn-booking-summary-row">
                                <span class="label">Rate per Day</span>
                                <span class="value" id="review-unit-price">—</span>
                            </div>
                            <div class="kn-booking-summary-row">
                                <span class="label">Subtotal</span>
                                <span class="value" id="review-subtotal">—</span>
                            </div>
                            <div class="kn-booking-summary-row" id="review-discount-row">
                                <span class="label">Discount</span>
                                <span class="value" id="review-discount" style="color: var(--kn-success);">—</span>
                            </div>
                            <div class="kn-booking-summary-row total-row">
                                <span class="label"><strong>Total Payable</strong></span>
                                <span class="value" id="review-total">—</span>
                            </div>
                        </div>

                        <div class="kn-alert kn-alert-info">
                            <i class="fa-solid fa-circle-info kn-alert-icon"></i>
                            <span>After booking you will be redirected to <strong>Flutterwave</strong> to complete payment in UGX. Your ad will go live after payment confirmation and creative upload.</span>
                        </div>

                        <!-- Hidden form inputs -->
                        <form id="booking-form" style="display: none;">
                            <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
                            <input type="hidden" id="input-format-key" name="format_key">
                            <input type="hidden" id="input-format-label" name="format_label">
                            <input type="hidden" id="input-start-date" name="start_date">
                            <input type="hidden" id="input-days" name="days">
                        </form>

                        <div style="margin-top: 24px; display: flex; justify-content: space-between; gap: 12px;">
                            <button type="button" id="wizard-step3-back" class="kn-btn kn-btn-outline-navy">
                                <i class="fa-solid fa-arrow-left"></i> Back
                            </button>
                            <button type="button" id="booking-submit-btn" class="kn-btn kn-btn-primary kn-btn-lg"
                                    onclick="document.getElementById('booking-form').dispatchEvent(new Event('submit', {cancelable:true,bubbles:true}))">
                                <i class="fa-solid fa-credit-card"></i> Proceed to Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /#kn-booking-wizard -->
    </div>
</div>

<script>
// Fix: bind the days label in price box separately
document.addEventListener('DOMContentLoaded', function() {
    const daysEl = document.getElementById('price-days');
    const daysLabelEl = document.getElementById('price-days-label');
    if (daysEl && daysLabelEl) {
        const obs = new MutationObserver(function() {
            daysLabelEl.textContent = daysEl.textContent;
        });
        obs.observe(daysEl, { childList: true, subtree: true, characterData: true });
    }
});
</script>

<?php require_once __DIR__ . '/shared/footer.php'; ?>
