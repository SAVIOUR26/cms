<?php
/**
 * KandaNews Africa — Main Hub
 * kandanews.africa
 */
$COUNTRY_CODE = '';
$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$COUNTRIES = [];
require_once __DIR__ . '/shared/includes/helpers.php';
require_once __DIR__ . '/shared/includes/country-config.php';

// Override for hub page
$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$_country_name = 'Africa';
// Set hub flag so header knows this is the hub page
$_is_hub = true;

require_once __DIR__ . '/shared/components/header.php';
?>

<!-- ===== HERO ===== -->
<section class="kn-hero kn-hero--hub kn-reveal" aria-label="Hero introduction" style="background: linear-gradient(135deg, var(--kn-primary) 0%, #0b1929 100%); color: #fff; padding: 6rem 0 5rem; position: relative; overflow: hidden;">
  <div class="container" style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 3.5rem; align-items: center;">

    <!-- Hero Text -->
    <div>
      <span class="kn-glass__badge" style="display: inline-block; background: rgba(255,255,255,0.12); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); border: 1px solid rgba(255,255,255,0.18); border-radius: 999px; padding: 0.45rem 1.2rem; font-size: 0.82rem; font-weight: 600; letter-spacing: 0.04em; color: rgba(255,255,255,0.95); margin-bottom: 1.25rem; text-transform: uppercase;">
        <i class="fa-solid fa-bolt" style="margin-right: 0.35rem; color: var(--kn-accent, #f5c518);"></i>
        Tap to Know. Tap to Grow.
      </span>

      <h1 style="font-size: clamp(2rem, 4.2vw, 3.1rem); font-weight: 900; line-height: 1.08; margin: 0 0 0.5rem; color: #fff;">
        Africa's First Digital<br>Flipping Newspaper
        <span id="af-phrase" aria-live="polite" style="display: block; color: var(--kn-accent, #f5c518); min-height: 1.5em; font-size: 0.72em;"></span>
      </h1>

      <p style="color: rgba(255,255,255,0.78); font-size: 1.12rem; max-width: 54ch; line-height: 1.65; margin-bottom: 1.8rem;">
        Mobile-first, interactive news built for <strong style="color: #fff;">students</strong>, <strong style="color: #fff;">professionals</strong> &amp; <strong style="color: #fff;">entrepreneurs</strong>. Flip through rich editions, listen to audio briefings, and stay ahead&nbsp;&mdash;&nbsp;anywhere&nbsp;in&nbsp;Africa.
      </p>

      <div style="display: flex; gap: 0.9rem; flex-wrap: wrap; margin-bottom: 1.8rem;">
        <a class="kn-btn kn-btn--primary" href="#download" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.8rem 1.8rem; font-weight: 700; border-radius: 8px; font-size: 0.95rem; background: var(--kn-accent, #f5c518); color: #0b1929; text-decoration: none; transition: transform 0.2s, box-shadow 0.2s;" aria-label="Download the KandaNews app">
          <i class="fa-solid fa-download"></i> Download the App
        </a>
        <a class="kn-btn kn-btn--ghost" href="#countries" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.8rem 1.8rem; font-weight: 700; border-radius: 8px; font-size: 0.95rem; border: 2px solid rgba(255,255,255,0.35); color: #fff; text-decoration: none; background: transparent; transition: border-color 0.2s, background 0.2s;" aria-label="Explore country editions">
          <i class="fa-solid fa-earth-africa"></i> Explore Countries
        </a>
      </div>

      <!-- Trust Indicators -->
      <div style="display: flex; gap: 1.8rem; color: rgba(255,255,255,0.7); font-size: 0.84rem; flex-wrap: wrap;" role="list" aria-label="Trust indicators">
        <span role="listitem" style="display: inline-flex; align-items: center; gap: 0.4rem;">
          <i class="fa-solid fa-earth-africa" style="color: var(--kn-accent, #f5c518);"></i> Multi-country editions
        </span>
        <span role="listitem" style="display: inline-flex; align-items: center; gap: 0.4rem;">
          <i class="fa-solid fa-bolt" style="color: var(--kn-accent, #f5c518);"></i> Bite-sized high impact
        </span>
        <span role="listitem" style="display: inline-flex; align-items: center; gap: 0.4rem;">
          <i class="fa-solid fa-shield-halved" style="color: var(--kn-accent, #f5c518);"></i> Secure subscriber-only
        </span>
      </div>
    </div>

    <!-- Hero Video -->
    <div style="position: relative; border-radius: 18px; overflow: hidden; box-shadow: 0 0 60px rgba(245,197,24,0.12), 0 20px 50px rgba(0,0,0,0.35);">
      <div style="position: absolute; inset: -2px; border-radius: 20px; background: linear-gradient(135deg, rgba(245,197,24,0.3), rgba(245,197,24,0.05), rgba(245,197,24,0.3)); z-index: 0; pointer-events: none;"></div>
      <video
        class="kn-hero__video"
        style="position: relative; z-index: 1; width: 100%; height: auto; aspect-ratio: 16/9; object-fit: cover; border-radius: 16px; display: block;"
        autoplay muted playsinline loop preload="auto"
        aria-label="KandaNews digital newspaper preview"
      >
        <source src="/shared/assets/video/hub-hero.mp4" type="video/mp4">
        Your browser does not support the video tag.
      </video>
    </div>

  </div>
</section>


<!-- ===== STATS BAR ===== -->
<section class="kn-stats kn-reveal" aria-label="Platform statistics" style="background: #0d1b2a; color: #fff; padding: 3rem 0;">
  <div class="container">
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 2rem; text-align: center;">

      <div class="kn-stats__item" style="padding: 1rem 0;">
        <div class="kn-counter" data-target="50" style="font-size: 2.6rem; font-weight: 900; color: var(--kn-accent, #f5c518); line-height: 1;">50+</div>
        <div style="font-size: 0.88rem; color: rgba(255,255,255,0.7); margin-top: 0.35rem; text-transform: uppercase; letter-spacing: 0.06em;">Editions Published</div>
      </div>

      <div class="kn-stats__item" style="padding: 1rem 0;">
        <div class="kn-counter" data-target="4" style="font-size: 2.6rem; font-weight: 900; color: var(--kn-accent, #f5c518); line-height: 1;">4</div>
        <div style="font-size: 0.88rem; color: rgba(255,255,255,0.7); margin-top: 0.35rem; text-transform: uppercase; letter-spacing: 0.06em;">Countries</div>
      </div>

      <div class="kn-stats__item" style="padding: 1rem 0;">
        <div class="kn-counter" data-target="1000" style="font-size: 2.6rem; font-weight: 900; color: var(--kn-accent, #f5c518); line-height: 1;">1,000+</div>
        <div style="font-size: 0.88rem; color: rgba(255,255,255,0.7); margin-top: 0.35rem; text-transform: uppercase; letter-spacing: 0.06em;">Readers</div>
      </div>

      <div class="kn-stats__item" style="padding: 1rem 0;">
        <div class="kn-counter" data-target="24" style="font-size: 2.6rem; font-weight: 900; color: var(--kn-accent, #f5c518); line-height: 1;">24/7</div>
        <div style="font-size: 0.88rem; color: rgba(255,255,255,0.7); margin-top: 0.35rem; text-transform: uppercase; letter-spacing: 0.06em;">Digital Access</div>
      </div>

    </div>
  </div>
</section>


<!-- ===== COUNTRIES ===== -->
<section id="countries" class="kn-section kn-section--alt kn-reveal" aria-labelledby="countries-heading" style="padding: 5rem 0;">
  <div class="container">
    <h2 id="countries-heading" class="kn-section__title text-center" style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;">
      <i class="fa-solid fa-earth-africa" style="color: var(--kn-accent, #f5c518); margin-right: 0.4rem;"></i>
      Choose Your Country
    </h2>
    <p class="kn-section__lead text-center" style="max-width: 52ch; margin: 0 auto 2.5rem; color: var(--kn-muted, #6c757d); font-size: 1.05rem;">
      Subscriptions &amp; Advertising Rate Cards are managed per country edition. Pick yours to begin.
    </p>

    <div class="kn-countries-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; max-width: 1000px; margin: 0 auto;" role="list">

      <!-- Uganda — LIVE -->
      <article class="kn-country-card kn-country-card--live kn-reveal" role="listitem" style="background: #fff; border-radius: 14px; padding: 2rem 1.5rem; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,0.06); border: 2px solid var(--kn-accent, #f5c518); position: relative; transition: transform 0.25s, box-shadow 0.25s; cursor: pointer;" onclick="location.href='https://ug.kandanews.africa'">
        <span style="position: absolute; top: 0.75rem; right: 0.75rem; background: var(--kn-accent, #f5c518); color: #0b1929; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; padding: 0.2rem 0.6rem; border-radius: 999px;">LIVE</span>
        <div class="kn-country-card__flag" style="font-size: 3.2rem; margin-bottom: 0.6rem;" aria-hidden="true">🇺🇬</div>
        <h3 class="kn-country-card__name" style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Uganda</h3>
        <div class="kn-country-card__links" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem;">
          <a class="kn-country-card__link kn-country-card__link--hot" href="https://ug.kandanews.africa" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; background: var(--kn-accent, #f5c518); color: #0b1929;" aria-label="Enter KandaNews Uganda">
            <i class="fa-solid fa-arrow-right"></i> Enter
          </a>
          <a class="kn-country-card__link" href="https://ug.kandanews.africa/#subscribe" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; background: rgba(0,0,0,0.06); color: var(--kn-text, #212529);" aria-label="Subscribe to KandaNews Uganda">
            Subscribe
          </a>
          <a class="kn-country-card__link" href="https://ug.kandanews.africa/#advertisers" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; background: rgba(0,0,0,0.06); color: var(--kn-text, #212529);" aria-label="View KandaNews Uganda rate card">
            Rate Card
          </a>
        </div>
      </article>

      <!-- Kenya — COMING SOON -->
      <article class="kn-country-card kn-country-card--soon kn-reveal" role="listitem" style="background: #fff; border-radius: 14px; padding: 2rem 1.5rem; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,0.06); border: 2px solid rgba(0,0,0,0.08); position: relative; transition: transform 0.25s, box-shadow 0.25s; opacity: 0.85;">
        <span style="position: absolute; top: 0.75rem; right: 0.75rem; background: rgba(0,0,0,0.08); color: var(--kn-muted, #6c757d); font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; padding: 0.2rem 0.6rem; border-radius: 999px;">COMING SOON</span>
        <div class="kn-country-card__flag" style="font-size: 3.2rem; margin-bottom: 0.6rem;" aria-hidden="true">🇰🇪</div>
        <h3 class="kn-country-card__name" style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Kenya</h3>
        <div class="kn-country-card__links" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem;">
          <a class="kn-country-card__link" href="https://ke.kandanews.africa/#notify" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; background: var(--kn-primary, #0d1b2a); color: #fff;" aria-label="Get notified when KandaNews Kenya launches">
            <i class="fa-solid fa-bell"></i> Notify Me
          </a>
          <a class="kn-country-card__link" href="https://ke.kandanews.africa" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; background: rgba(0,0,0,0.06); color: var(--kn-text, #212529);" aria-label="Learn more about KandaNews Kenya">
            Learn More
          </a>
        </div>
      </article>

      <!-- Nigeria — COMING SOON -->
      <article class="kn-country-card kn-country-card--soon kn-reveal" role="listitem" style="background: #fff; border-radius: 14px; padding: 2rem 1.5rem; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,0.06); border: 2px solid rgba(0,0,0,0.08); position: relative; transition: transform 0.25s, box-shadow 0.25s; opacity: 0.85;">
        <span style="position: absolute; top: 0.75rem; right: 0.75rem; background: rgba(0,0,0,0.08); color: var(--kn-muted, #6c757d); font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; padding: 0.2rem 0.6rem; border-radius: 999px;">COMING SOON</span>
        <div class="kn-country-card__flag" style="font-size: 3.2rem; margin-bottom: 0.6rem;" aria-hidden="true">🇳🇬</div>
        <h3 class="kn-country-card__name" style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">Nigeria</h3>
        <div class="kn-country-card__links" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem;">
          <a class="kn-country-card__link" href="https://ng.kandanews.africa/#notify" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; background: var(--kn-primary, #0d1b2a); color: #fff;" aria-label="Get notified when KandaNews Nigeria launches">
            <i class="fa-solid fa-bell"></i> Notify Me
          </a>
          <a class="kn-country-card__link" href="https://ng.kandanews.africa" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; background: rgba(0,0,0,0.06); color: var(--kn-text, #212529);" aria-label="Learn more about KandaNews Nigeria">
            Learn More
          </a>
        </div>
      </article>

      <!-- South Africa — COMING SOON -->
      <article class="kn-country-card kn-country-card--soon kn-reveal" role="listitem" style="background: #fff; border-radius: 14px; padding: 2rem 1.5rem; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,0.06); border: 2px solid rgba(0,0,0,0.08); position: relative; transition: transform 0.25s, box-shadow 0.25s; opacity: 0.85;">
        <span style="position: absolute; top: 0.75rem; right: 0.75rem; background: rgba(0,0,0,0.08); color: var(--kn-muted, #6c757d); font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; padding: 0.2rem 0.6rem; border-radius: 999px;">COMING SOON</span>
        <div class="kn-country-card__flag" style="font-size: 3.2rem; margin-bottom: 0.6rem;" aria-hidden="true">🇿🇦</div>
        <h3 class="kn-country-card__name" style="font-size: 1.15rem; font-weight: 700; margin-bottom: 1rem;">South Africa</h3>
        <div class="kn-country-card__links" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0.5rem;">
          <a class="kn-country-card__link" href="https://za.kandanews.africa/#notify" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; background: var(--kn-primary, #0d1b2a); color: #fff;" aria-label="Get notified when KandaNews South Africa launches">
            <i class="fa-solid fa-bell"></i> Notify Me
          </a>
          <a class="kn-country-card__link" href="https://za.kandanews.africa" style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.82rem; font-weight: 600; text-decoration: none; background: rgba(0,0,0,0.06); color: var(--kn-text, #212529);" aria-label="Learn more about KandaNews South Africa">
            Learn More
          </a>
        </div>
      </article>

    </div>

    <p class="text-center" style="color: var(--kn-muted, #6c757d); margin-top: 2rem; font-size: 0.92rem;">
      <i class="fa-solid fa-globe" style="margin-right: 0.3rem;"></i> More countries launching soon.
    </p>
  </div>
</section>


<!-- ===== HOW IT WORKS ===== -->
<section class="kn-steps kn-section kn-reveal" aria-labelledby="steps-heading" style="padding: 5rem 0;">
  <div class="container">
    <h2 id="steps-heading" class="kn-section__title text-center" style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;">
      How KandaNews Works
    </h2>
    <p class="kn-section__lead text-center" style="max-width: 50ch; margin: 0 auto 3rem; color: var(--kn-muted, #6c757d); font-size: 1.05rem;">
      Three simple steps to Africa's smartest news experience.
    </p>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; max-width: 960px; margin: 0 auto;" role="list">

      <!-- Step 1 -->
      <div class="kn-step kn-reveal" role="listitem" style="text-align: center; padding: 2.5rem 1.5rem; background: #fff; border-radius: 14px; box-shadow: 0 4px 24px rgba(0,0,0,0.05); position: relative;">
        <div style="width: 2rem; height: 2rem; background: var(--kn-accent, #f5c518); color: #0b1929; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; position: absolute; top: -0.75rem; left: 50%; transform: translateX(-50%);">1</div>
        <div style="font-size: 2.4rem; color: var(--kn-primary, #0d1b2a); margin-bottom: 1rem;">
          <i class="fa-solid fa-mobile-screen" aria-hidden="true"></i>
        </div>
        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem;">Download the App</h3>
        <p style="color: var(--kn-muted, #6c757d); font-size: 0.9rem; line-height: 1.55;">
          Get KandaNews on Android, iOS, Windows, Mac or Linux.
        </p>
      </div>

      <!-- Step 2 -->
      <div class="kn-step kn-reveal" role="listitem" style="text-align: center; padding: 2.5rem 1.5rem; background: #fff; border-radius: 14px; box-shadow: 0 4px 24px rgba(0,0,0,0.05); position: relative;">
        <div style="width: 2rem; height: 2rem; background: var(--kn-accent, #f5c518); color: #0b1929; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; position: absolute; top: -0.75rem; left: 50%; transform: translateX(-50%);">2</div>
        <div style="font-size: 2.4rem; color: var(--kn-primary, #0d1b2a); margin-bottom: 1rem;">
          <i class="fa-solid fa-credit-card" aria-hidden="true"></i>
        </div>
        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem;">Choose a Plan</h3>
        <p style="color: var(--kn-muted, #6c757d); font-size: 0.9rem; line-height: 1.55;">
          Pick daily, weekly or monthly &mdash; pay with mobile money or card.
        </p>
      </div>

      <!-- Step 3 -->
      <div class="kn-step kn-reveal" role="listitem" style="text-align: center; padding: 2.5rem 1.5rem; background: #fff; border-radius: 14px; box-shadow: 0 4px 24px rgba(0,0,0,0.05); position: relative;">
        <div style="width: 2rem; height: 2rem; background: var(--kn-accent, #f5c518); color: #0b1929; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.85rem; position: absolute; top: -0.75rem; left: 50%; transform: translateX(-50%);">3</div>
        <div style="font-size: 2.4rem; color: var(--kn-primary, #0d1b2a); margin-bottom: 1rem;">
          <i class="fa-solid fa-book-open" aria-hidden="true"></i>
        </div>
        <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem;">Read &amp; Grow</h3>
        <p style="color: var(--kn-muted, #6c757d); font-size: 0.9rem; line-height: 1.55;">
          Flip through interactive editions, listen to audio, watch explainers.
        </p>
      </div>

    </div>
  </div>
</section>


<!-- ===== REEL / SHOWCASE ===== -->
<section id="reel" class="kn-section kn-section--alt kn-reveal" aria-labelledby="reel-heading" style="padding: 5rem 0; overflow: hidden;">
  <div class="container">
    <h2 id="reel-heading" class="kn-section__title text-center" style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem;">
      Experience the Future of News
    </h2>
    <p class="kn-section__lead text-center" style="max-width: 48ch; margin: 0 auto 2.5rem; color: var(--kn-muted, #6c757d); font-size: 1.05rem;">
      A glimpse at the stories, formats and visual journalism inside every edition.
    </p>
  </div>
  <div class="kn-reel" aria-label="Scrolling showcase of KandaNews content" style="overflow: hidden; padding: 1rem 0;">
    <ul class="kn-reel__track" style="display: flex; gap: 1.5rem; list-style: none; padding: 0; margin: 0; animation: kn-scroll 40s linear infinite; width: max-content;">
      <?php
      $reel_items = [
        ['img' => '/shared/assets/img/portrait-1.jpg',    'eyebrow' => 'Interview',   'title' => 'How a campus startup scaled past borders'],
        ['img' => '/shared/assets/img/portrait-2.jpg',    'eyebrow' => 'Explainer',   'title' => 'Why mobile-first news wins attention'],
        ['img' => '/shared/assets/img/portrait-3.jpg',    'eyebrow' => 'Feature',     'title' => 'Creators shaping tomorrow\'s Africa'],
        ['img' => '/shared/assets/img/flip-sample1.png',  'eyebrow' => 'Flipbook',    'title' => '5 charts on youth employment trends'],
        ['img' => '/shared/assets/img/flip-sample2.png',  'eyebrow' => 'Marketplace', 'title' => 'Smart ads that actually help readers'],
        ['img' => '/shared/assets/img/flip-sample3.png',  'eyebrow' => 'Audio',       'title' => 'The 3-minute morning briefing'],
      ];
      // Double the items for seamless loop
      for ($loop = 0; $loop < 2; $loop++):
        foreach ($reel_items as $item):
      ?>
      <li class="kn-reel__item" style="flex: 0 0 280px; border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.06); transition: transform 0.3s;">
        <div style="overflow: hidden;">
          <img
            class="kn-reel__img"
            src="<?php echo h($item['img']); ?>"
            alt="<?php echo h($item['eyebrow'] . ': ' . $item['title']); ?>"
            loading="lazy"
            style="width: 100%; height: 200px; object-fit: cover; display: block; transition: transform 0.4s;"
          >
        </div>
        <div class="kn-reel__meta" style="padding: 1rem 1.1rem;">
          <div class="kn-reel__eyebrow" style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--kn-accent, #f5c518); margin-bottom: 0.3rem;"><?php echo h($item['eyebrow']); ?></div>
          <h3 class="kn-reel__title" style="font-size: 0.92rem; font-weight: 600; line-height: 1.35; color: var(--kn-text, #212529); margin: 0;"><?php echo h($item['title']); ?></h3>
        </div>
      </li>
      <?php endforeach; endfor; ?>
    </ul>
  </div>
</section>


<!-- ===== DOWNLOAD APP ===== -->
<section id="download" class="kn-download kn-reveal" aria-labelledby="download-heading" style="background: linear-gradient(135deg, #0d1b2a 0%, var(--kn-primary, #0d1b2a) 50%, #152238 100%); color: #fff; padding: 5rem 0; text-align: center;">
  <div class="container" style="max-width: 720px; margin: 0 auto;">
    <h2 id="download-heading" style="font-size: 2rem; font-weight: 800; margin-bottom: 0.5rem; color: #fff;">
      <i class="fa-solid fa-download" style="color: var(--kn-accent, #f5c518); margin-right: 0.4rem;"></i>
      Get KandaNews on Every Device
    </h2>
    <p style="color: rgba(255,255,255,0.75); font-size: 1.05rem; max-width: 48ch; margin: 0 auto 2.5rem; line-height: 1.6;">
      Whether you read on your phone during your commute, a tablet at lunch, or a laptop at work &mdash; KandaNews goes everywhere you do.
    </p>

    <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 0.9rem; margin-bottom: 2rem;" role="list" aria-label="App download options">

      <a class="kn-store-btn kn-btn--disabled" role="listitem" href="#download" aria-disabled="true" style="display: inline-flex; align-items: center; gap: 0.6rem; padding: 0.85rem 1.5rem; border-radius: 10px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.5); font-size: 0.88rem; font-weight: 600; text-decoration: none; pointer-events: none; cursor: default;">
        <i class="fa-brands fa-google-play" style="font-size: 1.2rem;"></i>
        <span>Google Play</span>
      </a>

      <a class="kn-store-btn kn-btn--disabled" role="listitem" href="#download" aria-disabled="true" style="display: inline-flex; align-items: center; gap: 0.6rem; padding: 0.85rem 1.5rem; border-radius: 10px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.5); font-size: 0.88rem; font-weight: 600; text-decoration: none; pointer-events: none; cursor: default;">
        <i class="fa-brands fa-apple" style="font-size: 1.2rem;"></i>
        <span>App Store</span>
      </a>

      <a class="kn-store-btn kn-btn--disabled" role="listitem" href="#download" aria-disabled="true" style="display: inline-flex; align-items: center; gap: 0.6rem; padding: 0.85rem 1.5rem; border-radius: 10px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.5); font-size: 0.88rem; font-weight: 600; text-decoration: none; pointer-events: none; cursor: default;">
        <i class="fa-brands fa-windows" style="font-size: 1.2rem;"></i>
        <span>Windows</span>
      </a>

      <a class="kn-store-btn kn-btn--disabled" role="listitem" href="#download" aria-disabled="true" style="display: inline-flex; align-items: center; gap: 0.6rem; padding: 0.85rem 1.5rem; border-radius: 10px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.5); font-size: 0.88rem; font-weight: 600; text-decoration: none; pointer-events: none; cursor: default;">
        <i class="fa-brands fa-apple" style="font-size: 1.2rem;"></i>
        <span>macOS</span>
      </a>

      <a class="kn-store-btn kn-btn--disabled" role="listitem" href="#download" aria-disabled="true" style="display: inline-flex; align-items: center; gap: 0.6rem; padding: 0.85rem 1.5rem; border-radius: 10px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); color: rgba(255,255,255,0.5); font-size: 0.88rem; font-weight: 600; text-decoration: none; pointer-events: none; cursor: default;">
        <i class="fa-brands fa-linux" style="font-size: 1.2rem;"></i>
        <span>Linux</span>
      </a>

    </div>

    <p style="color: rgba(255,255,255,0.45); font-size: 0.85rem; font-style: italic;">
      <i class="fa-solid fa-clock" style="margin-right: 0.3rem;"></i>
      Apps launching soon &mdash; stay tuned!
    </p>
  </div>
</section>


<!-- ===== ADVERTISE ===== -->
<section class="kn-section kn-reveal" aria-labelledby="advertise-heading" style="padding: 5rem 0;">
  <div class="container">
    <div class="kn-advertise" style="background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%); border: 2px solid rgba(0,0,0,0.06); border-radius: 18px; padding: 3.5rem; text-align: center; max-width: 800px; margin: 0 auto;">
      <div style="font-size: 2.5rem; margin-bottom: 1rem;">
        <i class="fa-solid fa-bullhorn" style="color: var(--kn-accent, #f5c518);" aria-hidden="true"></i>
      </div>
      <h2 id="advertise-heading" style="font-size: 1.8rem; font-weight: 800; margin-bottom: 0.6rem;">Advertise Across Africa</h2>
      <p style="color: var(--kn-muted, #6c757d); font-size: 1.05rem; max-width: 56ch; margin: 0 auto 0.8rem; line-height: 1.65;">
        Promote your brand inside Africa's most relevant news experiences &mdash; full-page spreads, native editorial placements, audio &amp; video sponsorships, and interactive rich-media ads.
      </p>
      <p style="color: var(--kn-muted, #6c757d); font-size: 0.95rem; max-width: 56ch; margin: 0 auto 2rem; line-height: 1.65;">
        Reach students on campuses, professionals in boardrooms, and entrepreneurs building the future. Our audience is young, mobile-first, and highly engaged. Country-specific rate cards available.
      </p>
      <div class="kn-cta-row" style="display: flex; justify-content: center; gap: 0.9rem; flex-wrap: wrap;">
        <a class="kn-btn kn-btn--primary" href="mailto:hello@kandanews.africa" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.8rem 1.8rem; font-weight: 700; border-radius: 8px; font-size: 0.95rem; background: var(--kn-accent, #f5c518); color: #0b1929; text-decoration: none;" aria-label="Contact KandaNews sales team">
          <i class="fa-solid fa-envelope"></i> Contact Sales
        </a>
        <a class="kn-btn kn-btn--ghost" href="#countries" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.8rem 1.8rem; font-weight: 700; border-radius: 8px; font-size: 0.95rem; border: 2px solid rgba(0,0,0,0.15); color: var(--kn-text, #212529); text-decoration: none; background: transparent;" aria-label="Download advertising rate card">
          <i class="fa-solid fa-file-arrow-down"></i> Download Rate Card
        </a>
      </div>
    </div>
  </div>
</section>


<!-- ===== TYPEWRITER SCRIPT ===== -->
<script>
(function(){
  var phrases = [
    'Inspiring Africa \u{1F30D}',
    'Forget Traditional Media \u2014 Go Smart \u{1F4F1}',
    'Smart Adverts, Smarter Reach \u{1F3AF}',
    'Campus Power for Students \u{1F393}',
    'Pro Playbook for Professionals \u{1F454}',
    'Startup & Hustle Stories \u{1F680}'
  ];
  var el = document.getElementById('af-phrase');
  if (!el) return;
  var idx = 0;

  function type(text, cb) {
    var chars = Array.from(text), i = 0;
    (function step() {
      if (i <= chars.length) {
        el.textContent = chars.slice(0, i).join('');
        i++;
        setTimeout(step, 65);
      } else {
        setTimeout(cb, 1200);
      }
    })();
  }

  function erase(cb) {
    var chars = Array.from(el.textContent);
    (function step() {
      if (chars.length > 0) {
        chars.pop();
        el.textContent = chars.join('');
        setTimeout(step, 15);
      } else {
        cb();
      }
    })();
  }

  function loop() {
    type(phrases[idx], function() {
      erase(function() {
        idx = (idx + 1) % phrases.length;
        setTimeout(loop, 200);
      });
    });
  }

  loop();
})();
</script>

<?php require_once __DIR__ . '/shared/components/footer.php'; ?>
