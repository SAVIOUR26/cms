<?php
/* Template Name: Kanda — Phone Login + Onboarding */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="description" content="Login to KandaNews with your Google or Apple account.">
  <link rel="icon" type="image/png" href="<?php echo esc_url( get_template_directory_uri().'/assets/kandanews-favicon-32x32.png'); ?>">
  <?php wp_head(); ?>
  <style>
    :root{--primary:#1e2b42;--accent:#f05a1a;--ink:#0f172a;--muted:#64748b;--border:#e5e7eb}
    * { box-sizing: border-box; } html, body { margin: 0; height: 100%; }
    body { font-family: system-ui,-apple-system,"Segoe UI",Roboto,Ubuntu,Arial,sans-serif; min-height: 100vh; display: grid; place-items: center; background: #000; }
    .bg { position: fixed; inset: 0; background: #000 url('<?php echo esc_url( get_template_directory_uri().'/assets/login-bg.gif'); ?>') center/cover no-repeat; opacity: .35; filter: grayscale(25%); }
    .scrim { position: fixed; inset: 0; background: linear-gradient(180deg,rgba(0,0,0,.25),rgba(0,0,0,.45)); }
    .wrap { position: relative; z-index: 10; width: min(92vw,560px); margin: auto; }
    .card { background: #fff; border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 8px 40px rgba(0,0,0,.35); padding: 22px; }
    h1 { margin: 0 0 .5rem; color: var(--primary); font-size: 1.8rem; }
    .muted { color: var(--muted); font-size: .95rem; }
    label { display: grid; gap: 6px; font-weight: 700; color: var(--ink); }
    input[type=text],input[type=tel] { padding: 12px; border: 1px solid var(--border); border-radius: 12px; font: inherit; }
    input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(30,43,66,.15); }
    .row { display: grid; gap: 10px; margin: 14px 0; }
    .btn { display: inline-block; padding: 12px 16px; border-radius: 12px; border: 2px solid var(--primary); font-weight: 700; color: var(--primary); text-decoration: none; background: #fff; }
    .btn.primary { background: var(--accent); border-color: var(--accent); color: #fff; }
    .error { color: #b91c1c; background: #fee2e2; border: 1px solid #fecaca; padding: 10px; border-radius: 10px; display: none; }
    .success { color: #065f46; background: #d1fae5; border: 1px solid #a7f3d0; padding: 10px; border-radius: 10px; display: none; }
    .choices{display:flex;gap:12px;flex-wrap:wrap}
    .choices label{display:flex;align-items:center;gap:8px;font-weight:800}
    small.muted{color:var(--muted)}
  </style>
</head>
<body <?php body_class('page-login'); ?>>
  <div class="bg"></div><div class="scrim"></div>
  <main class="wrap">
    <div class="card" role="main" aria-labelledby="knTitle">
      <h1 id="knTitle">Sign in to KandaNews</h1>
      <p class="muted">Use your Google or Apple account to sign in.</p>

      <!-- Step 1: buttons -->
      <div id="step1" class="row">
        <button id="googleSignIn" class="btn primary" aria-label="Sign in with Google">Continue with Google</button>
        <button id="appleSignIn" class="btn" aria-label="Sign in with Apple">Continue with Apple</button>
      </div>

      <!-- Step 3: first-time registration -->
      <div id="step3" class="row" style="display:none">
        <h2 style="margin:.2rem 0 .4rem;color:var(--primary)">KandaNews First-time Registration!</h2>

        <label for="name">Full names
          <input id="name" type="text" placeholder="Your full names" autocomplete="name" required>
        </label>

        <label for="phone">Phone number
          <input id="phone" type="tel" inputmode="numeric" pattern="0\d{9}" maxlength="10"
                 placeholder="e.g., 0772 123456" required>
          <small class="muted" aria-hidden="true">Format: 0XXXXXXXXX (10 digits)</small>
        </label>

        <div role="group" aria-labelledby="catLabel">
          <div id="catLabel" style="font-weight:800">I am a</div>
          <div class="choices">
            <label><input type="radio" name="cat" value="professional" required> Professional</label>
            <label><input type="radio" name="cat" value="student"> University Student</label>
            <label><input type="radio" name="cat" value="entrepreneur"> Entrepreneur</label>
          </div>
        </div>

        <label for="org">University / Company Name
          <input id="org" type="text" placeholder="e.g., Makerere University or MTN Uganda" required>
        </label>

        <label class="terms" style="display:flex;gap:8px;align-items:flex-start">
          <input id="terms" type="checkbox" style="margin-top:4px" required>
          <span>I agree to the <a href="<?php echo esc_url( home_url('/terms') ); ?>" target="_blank" rel="noopener">Terms</a> and <a href="<?php echo esc_url( home_url('/privacy') ); ?>" target="_blank" rel="noopener">Privacy Policy</a></span>
        </label>

        <button id="saveProfile" class="btn primary">Register & Continue</button>
      </div>

      <div id="msgError" class="error" aria-live="assertive"></div>
      <div id="msgOk" class="success" aria-live="polite">Signed in. Redirecting…</div>
    </div>
  </main>
  <?php wp_footer(); ?>
</body>
</html>
