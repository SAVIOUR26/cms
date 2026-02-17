<?php
/*
Template Name: Kanda — Uganda Landing (Hero: Huge + Glass)
Template Post Type: page
*/
if (!defined('ABSPATH')) exit;

/**
 * Brand config (override via kxn_brand_config())
 */
$cfg = function_exists('kxn_brand_config') ? kxn_brand_config() : [
    'brand' => 'KandaNews',
    'country' => 'Uganda',
    'flag' => '🇺🇬',
    'email' => 'hello@ug.kandanews.africa',
    'links' => [
        'switch_country' => 'https://kandanews.africa/#countries',
        'login' => home_url('/login/'),
        'about' => home_url('/about/'),
        'advertise' => home_url('/media/KANDA-NEWS-ADVERTISING-RATE-CARD.pdf'),
        'contact' => 'mailto:hello@ug.kandanews.africa'
    ],
];

$brand = $cfg['brand'] ?? 'KandaNews';
$country = $cfg['country'] ?? 'Country';
$flag = $cfg['flag'] ?? '🌍';
$hubUrl = $cfg['links']['switch_country'] ?? home_url('/');
$brandEmail = $cfg['email'] ?? 'hello@kandanews.africa';

$video_url = get_stylesheet_directory_uri() . '/assets/video/launch-hero.mp4';
$video_poster = get_stylesheet_directory_uri() . '/assets/video/launch-hero-poster.gif';

get_header();
?>

<section class="kn-hero kn-hero--huge" aria-label="KandaNews Uganda Hero">
  <div class="kn-media" aria-hidden="true">
    <?php if ($video_url): ?>
      <video class="kn-video" poster="<?php echo esc_attr($video_poster); ?>" playsinline muted autoplay loop preload="metadata" role="img" aria-label="Background visual">
        <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
      </video>
    <?php else: ?>
      <div class="kn-fallback" role="img" aria-label="KandaNews background"></div>
    <?php endif; ?>
  </div>

  <!-- Frosted glass content panel centered -->
  <div class="kn-overlay">
    <div class="kn-glass container" role="region" aria-labelledby="kn-hero-title">
      <div class="kn-glass__inner">
        <div class="kn-top">
          <span class="kn-badge">Tap to Know. Tap to Grow.</span>
        </div>

        <h1 class="kn-title" id="kn-hero-title">
          <span class="kn-brand"><?php echo esc_html($brand); ?></span>
          <span class="kn-country"><?php echo esc_html($country); ?></span>
          <span class="kn-flag" aria-hidden="true"><?php echo esc_html($flag); ?></span>
        </h1>

        <p class="kn-tagline">
          The <strong>Future of News</strong> is already here — , shaping what news feels like.
        </p>

        <p class="kn-sub">
          Personal, local and pan-Africa coverage for students, professionals and entrepreneurs — delivered fast, mobile-first, and built to help you grow.
        </p>

        <div class="kn-hero-ctas" role="navigation" aria-label="Hero actions">
          <a class="kn-btn kn-btn--primary" href="<?php echo esc_url(add_query_arg(['next' => '/user-dashboard/'], $cfg['links']['login'])); ?>">
            Subscribe & Join
          </a>
          <a class="kn-btn kn-btn--ghost" href="<?php echo esc_url($hubUrl); ?>">
            Visit Africa Hub
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<main class="kn-wrap container" aria-label="KandaNews Uganda landing">
  <!-- keep the rest of your sections unchanged (pricing, features, advertisers, apps) -->
  <!-- SUBSCRIPTION / PRICING -->
  <section id="subscribe" class="kn-panel kn-pricing" aria-labelledby="pricing-title">
    <h2 id="pricing-title">Choose Your Plan</h2>
    <p class="kn-lead">Flexible access options — pick the plan that fits your rhythm.</p>

    <div class="kn-pricing-grid" role="list">
      <article class="kn-card" role="listitem" aria-label="Daily plan">
        <h3>Daily</h3>
        <p class="kn-price"><strong>UGX 500</strong> / day</p>
        <a href="<?php echo esc_url($cfg['links']['login']); ?>" class="kn-btn kn-btn--primary">Subscribe</a>
      </article>

      <article class="kn-card popular" role="listitem" aria-label="Weekly plan (most popular)">
        <span class="kn-badge-popular">Most Popular</span>
        <h3>Weekly</h3>
        <p class="kn-price"><strong>UGX 2,500</strong> / week</p>
        <a href="<?php echo esc_url($cfg['links']['login']); ?>" class="kn-btn kn-btn--primary">Subscribe</a>
      </article>

      <article class="kn-card" role="listitem" aria-label="Monthly plan">
        <h3>Monthly</h3>
        <p class="kn-price"><strong>UGX 7,500</strong> / month</p>
        <a href="<?php echo esc_url($cfg['links']['login']); ?>" class="kn-btn kn-btn--primary">Subscribe</a>
      </article>
    </div>
  </section>

  <!-- WHY SUBSCRIBE -->
  <section class="kn-features" aria-labelledby="why-title">
    <h2 id="why-title">Why Subscribe?</h2>
    <p class="kn-lead">More than headlines — tools for growth, connection and opportunity across Uganda and the region.</p>

    <ul class="kn-feature-list" role="list">
      <li class="kn-feature-item" role="listitem">📖 Daily interactive flipbook editions</li>
      <li class="kn-feature-item" role="listitem">🎧 Audio interviews & storytelling</li>
      <li class="kn-feature-item" role="listitem">📹 Short video explainers and features</li>
      <li class="kn-feature-item" role="listitem">🛒 Smart marketplace & campaign features</li>
      <li class="kn-feature-item" role="listitem">💡 Career & student-focused resources</li>
      <li class="kn-feature-item" role="listitem">🌍 Pan-Africa perspective, locally delivered</li>
    </ul>
  </section>

  <!-- ADVERTISERS / PARTNERS -->
  <section id="advertisers" class="kn-panel" aria-labelledby="ads-title">
    <h2 id="ads-title">Advertise / Partner with Us</h2>
    <p>Promote your brand inside Uganda’s most relevant news experiences — full pages, native placements, audio & video sponsorships.</p>

    <div class="kn-cta-row">
      <a class="kn-btn kn-btn--primary" href="<?php echo esc_url($cfg['links']['advertise']); ?>" target="_blank" rel="noopener">
        📥 Download Rate Card
      </a>

      <a class="kn-btn kn-btn--outline" href="<?php echo esc_url($cfg['links']['contact']); ?>">
        ✉️ Contact Sales
      </a>
    </div>
  </section>

  <!-- APPS COMING SOON -->
  <section id="apps" class="kn-apps" aria-labelledby="apps-title">
    <h2 id="apps-title">Apps Coming Soon</h2>
    <p class="kn-lead">Our apps are arriving on Android and iOS — a full KandaNews experience for your pocket.</p>

    <div class="kn-store-row" role="navigation" aria-label="App stores">
      <a class="kn-store-btn" href="#" aria-label="Google Play coming soon"><i class="fa-brands fa-google-play" aria-hidden="true"></i> Google Play</a>
      <a class="kn-store-btn" href="#" aria-label="App Store coming soon"><i class="fa-brands fa-apple" aria-hidden="true"></i> App Store</a>
    </div>
  </section>

</main>

<style>
  /* --- Hero: Huge + Glass styles --- */
  :root{
    --kn-accent:#f05a1a;
    --kn-bg-dark:#061326;
    --kn-contrast:#ffffff;
    --kn-muted:#cbd5e1;
    --kn-radius:20px;
    --container-max:1200px;
    font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
  }

  /* Hero: full-height cinematic */
  .kn-hero--huge{ position:relative; min-height:92vh; display:flex; align-items:center; justify-content:center; overflow:hidden; background:var(--kn-bg-dark); color:var(--kn-contrast); }
  .kn-media{ position:absolute; inset:0; z-index:0; overflow:hidden; }
  .kn-video{ width:100%; height:100%; object-fit:cover; display:block; filter:brightness(.55) contrast(.98) saturate(.95); transform:scale(1.02); }
  .kn-fallback{ width:100%; height:100%; background:linear-gradient(135deg,#052038,#0b2c45); }

  /* dark gradient overlay for extra depth */
  .kn-hero--huge::before{
    content:""; position:absolute; inset:0; z-index:1;
    background:linear-gradient(180deg, rgba(3,7,18,0.48), rgba(3,7,18,0.62));
    pointer-events:none;
  }

  .kn-overlay{ position:relative; z-index:3; width:100%; padding:4rem 1rem; display:flex; justify-content:center; align-items:center; }

  /* glass panel */
  .kn-glass{ max-width:1100px; width:100%; padding:2rem; border-radius:20px; background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03)); box-shadow: 0 20px 60px rgba(3,8,20,0.6); border: 1px solid rgba(255,255,255,0.07); backdrop-filter: blur(10px) saturate(.95); -webkit-backdrop-filter: blur(10px); }
  .kn-glass__inner{ text-align:center; padding:1rem 0; color:var(--kn-contrast); }

  .kn-top{ display:flex; align-items:center; justify-content:center; margin-bottom:0.6rem; }
  .kn-badge{ display:inline-block; background: rgba(255,255,255,0.08); color:var(--kn-contrast); padding:.5rem 1rem; border-radius:999px; font-weight:800; letter-spacing:0.02em; }

  /* title — huge */
  .kn-title{ font-size:3.1rem; line-height:1.02; margin:0.35rem 0 0; font-weight:900; letter-spacing:-0.01em; display:flex; align-items:center; justify-content:center; gap:0.6rem; flex-wrap:wrap; }
  .kn-brand{ color:#fff; display:inline-block; }
  .kn-country{ color:rgba(255,255,255,0.95); font-weight:800; }
  .kn-flag{ font-size:1.2rem; margin-left:0.25rem; }

  .kn-tagline{ font-size:1.35rem; margin:0.6rem auto 0; color:var(--kn-muted); max-width:78ch; }
  .kn-sub{ margin-top:0.5rem; color:rgba(255,255,255,0.9); max-width:64ch; }

  .kn-hero-ctas{ display:inline-flex; gap:0.85rem; margin-top:1.2rem; justify-content:center; flex-wrap:wrap; }

  /* buttons */
  .kn-btn{ display:inline-flex; align-items:center; gap:.6rem; padding:.95rem 1.15rem; border-radius:12px; font-weight:800; text-decoration:none; border:1px solid rgba(255,255,255,0.06); transition: transform .14s ease, box-shadow .14s ease; }
  .kn-btn:focus{ outline:3px solid rgba(240,90,26,0.12); transform:translateY(-3px); }
  .kn-btn--primary{ background: linear-gradient(90deg,var(--kn-accent), #ff7b48); color:#fff; box-shadow: 0 12px 36px rgba(3,8,20,0.55); border:none; }
  .kn-btn--ghost{ background: rgba(255,255,255,0.06); color:#fff; border:1px solid rgba(255,255,255,0.1); }

  /* bring the pricing upward for overlap effect */
  .kn-pricing{ margin-top:-3.4rem; }

  /* rest of page kept compact and legible */
  .kn-wrap{ padding:2rem 0 4rem; }
  .kn-panel{ background:#fff; border-radius:16px; padding:1.25rem; margin:1.25rem 0; box-shadow:0 10px 30px rgba(6,12,20,0.06); }

  /* smaller heading styles below hero */
  h2{ margin:0 0 .5rem; font-size:1.5rem; color:#07122a; font-weight:800; }
  .kn-lead{ color:#4b5563; margin-bottom:1rem; }

  /* pricing grid / cards */
  .kn-pricing-grid{ display:flex; gap:1rem; flex-wrap:wrap; justify-content:center; }
  .kn-card{ flex:1 1 240px; max-width:320px; padding:1.25rem; border-radius:12px; text-align:center; background:#fff; border:1px solid #eef2f7; }
  .kn-card.popular{ border:2px solid var(--kn-accent); box-shadow:0 12px 28px rgba(240,90,26,0.08); }

  .kn-feature-list{ list-style:none; padding:0; display:grid; grid-template-columns: repeat(auto-fit,minmax(220px,1fr)); gap:0.85rem; margin-top:1rem; }
  .kn-feature-item{ background:#fff; padding:1rem; border-radius:12px; border:1px solid #eef2f7; font-weight:700; color:#07122a; }

  .kn-cta-row{ display:flex; gap:.75rem; justify-content:center; margin-top:1rem; flex-wrap:wrap }
  .kn-store-row{ display:flex; gap:1rem; justify-content:center; margin-top:0.75rem; flex-wrap:wrap }
  .kn-store-btn{ display:inline-flex; align-items:center; gap:.6rem; padding:.6rem 1rem; border-radius:10px; border:1px solid #e3e8ef; background:#fff; color:#07122a; text-decoration:none; font-weight:700 }

  /* responsive */
  @media (max-width:1100px){
    .kn-title{ font-size:2.6rem; }
    .kn-glass{ padding:1.5rem; }
  }
  @media (max-width:720px){
    .kn-title{ font-size:2.1rem; }
    .kn-tagline{ font-size:1.05rem; }
    .kn-hero--huge{ min-height:82vh; }
    .kn-pricing{ margin-top:-2.4rem; }
    .kn-glass{ border-radius:14px; padding:1.1rem; }
  }
  @media (max-width:420px){
    .kn-title{ font-size:1.7rem; }
    .kn-sub{ font-size:0.95rem; }
    .kn-hero-ctas{ flex-direction:column; gap:.6rem; }
  }

  /* reduced motion preference */
  @media (prefers-reduced-motion: reduce){
    .kn-video, .kn-btn{ transition:none !important; }
  }
</style>

<script>
(function(){
  'use strict';
  // autoplay attempt
  var v = document.querySelector('.kn-video');
  if (v && v.play) {
    var p = v.play();
    if (p && p.catch) p.catch(function(){ /* autoplay blocked */ });
  }
})();
</script>

<?php get_footer(); ?>
