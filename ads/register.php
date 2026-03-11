<?php
require_once __DIR__ . '/shared/config.php';
require_once __DIR__ . '/shared/helpers.php';
require_once __DIR__ . '/shared/auth.php';

ads_session_start();

// Already logged in? Redirect to dashboard
if (current_advertiser()) {
    redirect('/dashboard.php');
}

// Generate CSRF token
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$page_title = 'Register — Start Advertising';
$active_nav = 'register';
$__adv      = null;

$countries = ['Uganda','Kenya','Tanzania','Rwanda','Nigeria','Ghana','South Africa','Ethiopia','Other'];

require_once __DIR__ . '/shared/header.php';
?>

<section class="kn-auth-page">
    <div style="width: 100%; max-width: 560px; margin: 0 auto;">

        <!-- Flash messages -->
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
                    <i class="fa-solid fa-user-plus"></i> New Advertiser
                </div>
                <h1 class="kn-form-card-title">Create Your Account</h1>
                <p class="kn-form-card-subtitle">Start advertising on KandaNews Uganda Edition today.</p>
            </div>
            <div class="kn-form-card-body">
                <div id="register-alert"></div>

                <form id="register-form" novalidate>
                    <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">

                    <div class="kn-form-group">
                        <label class="kn-label" for="company_name">
                            Business / Company Name <span class="req">*</span>
                        </label>
                        <input type="text" id="company_name" name="company_name"
                               class="kn-input" placeholder="e.g. Kampala Foods Ltd"
                               required autocomplete="organization">
                    </div>

                    <div class="kn-form-group">
                        <label class="kn-label" for="contact_name">
                            Your Full Name <span class="req">*</span>
                        </label>
                        <input type="text" id="contact_name" name="contact_name"
                               class="kn-input" placeholder="e.g. John Mugisha"
                               required autocomplete="name">
                    </div>

                    <div class="kn-form-row">
                        <div class="kn-form-group">
                            <label class="kn-label" for="email">
                                Email Address <span class="req">*</span>
                            </label>
                            <input type="email" id="email" name="email"
                                   class="kn-input" placeholder="you@company.com"
                                   required autocomplete="email">
                        </div>
                        <div class="kn-form-group">
                            <label class="kn-label" for="phone">
                                WhatsApp Phone <span class="req">*</span>
                            </label>
                            <input type="tel" id="phone" name="phone"
                                   class="kn-input" placeholder="+256 7XX XXX XXX"
                                   required autocomplete="tel">
                        </div>
                    </div>

                    <div class="kn-form-group">
                        <label class="kn-label" for="country">Country</label>
                        <select id="country" name="country" class="kn-select">
                            <?php foreach ($countries as $c): ?>
                                <option value="<?= h($c) ?>" <?= $c === 'Uganda' ? 'selected' : '' ?>><?= h($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="kn-form-row">
                        <div class="kn-form-group">
                            <label class="kn-label" for="password">
                                Password <span class="req">*</span>
                            </label>
                            <input type="password" id="password" name="password"
                                   class="kn-input" placeholder="Min. 8 characters"
                                   required autocomplete="new-password" minlength="8">
                        </div>
                        <div class="kn-form-group">
                            <label class="kn-label" for="password_confirm">
                                Confirm Password <span class="req">*</span>
                            </label>
                            <input type="password" id="password_confirm" name="password_confirm"
                                   class="kn-input" placeholder="Repeat password"
                                   required autocomplete="new-password">
                        </div>
                    </div>

                    <p style="font-size: 0.8125rem; color: var(--kn-muted); margin-bottom: 20px; line-height: 1.5;">
                        By registering you agree to our
                        <a href="https://kandanews.africa/terms" target="_blank" style="color: var(--kn-orange);">Terms of Service</a>
                        and
                        <a href="https://kandanews.africa/ads-policy" target="_blank" style="color: var(--kn-orange);">Advertising Policy</a>.
                    </p>

                    <button type="submit" id="register-btn" class="kn-btn kn-btn-primary kn-btn-full kn-btn-lg">
                        <i class="fa-solid fa-user-plus"></i> Create Account
                    </button>
                </form>

                <p style="text-align: center; margin-top: 20px; font-size: 0.875rem; color: var(--kn-muted);">
                    Already have an account?
                    <a href="/login.php" style="color: var(--kn-orange); font-weight: 600;">Login here</a>
                </p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/shared/footer.php'; ?>
