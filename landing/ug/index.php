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
?>

<!-- ============================================================
     1. HERO — Full-screen video background + glass morphism panel
     ============================================================ -->
<section class="kn-hero kn-reveal" aria-label="KandaNews <?php echo h($COUNTRY['name']); ?> Hero">
  <div class="kn-hero__media" aria-hidden="true">
    <video class="kn-hero__video"
           poster="/shared/assets/video/hub-hero-poster.gif"
           playsinline muted autoplay loop preload="metadata"
           role="img"
           aria-label="Background visual">
      <source src="/shared/assets/video/hub-hero.mp4" type="video/mp4">
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
        The <strong>Future of News</strong> is already here &mdash; shaping what news <em>feels</em> like.
      </p>

      <p class="kn-glass__sub">
        Personal, local and pan-Africa coverage for students, professionals and entrepreneurs &mdash;
        delivered fast, mobile-first, and built to help you grow.
      </p>

      <div class="kn-glass__ctas" role="navigation" aria-label="Hero actions">
        <a class="kn-btn kn-btn--primary" href="#download">
          <i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i> Download the App
        </a>
        <a class="kn-btn kn-btn--ghost" href="https://kandanews.africa">
          <i class="fa-solid fa-globe" aria-hidden="true"></i> Visit Africa Hub
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     2. STATS BAR — Key figures at a glance
     ============================================================ -->
<section class="kn-stats kn-reveal" aria-label="KandaNews <?php echo h($COUNTRY['name']); ?> at a glance">
  <div class="container">
    <div class="kn-stats__inner">
      <div class="kn-stats__item">
        <span class="kn-stats__number kn-counter" data-target="4">4</span>
        <span class="kn-stats__label">Country Editions</span>
      </div>
      <div class="kn-stats__item">
        <span class="kn-stats__number kn-counter" data-target="10000">10,000+</span>
        <span class="kn-stats__label">Early Subscribers</span>
      </div>
      <div class="kn-stats__item">
        <span class="kn-stats__number kn-counter" data-target="365">365</span>
        <span class="kn-stats__label">Daily Updates / Year</span>
      </div>
      <div class="kn-stats__item">
        <span class="kn-stats__number kn-counter" data-target="50">50+</span>
        <span class="kn-stats__label">Content Categories</span>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     3. PRICING — Choose Your Plan
     ============================================================ -->
<section id="subscribe" class="kn-section kn-pricing kn-reveal" aria-labelledby="pricing-title">
  <div class="container">
    <h2 id="pricing-title" class="kn-section__title text-center">Choose Your Plan</h2>
    <p class="kn-section__lead text-center" style="margin-left:auto;margin-right:auto;">
      Flexible access options &mdash; pick the plan that fits your rhythm.
    </p>

    <div class="kn-pricing-grid">
      <!-- Daily Plan -->
      <article class="kn-card" aria-label="Daily plan">
        <div class="kn-card__icon" aria-hidden="true">
          <i class="fa-regular fa-newspaper"></i>
        </div>
        <h3>Daily</h3>
        <p class="kn-price">
          <strong><?php echo h($plans['daily']['label']); ?></strong>
        </p>
        <ul class="kn-card__perks" aria-label="Daily plan features">
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Full edition access</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Audio &amp; video content</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Cancel any time</li>
        </ul>
        <a href="#download" class="kn-btn kn-btn--outline">Get Started</a>
      </article>

      <!-- Weekly Plan (Popular) -->
      <article class="kn-card kn-card--popular" aria-label="Weekly plan (most popular)">
        <span class="kn-badge-popular">Most Popular</span>
        <div class="kn-card__icon" aria-hidden="true">
          <i class="fa-solid fa-star"></i>
        </div>
        <h3>Weekly</h3>
        <p class="kn-price">
          <strong><?php echo h($plans['weekly']['label']); ?></strong>
        </p>
        <ul class="kn-card__perks" aria-label="Weekly plan features">
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Full edition access</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Audio &amp; video content</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Marketplace features</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Best value for regular readers</li>
        </ul>
        <a href="#download" class="kn-btn kn-btn--primary">Get Started</a>
      </article>

      <!-- Monthly Plan -->
      <article class="kn-card" aria-label="Monthly plan">
        <div class="kn-card__icon" aria-hidden="true">
          <i class="fa-solid fa-crown"></i>
        </div>
        <h3>Monthly</h3>
        <p class="kn-price">
          <strong><?php echo h($plans['monthly']['label']); ?></strong>
        </p>
        <ul class="kn-card__perks" aria-label="Monthly plan features">
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Full edition access</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Audio &amp; video content</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Marketplace features</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Priority support</li>
          <li><i class="fa-solid fa-check" aria-hidden="true"></i> Maximum savings</li>
        </ul>
        <a href="#download" class="kn-btn kn-btn--outline">Get Started</a>
      </article>
    </div>
  </div>
</section>

<!-- ============================================================
     4. WHY SUBSCRIBE — Features grid (6 items)
     ============================================================ -->
<section class="kn-section kn-section--alt kn-reveal" aria-labelledby="why-title">
  <div class="container">
    <h2 id="why-title" class="kn-section__title text-center">Why Subscribe?</h2>
    <p class="kn-section__lead text-center" style="margin-left:auto;margin-right:auto;">
      More than headlines &mdash; tools for growth, connection and opportunity across
      <?php echo h($COUNTRY['name']); ?> and the continent.
    </p>

    <div class="kn-features-grid">
      <div class="kn-feature" role="listitem">
        <span class="kn-feature__icon" aria-hidden="true">&#x1F4D6;</span>
        <span class="kn-feature__title">Daily interactive flipbook editions</span>
        <p class="kn-feature__desc">Swipe through beautifully designed daily news &mdash; optimised for your phone and tablet.</p>
      </div>
      <div class="kn-feature" role="listitem">
        <span class="kn-feature__icon" aria-hidden="true">&#x1F3A7;</span>
        <span class="kn-feature__title">Audio interviews &amp; storytelling</span>
        <p class="kn-feature__desc">Listen to in-depth conversations with leaders, creators and change-makers across Africa.</p>
      </div>
      <div class="kn-feature" role="listitem">
        <span class="kn-feature__icon" aria-hidden="true">&#x1F4F9;</span>
        <span class="kn-feature__title">Short video explainers and features</span>
        <p class="kn-feature__desc">Bite-sized video content that breaks down the stories shaping <?php echo h($COUNTRY['name']); ?> and the region.</p>
      </div>
      <div class="kn-feature" role="listitem">
        <span class="kn-feature__icon" aria-hidden="true">&#x1F6D2;</span>
        <span class="kn-feature__title">Smart marketplace &amp; campaign features</span>
        <p class="kn-feature__desc">Discover opportunities, promote your business and connect with a growing audience.</p>
      </div>
      <div class="kn-feature" role="listitem">
        <span class="kn-feature__icon" aria-hidden="true">&#x1F4A1;</span>
        <span class="kn-feature__title">Career &amp; student-focused resources</span>
        <p class="kn-feature__desc">Scholarships, internships and career tools curated for the next generation of African talent.</p>
      </div>
      <div class="kn-feature" role="listitem">
        <span class="kn-feature__icon" aria-hidden="true">&#x1F30D;</span>
        <span class="kn-feature__title">Pan-Africa perspective, locally delivered</span>
        <p class="kn-feature__desc">Continental insight with local depth &mdash; from <?php echo h($COUNTRY['name']); ?> to every corner of Africa.</p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     5. HOW IT WORKS — 3 simple steps
     ============================================================ -->
<section class="kn-section kn-reveal" aria-labelledby="steps-title">
  <div class="container">
    <h2 id="steps-title" class="kn-section__title text-center">How It Works</h2>
    <p class="kn-section__lead text-center" style="margin-left:auto;margin-right:auto;">
      Get started with KandaNews in three easy steps.
    </p>

    <div class="kn-steps" role="list">
      <div class="kn-step" role="listitem">
        <div class="kn-step__circle" aria-hidden="true">
          <span class="kn-step__icon"><i class="fa-solid fa-cloud-arrow-down"></i></span>
        </div>
        <h3 class="kn-step__title">Download the App</h3>
        <p class="kn-step__desc">
          Get KandaNews on your Android, iOS, or desktop device &mdash; free to install.
        </p>
      </div>
      <div class="kn-step" role="listitem">
        <div class="kn-step__circle" aria-hidden="true">
          <span class="kn-step__icon"><i class="fa-solid fa-hand-pointer"></i></span>
        </div>
        <h3 class="kn-step__title">Choose Your Plan</h3>
        <p class="kn-step__desc">
          Select a daily, weekly or monthly plan that suits your budget and reading style.
        </p>
      </div>
      <div class="kn-step" role="listitem">
        <div class="kn-step__circle" aria-hidden="true">
          <span class="kn-step__icon"><i class="fa-solid fa-book-open-reader"></i></span>
        </div>
        <h3 class="kn-step__title">Start Reading</h3>
        <p class="kn-step__desc">
          Enjoy daily interactive editions, audio, video and marketplace features &mdash; all in one app.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     6. DOWNLOAD APP — Prominent dark section
     ============================================================ -->
<section id="download" class="kn-download kn-reveal" aria-labelledby="download-title">
  <div class="kn-download__inner" style="grid-template-columns:1fr;text-align:center;">
    <div class="kn-download__text">
      <h2 id="download-title">
        Get KandaNews <?php echo h($COUNTRY['name']); ?> on Your Device
      </h2>
      <p>Available soon on every major platform. Download once &mdash; stay informed always.</p>

      <div class="kn-store-row" style="justify-content:center;" role="list" aria-label="App download options">
        <span class="kn-store-btn kn-btn--disabled" role="listitem" aria-label="Google Play coming soon">
          <i class="fa-brands fa-google-play" aria-hidden="true"></i> Google Play
        </span>
        <span class="kn-store-btn kn-btn--disabled" role="listitem" aria-label="App Store coming soon">
          <i class="fa-brands fa-apple" aria-hidden="true"></i> App Store
        </span>
        <span class="kn-store-btn kn-btn--disabled" role="listitem" aria-label="Windows coming soon">
          <i class="fa-brands fa-windows" aria-hidden="true"></i> Windows
        </span>
        <span class="kn-store-btn kn-btn--disabled" role="listitem" aria-label="macOS coming soon">
          <i class="fa-brands fa-apple" aria-hidden="true"></i> macOS
        </span>
        <span class="kn-store-btn kn-btn--disabled" role="listitem" aria-label="Linux coming soon">
          <i class="fa-brands fa-linux" aria-hidden="true"></i> Linux
        </span>
      </div>

      <p style="color:var(--kn-orange);font-weight:700;font-size:0.9rem;margin-top:1rem;">
        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
        Apps launching soon &mdash; stay tuned!
      </p>
    </div>
  </div>
</section>

<!-- ============================================================
     7. ADVERTISE — Partner / Promote your brand
     ============================================================ -->
<section id="advertisers" class="kn-section kn-reveal" aria-labelledby="ads-title">
  <div class="container">
    <div class="kn-advertise">
      <h2 id="ads-title">Advertise in <?php echo h($COUNTRY['name']); ?></h2>
      <p>
        Promote your brand inside <?php echo h($COUNTRY['name']); ?>&rsquo;s most relevant news experience &mdash;
        full-page placements, native advertising, audio &amp; video sponsorships,
        and smart campaign tools designed for African audiences.
      </p>
      <div class="kn-cta-row">
        <a class="kn-btn kn-btn--primary" href="mailto:<?php echo h($COUNTRY['email']); ?>">
          <i class="fa-solid fa-envelope" aria-hidden="true"></i> Contact Sales
        </a>
        <a class="kn-btn kn-btn--ghost" href="mailto:<?php echo h($COUNTRY['email']); ?>?subject=Rate%20Card%20Request">
          <i class="fa-solid fa-download" aria-hidden="true"></i> Download Rate Card
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ============================================================
     8. APPS COMING SOON — Brief teaser
     ============================================================ -->
<section class="kn-section kn-section--alt kn-reveal" aria-labelledby="apps-title">
  <div class="container text-center">
    <h2 id="apps-title" class="kn-section__title">Apps Coming Soon</h2>
    <p class="kn-section__lead" style="margin-left:auto;margin-right:auto;">
      Native apps for Android, iOS, Windows, macOS and Linux are on the way &mdash;
      bringing the full KandaNews <?php echo h($COUNTRY['name']); ?> experience to every device you own.
    </p>
    <div class="kn-store-row">
      <span class="kn-store-btn kn-btn--disabled" aria-label="Google Play coming soon">
        <i class="fa-brands fa-google-play" aria-hidden="true"></i> Google Play
      </span>
      <span class="kn-store-btn kn-btn--disabled" aria-label="App Store coming soon">
        <i class="fa-brands fa-apple" aria-hidden="true"></i> App Store
      </span>
      <span class="kn-store-btn kn-btn--disabled" aria-label="Windows coming soon">
        <i class="fa-brands fa-windows" aria-hidden="true"></i> Windows
      </span>
      <span class="kn-store-btn kn-btn--disabled" aria-label="macOS coming soon">
        <i class="fa-brands fa-apple" aria-hidden="true"></i> macOS
      </span>
      <span class="kn-store-btn kn-btn--disabled" aria-label="Linux coming soon">
        <i class="fa-brands fa-linux" aria-hidden="true"></i> Linux
      </span>
    </div>
    <p class="mt-2" style="color:var(--kn-muted);font-size:0.92rem;">
      Be among the first to know &mdash; subscribe and we will notify you the moment apps go live.
    </p>
  </div>
</section>

<!-- Page-specific styles for pricing card icons & perks (supplements base.css) -->
<style>
.kn-card__icon {
    font-size: 2rem;
    color: var(--kn-orange);
    margin-bottom: 0.6rem;
}
.kn-card__perks {
    list-style: none;
    padding: 0;
    margin: 0 0 1.2rem;
    text-align: left;
    font-size: 0.88rem;
    color: var(--kn-muted);
}
.kn-card__perks li {
    padding: 0.35rem 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.kn-card__perks li i {
    color: var(--kn-success);
    font-size: 0.75rem;
    flex-shrink: 0;
}
</style>

<?php require_once __DIR__ . '/shared/components/footer.php'; ?>
