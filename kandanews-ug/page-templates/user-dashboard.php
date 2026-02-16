<?php
/* Template Name: Kanda — user-dashboard */

if (!is_user_logged_in()) {
    wp_safe_redirect(home_url('/login/'));
    exit;
}

get_header();

$current_user   = wp_get_current_user();
$user_name_safe = esc_html($current_user->display_name ?: $current_user->user_login);
$logout_url     = wp_logout_url(home_url('/'));
$logo_url       = esc_url(get_theme_file_uri('assets/kandanews-logo.gif'));

$country = sanitize_text_field(get_user_meta($current_user->ID, 'kanda_country', true));
$host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : '';

$cc = '';
if (preg_match('/^([a-z]{2})\./i', $host, $m)) {
    $cc = strtoupper($m[1]);
} elseif ($country) {
    $cc = strtoupper(substr($country, 0, 2));
}
if (!preg_match('/^[A-Z]{2}$/', $cc)) {
    $cc = '';
}

function kanda_cc_flag($cc) {
    if (!is_string($cc) || strlen($cc) !== 2) {
        return '';
    }
    $cc = strtoupper($cc);
    $base = 0x1F1E6;
    $entities = array();

    for ($i = 0; $i < 2; $i++) {
        $char = ord($cc[$i]);
        if ($char < ord('A') || $char > ord('Z')) {
            return '';
        }
        $entities[] = '&#' . ($base + ($char - ord('A'))) . ';';
    }

    return mb_convert_encoding(implode('', $entities), 'UTF-8', 'HTML-ENTITIES');
}

$flag = $cc ? kanda_cc_flag($cc) : '🌍';
$cc_label = $cc ?: 'Global';

?>
<div class="kanda-dashboard-container">
    <div class="wrap">
        <section class="dashboard-section" aria-labelledby="welcomeTitle">
            <div class="hero">
                <h1 id="welcomeTitle" class="h">Welcome, <?php echo $user_name_safe; ?> 👋</h1>
                <div class="header-pills">
                    <button id="profileButton" class="btn ghost" type="button" aria-controls="profileInfoCard" aria-expanded="false">Your Profile 👤</button>
                    <span class="pill"><?php echo wp_kses_post($flag . ' ' . esc_html($cc_label)); ?></span>
                    <span id="subStatusPill" class="pill">Loading…</span>
                    <a class="btn ghost" href="<?php echo esc_url($logout_url); ?>" title="Logout">Logout</a>
                </div>
            </div>
            
            <div class="ticker" aria-label="KandaNews audience">
                <div class="track">
                    <strong>KandaNews Africa</strong> — Designed for <em>Professionals</em> <span class="dot">•</span> <em>University Students</em> <span class="dot">•</span> <em>Entrepreneurs</em>
                    <strong>KandaNews Africa</strong> — Designed for <em>Professionals</em> <span class="dot">•</span> <em>University Students</em> <span class="dot">•</span> <em>Entrepreneurs</em>
                </div>
            </div>

            <div class="hero">
                <div class="type" id="typeTarget" aria-live="polite"></div>
                <?php if ($logo_url) : ?>
                    <img id="brandLogo" class="logo logoReveal" src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr__('KandaNews Logo', 'kandanews'); ?>" loading="lazy">
                <?php else : ?>
                    <div id="brandLogo" class="logoReveal logo-text">KandaNews</div>
                <?php endif; ?>
            </div>

            <div id="notif" class="pill" hidden></div>

            <div class="tiles" role="navigation" aria-label="Primary">
                <button class="btn primary" id="read-latest" type="button">Read latest</button>
                <a class="btn" href="#plans">Subscribe / Renew</a>
                <a class="btn" href="#history">Past editions</a>
            </div>
        </section>

        <div class="dashboard-content-grid">
            <section class="dashboard-section" aria-labelledby="latestTitle">
                <h2 id="latestTitle" class="h">Latest edition</h2>
                <p id="latestMeta" class="muted">Loading…</p>
                <div><button id="openLatest" class="btn primary" type="button" disabled aria-disabled="true">Open</button></div>
                <div id="latestError" class="pill err" hidden></div>
                <div id="latestSkeleton" class="skeleton-line"></div>
            </section>

            <section class="dashboard-section" aria-labelledby="subTitle">
                <h2 id="subTitle" class="h">Subscription</h2>
                <p class="muted">Choose a plan and select your preferred payment method.</p>
                <div class="list" id="plans" role="list">
                    <button class="row" role="listitem" type="button" data-plan="daily" data-amount="500" data-currency="UGX" aria-label="Daily subscription — 500 Uganda shillings">
                        <span>Daily — UGX 500</span><span>Choose</span>
                    </button>
                    <button class="row" role="listitem" type="button" data-plan="weekly" data-amount="2500" data-currency="UGX" aria-label="Weekly subscription — 2,500 Uganda shillings">
                        <span>Weekly — UGX 2,500</span><span>Choose</span>
                    </button>
                    <button class="row" role="listitem" type="button" data-plan="monthly" data-amount="7500" data-currency="UGX" aria-label="Monthly subscription — 7,500 Uganda shillings">
                        <span>Monthly — UGX 7,500</span><span>Choose</span>
                    </button>
                </div>
                <div id="planMsg" class="muted" aria-live="polite"></div>
            </section>
        </div>

        <div class="dashboard-profile-grid">
            <section class="dashboard-section" id="history" aria-labelledby="historyTitle">
                <div class="archive-header">
                    <h2 id="historyTitle" class="h">Past editions</h2>
                    <div class="filter-controls">
                        <select id="monthFilter"><option value="">All months</option></select>
                        <input id="searchInput" type="search" placeholder="Search title or ID…" aria-label="Search past editions">
                        <button class="btn ghost" id="applyFilters" type="button">Filter</button>
                    </div>
                </div>
                <div class="list" id="archiveList" aria-live="polite" aria-busy="true">
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line"></div>
                    <div class="skeleton-line"></div>
                </div>
                <div id="archiveEmpty" class="muted" hidden>No results.</div>
                <div id="archiveError" class="pill err" hidden></div>
            </section>

            <section class="dashboard-section" aria-labelledby="updatesTitle">
                <h2 id="updatesTitle" class="h">Updates</h2>
                <div class="updates-slider-container" id="updates-slider-container">
                    <div class="updates-slider-track" id="updates-slider-track">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/update-slide-1.png'); ?>" alt="<?php echo esc_attr__('Tap to Know. Tap to Grow.', 'kandanews'); ?>" class="slider-image">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/update-slide-2.png'); ?>" alt="<?php echo esc_attr__('Affordable Subscription Packages', 'kandanews'); ?>" class="slider-image">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/update-slide-3.png'); ?>" alt="<?php echo esc_attr__('Your Brand. Right in Their Pocket.', 'kandanews'); ?>" class="slider-image">
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Payment Gateway Chooser Modal -->
<div id="paymentChooser" class="payment-chooser-overlay" hidden aria-hidden="true" style="display: none;">
    <div class="payment-chooser-card" role="dialog" aria-labelledby="paymentChooserTitle" aria-modal="true">
        <h2 id="paymentChooserTitle" class="h">Choose Payment Method</h2>
        <p class="payment-details">
            <strong>Plan:</strong> <span id="chooserPlanLabel">—</span><br>
            <strong>Amount:</strong> <span id="chooserAmountLabel">—</span>
        </p>
        
        <div class="payment-buttons">
            <button id="btnPayDPO" class="btn payment-btn" type="button">
                <span class="payment-icon">💳</span>
                <div>
                    <strong>Pay with DPO</strong>
                    <small>Mobile Money • Cards • Bank Transfer</small>
                </div>
            </button>
            
            <button id="btnPayFlutterwave" class="btn payment-btn" type="button">
                <span class="payment-icon">💰</span>
                <div>
                    <strong>Pay with Flutterwave</strong>
                    <small>Mobile Money • Cards</small>
                </div>
            </button>
        </div>
        
        <button id="btnCancelChooser" class="btn ghost" type="button">Cancel</button>
    </div>
</div>

<!-- Profile Card -->
<div id="profileInfoCard" class="profile-card-overlay" hidden aria-hidden="true">
    <div class="profile-card-content" role="dialog" aria-labelledby="profileCardTitle">
        <button class="modal-close" id="closeProfileCard" aria-label="<?php echo esc_attr__('Close profile dialog', 'kandanews'); ?>">×</button>
        <h2 id="profileCardTitle" class="h">Your Profile</h2>
        <div class="kv">
            <div class="k">Name</div><div id="profileName">—</div>
            <div class="k">Email</div><div id="profileEmail">—</div>
            <div class="k">WhatsApp</div><div id="profileWhatsapp">—</div>
            <div class="k">Category</div><div id="profileCategory">—</div>
            <div class="k">University / Company</div><div id="profileOrg">—</div>
        </div>
    </div>
</div>

<style>
/* Payment Chooser Styles */
.payment-chooser-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: none; /* Hidden by default */
    align-items: center;
    justify-content: center;
    z-index: 9999;
    backdrop-filter: blur(4px);
}

.payment-chooser-overlay[hidden] {
    display: none !important;
}

.payment-chooser-overlay:not([hidden]) {
    display: flex !important;
}

.payment-chooser-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.payment-chooser-card .h {
    margin: 0 0 16px 0;
    font-size: 24px;
    color: var(--ink, #0f172a);
}

.payment-details {
    background: var(--light, #f9f9f9);
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    font-size: 14px;
    line-height: 1.6;
}

.payment-details strong {
    color: var(--ink, #0f172a);
}

.payment-buttons {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 16px;
}

.payment-btn {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: white;
    border: 2px solid var(--border, #e5e7eb);
    border-radius: 8px;
    text-align: left;
    transition: all 0.2s;
    cursor: pointer;
}

.payment-btn:hover {
    border-color: var(--accent, #f05a1a);
    background: var(--light, #f9f9f9);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.payment-icon {
    font-size: 32px;
    line-height: 1;
}

.payment-btn strong {
    display: block;
    color: var(--ink, #0f172a);
    font-size: 16px;
    margin-bottom: 4px;
}

.payment-btn small {
    display: block;
    color: #64748b;
    font-size: 13px;
}

#btnCancelChooser {
    width: 100%;
    margin-top: 8px;
}
</style>

<?php
get_footer();
?>