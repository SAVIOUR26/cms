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
require_once __DIR__ . '/../blog/includes/markdown.php';

// Override for hub page
$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$_country_name = 'Africa';
// Set hub flag so header knows this is the hub page
$_is_hub = true;

// Load latest 3 blog posts for the landing section
$_blog_posts = array_slice(get_all_posts(__DIR__ . '/../blog/posts'), 0, 3);

require_once __DIR__ . '/shared/components/header.php';
?>

<!-- ===== HERO ===== -->
<section class="kn-hero kn-hero--hub kn-reveal" aria-label="Hero introduction">
  <div class="kn-hero__content">

    <!-- Hero Text -->
    <div class="kn-hero__text">
      <span class="kn-hero__eyebrow">
        <i class="fa-solid fa-bolt" style="margin-right:0.35rem;"></i>
        Tap to Know. Tap to Grow.
      </span>

      <h1 class="kn-hero__title">
        Africa's First Digital<br>Flipping Newspaper
        <span id="af-phrase" aria-live="polite" style="display:block;color:var(--kn-orange);min-height:1.5em;font-size:0.72em;"></span>
      </h1>

      <p class="kn-hero__desc">
        Mobile-first, interactive news built for <strong style="color:#fff;">students</strong>, <strong style="color:#fff;">professionals</strong> &amp; <strong style="color:#fff;">entrepreneurs</strong>. Flip through rich editions, listen to audio briefings, and stay ahead&nbsp;&mdash;&nbsp;anywhere&nbsp;in&nbsp;Africa.
      </p>

      <div class="kn-hero__ctas">
        <a class="kn-btn kn-btn--primary" href="#download" aria-label="Download the KandaNews app">
          <i class="fa-solid fa-download"></i> Download the App
        </a>
        <a class="kn-btn kn-btn--ghost" href="#countries" aria-label="Explore country editions">
          <i class="fa-solid fa-earth-africa"></i> Explore Countries
        </a>
      </div>

      <!-- Trust Indicators -->
      <div style="display:flex;gap:1.8rem;color:rgba(255,255,255,0.7);font-size:0.84rem;flex-wrap:wrap;margin-top:1.8rem;" role="list" aria-label="Trust indicators">
        <span role="listitem" style="display:inline-flex;align-items:center;gap:0.4rem;">
          <i class="fa-solid fa-earth-africa" style="color:var(--kn-orange);"></i> Multi-country editions
        </span>
        <span role="listitem" style="display:inline-flex;align-items:center;gap:0.4rem;">
          <i class="fa-solid fa-bolt" style="color:var(--kn-orange);"></i> Bite-sized high impact
        </span>
        <span role="listitem" style="display:inline-flex;align-items:center;gap:0.4rem;">
          <i class="fa-solid fa-shield-halved" style="color:var(--kn-orange);"></i> Secure subscriber-only
        </span>
      </div>
    </div>

    <!-- Hero Video -->
    <div class="kn-hero__visual">
      <video
        class="kn-hero__video"
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
<section class="kn-stats kn-reveal" aria-label="Platform statistics">
  <div class="kn-stats__inner">

    <div class="kn-stats__item">
      <span class="kn-stats__number kn-counter" data-target="50">50+</span>
      <span class="kn-stats__label">Editions Published</span>
    </div>

    <div class="kn-stats__item">
      <span class="kn-stats__number kn-counter" data-target="6">6</span>
      <span class="kn-stats__label">Countries</span>
    </div>

    <div class="kn-stats__item">
      <span class="kn-stats__number kn-counter" data-target="1000">1,000+</span>
      <span class="kn-stats__label">Readers</span>
    </div>

    <div class="kn-stats__item">
      <span class="kn-stats__number kn-counter" data-target="24">24/7</span>
      <span class="kn-stats__label">Digital Access</span>
    </div>

  </div>
</section>


<!-- ===== COUNTRIES ===== -->
<section id="countries" class="kn-section kn-section--alt kn-reveal" aria-labelledby="countries-heading">
  <div class="container">
    <div class="kn-section__header">
      <h2 id="countries-heading" class="kn-section__title">
        <i class="fa-solid fa-earth-africa" style="color:var(--kn-orange);margin-right:0.4rem;"></i>
        Choose Your Country
      </h2>
      <p class="kn-section__lead">
        Subscriptions &amp; Advertising Rate Cards are managed per country edition. Pick yours to begin.
      </p>
    </div>

    <div class="kn-countries-grid" style="max-width:1100px;margin:0 auto;" role="list">

      <!-- Uganda — LIVE -->
      <article class="kn-country-card kn-country-card--live kn-reveal" role="listitem" style="cursor:pointer;" onclick="location.href='https://ug.kandanews.africa'">
        <span class="kn-country-card__status">LIVE</span>
        <div class="kn-country-card__flag" aria-hidden="true">🇺🇬</div>
        <h3 class="kn-country-card__name">Uganda</h3>
        <div class="kn-country-card__links">
          <a class="kn-country-card__link kn-country-card__link--primary" href="https://ug.kandanews.africa" aria-label="Enter KandaNews Uganda">
            <i class="fa-solid fa-arrow-right"></i> Enter
          </a>
          <a class="kn-country-card__link" href="https://ug.kandanews.africa/#subscribe" aria-label="Subscribe to KandaNews Uganda">
            Subscribe
          </a>
          <a class="kn-country-card__link" href="https://ug.kandanews.africa/#advertisers" aria-label="View KandaNews Uganda rate card">
            Rate Card
          </a>
        </div>
      </article>

      <!-- Kenya — LIVE -->
      <article class="kn-country-card kn-country-card--live kn-reveal" role="listitem" style="cursor:pointer;" onclick="location.href='https://ke.kandanews.africa'">
        <span class="kn-country-card__status">LIVE</span>
        <div class="kn-country-card__flag" aria-hidden="true">🇰🇪</div>
        <h3 class="kn-country-card__name">Kenya</h3>
        <div class="kn-country-card__links">
          <a class="kn-country-card__link kn-country-card__link--primary" href="https://ke.kandanews.africa" aria-label="Enter KandaNews Kenya">
            <i class="fa-solid fa-arrow-right"></i> Enter
          </a>
          <a class="kn-country-card__link" href="https://ke.kandanews.africa/#subscribe" aria-label="Subscribe to KandaNews Kenya">
            Subscribe
          </a>
          <a class="kn-country-card__link" href="https://ke.kandanews.africa/#advertisers" aria-label="View KandaNews Kenya rate card">
            Rate Card
          </a>
        </div>
      </article>

      <!-- Nigeria — LIVE -->
      <article class="kn-country-card kn-country-card--live kn-reveal" role="listitem" style="cursor:pointer;" onclick="location.href='https://ng.kandanews.africa'">
        <span class="kn-country-card__status">LIVE</span>
        <div class="kn-country-card__flag" aria-hidden="true">🇳🇬</div>
        <h3 class="kn-country-card__name">Nigeria</h3>
        <div class="kn-country-card__links">
          <a class="kn-country-card__link kn-country-card__link--primary" href="https://ng.kandanews.africa" aria-label="Enter KandaNews Nigeria">
            <i class="fa-solid fa-arrow-right"></i> Enter
          </a>
          <a class="kn-country-card__link" href="https://ng.kandanews.africa/#subscribe" aria-label="Subscribe to KandaNews Nigeria">
            Subscribe
          </a>
          <a class="kn-country-card__link" href="https://ng.kandanews.africa/#advertisers" aria-label="View KandaNews Nigeria rate card">
            Rate Card
          </a>
        </div>
      </article>

      <!-- South Africa — LIVE -->
      <article class="kn-country-card kn-country-card--live kn-reveal" role="listitem" style="cursor:pointer;" onclick="location.href='https://za.kandanews.africa'">
        <span class="kn-country-card__status">LIVE</span>
        <div class="kn-country-card__flag" aria-hidden="true">🇿🇦</div>
        <h3 class="kn-country-card__name">South Africa</h3>
        <div class="kn-country-card__links">
          <a class="kn-country-card__link kn-country-card__link--primary" href="https://za.kandanews.africa" aria-label="Enter KandaNews South Africa">
            <i class="fa-solid fa-arrow-right"></i> Enter
          </a>
          <a class="kn-country-card__link" href="https://za.kandanews.africa/#subscribe" aria-label="Subscribe to KandaNews South Africa">
            Subscribe
          </a>
          <a class="kn-country-card__link" href="https://za.kandanews.africa/#advertisers" aria-label="View KandaNews South Africa rate card">
            Rate Card
          </a>
        </div>
      </article>

      <!-- Ghana — COMING SOON -->
      <article class="kn-country-card kn-country-card--soon kn-reveal" role="listitem">
        <span class="kn-country-card__status">COMING SOON</span>
        <div class="kn-country-card__flag" aria-hidden="true">🇬🇭</div>
        <h3 class="kn-country-card__name">Ghana</h3>
        <div class="kn-country-card__links">
          <a class="kn-country-card__link kn-country-card__link--primary" href="mailto:hello@kandanews.africa?subject=Notify%20me%20-%20Ghana" aria-label="Get notified when KandaNews Ghana launches">
            <i class="fa-solid fa-bell"></i> Notify Me
          </a>
        </div>
      </article>

      <!-- Rwanda — COMING SOON -->
      <article class="kn-country-card kn-country-card--soon kn-reveal" role="listitem">
        <span class="kn-country-card__status">COMING SOON</span>
        <div class="kn-country-card__flag" aria-hidden="true">🇷🇼</div>
        <h3 class="kn-country-card__name">Rwanda</h3>
        <div class="kn-country-card__links">
          <a class="kn-country-card__link kn-country-card__link--primary" href="mailto:hello@kandanews.africa?subject=Notify%20me%20-%20Rwanda" aria-label="Get notified when KandaNews Rwanda launches">
            <i class="fa-solid fa-bell"></i> Notify Me
          </a>
        </div>
      </article>

    </div>

    <p class="text-center mt-3" style="color:var(--kn-muted);font-size:0.92rem;">
      <i class="fa-solid fa-globe" style="margin-right:0.3rem;"></i>
      4 editions live now &mdash; Ghana &amp; Rwanda coming soon.
    </p>
  </div>
</section>


<!-- ===== HOW IT WORKS ===== -->
<section class="kn-section kn-reveal" aria-labelledby="steps-heading">
  <div class="container">
    <div class="kn-section__header">
      <h2 id="steps-heading" class="kn-section__title">How KandaNews Works</h2>
      <p class="kn-section__lead">Three simple steps to Africa's smartest news experience.</p>
    </div>

    <div class="kn-steps" role="list">

      <div class="kn-step kn-reveal" role="listitem">
        <div class="kn-step__circle" aria-hidden="true">
          <span class="kn-step__icon"><i class="fa-solid fa-mobile-screen"></i></span>
        </div>
        <h3 class="kn-step__title">Download the App</h3>
        <p class="kn-step__desc">Get KandaNews on Android, iOS, Windows, Mac or Linux.</p>
      </div>

      <div class="kn-step kn-reveal" role="listitem">
        <div class="kn-step__circle" aria-hidden="true">
          <span class="kn-step__icon"><i class="fa-solid fa-credit-card"></i></span>
        </div>
        <h3 class="kn-step__title">Choose a Plan</h3>
        <p class="kn-step__desc">Pick daily, weekly or monthly &mdash; pay with mobile money or card.</p>
      </div>

      <div class="kn-step kn-reveal" role="listitem">
        <div class="kn-step__circle" aria-hidden="true">
          <span class="kn-step__icon"><i class="fa-solid fa-book-open"></i></span>
        </div>
        <h3 class="kn-step__title">Read &amp; Grow</h3>
        <p class="kn-step__desc">Flip through interactive editions, listen to audio, watch explainers.</p>
      </div>

    </div>
  </div>
</section>


<!-- ===== REEL / SHOWCASE ===== -->
<section id="reel" class="kn-section kn-section--alt kn-reveal" aria-labelledby="reel-heading">
  <div class="container">
    <div class="kn-section__header">
      <h2 id="reel-heading" class="kn-section__title">Experience the Future of News</h2>
      <p class="kn-section__lead">A glimpse at the stories, formats and visual journalism inside every edition.</p>
    </div>
  </div>
  <div class="kn-reel" aria-label="Scrolling showcase of KandaNews content">
    <ul class="kn-reel__track">
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
      <li class="kn-reel__item">
        <img
          class="kn-reel__img"
          src="<?php echo h($item['img']); ?>"
          alt="<?php echo h($item['eyebrow'] . ': ' . $item['title']); ?>"
          loading="lazy"
        >
        <div class="kn-reel__meta">
          <div class="kn-reel__eyebrow"><?php echo h($item['eyebrow']); ?></div>
          <h3 class="kn-reel__title"><?php echo h($item['title']); ?></h3>
        </div>
      </li>
      <?php endforeach; endfor; ?>
    </ul>
  </div>
</section>


<!-- ===== FROM THE BLOG ===== -->
<?php if (!empty($_blog_posts)): ?>
<section class="kn-section kn-reveal" aria-labelledby="blog-heading">
  <div class="container">
    <div class="kn-section__header">
      <h2 id="blog-heading" class="kn-section__title">
        <i class="fa-solid fa-pen-nib" style="color:var(--kn-orange);margin-right:0.4rem;"></i>
        From the Blog
      </h2>
      <p class="kn-section__lead">News, insights and updates from our team across the continent.</p>
    </div>

    <div class="kn-posts-grid" role="list">
      <?php foreach ($_blog_posts as $post): ?>
        <article class="kn-post-card kn-reveal" role="listitem">

          <?php if ($post['image']): ?>
            <a href="/blog/post.php?slug=<?php echo urlencode($post['slug']); ?>" class="kn-post-card__img-link" aria-hidden="true" tabindex="-1">
              <img
                class="kn-post-card__img"
                src="<?php echo h($post['image']); ?>"
                alt="<?php echo h($post['title']); ?>"
                loading="lazy"
              >
            </a>
          <?php endif; ?>

          <div class="kn-post-card__body">
            <div class="kn-post-card__meta">
              <time datetime="<?php echo h($post['date']); ?>"><?php echo format_date($post['date']); ?></time>
              <?php if ($post['author']): ?>
                <span>&middot;</span>
                <span><?php echo h($post['author']); ?></span>
              <?php endif; ?>
              <?php if ($post['tags']): ?>
                <span>&middot;</span>
                <span class="kn-post-card__tag"><?php echo h($post['tags']); ?></span>
              <?php endif; ?>
            </div>

            <h3 class="kn-post-card__title">
              <a href="/blog/post.php?slug=<?php echo urlencode($post['slug']); ?>">
                <?php echo h($post['title']); ?>
              </a>
            </h3>

            <?php if ($post['excerpt']): ?>
              <p class="kn-post-card__excerpt"><?php echo h($post['excerpt']); ?></p>
            <?php endif; ?>

            <a href="/blog/post.php?slug=<?php echo urlencode($post['slug']); ?>" class="kn-post-card__read">
              Read article <i class="fa-solid fa-arrow-right"></i>
            </a>
          </div>

        </article>
      <?php endforeach; ?>
    </div>

    <div class="text-center" style="margin-top:2.5rem;">
      <a href="/blog/" class="kn-btn kn-btn--outline" aria-label="View all blog posts">
        <i class="fa-solid fa-book-open"></i> View all posts
      </a>
    </div>

  </div>
</section>
<?php endif; ?>


<!-- ===== DOWNLOAD APP ===== -->
<section id="download" class="kn-download kn-download--centered kn-reveal" aria-labelledby="download-heading">
  <div class="kn-download__inner">
    <div class="kn-download__text">
      <h2 id="download-heading">
        <i class="fa-solid fa-download" style="color:var(--kn-orange);margin-right:0.4rem;"></i>
        Get KandaNews on Every Device
      </h2>
      <p>Whether you read on your phone during your commute, a tablet at lunch, or a laptop at work &mdash; KandaNews goes everywhere you do.</p>

      <div class="kn-store-row" role="list" aria-label="App download options">
        <span class="kn-store-btn kn-btn--disabled" role="listitem" aria-label="Google Play coming soon">
          <i class="fa-brands fa-google-play"></i> Google Play
        </span>
        <span class="kn-store-btn kn-btn--disabled" role="listitem" aria-label="App Store coming soon">
          <i class="fa-brands fa-apple"></i> App Store
        </span>
        <span class="kn-store-btn kn-btn--disabled" role="listitem" aria-label="Windows coming soon">
          <i class="fa-brands fa-windows"></i> Windows
        </span>
        <span class="kn-store-btn kn-btn--disabled" role="listitem" aria-label="macOS coming soon">
          <i class="fa-brands fa-apple"></i> macOS
        </span>
        <span class="kn-store-btn kn-btn--disabled" role="listitem" aria-label="Linux coming soon">
          <i class="fa-brands fa-linux"></i> Linux
        </span>
      </div>

      <p style="color:rgba(255,255,255,0.45);font-size:0.85rem;font-style:italic;margin-top:1rem;">
        <i class="fa-solid fa-clock" style="margin-right:0.3rem;"></i>
        Apps launching soon &mdash; stay tuned!
      </p>
    </div>
  </div>
</section>


<!-- ===== ADVERTISE ===== -->
<section class="kn-section kn-reveal" aria-labelledby="advertise-heading">
  <div class="container">
    <div class="kn-advertise">
      <h2 id="advertise-heading">Advertise Across Africa</h2>
      <p>
        Promote your brand inside Africa's most relevant news experiences &mdash; full-page spreads, native editorial placements, audio &amp; video sponsorships, and interactive rich-media ads.
        Reach students on campuses, professionals in boardrooms, and entrepreneurs building the future.
      </p>
      <div class="kn-cta-row">
        <a class="kn-btn kn-btn--primary" href="mailto:hello@kandanews.africa" aria-label="Contact KandaNews sales team">
          <i class="fa-solid fa-envelope"></i> Contact Sales
        </a>
        <a class="kn-btn kn-btn--ghost" href="#countries" aria-label="Download advertising rate card">
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
