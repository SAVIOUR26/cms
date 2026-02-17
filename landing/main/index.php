<?php
/**
 * KandaNews Africa — Main Hub
 * kandanews.africa
 */
$COUNTRY_CODE = '';
$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$COUNTRIES = [];
require_once __DIR__ . '/../shared/includes/helpers.php';
require_once __DIR__ . '/../shared/includes/country-config.php';

// Override for hub page
$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$_country_name = 'Africa';

require_once __DIR__ . '/../shared/components/header.php';
?>

<!-- HERO: Two column - text left, video right -->
<section class="kn-section" style="background: linear-gradient(135deg, var(--kn-primary) 0%, #0b1929 100%); color: #fff; padding: 5rem 0 4rem;">
  <div class="container" style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 3rem; align-items: center;">
    <div>
      <span class="kn-glass__badge">Tap to Know. Tap to Grow.</span>
      <h1 style="font-size: 2.8rem; font-weight: 900; line-height: 1.05; margin: 1rem 0 0.5rem; color: #fff;">
        Africa's First Digital<br>Flipping Newspaper
        <span id="af-phrase" style="display: block; color: var(--kn-accent); min-height: 1.4em;"></span>
      </h1>
      <p style="color: rgba(255,255,255,0.8); font-size: 1.1rem; max-width: 52ch; margin-bottom: 1.5rem;">
        Made for students, professionals &amp; entrepreneurs — fast, visual, and delivered mobile-first.
      </p>
      <div style="display: flex; gap: 0.8rem; flex-wrap: wrap;">
        <a class="kn-btn kn-btn--primary" href="#countries">Choose Country</a>
        <a class="kn-btn kn-btn--ghost" href="#reel">How It Feels</a>
      </div>
      <div style="display: flex; gap: 1.5rem; margin-top: 1.5rem; color: rgba(255,255,255,0.7); font-size: 0.85rem; flex-wrap: wrap;">
        <span><i class="fa-solid fa-earth-africa"></i> Multi-country editions</span>
        <span><i class="fa-solid fa-bolt"></i> Bite-sized, high impact</span>
        <span><i class="fa-solid fa-shield-halved"></i> Secure, subscriber-only</span>
      </div>
    </div>
    <div style="border-radius: 16px; overflow: hidden;">
      <video class="kn-hero__video" style="width: 100%; height: auto; aspect-ratio: 16/9; object-fit: contain; border-radius: 14px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 10px 28px rgba(0,0,0,0.12); filter: none; transform: none;" autoplay muted playsinline loop preload="auto">
        <source src="/shared/assets/video/launch-hero.mp4" type="video/mp4">
      </video>
    </div>
  </div>
</section>

<!-- COUNTRIES -->
<section id="countries" class="kn-section kn-section--alt kn-reveal">
  <div class="container">
    <h2 class="kn-section__title text-center"><i class="fa-solid fa-earth-africa"></i> Choose Your Country</h2>
    <p class="kn-section__lead text-center" style="margin-left: auto; margin-right: auto;">Subscriptions &amp; Advertising Rate Cards are managed per country edition.</p>
    <div class="kn-countries-grid">
      <?php
      $country_links = [
          ['Uganda',       '🇺🇬', 'https://ug.kandanews.africa'],
          ['Kenya',        '🇰🇪', 'https://ke.kandanews.africa'],
          ['Nigeria',      '🇳🇬', 'https://ng.kandanews.africa'],
          ['South Africa', '🇿🇦', 'https://za.kandanews.africa'],
      ];
      foreach ($country_links as [$name, $flag, $url]):
      ?>
      <article class="kn-country-card kn-reveal" onclick="location.href='<?php echo h($url); ?>'">
        <div class="kn-country-card__flag"><?php echo $flag; ?></div>
        <div class="kn-country-card__name"><?php echo h($name); ?></div>
        <div class="kn-country-card__links">
          <a class="kn-country-card__link kn-country-card__link--hot" href="<?php echo h($url); ?>">Enter</a>
          <a class="kn-country-card__link" href="<?php echo h($url); ?>/#subscribe">Subscribe</a>
          <a class="kn-country-card__link" href="<?php echo h($url); ?>/#advertisers">Rate Card</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <p class="text-center mt-3" style="color: var(--kn-muted);">More countries launching soon.</p>
  </div>
</section>

<!-- REEL / SHOWCASE -->
<section id="reel" class="kn-section kn-reveal">
  <div class="container">
    <h2 class="kn-section__title text-center">Experience the Future of News</h2>
  </div>
  <div class="kn-reel">
    <ul class="kn-reel__track">
      <?php
      $reel_items = [
        ['img' => '/shared/assets/img/portrait-1.jpg',    'eyebrow' => 'Interview',   'title' => 'How a campus startup scaled past borders'],
        ['img' => '/shared/assets/img/portrait-2.jpg',    'eyebrow' => 'Explainer',   'title' => 'Why mobile-first news wins attention'],
        ['img' => '/shared/assets/img/portrait-3.jpg',    'eyebrow' => 'Feature',     'title' => 'Creators shaping tomorrow\'s Africa'],
        ['img' => '/shared/assets/img/flip-sample1.png',  'eyebrow' => 'Flipbook',    'title' => '5 charts on youth employment'],
        ['img' => '/shared/assets/img/flip-sample2.png',  'eyebrow' => 'Marketplace', 'title' => 'Smart ads that actually help'],
        ['img' => '/shared/assets/img/flip-sample3.png',  'eyebrow' => 'Audio',       'title' => 'The 3-minute briefing'],
      ];
      // Double the items for seamless loop
      for ($loop = 0; $loop < 2; $loop++):
        foreach ($reel_items as $item):
      ?>
      <li class="kn-reel__item">
        <div><img class="kn-reel__img" src="<?php echo h($item['img']); ?>" alt="" loading="lazy"></div>
        <div class="kn-reel__meta">
          <div class="kn-reel__eyebrow"><?php echo h($item['eyebrow']); ?></div>
          <h3 class="kn-reel__title"><?php echo h($item['title']); ?></h3>
        </div>
      </li>
      <?php endforeach; endfor; ?>
    </ul>
  </div>
</section>

<!-- ADVERTISE -->
<section class="kn-section kn-reveal">
  <div class="container">
    <div class="kn-advertise">
      <h2>Advertise Across Africa</h2>
      <p>Promote your brand inside Africa's most relevant news experiences — full pages, native placements, audio &amp; video sponsorships.</p>
      <div class="kn-cta-row">
        <a class="kn-btn kn-btn--primary" href="mailto:hello@kandanews.africa">Contact Sales</a>
      </div>
    </div>
  </div>
</section>

<!-- TYPEWRITER SCRIPT -->
<script>
(function(){
  var phrases = [
    'Inspiring Africa \u{1F30D}',
    'Forget Traditional Media \u2014 Go Smart \u{1F4F2}',
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
      } else { cb(); }
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

<?php require_once __DIR__ . '/../shared/components/footer.php'; ?>
