<?php
/**
 * KandaNews — User Dashboard
 * Pure PHP, no WordPress.
 */
require_once __DIR__ . '/../shared/includes/helpers.php';
require_once __DIR__ . '/../shared/includes/env.php';
loadEnv(__DIR__ . '/../../.env');
require_once __DIR__ . '/../shared/includes/country-config.php';
require_once __DIR__ . '/../shared/includes/auth-guard.php';

// Require authentication
requireAuth('/login.php');

$user = getCurrentUser();
$user_name = h($user['name'] ?? 'User');

// Country detection from subdomain
$host = $_SERVER['HTTP_HOST'] ?? '';
$cc = '';
if (preg_match('/^([a-z]{2})\./', $host, $m)) {
    $cc = strtoupper($m[1]);
} elseif (!empty($user['country'])) {
    $cc = strtoupper(substr($user['country'], 0, 2));
}
$cc_label = $cc ?: 'Global';
$flag = $cc ? countryFlag($cc) : '🌍';
$plans = $COUNTRY['plans'];
$currency = $COUNTRY['currency'];
$csrf = generateCSRF();

// API base for JS
$api_base = '/api';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — KandaNews <?php echo h($COUNTRY['name']); ?></title>
    <link rel="icon" type="image/png" href="/shared/assets/img/kanda-square.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="/shared/assets/css/dashboard.css">
</head>
<body>

<main class="wrap" role="main">
    <!-- Welcome Section -->
    <section class="card" aria-labelledby="welcomeTitle">
        <div class="hero">
            <h1 id="welcomeTitle" class="h" style="margin-bottom: 0;">Welcome, <?php echo $user_name; ?> 👋</h1>
            <div class="header-pills">
                <button id="profileButton" class="btn ghost" type="button" aria-controls="profileInfoCard" aria-expanded="false">Your Profile 👤</button>
                <span class="pill"><?php echo $flag . ' ' . h($cc_label); ?></span>
                <span id="subStatusPill" class="pill">Loading…</span>
                <a class="btn ghost" href="/logout.php" title="Logout">Logout</a>
            </div>
        </div>

        <!-- Ticker -->
        <div class="ticker" aria-label="KandaNews audience">
            <div class="track">
                <strong>KandaNews Africa</strong> — Designed for <em>Professionals</em> <span class="dot">•</span> <em>University Students</em> <span class="dot">•</span> <em>Entrepreneurs</em>
                <strong>KandaNews Africa</strong> — Designed for <em>Professionals</em> <span class="dot">•</span> <em>University Students</em> <span class="dot">•</span> <em>Entrepreneurs</em>
            </div>
        </div>

        <!-- Typewriter + Logo -->
        <div class="hero">
            <div class="type" id="typeTarget" aria-live="polite"></div>
            <img id="brandLogo" class="logo logoReveal" src="/shared/assets/img/kandanews-logo.gif" alt="KandaNews Logo" loading="lazy">
        </div>

        <div id="notif" class="pill" hidden></div>

        <!-- Action tiles -->
        <div class="tiles" role="navigation" aria-label="Primary actions">
            <button class="btn primary" id="read-latest" type="button">Read latest</button>
            <a class="btn" href="#plans">Subscribe / Renew</a>
            <a class="btn" href="#history">Past editions</a>
            <a class="btn" href="#advertise">Advertise</a>
        </div>
    </section>

    <!-- Latest + Subscription -->
    <section class="grid" style="margin-top: 16px;">
        <aside class="card" aria-labelledby="latestTitle">
            <h2 id="latestTitle" class="h">Latest edition</h2>
            <p id="latestMeta" class="muted" style="margin-top: 0;">Loading…</p>
            <div><button id="openLatest" class="btn primary" type="button" disabled>Open</button></div>
            <div id="latestError" class="pill err" hidden></div>
            <div id="latestSkeleton" class="skeleton-line"></div>
        </aside>

        <aside class="card" aria-labelledby="subTitle">
            <h2 id="subTitle" class="h">Subscription</h2>
            <p class="muted" style="margin-top: 0;">Choose a plan and select your preferred payment method.</p>
            <div class="list" id="plans" role="list">
                <?php foreach ($plans as $key => $plan): ?>
                <button class="row" role="listitem" type="button"
                        data-plan="<?php echo h($key); ?>"
                        data-amount="<?php echo (int)$plan['amount']; ?>"
                        data-currency="<?php echo h($currency); ?>"
                        aria-label="<?php echo ucfirst($key); ?> subscription — <?php echo h($plan['label']); ?>">
                    <span><?php echo ucfirst($key); ?> — <?php echo h($plan['label']); ?></span>
                    <span>Choose</span>
                </button>
                <?php endforeach; ?>
            </div>
            <div id="planMsg" class="muted" style="margin-top: 8px;" aria-live="polite"></div>
        </aside>
    </section>

    <!-- Profile + Advertise -->
    <section class="grid" style="margin-top: 16px;">
        <aside class="card" id="history" aria-labelledby="historyTitle">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; gap: 8px; flex-wrap: wrap;">
                <h2 id="historyTitle" class="h">Past editions</h2>
                <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                    <select id="monthFilter"><option value="">All months</option></select>
                    <input id="searchInput" type="search" placeholder="Search title or ID…" aria-label="Search past editions" style="padding: 10px 12px; border: 1px solid var(--border); border-radius: 10px;">
                    <button class="btn ghost" id="applyFilters" type="button">Filter</button>
                </div>
            </div>
            <div class="list" id="archiveList" aria-live="polite" aria-busy="true" style="margin-top: 8px;">
                <div class="skeleton-line"></div>
                <div class="skeleton-line"></div>
                <div class="skeleton-line"></div>
            </div>
            <div id="archiveEmpty" class="muted" hidden>No results.</div>
            <div id="archiveError" class="pill err" hidden></div>
        </aside>

        <aside class="card" id="advertise" aria-labelledby="adTitle">
            <h2 id="adTitle" class="h">Advertise with Us</h2>
            <div class="ad muted">Your brand here — reach students, professionals &amp; entrepreneurs.</div>
            <div class="ad-actions">
                <a class="btn ghost" href="mailto:<?php echo h($COUNTRY['email']); ?>">Advertise with Kanda</a>
                <a class="btn ghost" href="mailto:<?php echo h($COUNTRY['email']); ?>">Rate Card</a>
            </div>
        </aside>
    </section>
</main>

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
        <button id="btnCancelChooser" class="btn ghost" type="button" style="width: 100%; margin-top: 8px;">Cancel</button>
    </div>
</div>

<!-- Profile Card Modal -->
<div id="profileInfoCard" class="profile-card-overlay" hidden aria-hidden="true">
    <div class="profile-card-content" role="dialog" aria-labelledby="profileCardTitle">
        <button class="modal-close" id="closeProfileCard" aria-label="Close profile">&times;</button>
        <h2 id="profileCardTitle" class="h">Your Profile</h2>
        <div class="kv">
            <div class="k">Name</div><div id="profileName"><?php echo $user_name; ?></div>
            <div class="k">Email</div><div id="profileEmail"><?php echo h($user['email'] ?? '—'); ?></div>
            <div class="k">WhatsApp</div><div id="profileWhatsapp"><?php echo h($user['whatsapp'] ?? '—'); ?></div>
            <div class="k">Category</div><div id="profileCategory"><?php echo h(ucfirst($user['category'] ?? '—')); ?></div>
            <div class="k">Organization</div><div id="profileOrg"><?php echo h($user['org'] ?? '—'); ?></div>
        </div>
    </div>
</div>

<footer class="site-footer">
    <div>&copy; <?php echo date('Y'); ?> KandaNews Africa</div>
    <div><a href="/privacy.php">Privacy</a> · <a href="/terms.php">Terms</a></div>
</footer>

<!-- Dashboard Configuration -->
<script>
window.KandaDashboard = {
    restBase: '<?php echo $api_base; ?>',
    restBaseFLW: '<?php echo $api_base; ?>/flutterwave',
    restBaseDPO: '<?php echo $api_base; ?>/dpo',
    nonce: '<?php echo $csrf; ?>',
    user_profile: <?php echo json_encode([
        'name'     => $user['name'] ?? '',
        'email'    => $user['email'] ?? '',
        'whatsapp' => $user['whatsapp'] ?? '',
        'category' => $user['category'] ?? '',
        'org'      => $user['org'] ?? '',
    ], JSON_HEX_TAG | JSON_HEX_AMP); ?>
};
</script>
<script src="/shared/assets/js/kanda-dashboard.js"></script>
</body>
</html>
