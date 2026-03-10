<?php
/**
 * KandaNews Africa — Terms of Service
 * kandanews.africa/terms.php
 */
$COUNTRY_CODE = '';
$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$COUNTRIES = [];
require_once __DIR__ . '/shared/includes/helpers.php';
require_once __DIR__ . '/shared/includes/country-config.php';

$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$_is_hub = true;
$page_title  = 'Terms of Service — KandaNews Africa';
$page_description = 'Read KandaNews Africa\'s Terms of Service — the rules and conditions governing your use of the KandaNews platform, app, and digital editions.';

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
    "><i class="fa-solid fa-file-contract"></i> Legal</span>
    <h1 style="font-size:clamp(1.9rem,4vw,2.9rem);font-weight:900;color:#fff;margin-bottom:.9rem;">Terms of Service</h1>
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
          These Terms of Service ("Terms") govern your access to and use of the KandaNews platform,
          including the website (<a href="https://kandanews.africa" style="color:var(--kn-orange);">kandanews.africa</a>)
          and all country-specific subdomains, the KandaNews mobile application, and all related digital editions and content,
          operated by <strong style="color:var(--kn-navy);">Thirdsan Enterprises Ltd</strong> ("KandaNews", "we", "us", "our").
          <br><br>
          By accessing or using our Service, you agree to be bound by these Terms.
          If you do not agree, please do not use our Service.
      </p>
    </div>

    <?php
    $sections = [

      [
        'icon' => 'fa-circle-check',
        'title' => '1. Eligibility &amp; Account Registration',
        'body' => '
          <ul>
            <li>You must be at least <strong>13 years old</strong> to use KandaNews. If you are under 18, you confirm that a parent or guardian has consented to these Terms on your behalf.</li>
            <li>You create an account by verifying your phone number via a one-time password (OTP). You are responsible for all activity that occurs under your account.</li>
            <li>You agree to provide accurate, current and complete information during registration and to keep it up to date.</li>
            <li>You must not impersonate any person or use another person\'s account without authorisation.</li>
          </ul>
        ',
      ],

      [
        'icon' => 'fa-newspaper',
        'title' => '2. The Service',
        'body' => '
          <p>KandaNews provides:</p>
          <ul>
            <li>Digital interactive newspaper editions published on a scheduled basis.</li>
            <li>A mobile and web application through which subscribers access editions.</li>
            <li>Audio briefings, polls, special editions and associated content.</li>
          </ul>
          <p>
            We reserve the right to modify, suspend or discontinue any part of the Service at any time
            with reasonable notice where practicable. We are not liable for any disruption caused by
            events outside our control (force majeure).
          </p>
        ',
      ],

      [
        'icon' => 'fa-credit-card',
        'title' => '3. Subscriptions &amp; Payments',
        'body' => '
          <h4>3.1 Plans</h4>
          <p>
            KandaNews offers Daily, Weekly and Monthly subscription plans. Pricing is set per country edition
            and displayed in the app and on the country edition website before purchase.
          </p>
          <h4>3.2 Payment Processing</h4>
          <p>
            Payments are processed by third-party providers (currently <strong>Flutterwave</strong> and <strong>DPO / 3G Direct Pay</strong>).
            By purchasing a subscription you agree to the terms and privacy policies of the relevant payment provider.
            KandaNews does not store card numbers or mobile-money PINs.
          </p>
          <h4>3.3 Billing &amp; Auto-Renewal</h4>
          <p>
            Subscriptions are not automatically renewed. Each subscription covers one fixed period (1, 7 or 30 days).
            You must manually renew to continue access after expiry.
          </p>
          <h4>3.4 Refunds</h4>
          <p>
            All purchases are <strong>non-refundable</strong> once a subscription period has started and access has been granted,
            except where required by applicable consumer protection law.
            If you experience a technical issue preventing access, contact
            <a href="mailto:support@kandanews.africa" style="color:var(--kn-orange);">support@kandanews.africa</a>
            within 48 hours of the charge.
          </p>
        ',
      ],

      [
        'icon' => 'fa-book-open',
        'title' => '4. Acceptable Use',
        'body' => '
          <p>You agree <strong>not</strong> to:</p>
          <ul>
            <li>Reproduce, redistribute, scrape, copy or republish any KandaNews content without our prior written consent.</li>
            <li>Share your account credentials with others or allow multiple people to access one account simultaneously.</li>
            <li>Use the Service for any unlawful purpose or in violation of any applicable regulation.</li>
            <li>Attempt to reverse-engineer, decompile or tamper with the KandaNews application.</li>
            <li>Upload, transmit or link to malicious code, spam, or any content that infringes third-party rights.</li>
            <li>Use automated tools, bots or scrapers to access the Service without express written permission.</li>
          </ul>
          <p>
            We reserve the right to suspend or terminate accounts that violate these rules without prior notice.
          </p>
        ',
      ],

      [
        'icon' => 'fa-copyright',
        'title' => '5. Intellectual Property',
        'body' => '
          <p>
            All content on the KandaNews platform — including but not limited to text, images, audio, video, graphics,
            logos, edition layouts and software — is the intellectual property of Thirdsan Enterprises Ltd or its
            licensed content providers and is protected by Ugandan and international copyright law.
          </p>
          <p>
            Your subscription grants you a <strong>personal, non-exclusive, non-transferable</strong> licence to access
            and read editions for your own private use. No rights are granted beyond this limited licence.
          </p>
        ',
      ],

      [
        'icon' => 'fa-triangle-exclamation',
        'title' => '6. Disclaimers &amp; Limitation of Liability',
        'body' => '
          <p>
            The Service is provided <strong>"as is"</strong> and <strong>"as available"</strong> without warranties
            of any kind, express or implied. We do not guarantee that the Service will be uninterrupted, error-free or
            free of viruses.
          </p>
          <p>
            To the fullest extent permitted by law, KandaNews and Thirdsan Enterprises Ltd shall not be liable for
            any indirect, incidental, special, consequential or punitive damages arising out of your use or inability
            to use the Service.
          </p>
          <p>
            Our total liability for any claim related to the Service shall not exceed the amount you paid us
            in the <strong>3 months preceding</strong> the event giving rise to the claim.
          </p>
        ',
      ],

      [
        'icon' => 'fa-user-slash',
        'title' => '7. Account Termination',
        'body' => '
          <p>
            You may delete your account at any time by contacting
            <a href="mailto:support@kandanews.africa" style="color:var(--kn-orange);">support@kandanews.africa</a>.
            Unused subscription time is not refunded upon voluntary deletion.
          </p>
          <p>
            We reserve the right to suspend or terminate your account if you breach these Terms,
            engage in fraudulent activity, or if continued access poses a risk to our platform or other users.
          </p>
        ',
      ],

      [
        'icon' => 'fa-link',
        'title' => '8. Third-Party Links &amp; Services',
        'body' => '
          <p>
            The Service may contain links to third-party websites or integrate third-party services
            (e.g. payment gateways, app stores). We are not responsible for the content, accuracy or
            privacy practices of any third-party sites or services. Use them at your own risk.
          </p>
        ',
      ],

      [
        'icon' => 'fa-globe',
        'title' => '9. Governing Law',
        'body' => '
          <p>
            These Terms are governed by and construed in accordance with the laws of the
            <strong>Republic of Uganda</strong>. Any disputes arising from or relating to these Terms
            shall be subject to the exclusive jurisdiction of the courts of Uganda.
          </p>
          <p>
            Users in other jurisdictions acknowledge they are accessing the Service voluntarily and
            are responsible for compliance with local laws.
          </p>
        ',
      ],

      [
        'icon' => 'fa-arrows-rotate',
        'title' => '10. Changes to These Terms',
        'body' => '
          <p>
            We may update these Terms from time to time. We will notify you of material changes via in-app
            notification or email (if provided) at least <strong>14 days before</strong> the changes take effect.
            Continued use of the Service after the effective date constitutes acceptance of the revised Terms.
          </p>
        ',
      ],

      [
        'icon' => 'fa-envelope',
        'title' => '11. Contact',
        'body' => '
          <p>For any questions about these Terms, please contact:</p>
          <ul>
            <li><strong>Email:</strong> <a href="mailto:legal@kandanews.africa" style="color:var(--kn-orange);">legal@kandanews.africa</a></li>
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

    <!-- Footer note -->
    <div style="
        text-align:center;padding:1.8rem;
        background:var(--kn-navy);border-radius:var(--kn-radius-lg);
        color:rgba(255,255,255,.6);font-size:.88rem;line-height:1.7;
    ">
      <i class="fa-solid fa-scale-balanced" style="color:var(--kn-orange);font-size:1.4rem;display:block;margin-bottom:.7rem;"></i>
      These Terms were last updated in <?php echo $last_updated; ?>.
      By using KandaNews you confirm you have read and agree to these Terms and our
      <a href="https://kandanews.africa/privacy.php" style="color:var(--kn-orange);">Privacy Policy</a>.
    </div>

  </div>
</section>

<style>
section h4 { color:var(--kn-navy);font-size:.95rem;font-weight:700;margin:1rem 0 .4rem; }
section ul { padding-left:1.4rem;margin:.5rem 0 .8rem; }
section ul li { margin-bottom:.4rem; }
</style>

<?php require_once __DIR__ . '/shared/components/footer.php'; ?>
