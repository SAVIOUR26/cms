<?php
/**
 * KandaNews Africa — Privacy Policy
 * kandanews.africa/privacy.php
 */
$COUNTRY_CODE = '';
$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$COUNTRIES = [];
require_once __DIR__ . '/shared/includes/helpers.php';
require_once __DIR__ . '/shared/includes/country-config.php';

$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$_is_hub = true;
$page_title  = 'Privacy Policy — KandaNews Africa';
$page_description = 'Read KandaNews Africa\'s Privacy Policy — how we collect, use, store and protect your personal data in compliance with applicable data protection laws.';

require_once __DIR__ . '/shared/components/header.php';

$last_updated = 'March 2025';
?>

<!-- ===== PAGE HERO ===== -->
<section style="
    background:linear-gradient(135deg,var(--kn-navy-dark) 0%,var(--kn-navy) 100%);
    padding:5.5rem 1.5rem 3.5rem;text-align:center;
">
  <div class="container" style="max-width:740px;">
    <span style="
        display:inline-flex;align-items:center;gap:.4rem;
        background:rgba(240,90,26,.15);border:1px solid rgba(240,90,26,.3);
        color:var(--kn-orange);font-size:.78rem;font-weight:700;letter-spacing:.08em;
        text-transform:uppercase;padding:.4rem 1rem;border-radius:999px;margin-bottom:1.4rem;
    "><i class="fa-solid fa-shield-halved"></i> Legal</span>
    <h1 style="font-size:clamp(1.9rem,4vw,2.9rem);font-weight:900;color:#fff;margin-bottom:.9rem;">Privacy Policy</h1>
    <p style="color:rgba(255,255,255,.6);font-size:.95rem;">Last updated: <?php echo $last_updated; ?></p>
  </div>
</section>


<!-- ===== CONTENT ===== -->
<section style="padding:3.5rem 1.5rem 5rem;background:var(--kn-gray-50);">
  <div style="max-width:780px;margin:0 auto;">

    <!-- Intro card -->
    <div style="
        background:#fff;border-radius:var(--kn-radius-lg);padding:2.2rem 2.4rem;
        border-left:4px solid var(--kn-orange);box-shadow:var(--kn-shadow-sm);
        margin-bottom:2.4rem;
    ">
      <p style="color:var(--kn-muted);line-height:1.75;font-size:1rem;">
          This Privacy Policy explains how <strong style="color:var(--kn-navy);">KandaNews Africa</strong>, operated by
          <strong style="color:var(--kn-navy);">Thirdsan Enterprises Ltd</strong> ("we", "us", "our"),
          collects, uses, stores and protects personal information when you use our website
          (<a href="https://kandanews.africa" style="color:var(--kn-orange);">kandanews.africa</a>),
          mobile app, or any country-specific edition (collectively, the "Service").
          By using our Service you agree to the practices described in this Policy.
      </p>
    </div>

    <?php
    $sections = [

      [
        'icon' => 'fa-database',
        'title' => '1. Information We Collect',
        'body' => '
          <p>We collect information to provide, improve and personalise the Service. The categories we collect include:</p>
          <h4>Information you provide directly</h4>
          <ul>
            <li><strong>Phone number</strong> — required to create an account via OTP verification.</li>
            <li><strong>Name &amp; profile details</strong> — first name, surname, role (student / professional / entrepreneur) and role detail (e.g. university name) collected at registration.</li>
            <li><strong>Email address</strong> — optional; collected if you provide it for account recovery or billing.</li>
            <li><strong>Age</strong> — used to ensure our Service is appropriate for you (minimum age: 13).</li>
            <li><strong>Communications</strong> — messages you send us via email, WhatsApp or support forms.</li>
          </ul>
          <h4>Information collected automatically</h4>
          <ul>
            <li><strong>Device &amp; usage data</strong> — device type, operating system, app version, edition views, session duration, crash reports.</li>
            <li><strong>Country &amp; language</strong> — inferred from your phone number prefix and device settings.</li>
            <li><strong>Payment metadata</strong> — payment reference numbers and status; we do <em>not</em> store card numbers or mobile-money PINs.</li>
          </ul>
        ',
      ],

      [
        'icon' => 'fa-cogs',
        'title' => '2. How We Use Your Information',
        'body' => '
          <p>We use the information we collect to:</p>
          <ul>
            <li>Create and manage your account and authenticate you via OTP.</li>
            <li>Process subscription payments and verify transactions via Flutterwave or DPO.</li>
            <li>Deliver the correct country edition and personalise your reading experience.</li>
            <li>Send important service communications (e.g. OTP codes, payment receipts, edition alerts).</li>
            <li>Improve our product through aggregate usage analytics.</li>
            <li>Comply with legal obligations and enforce our Terms of Service.</li>
            <li>Contact you with marketing messages <em>only</em> if you have opted in.</li>
          </ul>
          <p>We do <strong>not</strong> sell your personal data to third parties.</p>
        ',
      ],

      [
        'icon' => 'fa-share-nodes',
        'title' => '3. Sharing of Information',
        'body' => '
          <p>We share data only in the following circumstances:</p>
          <ul>
            <li><strong>Payment providers</strong> — Flutterwave and DPO receive transaction details necessary to process payments. Their privacy policies govern their use of this data.</li>
            <li><strong>SMS provider</strong> — Africa\'s Talking receives your phone number solely to deliver OTP codes.</li>
            <li><strong>Legal requirements</strong> — We may disclose information if required by law, court order, or to protect the rights, property or safety of KandaNews, our users, or the public.</li>
            <li><strong>Business transfers</strong> — In the event of a merger, acquisition or asset sale, user data may be transferred. We will notify you before that happens.</li>
          </ul>
        ',
      ],

      [
        'icon' => 'fa-lock',
        'title' => '4. Data Security',
        'body' => '
          <p>
            We implement industry-standard security measures to protect your data:
          </p>
          <ul>
            <li>All data in transit is encrypted using <strong>TLS 1.2+</strong> (HTTPS).</li>
            <li>Passwords and OTP codes are <strong>never stored in plain text</strong>; we use bcrypt hashing.</li>
            <li>Access tokens are short-lived JWTs; refresh tokens are rotated on use.</li>
            <li>Database access is restricted to authorised server processes only.</li>
          </ul>
          <p>
            Despite these measures, no system is 100% secure. If you suspect a security issue,
            please contact us immediately at
            <a href="mailto:security@kandanews.africa" style="color:var(--kn-orange);">security@kandanews.africa</a>.
          </p>
        ',
      ],

      [
        'icon' => 'fa-clock',
        'title' => '5. Data Retention',
        'body' => '
          <p>
            We retain your account data for as long as your account is active or as needed to provide the Service.
            If you request account deletion, we will remove your personal data within <strong>30 days</strong>,
            except where retention is required by law or for legitimate business reasons (e.g. resolving disputes, preventing fraud).
            Anonymised, aggregated analytics data may be retained indefinitely.
          </p>
        ',
      ],

      [
        'icon' => 'fa-child',
        'title' => '6. Children\'s Privacy',
        'body' => '
          <p>
            Our Service is not directed to children under the age of <strong>13</strong>.
            We do not knowingly collect personal information from children under 13.
            If you believe we have inadvertently collected such information, please contact us and we will promptly delete it.
          </p>
        ',
      ],

      [
        'icon' => 'fa-user-check',
        'title' => '7. Your Rights',
        'body' => '
          <p>Depending on your country\'s data protection laws, you may have the right to:</p>
          <ul>
            <li><strong>Access</strong> the personal data we hold about you.</li>
            <li><strong>Correct</strong> inaccurate or incomplete data.</li>
            <li><strong>Delete</strong> your account and personal data ("right to be forgotten").</li>
            <li><strong>Restrict</strong> certain processing activities.</li>
            <li><strong>Data portability</strong> — receive a copy of your data in a machine-readable format.</li>
            <li><strong>Opt out</strong> of marketing communications at any time.</li>
          </ul>
          <p>
            To exercise any of these rights, contact us at
            <a href="mailto:privacy@kandanews.africa" style="color:var(--kn-orange);">privacy@kandanews.africa</a>.
            We will respond within 30 days.
          </p>
        ',
      ],

      [
        'icon' => 'fa-cookie-bite',
        'title' => '8. Cookies &amp; Local Storage',
        'body' => '
          <p>
            Our websites use minimal cookies — primarily to maintain session state and analyse traffic (via privacy-respecting analytics).
            Our mobile app uses device local storage to cache your access tokens and reading preferences.
            You can clear app storage at any time via your device settings.
          </p>
        ',
      ],

      [
        'icon' => 'fa-arrows-rotate',
        'title' => '9. Changes to This Policy',
        'body' => '
          <p>
            We may update this Privacy Policy from time to time. When we make significant changes we will notify you
            via in-app notification or email (if provided). The "Last updated" date at the top of this page reflects
            the most recent revision. Continued use of the Service after changes constitutes acceptance of the revised Policy.
          </p>
        ',
      ],

      [
        'icon' => 'fa-envelope',
        'title' => '10. Contact Us',
        'body' => '
          <p>If you have questions about this Privacy Policy or your personal data, please reach us at:</p>
          <ul>
            <li><strong>Email:</strong> <a href="mailto:privacy@kandanews.africa" style="color:var(--kn-orange);">privacy@kandanews.africa</a></li>
            <li><strong>General:</strong> <a href="mailto:hello@kandanews.africa" style="color:var(--kn-orange);">hello@kandanews.africa</a></li>
            <li><strong>Company:</strong> Thirdsan Enterprises Ltd, Kampala, Uganda</li>
          </ul>
        ',
      ],

    ];

    foreach ($sections as $sec): ?>
    <div style="
        background:#fff;border-radius:var(--kn-radius-lg);padding:2rem 2.4rem;
        margin-bottom:1.4rem;box-shadow:var(--kn-shadow-sm);border:1px solid var(--kn-border);
    ">
      <h2 style="
          display:flex;align-items:center;gap:.7rem;
          font-size:1.08rem;font-weight:800;color:var(--kn-navy);
          margin-bottom:1.1rem;padding-bottom:.8rem;
          border-bottom:1px solid var(--kn-border);
      ">
        <span style="
            width:34px;height:34px;border-radius:9px;
            background:var(--kn-orange-light);display:inline-flex;
            align-items:center;justify-content:center;flex-shrink:0;
        ">
            <i class="fa-solid <?php echo $sec['icon']; ?>" style="color:var(--kn-orange);font-size:.85rem;"></i>
        </span>
        <?php echo $sec['title']; ?>
      </h2>
      <div style="color:var(--kn-muted);line-height:1.75;font-size:.96rem;">
        <?php echo $sec['body']; ?>
      </div>
    </div>
    <?php endforeach; ?>

  </div>
</section>

<style>
.kn-legal-content h4 { color:var(--kn-navy);font-size:.95rem;font-weight:700;margin:1rem 0 .4rem; }
section ul { padding-left:1.4rem;margin:.5rem 0 .8rem; }
section ul li { margin-bottom:.4rem; }
</style>

<?php require_once __DIR__ . '/shared/components/footer.php'; ?>
