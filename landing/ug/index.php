<?php
/**
 * KandaNews Uganda — Country Landing Page
 * ug.kandanews.africa
 */
require_once __DIR__ . '/shared/includes/helpers.php';
require_once __DIR__ . '/shared/includes/country-config.php';
require_once __DIR__ . '/shared/components/header.php';

$plans = $COUNTRY['plans'];
$currency = $COUNTRY['currency'];
$login_url = '/login.php';
?>

<!-- HERO: Full-height cinematic video + glass panel -->
<section class="kn-hero" aria-label="KandaNews <?php echo h($COUNTRY['name']); ?> Hero">
  <div class="kn-hero__media" aria-hidden="true">
    <video class="kn-hero__video" poster="/shared/assets/video/launch-hero-poster.gif" playsinline muted autoplay loop preload="metadata" role="img" aria-label="Background visual">
      <source src="/shared/assets/video/launch-hero.mp4" type="video/mp4">
    </video>
  </div>

  <div class="kn-hero__overlay">
    <div class="kn-glass container" role="region" aria-labelledby="kn-hero-title">
      <span class="kn-glass__badge">Tap to Know. Tap to Grow.</span>

      <h1 class="kn-glass__title" id="kn-hero-title">
        <span class="kn-glass__brand">KandaNews</span>
        <span class="kn-glass__country"><?php echo h($COUNTRY['name']); ?></span>
        <span class="kn-glass__flag" aria-hidden="true"><?php echo $COUNTRY['flag']; ?></span>
      </h1>

      <p class="kn-glass__tagline">
        The <strong>Future of News</strong> is already here — shaping what news feels like.
      </p>

      <p class="kn-glass__sub">
        Personal, local and pan-Africa coverage for students, professionals and entrepreneurs — delivered fast, mobile-first, and built to help you grow.
      </p>

      <div class="kn-glass__ctas" role="navigation" aria-label="Hero actions">
        <a class="kn-btn kn-btn--primary" href="#subscribe">Subscribe &amp; Join</a>
        <a class="kn-btn kn-btn--ghost" href="https://kandanews.africa">Visit Africa Hub</a>
      </div>
    </div>
  </div>
</section>

<!-- PRICING -->
<section id="subscribe" class="kn-section kn-pricing kn-reveal" aria-labelledby="pricing-title">
  <div class="container">
    <h2 id="pricing-title" class="kn-section__title text-center">Choose Your Plan</h2>
    <p class="kn-section__lead text-center" style="margin-left:auto;margin-right:auto;">Flexible access options — pick the plan that fits your rhythm.</p>

    <div class="kn-pricing-grid">
      <article class="kn-card" aria-label="Daily plan">
        <h3>Daily</h3>
        <p class="kn-price"><strong><?php echo h($plans['daily']['label']); ?></strong></p>
        <a href="<?php echo h($login_url); ?>" class="kn-btn kn-btn--primary">Subscribe</a>
      </article>

      <article class="kn-card kn-card--popular" aria-label="Weekly plan (most popular)">
        <span class="kn-badge-popular">Most Popular</span>
        <h3>Weekly</h3>
        <p class="kn-price"><strong><?php echo h($plans['weekly']['label']); ?></strong></p>
        <a href="<?php echo h($login_url); ?>" class="kn-btn kn-btn--primary">Subscribe</a>
      </article>

      <article class="kn-card" aria-label="Monthly plan">
        <h3>Monthly</h3>
        <p class="kn-price"><strong><?php echo h($plans['monthly']['label']); ?></strong></p>
        <a href="<?php echo h($login_url); ?>" class="kn-btn kn-btn--primary">Subscribe</a>
      </article>
    </div>
  </div>
</section>

<!-- WHY SUBSCRIBE -->
<section class="kn-section kn-section--alt kn-reveal" aria-labelledby="why-title">
  <div class="container">
    <h2 id="why-title" class="kn-section__title">Why Subscribe?</h2>
    <p class="kn-section__lead">More than headlines — tools for growth, connection and opportunity across <?php echo h($COUNTRY['name']); ?> and the region.</p>

    <div class="kn-features-grid">
      <div class="kn-feature">
        <span class="kn-feature__icon">📖</span>
        <span class="kn-feature__title">Daily interactive flipbook editions</span>
      </div>
      <div class="kn-feature">
        <span class="kn-feature__icon">🎧</span>
        <span class="kn-feature__title">Audio interviews &amp; storytelling</span>
      </div>
      <div class="kn-feature">
        <span class="kn-feature__icon">📹</span>
        <span class="kn-feature__title">Short video explainers and features</span>
      </div>
      <div class="kn-feature">
        <span class="kn-feature__icon">🛒</span>
        <span class="kn-feature__title">Smart marketplace &amp; campaign features</span>
      </div>
      <div class="kn-feature">
        <span class="kn-feature__icon">💡</span>
        <span class="kn-feature__title">Career &amp; student-focused resources</span>
      </div>
      <div class="kn-feature">
        <span class="kn-feature__icon">🌍</span>
        <span class="kn-feature__title">Pan-Africa perspective, locally delivered</span>
      </div>
    </div>
  </div>
</section>

<!-- ADVERTISE -->
<section id="advertisers" class="kn-section kn-reveal" aria-labelledby="ads-title">
  <div class="container">
    <div class="kn-advertise">
      <h2 id="ads-title">Advertise / Partner with Us</h2>
      <p>Promote your brand inside <?php echo h($COUNTRY['name']); ?>'s most relevant news experiences — full pages, native placements, audio &amp; video sponsorships.</p>
      <div class="kn-cta-row">
        <a class="kn-btn kn-btn--primary" href="mailto:<?php echo h($COUNTRY['email']); ?>">
          <i class="fa-solid fa-envelope"></i> Contact Sales
        </a>
        <a class="kn-btn kn-btn--ghost" href="mailto:<?php echo h($COUNTRY['email']); ?>">
          <i class="fa-solid fa-download"></i> Download Rate Card
        </a>
      </div>
    </div>
  </div>
</section>

<!-- APPS COMING SOON -->
<section class="kn-section kn-section--alt kn-reveal" aria-labelledby="apps-title">
  <div class="container text-center">
    <h2 id="apps-title" class="kn-section__title">Apps Coming Soon</h2>
    <p class="kn-section__lead" style="margin-left:auto;margin-right:auto;">Our apps are arriving on Android &amp; iOS — a full KandaNews experience for your pocket.</p>
    <div class="kn-store-row">
      <span class="kn-store-btn kn-btn--disabled" aria-label="Google Play coming soon"><i class="fa-brands fa-google-play"></i> Google Play</span>
      <span class="kn-store-btn kn-btn--disabled" aria-label="App Store coming soon"><i class="fa-brands fa-apple"></i> App Store</span>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/shared/components/footer.php'; ?>
