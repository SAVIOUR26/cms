<?php
/**
 * KandaNews — Country "Launching Soon" Page
 * Used for KE, ZA, NG subdomains until those countries launch.
 * Auto-detects country from subdomain via country-config.php
 */
require_once __DIR__ . '/shared/includes/helpers.php';
require_once __DIR__ . '/shared/includes/country-config.php';
require_once __DIR__ . '/shared/components/header.php';

$plans = $COUNTRY['plans'];
$currency = $COUNTRY['currency'];
?>

<!-- HERO: Coming Soon -->
<section class="kn-hero" aria-label="KandaNews <?php echo h($COUNTRY['name']); ?> Coming Soon">
  <div class="kn-hero__media" aria-hidden="true">
    <video class="kn-hero__video" playsinline muted autoplay loop preload="metadata">
      <source src="/shared/assets/video/hub-hero.mp4" type="video/mp4">
    </video>
  </div>

  <div class="kn-hero__overlay">
    <div class="kn-glass container" role="region" aria-labelledby="kn-coming-title">
      <span class="kn-glass__badge">Launching Soon</span>

      <h1 class="kn-glass__title" id="kn-coming-title">
        <span class="kn-glass__brand">KandaNews</span>
        <span class="kn-glass__country"><?php echo h($COUNTRY['name']); ?></span>
        <span class="kn-glass__flag" aria-hidden="true"><?php echo $COUNTRY['flag']; ?></span>
      </h1>

      <p class="kn-glass__tagline">
        Africa's digital flipping newspaper is <strong>coming to <?php echo h($COUNTRY['name']); ?></strong>
      </p>

      <p class="kn-glass__sub">
        Personal, local and pan-Africa news coverage for students, professionals and entrepreneurs — delivered mobile-first.
      </p>

      <div class="kn-glass__ctas" role="navigation" aria-label="Hero actions">
        <a class="kn-btn kn-btn--primary" href="#notify">Get Notified</a>
        <a class="kn-btn kn-btn--ghost" href="https://ug.kandanews.africa">Try Uganda Edition</a>
      </div>
    </div>
  </div>
</section>

<!-- WHAT TO EXPECT -->
<section class="kn-section kn-section--alt kn-reveal" aria-labelledby="expect-title">
  <div class="container">
    <h2 id="expect-title" class="kn-section__title text-center">What to Expect</h2>
    <p class="kn-section__lead text-center">KandaNews <?php echo h($COUNTRY['name']); ?> will bring you everything you need — local, pan-Africa, and built for your growth.</p>

    <div class="kn-features-grid">
      <div class="kn-feature kn-reveal">
        <span class="kn-feature__icon"><i class="fa-solid fa-book-open"></i></span>
        <span class="kn-feature__title">Interactive Flipbook Editions</span>
        <p class="kn-feature__desc">Daily digital newspapers you swipe through — fast, visual, and packed with local stories.</p>
      </div>
      <div class="kn-feature kn-reveal">
        <span class="kn-feature__icon"><i class="fa-solid fa-headphones"></i></span>
        <span class="kn-feature__title">Audio & Video Content</span>
        <p class="kn-feature__desc">Listen to interviews, watch explainers, and get briefed in minutes.</p>
      </div>
      <div class="kn-feature kn-reveal">
        <span class="kn-feature__icon"><i class="fa-solid fa-mobile-screen"></i></span>
        <span class="kn-feature__title">Apps on Every Platform</span>
        <p class="kn-feature__desc">Native apps for Android, iOS, Windows, Mac and Linux — news on any device.</p>
      </div>
      <div class="kn-feature kn-reveal">
        <span class="kn-feature__icon"><i class="fa-solid fa-money-bill-wave"></i></span>
        <span class="kn-feature__title">Local Pricing in <?php echo h($currency); ?></span>
        <p class="kn-feature__desc">Affordable plans starting from <?php echo h($plans['daily']['label']); ?>. Pay with mobile money or card.</p>
      </div>
      <div class="kn-feature kn-reveal">
        <span class="kn-feature__icon"><i class="fa-solid fa-bullhorn"></i></span>
        <span class="kn-feature__title">Smart Advertising</span>
        <p class="kn-feature__desc">Brands reach real audiences through native placements, full-page ads, and sponsorships.</p>
      </div>
      <div class="kn-feature kn-reveal">
        <span class="kn-feature__icon"><i class="fa-solid fa-earth-africa"></i></span>
        <span class="kn-feature__title">Pan-Africa Perspective</span>
        <p class="kn-feature__desc">Local stories plus continent-wide coverage — Africa's future, from your country.</p>
      </div>
    </div>
  </div>
</section>

<!-- PREVIEW PRICING -->
<section class="kn-section kn-reveal" aria-labelledby="pricing-preview-title">
  <div class="container">
    <h2 id="pricing-preview-title" class="kn-section__title text-center">Planned Pricing for <?php echo h($COUNTRY['name']); ?></h2>
    <p class="kn-section__lead text-center">Flexible access options when we launch — pick the plan that fits your rhythm.</p>

    <div class="kn-pricing-grid">
      <article class="kn-card kn-reveal" aria-label="Daily plan">
        <h3>Daily</h3>
        <p class="kn-price"><strong><?php echo h($plans['daily']['label']); ?></strong></p>
        <span class="kn-btn kn-btn--outline kn-btn--disabled">Coming Soon</span>
      </article>

      <article class="kn-card kn-card--popular kn-reveal" aria-label="Weekly plan">
        <span class="kn-badge-popular">Best Value</span>
        <h3>Weekly</h3>
        <p class="kn-price"><strong><?php echo h($plans['weekly']['label']); ?></strong></p>
        <span class="kn-btn kn-btn--outline kn-btn--disabled">Coming Soon</span>
      </article>

      <article class="kn-card kn-reveal" aria-label="Monthly plan">
        <h3>Monthly</h3>
        <p class="kn-price"><strong><?php echo h($plans['monthly']['label']); ?></strong></p>
        <span class="kn-btn kn-btn--outline kn-btn--disabled">Coming Soon</span>
      </article>
    </div>
  </div>
</section>

<!-- GET NOTIFIED -->
<section id="notify" class="kn-section kn-section--dark kn-reveal" aria-labelledby="notify-title">
  <div class="container text-center">
    <h2 id="notify-title" class="kn-section__title">Be the First to Know</h2>
    <p class="kn-section__lead">
      Drop your email and we'll notify you as soon as KandaNews <?php echo h($COUNTRY['name']); ?> launches.
    </p>
    <form class="kn-coming__form" action="mailto:<?php echo h($COUNTRY['email']); ?>" method="GET" enctype="text/plain">
      <input type="email" name="subject" class="kn-coming__input" placeholder="Your email address" required aria-label="Email address">
      <button type="submit" class="kn-btn kn-btn--primary">
        <i class="fa-solid fa-bell"></i> Notify Me
      </button>
    </form>
    <p style="color:rgba(255,255,255,0.5);font-size:0.85rem;margin-top:1rem;">No spam. Just a launch notification.</p>
  </div>
</section>

<!-- TRY UGANDA -->
<section class="kn-section kn-reveal" aria-labelledby="try-title">
  <div class="container">
    <div class="kn-advertise">
      <h2 id="try-title">Can't Wait? Try KandaNews Uganda</h2>
      <p>KandaNews Uganda is already live — experience the future of news today while we prepare the <?php echo h($COUNTRY['name']); ?> edition.</p>
      <div class="kn-cta-row">
        <a class="kn-btn kn-btn--primary" href="https://ug.kandanews.africa">
          <i class="fa-solid fa-arrow-right"></i> Visit Uganda Edition
        </a>
        <a class="kn-btn kn-btn--ghost" href="https://kandanews.africa">
          <i class="fa-solid fa-earth-africa"></i> Africa Hub
        </a>
      </div>
    </div>
  </div>
</section>

<!-- DOWNLOAD APP -->
<section id="download" class="kn-section kn-section--dark kn-reveal" aria-labelledby="download-title">
  <div class="container text-center">
    <h2 id="download-title" class="kn-section__title">
      <i class="fa-solid fa-mobile-screen"></i> Get the App
    </h2>
    <p class="kn-section__lead">
      Our apps for Android, iOS, Windows, Mac and Linux are launching soon.
    </p>
    <div class="kn-store-row">
      <span class="kn-store-btn kn-btn--disabled"><i class="fa-brands fa-google-play"></i> Google Play</span>
      <span class="kn-store-btn kn-btn--disabled"><i class="fa-brands fa-apple"></i> App Store</span>
      <span class="kn-store-btn kn-btn--disabled"><i class="fa-brands fa-windows"></i> Windows</span>
      <span class="kn-store-btn kn-btn--disabled"><i class="fa-brands fa-apple"></i> macOS</span>
      <span class="kn-store-btn kn-btn--disabled"><i class="fa-brands fa-linux"></i> Linux</span>
    </div>
    <p style="color:rgba(255,255,255,0.5);font-size:0.9rem;margin-top:1.2rem;">Apps launching soon — stay tuned!</p>
  </div>
</section>

<?php require_once __DIR__ . '/shared/components/footer.php'; ?>
