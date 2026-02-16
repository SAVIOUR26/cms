<?php
/*
Template Name: Kanda — Africa Hub (Home)
Template Post Type: page
*/
if (!defined('ABSPATH')) exit;
get_header();
?>

<style>
    /* Right pane still controls the box */
    .af-hero__right{
    position:relative;
    border-radius:16px;
    overflow:hidden;
    min-height:unset;          /* let content define height */
    }

    /* Frame wrapper */
    .af-vidframe{
    position:relative;
    display:grid;
    place-items:center;
    padding:0;                 /* no inner padding for max width */
    background:transparent;
    }

    /* 16:9 video that shows the whole frame */
    .af-vidframe__el{
    width:100%;
    aspect-ratio:16/9;         /* landscape */
    height:auto;               /* derive height from width */
    object-fit:contain;        /* show entire video (no crop) */
    object-position:center;
    background:#000;           /* letterbox where needed */
    border-radius:14px;
    border:1px solid rgba(0,0,0,.1);
    box-shadow:0 10px 28px rgba(0,0,0,.12);
    }

    /* Make sure the right column doesn’t force a tall min-height */
    @media (min-width:981px){
    .af-hero__grid{
        grid-template-columns:1.1fr .9fr; /* as you had */
        align-items:center;
    }
    }
    @media (max-width:980px){
    .af-hero__right{ margin-top:12px; }
    }


</style>

<section class="af-hero">
  <div class="container af-hero__grid">
    <div class="af-hero__left">
      <span class="badge">Tap to Know. Tap to Grow.</span>
      <h1 class="af-title">
        Africa’s First Digital Flipping Newspaper
        <span class="af-phrases" aria-live="polite" aria-atomic="true">
          <span id="af-phrase"></span><span class="af-caret" aria-hidden="true"></span>
        </span>
      </h1>

      <p class="af-sub">Made for students, professionals & entrepreneurs — fast, visual, and delivered on WhatsApp.</p>
      <div class="cta">
        <a class="btn primary" href="#countries">Choose Country</a>
        <a class="btn" href="#reel">How it feels</a>
      </div>
      <div class="af-stats">
        <div class="af-stat"><i class="fa-solid fa-earth-africa"></i> Multi-country editions</div>
        <div class="af-stat"><i class="fa-solid fa-bolt"></i> Bite-sized, high impact</div>
        <div class="af-stat"><i class="fa-solid fa-shield-halved"></i> Secure, subscriber-only</div>
      </div>
    </div>

    <!-- RIGHT: portrait autoplay video replaces GIF/portraits -->
    <aside class="af-hero__right" aria-label="Showcase">
      <div class="af-vidframe">
        <video
          id="af-hero-video"
          class="af-vidframe__el"
          src="<?php echo esc_url( get_template_directory_uri().'/assets/video/future-of-news.mp4' ); ?>"
          autoplay muted playsinline loop preload="auto"
          poster="<?php echo esc_url( get_template_directory_uri().'/assets/video/future-of-news-poster.jpg' ); ?>">
        </video>
      </div>
    </aside>
  </div>
</section>

<section id="countries" class="countries">
  <div class="container">
    <h2 class="af-h2"><i class="fa-solid fa-earth-africa"></i> Choose Your Country</h2>
    <p class="af-muted">Subscriptions & Advertising Rate Cards are managed per country edition.</p>
    <div class="grid grid-4" role="list" aria-label="Country editions">
      <?php
      $countries = [
        ['Uganda','🇺🇬','https://ug.kandanews.africa'],
        ['Kenya','🇰🇪','https://ke.kandanews.africa'],
        ['Nigeria','🇳🇬','https://ng.kandanews.africa'],
        ['South Africa','🇿🇦','https://za.kandanews.africa'],
      ];
      foreach($countries as [$name,$flag,$url]): ?>
        <article class="country" role="listitem" onclick="location.href='<?php echo esc_url($url); ?>'">
          <div class="flag" aria-hidden="true"><?php echo esc_html($flag); ?></div>
          <div class="name"><?php echo esc_html($name); ?></div>
          <div class="links">
            <a class="hot" href="<?php echo esc_url($url); ?>">Enter</a>
            <a href="<?php echo esc_url($url.'/#rates'); ?>">Rate Card</a>
            <a href="<?php echo esc_url($url.'/#subscribe'); ?>">Subscribe</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <p class="af-muted" style="margin-top:10px">More countries launching soon.</p>
  </div>
</section>

<!-- CONTINUOUS REEL WITH HEADLINES (above footer) -->
<?php
// Update these items to your real assets/headlines; duplicate set is rendered automatically.
$reel_items = [
  ['img'=>'assets/img/portrait-1.jpg',  'eyebrow'=>'Interview', 'title'=>'How a campus startup scaled past borders'],
  ['img'=>'assets/img/portrait-2.jpg',  'eyebrow'=>'Explainer', 'title'=>'Why mobile-first news wins attention'],
  ['img'=>'assets/img/portrait-3.jpg',  'eyebrow'=>'Feature',   'title'=>'Creators shaping tomorrow’s Africa'],
  ['img'=>'assets/img/flip-sample1.png','eyebrow'=>'Flipbook',  'title'=>'5 charts on youth employment'],
  ['img'=>'assets/img/flip-sample2.png','eyebrow'=>'Marketplace','title'=>'Smart ads that actually help'],
  ['img'=>'assets/img/flip-sample3.png','eyebrow'=>'Audio',     'title'=>'The 3-minute briefing'],
];
?>
<section id="reel" class="af-reel" aria-label="KandaNews showcase">
  <div class="container">
    <h2 class="af-h2" style="margin-bottom:10px">Experience the future of news</h2>
  </div>

  <div class="af-reel__mask">
    <ul class="af-reel__track">
      <?php
      // We print two sets for seamless loop
      for ($loop=0; $loop<2; $loop++):
        foreach ($reel_items as $item):
          $src = get_template_directory_uri().'/'.ltrim($item['img'],'/');
      ?>
        <li class="af-reel__item">
          <div class="af-reel__media">
            <img class="af-reel__img" src="<?php echo esc_url($src); ?>" alt="">
          </div>
          <div class="af-reel__meta">
            <div class="af-reel__eyebrow"><?php echo esc_html($item['eyebrow']); ?></div>
            <h3 class="af-reel__title"><?php echo esc_html($item['title']); ?></h3>
          </div>
        </li>

      <?php
        endforeach;
      endfor;
      ?>
    </ul>
  </div>
</section>

<script>
  // Tiny autoplay nudge for iOS
  document.addEventListener('DOMContentLoaded', function(){
    var v = document.getElementById('af-hero-video');
    if (v){ var p = v.play(); if (p && p.catch){ p.catch(()=>{}); } }
  });
</script>

<?php get_footer(); ?>
