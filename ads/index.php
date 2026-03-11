<?php
require_once __DIR__ . '/shared/config.php';
require_once __DIR__ . '/shared/helpers.php';
require_once __DIR__ . '/shared/auth.php';

ads_session_start();
$__adv      = current_advertiser();
$page_title = 'Advertise with KandaNews';
$active_nav = 'home';

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

<!-- ===== HERO ===== -->
<section class="kn-hero">
    <div class="container">
        <div class="kn-hero-inner">
            <div class="kn-hero-badge">
                <i class="fa-solid fa-bolt"></i>
                Uganda Edition — Now Open for Advertisers
            </div>
            <h1 class="kn-hero-title">
                Your Brand.<br>
                <span class="highlight">Right in Their Pocket.</span>
            </h1>
            <p class="kn-hero-subtitle">
                Reach 10,000+ verified KandaNews subscribers daily. Get your ads in front of Uganda's most engaged digital news audience — audio, video, banners, and more.
            </p>
            <div class="kn-hero-cta">
                <a href="/register.php" class="kn-btn kn-btn-primary kn-btn-xl">
                    <i class="fa-solid fa-rocket"></i> Start Advertising
                </a>
                <a href="/login.php" class="kn-btn kn-btn-outline kn-btn-lg">
                    <i class="fa-solid fa-right-to-bracket"></i> Login to Portal
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ===== STATS BAR ===== -->
<section class="kn-stats-bar">
    <div class="container">
        <div class="kn-stats-grid">
            <div class="kn-stat-item">
                <span class="kn-stat-number">10,000+</span>
                <span class="kn-stat-label">Subscribers</span>
            </div>
            <div class="kn-stat-item">
                <span class="kn-stat-number">365</span>
                <span class="kn-stat-label">Daily Editions</span>
            </div>
            <div class="kn-stat-item">
                <span class="kn-stat-number">50+</span>
                <span class="kn-stat-label">Content Categories</span>
            </div>
            <div class="kn-stat-item">
                <span class="kn-stat-number">4</span>
                <span class="kn-stat-label">Countries</span>
            </div>
        </div>
    </div>
</section>

<!-- ===== WHY ADVERTISE ===== -->
<section class="section" style="background: var(--kn-gray-50);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Why Advertise with KandaNews?</h2>
            <p class="section-subtitle">We connect your brand directly with a loyal, engaged African news audience.</p>
        </div>
        <div class="kn-feature-grid">
            <div class="kn-feature-card" data-animate>
                <div class="kn-feature-icon"><i class="fa-solid fa-bullseye"></i></div>
                <h3 class="kn-feature-title">Direct to Verified Subscribers</h3>
                <p class="kn-feature-desc">Your ads are delivered straight into the KandaNews app for verified, active subscribers — not just website visitors.</p>
            </div>
            <div class="kn-feature-card" data-animate>
                <div class="kn-feature-icon"><i class="fa-solid fa-link"></i></div>
                <h3 class="kn-feature-title">Clickable Links to Your Website</h3>
                <p class="kn-feature-desc">Every ad format supports direct links — drive traffic to your website, landing page, or WhatsApp with a single tap.</p>
            </div>
            <div class="kn-feature-card" data-animate>
                <div class="kn-feature-icon"><i class="fa-solid fa-chart-line"></i></div>
                <h3 class="kn-feature-title">Built-In Engagement Metrics</h3>
                <p class="kn-feature-desc">Track impressions, clicks, and engagement through your advertiser dashboard in real time.</p>
            </div>
            <div class="kn-feature-card" data-animate>
                <div class="kn-feature-icon"><i class="fa-solid fa-cart-shopping"></i></div>
                <h3 class="kn-feature-title">Smart Cart Integration</h3>
                <p class="kn-feature-desc">Cart Ad format lets readers add your products directly from the news edition — turn readers into buyers instantly.</p>
            </div>
            <div class="kn-feature-card" data-animate>
                <div class="kn-feature-icon"><i class="fa-solid fa-headset"></i></div>
                <h3 class="kn-feature-title">Dedicated Ad Manager</h3>
                <p class="kn-feature-desc">Our team reviews every campaign and provides hands-on support to ensure your ads go live on time, every time.</p>
            </div>
            <div class="kn-feature-card" data-animate>
                <div class="kn-feature-icon"><i class="fa-solid fa-palette"></i></div>
                <h3 class="kn-feature-title">Free Design Support</h3>
                <p class="kn-feature-desc">Don't have a creative? We'll help. Basic ad design assistance is included free for all advertisers on the platform.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== AD FORMATS TABLE ===== -->
<section class="section" id="formats">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Uganda Edition — Rate Card</h2>
            <p class="section-subtitle">10 ad formats designed for the KandaNews mobile reader experience. All prices in UGX per day.</p>
        </div>
        <div class="kn-table-wrap">
            <table class="kn-table">
                <thead>
                    <tr>
                        <th>Ad Format</th>
                        <th>Description</th>
                        <th>Price / Day</th>
                        <th>7-Day (10% off)</th>
                        <th>30-Day (20% off)</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($formats as $key => $fmt): ?>
                    <tr>
                        <td data-label="Format">
                            <div class="format-cell">
                                <span class="format-icon">
                                    <i class="fa-solid <?= h($format_icons[$key] ?? 'fa-ad') ?>"></i>
                                </span>
                                <strong><?= h($fmt['label']) ?></strong>
                            </div>
                        </td>
                        <td data-label="Description"><?= h($fmt['desc']) ?></td>
                        <td data-label="Price/Day"><strong><?= format_ugx($fmt['price']) ?></strong></td>
                        <td data-label="7-Day Rate"><?= format_ugx((int)round($fmt['price'] * 7 * 0.9)) ?></td>
                        <td data-label="30-Day Rate"><?= format_ugx((int)round($fmt['price'] * 30 * 0.8)) ?></td>
                        <td data-label="">
                            <a href="/register.php" class="kn-btn kn-btn-primary kn-btn-sm">
                                Book <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- ===== DISCOUNT PACKAGES ===== -->
<section class="section" style="background: var(--kn-gray-50);">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Discount Packages</h2>
            <p class="section-subtitle">Pre-book and prepay for weekly or monthly campaigns and save big.</p>
        </div>
        <div class="kn-discount-grid">
            <div class="kn-discount-card">
                <div class="kn-discount-badge">10% OFF</div>
                <h3 class="kn-discount-title">Weekly Package</h3>
                <p class="kn-discount-desc">Book for 7 or more consecutive days and enjoy a 10% discount on your total campaign cost. Perfect for product launches and events.</p>
                <div style="margin-top: 20px;">
                    <a href="/register.php" class="kn-btn kn-btn-outline-navy">Book Weekly</a>
                </div>
            </div>
            <div class="kn-discount-card">
                <div class="kn-discount-badge">20% OFF</div>
                <h3 class="kn-discount-title">Monthly Package</h3>
                <p class="kn-discount-desc">Book for 30 or more consecutive days and unlock a 20% discount. Maximum brand visibility for growing businesses.</p>
                <div style="margin-top: 20px;">
                    <a href="/register.php" class="kn-btn kn-btn-primary">Book Monthly</a>
                </div>
            </div>
        </div>
        <p class="text-center text-muted" style="margin-top: 24px; font-size: 0.875rem;">
            <i class="fa-solid fa-circle-info" style="color: var(--kn-orange);"></i>
            Discounts apply automatically when booking 7+ or 30+ consecutive days. All campaigns are pre-booked and prepaid.
        </p>
    </div>
</section>

<!-- ===== HOW IT WORKS ===== -->
<section class="section" id="how-it-works">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">How It Works</h2>
            <p class="section-subtitle">Go from sign-up to live campaign in just 4 simple steps.</p>
        </div>
        <div class="kn-steps">
            <div class="kn-step">
                <div class="kn-step-num">
                    <i class="fa-solid fa-user-plus kn-step-icon"></i>
                </div>
                <h3 class="kn-step-label">Register Business</h3>
                <p class="kn-step-desc">Create your free advertiser account with your business details in under 2 minutes.</p>
            </div>
            <div class="kn-step">
                <div class="kn-step-num">
                    <i class="fa-solid fa-calendar-check kn-step-icon"></i>
                </div>
                <h3 class="kn-step-label">Choose Format & Dates</h3>
                <p class="kn-step-desc">Pick from 10 ad formats and select your campaign start date and duration.</p>
            </div>
            <div class="kn-step">
                <div class="kn-step-num">
                    <i class="fa-solid fa-upload kn-step-icon"></i>
                </div>
                <h3 class="kn-step-label">Upload Creative</h3>
                <p class="kn-step-desc">Pay securely via Flutterwave, then upload your ad creative. Our team reviews within 24 hours.</p>
            </div>
            <div class="kn-step">
                <div class="kn-step-num">
                    <i class="fa-solid fa-rocket kn-step-icon"></i>
                </div>
                <h3 class="kn-step-label">Go Live!</h3>
                <p class="kn-step-desc">Your ad goes live on your chosen start date, reaching thousands of subscribers every day.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== CTA SECTION ===== -->
<section class="kn-cta-section">
    <div class="container">
        <div style="position: relative; z-index: 1;">
            <h2 class="kn-cta-title">
                Ready to Reach Uganda?
            </h2>
            <p class="kn-cta-desc">
                Join hundreds of brands advertising with KandaNews. Start your campaign today with as little as UGX 10,000 per day.
            </p>
            <a href="/register.php" class="kn-btn kn-btn-primary kn-btn-xl">
                <i class="fa-solid fa-rocket"></i> Register Now — It's Free
            </a>
            <div style="margin-top: 20px; font-size: 0.875rem; color: rgba(255,255,255,0.6);">
                Questions? 
                <a href="https://wa.me/256772253804" style="color: var(--kn-orange);" target="_blank" rel="noopener">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp us at +256 772 253804
                </a>
                &nbsp;|&nbsp;
                <a href="mailto:adverts@ug.kandanews.africa" style="color: var(--kn-orange);">
                    <i class="fa-solid fa-envelope"></i> adverts@ug.kandanews.africa
                </a>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/shared/footer.php'; ?>
