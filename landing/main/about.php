<?php
/**
 * KandaNews Africa — About Us
 * kandanews.africa/about.php
 */
$COUNTRY_CODE = '';
$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$COUNTRIES = [];
require_once __DIR__ . '/shared/includes/helpers.php';
require_once __DIR__ . '/shared/includes/country-config.php';

$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$_is_hub = true;
$page_title  = 'About Us — KandaNews Africa';
$page_description = 'Learn how KandaNews is building Africa's first digital flipping newspaper — our mission, story, team, and vision for the future of news on the continent.';

require_once __DIR__ . '/shared/components/header.php';
?>

<!-- ===== ABOUT HERO ===== -->
<section class="kn-page-hero kn-reveal" style="
    background: linear-gradient(135deg, var(--kn-navy-dark) 0%, var(--kn-navy) 60%, #243554 100%);
    padding: 7rem 1.5rem 5rem;
    text-align: center;
    position: relative;
    overflow: hidden;
">
  <!-- Background decoration -->
  <div aria-hidden="true" style="
      position:absolute;top:-60px;right:-80px;
      width:420px;height:420px;border-radius:50%;
      background:radial-gradient(circle,rgba(240,90,26,.18) 0%,transparent 70%);
      pointer-events:none;">
  </div>
  <div aria-hidden="true" style="
      position:absolute;bottom:-80px;left:-60px;
      width:340px;height:340px;border-radius:50%;
      background:radial-gradient(circle,rgba(240,90,26,.12) 0%,transparent 70%);
      pointer-events:none;">
  </div>

  <div class="container" style="position:relative;z-index:1;">
    <span style="
        display:inline-flex;align-items:center;gap:.45rem;
        background:rgba(240,90,26,.15);border:1px solid rgba(240,90,26,.35);
        color:var(--kn-orange);font-size:.82rem;font-weight:700;letter-spacing:.07em;
        text-transform:uppercase;padding:.45rem 1.1rem;border-radius:999px;
        margin-bottom:1.6rem;
    ">
        <i class="fa-solid fa-earth-africa"></i> Our Story
    </span>
    <h1 style="
        font-size:clamp(2.2rem,5vw,3.6rem);font-weight:900;color:#fff;
        line-height:1.12;margin-bottom:1.4rem;letter-spacing:-.02em;
    ">
        Redefining How Africa<br>
        <span style="color:var(--kn-orange);">Reads, Thinks &amp; Grows</span>
    </h1>
    <p style="
        font-size:1.15rem;color:rgba(255,255,255,.72);max-width:640px;
        margin:0 auto 2.4rem;line-height:1.7;
    ">
        We are building Africa's first digital flipping newspaper — a platform where students,
        professionals and entrepreneurs get the insight they need to move forward.
    </p>
    <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
        <a href="#mission" class="kn-btn kn-btn--primary">
            <i class="fa-solid fa-arrow-down"></i> Our Mission
        </a>
        <a href="mailto:hello@kandanews.africa" class="kn-btn kn-btn--ghost">
            <i class="fa-solid fa-envelope"></i> Get in Touch
        </a>
    </div>
  </div>
</section>


<!-- ===== QUICK STATS ===== -->
<section class="kn-stats kn-reveal" aria-label="Company at a glance">
  <div class="kn-stats__inner">
    <div class="kn-stats__item">
      <span class="kn-stats__number">2023</span>
      <span class="kn-stats__label">Year Founded</span>
    </div>
    <div class="kn-stats__item">
      <span class="kn-stats__number kn-counter" data-target="6">6</span>
      <span class="kn-stats__label">Countries</span>
    </div>
    <div class="kn-stats__item">
      <span class="kn-stats__number kn-counter" data-target="50">50+</span>
      <span class="kn-stats__label">Editions Published</span>
    </div>
    <div class="kn-stats__item">
      <span class="kn-stats__number kn-counter" data-target="1000">1K+</span>
      <span class="kn-stats__label">Active Readers</span>
    </div>
  </div>
</section>


<!-- ===== MISSION ===== -->
<section id="mission" class="kn-section kn-reveal" aria-labelledby="mission-heading">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4rem;align-items:center;" class="kn-about-split">

      <div>
        <span style="
            display:inline-block;background:var(--kn-orange-light);
            color:var(--kn-orange);font-size:.78rem;font-weight:700;
            letter-spacing:.1em;text-transform:uppercase;
            padding:.35rem .9rem;border-radius:999px;margin-bottom:1rem;
        ">Our Mission</span>
        <h2 id="mission-heading" style="
            font-size:clamp(1.8rem,3.5vw,2.6rem);font-weight:900;
            color:var(--kn-navy);line-height:1.18;margin-bottom:1.2rem;
        ">
            Information is Power.<br>We Make It Accessible.
        </h2>
        <p style="color:var(--kn-muted);line-height:1.75;margin-bottom:1.2rem;font-size:1.02rem;">
            At KandaNews, our mission is simple: <strong style="color:var(--kn-navy);">democratise access to high-impact information across Africa.</strong>
            We believe that every student on a campus, every professional in an office, and every entrepreneur in a market deserves concise, credible,
            and culturally relevant news — delivered in a format built for their device and their pace of life.
        </p>
        <p style="color:var(--kn-muted);line-height:1.75;font-size:1.02rem;">
            Through interactive flipping editions, audio briefings, and smart advertising that actually serves readers,
            we are turning the page on traditional media — one country at a time.
        </p>
      </div>

      <div style="
          background:linear-gradient(135deg,var(--kn-navy) 0%,#243554 100%);
          border-radius:var(--kn-radius-xl);padding:2.8rem;
          box-shadow:var(--kn-shadow-xl);
      ">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.4rem;">
          <?php
          $pillars = [
            ['icon'=>'fa-bolt','title'=>'Speed','desc'=>'Daily editions published before you start your morning.'],
            ['icon'=>'fa-mobile-screen','title'=>'Mobile-First','desc'=>'Designed for African smartphones and data budgets.'],
            ['icon'=>'fa-shield-halved','title'=>'Credible','desc'=>'Rigorous editorial standards, zero sensationalism.'],
            ['icon'=>'fa-earth-africa','title'=>'Local','desc'=>'Content rooted in each country\'s culture & economy.'],
          ];
          foreach ($pillars as $p): ?>
          <div style="background:rgba(255,255,255,.06);border-radius:var(--kn-radius);padding:1.4rem;">
            <div style="
                width:44px;height:44px;background:rgba(240,90,26,.2);
                border-radius:12px;display:flex;align-items:center;
                justify-content:center;margin-bottom:.9rem;
            ">
                <i class="fa-solid <?php echo $p['icon']; ?>" style="color:var(--kn-orange);font-size:1.1rem;"></i>
            </div>
            <h4 style="color:#fff;font-size:.95rem;font-weight:700;margin-bottom:.35rem;"><?php echo $p['title']; ?></h4>
            <p style="color:rgba(255,255,255,.58);font-size:.84rem;line-height:1.5;"><?php echo $p['desc']; ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</section>


<!-- ===== STORY ===== -->
<section class="kn-section kn-section--alt kn-reveal" aria-labelledby="story-heading">
  <div class="container" style="max-width:800px;">
    <div class="kn-section__header">
      <h2 id="story-heading" class="kn-section__title">How It Started</h2>
      <p class="kn-section__lead">A frustration turned into a movement.</p>
    </div>

    <div style="background:#fff;border-radius:var(--kn-radius-xl);padding:2.8rem 3rem;box-shadow:var(--kn-shadow);">
      <p style="color:var(--kn-muted);line-height:1.85;font-size:1.03rem;margin-bottom:1.4rem;">
          KandaNews was born out of a simple observation: African readers — especially the young, ambitious generation
          entering universities and the workforce — were underserved by the media landscape.
          Existing newspapers were expensive, print-heavy, or full of noise. Digital platforms were fragmented,
          agenda-driven, or simply irrelevant to the daily realities of life in Kampala, Lagos, Nairobi or Cape Town.
      </p>
      <p style="color:var(--kn-muted);line-height:1.85;font-size:1.03rem;margin-bottom:1.4rem;">
          Founded in Uganda in 2023 under <strong style="color:var(--kn-navy);">Thirdsan Enterprises Ltd</strong>,
          KandaNews launched its first digital flipping edition for Ugandan readers with a bold idea:
          what if a newspaper felt less like a chore and more like a conversation?
          Interactive, fast, affordable, and — crucially — local.
      </p>
      <p style="color:var(--kn-muted);line-height:1.85;font-size:1.03rem;">
          The response was immediate. Readers who had never consistently followed a newspaper were suddenly
          opening their phones every morning. Word spread to campuses, offices, and WhatsApp groups.
          Within months, the model had proven itself in Uganda, and the vision expanded to the rest of the continent.
          Today, KandaNews is live in multiple countries with more on the horizon.
      </p>
    </div>
  </div>
</section>


<!-- ===== WHAT WE DO ===== -->
<section class="kn-section kn-reveal" aria-labelledby="what-heading">
  <div class="container">
    <div class="kn-section__header">
      <h2 id="what-heading" class="kn-section__title">What We Build</h2>
      <p class="kn-section__lead">A full ecosystem around the news experience.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:1.8rem;">
      <?php
      $offerings = [
        [
          'icon'  => 'fa-newspaper',
          'color' => '#3b82f6',
          'title' => 'Daily Digital Editions',
          'desc'  => 'Beautifully crafted interactive flipbooks published every morning — stories that matter in formats that delight.',
        ],
        [
          'icon'  => 'fa-mobile-screen',
          'color' => 'var(--kn-orange)',
          'title' => 'Cross-Platform App',
          'desc'  => 'Read on Android, iOS, Windows, Mac or Linux. One subscription. Every device. No exceptions.',
        ],
        [
          'icon'  => 'fa-headphones',
          'color' => '#8b5cf6',
          'title' => 'Audio Briefings',
          'desc'  => 'Too busy to read? Our 3-minute morning audio editions keep you informed on the go.',
        ],
        [
          'icon'  => 'fa-bullhorn',
          'color' => '#10b981',
          'title' => 'Smart Advertising',
          'desc'  => 'Full-page spreads, native editorial, audio sponsorships and interactive rich-media ads that readers actually engage with.',
        ],
        [
          'icon'  => 'fa-graduation-cap',
          'color' => '#f59e0b',
          'title' => 'Special Editions',
          'desc'  => 'University, corporate, entrepreneurship and campaign editions tailored for specific audiences and contexts.',
        ],
        [
          'icon'  => 'fa-chart-line',
          'color' => '#ef4444',
          'title' => 'Polls & Trends',
          'desc'  => 'Real-time reader polls, trending topics and engagement analytics that put the audience at the centre.',
        ],
      ];
      foreach ($offerings as $o): ?>
      <div class="kn-reveal" style="
          background:#fff;border-radius:var(--kn-radius-lg);
          padding:2rem;border:1.5px solid var(--kn-border);
          box-shadow:var(--kn-shadow-sm);
          transition:transform .25s var(--kn-ease),box-shadow .25s var(--kn-ease);
      " onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='var(--kn-shadow-md)';"
         onmouseout="this.style.transform='';this.style.boxShadow='var(--kn-shadow-sm)';">
        <div style="
            width:52px;height:52px;border-radius:14px;
            background:<?php echo $o['color']; ?>1a;
            display:flex;align-items:center;justify-content:center;
            margin-bottom:1.1rem;
        ">
            <i class="fa-solid <?php echo $o['icon']; ?>" style="color:<?php echo $o['color']; ?>;font-size:1.3rem;"></i>
        </div>
        <h3 style="font-size:1.06rem;font-weight:700;color:var(--kn-navy);margin-bottom:.5rem;"><?php echo $o['title']; ?></h3>
        <p style="color:var(--kn-muted);font-size:.92rem;line-height:1.6;"><?php echo $o['desc']; ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ===== VALUES ===== -->
<section class="kn-section kn-section--alt kn-reveal" aria-labelledby="values-heading">
  <div class="container" style="max-width:880px;">
    <div class="kn-section__header">
      <h2 id="values-heading" class="kn-section__title">Our Values</h2>
      <p class="kn-section__lead">The principles that guide every edition, every feature, every hire.</p>
    </div>

    <div style="display:flex;flex-direction:column;gap:1rem;">
      <?php
      $values = [
        ['num'=>'01','title'=>'Accuracy Above All', 'desc'=>'We do not publish until we are confident. Speed matters — but never at the cost of truth.'],
        ['num'=>'02','title'=>'Africa-First Thinking','desc'=>'Every product decision starts with the lived reality of our readers — not Silicon Valley playbooks.'],
        ['num'=>'03','title'=>'Radical Accessibility','desc'=>'Great journalism should not be a luxury. We price to reach, not to exclude.'],
        ['num'=>'04','title'=>'Builder Mindset',       'desc'=>'We ship, iterate, learn. We are a technology company as much as a media company.'],
        ['num'=>'05','title'=>'Reader Respect',        'desc'=>'We respect your time, your intelligence and your privacy. No clickbait. No manipulation. Ever.'],
      ];
      foreach ($values as $v): ?>
      <div class="kn-reveal" style="
          display:flex;gap:1.6rem;align-items:flex-start;
          background:#fff;border-radius:var(--kn-radius-lg);
          padding:1.6rem 2rem;border:1.5px solid var(--kn-border);
          box-shadow:var(--kn-shadow-sm);
      ">
        <span style="
            font-size:1.6rem;font-weight:900;color:var(--kn-orange);
            min-width:2.8rem;line-height:1;padding-top:.1rem;
            font-variant-numeric:tabular-nums;
        "><?php echo $v['num']; ?></span>
        <div>
          <h3 style="font-size:1rem;font-weight:700;color:var(--kn-navy);margin-bottom:.3rem;"><?php echo $v['title']; ?></h3>
          <p style="color:var(--kn-muted);font-size:.93rem;line-height:1.6;"><?php echo $v['desc']; ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ===== COUNTRIES PRESENCE ===== -->
<section class="kn-section kn-reveal" aria-labelledby="presence-heading">
  <div class="container">
    <div class="kn-section__header">
      <h2 id="presence-heading" class="kn-section__title">
        <i class="fa-solid fa-earth-africa" style="color:var(--kn-orange);margin-right:.4rem;"></i>
        Where We Are
      </h2>
      <p class="kn-section__lead">From East to West, South to Central — KandaNews is growing across the continent.</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.2rem;max-width:900px;margin:0 auto;">
      <?php
      $countries = [
        ['flag'=>'🇺🇬','name'=>'Uganda',       'status'=>'live',  'url'=>'https://ug.kandanews.africa'],
        ['flag'=>'🇰🇪','name'=>'Kenya',        'status'=>'live',  'url'=>'https://ke.kandanews.africa'],
        ['flag'=>'🇳🇬','name'=>'Nigeria',      'status'=>'live',  'url'=>'https://ng.kandanews.africa'],
        ['flag'=>'🇿🇦','name'=>'South Africa', 'status'=>'live',  'url'=>'https://za.kandanews.africa'],
        ['flag'=>'🇬🇭','name'=>'Ghana',        'status'=>'soon',  'url'=>null],
        ['flag'=>'🇷🇼','name'=>'Rwanda',       'status'=>'soon',  'url'=>null],
      ];
      foreach ($countries as $c):
        $isLive = $c['status'] === 'live';
      ?>
      <div style="
          background:<?php echo $isLive ? 'var(--kn-navy)' : '#fff'; ?>;
          border-radius:var(--kn-radius-lg);padding:1.6rem 1rem;text-align:center;
          border:1.5px solid <?php echo $isLive ? 'var(--kn-navy)' : 'var(--kn-border)'; ?>;
          box-shadow:var(--kn-shadow-sm);
          <?php if ($isLive && $c['url']): ?>cursor:pointer;<?php endif; ?>
          transition:transform .2s;
      " <?php if ($isLive && $c['url']): ?>onclick="location.href='<?php echo $c['url']; ?>'" onmouseover="this.style.transform='translateY(-3px)';" onmouseout="this.style.transform='';"<?php endif; ?>>
        <div style="font-size:2.4rem;margin-bottom:.5rem;"><?php echo $c['flag']; ?></div>
        <div style="font-weight:700;font-size:.95rem;color:<?php echo $isLive ? '#fff' : 'var(--kn-navy)'; ?>;margin-bottom:.4rem;">
            <?php echo $c['name']; ?>
        </div>
        <span style="
            font-size:.72rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;
            padding:.25rem .7rem;border-radius:999px;
            background:<?php echo $isLive ? 'rgba(240,90,26,.25)' : 'rgba(100,116,139,.12)'; ?>;
            color:<?php echo $isLive ? 'var(--kn-orange)' : 'var(--kn-muted)'; ?>;
        "><?php echo $isLive ? 'LIVE' : 'Coming Soon'; ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>


<!-- ===== CONTACT CTA ===== -->
<section class="kn-download kn-download--centered kn-reveal" aria-labelledby="contact-heading">
  <div class="kn-download__inner">
    <div class="kn-download__text">
      <h2 id="contact-heading">
        <i class="fa-solid fa-handshake" style="color:var(--kn-orange);margin-right:.4rem;"></i>
        Let's Build the Future Together
      </h2>
      <p>
        Whether you are a brand wanting to reach African audiences, a journalist who believes in our mission,
        an investor looking at the future of African media, or simply a reader who wants to say hello
        &mdash; we would love to hear from you.
      </p>
      <div class="kn-cta-row" style="justify-content:center;">
        <a class="kn-btn kn-btn--primary" href="mailto:hello@kandanews.africa">
            <i class="fa-solid fa-envelope"></i> hello@kandanews.africa
        </a>
        <a class="kn-btn kn-btn--ghost" href="https://wa.me/256200901370">
            <i class="fa-brands fa-whatsapp"></i> WhatsApp Us
        </a>
      </div>
      <div style="
          display:flex;gap:2rem;justify-content:center;margin-top:2rem;
          color:rgba(255,255,255,.55);font-size:.85rem;flex-wrap:wrap;
      ">
        <span><i class="fa-solid fa-map-marker-alt" style="color:var(--kn-orange);margin-right:.3rem;"></i>Kampala, Uganda</span>
        <span><i class="fa-solid fa-building" style="color:var(--kn-orange);margin-right:.3rem;"></i>Thirdsan Enterprises Ltd</span>
        <span><i class="fa-solid fa-envelope" style="color:var(--kn-orange);margin-right:.3rem;"></i>hello@kandanews.africa</span>
      </div>
    </div>
  </div>
</section>

<style>
/* Responsive fix for about split layout */
@media (max-width: 780px) {
  .kn-about-split { grid-template-columns: 1fr !important; gap: 2rem !important; }
}
</style>

<?php require_once __DIR__ . '/shared/components/footer.php'; ?>
