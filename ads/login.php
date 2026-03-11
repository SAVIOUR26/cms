<?php
require_once __DIR__ . '/shared/config.php';
require_once __DIR__ . '/shared/helpers.php';
require_once __DIR__ . '/shared/auth.php';

ads_session_start();

if (current_advertiser()) {
    redirect('/dashboard.php');
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$next       = preg_replace('/[^a-zA-Z0-9\/?=&_\-]/', '', $_GET['next'] ?? '/dashboard.php');
$page_title = 'Login — Advertiser Portal';
$active_nav = 'login';
$__adv      = null;

require_once __DIR__ . '/shared/header.php';
?>

<section class="kn-auth-page">
    <div style="width: 100%; max-width: 460px; margin: 0 auto;">

        <?php if ($msg = flash('success')): ?>
            <div class="kn-alert kn-alert-success mb-24">
                <i class="fa-solid fa-circle-check kn-alert-icon"></i> <?= h($msg) ?>
            </div>
        <?php endif; ?>
        <?php if ($msg = flash('error')): ?>
            <div class="kn-alert kn-alert-error mb-24">
                <i class="fa-solid fa-circle-exclamation kn-alert-icon"></i> <?= h($msg) ?>
            </div>
        <?php endif; ?>

        <div class="kn-form-card">
            <div class="kn-form-card-header">
                <div class="kn-hero-badge" style="margin-bottom: 12px;">
                    <i class="fa-solid fa-right-to-bracket"></i> Advertiser Login
                </div>
                <h1 class="kn-form-card-title">Welcome Back</h1>
                <p class="kn-form-card-subtitle">Login to manage your ad campaigns.</p>
            </div>
            <div class="kn-form-card-body">
                <div id="login-alert"></div>

                <form id="login-form" novalidate>
                    <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">

                    <div class="kn-form-group">
                        <label class="kn-label" for="email">
                            Email Address <span class="req">*</span>
                        </label>
                        <input type="email" id="email" name="email"
                               class="kn-input" placeholder="you@company.com"
                               required autocomplete="email">
                    </div>

                    <div class="kn-form-group">
                        <label class="kn-label" for="password">
                            Password <span class="req">*</span>
                        </label>
                        <input type="password" id="password" name="password"
                               class="kn-input" placeholder="Your password"
                               required autocomplete="current-password">
                    </div>

                    <button type="submit" id="login-btn" class="kn-btn kn-btn-primary kn-btn-full kn-btn-lg" style="margin-top: 8px;">
                        <i class="fa-solid fa-right-to-bracket"></i> Login to Portal
                    </button>
                </form>

                <p style="text-align: center; margin-top: 20px; font-size: 0.875rem; color: var(--kn-muted);">
                    Don't have an account?
                    <a href="/register.php" style="color: var(--kn-orange); font-weight: 600;">Register here</a>
                </p>

                <hr class="kn-divider">

                <p style="text-align: center; font-size: 0.8rem; color: var(--kn-muted);">
                    Need help?
                    <a href="https://wa.me/256772253804" target="_blank" style="color: var(--kn-orange);">
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp support
                    </a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/shared/footer.php'; ?>
