<?php
/*
Template Name: Kanda — Phone Login + Onboarding
Template Post Type: page
*/
defined( 'ABSPATH' ) || exit;

get_header(); ?>

<style>
    /* Keep your CSS here or better: enqueue as a separate stylesheet */
    :root {
        --primary: #1e2b42;
        --accent: #f05a1a;
        --ink: #0f172a;
        --muted: #64748b;
        --border: #e5e7eb;
    }
    * { box-sizing: border-box; }
    .kanda-login-root { min-height: 70vh; display: grid; place-items: center; padding: 40px 0; }
    .kanda-login-bg {
        position: fixed;
        inset: 0;
        background: #000 url('<?php echo esc_url( get_template_directory_uri() . '/assets/login-bg.gif' ); ?>') center/cover no-repeat;
        opacity: .35; filter: grayscale(25%); z-index: 1;
    }
    .kanda-login-scrim {
        position: fixed; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,.25), rgba(0,0,0,.45)); z-index:2;
    }
    .kanda-wrap { position: relative; z-index: 10; width: min(92vw, 560px); margin: 0 auto; }
    .kanda-card { background: #fff; border: 1px solid var(--border); border-radius: 16px; box-shadow: 0 8px 40px rgba(0,0,0,.35); padding: 22px; }
    h1 { margin: 0 0 .5rem; color: var(--primary); font-size: 1.8rem; }
    .muted { color: var(--muted); font-size: .95rem; }
    label { display: grid; gap: 6px; font-weight: 700; color: var(--ink); }
    input[type=text], input[type=tel] { padding: 12px; border: 1px solid var(--border); border-radius: 12px; font: inherit; }
    input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(30,43,66,.15); }
    .row { display: grid; gap: 10px; margin: 14px 0; }
    .btn { display: inline-block; padding: 12px 16px; border-radius: 12px; border: 2px solid var(--primary); font-weight:700; color:var(--primary); text-decoration:none; background:#fff; cursor:pointer; }
    .btn.primary { background: var(--accent); border-color: var(--accent); color:#fff; }
    .btn.google { background:#DB4437; border-color:#DB4437; color:#fff; }
    .btn.apple { background:#000; border-color:#000; color:#fff; }
    .btn.microsoft { background:#00A4EF; border-color:#00A4EF; color:#fff; }
    .btn i { margin-right: 8px; }
    .error { color:#b91c1c; background:#fee2e2; border:1px solid #fecaca; padding:10px; border-radius:10px; display:none; }
    .success { color:#065f46; background:#d1fae5; border:1px solid #a7f3d0; padding:10px; border-radius:10px; display:none; }
    .choices { display:flex; gap:12px; flex-wrap:wrap; }
    .choices label { display:flex; align-items:center; gap:8px; font-weight:800; }
    small.muted { color:var(--muted); }
    .phone-container { display: flex; align-items: center; }
    .phone-container .prefix { padding: 12px 0; border: 1px solid var(--border); border-right: none; border-radius: 12px 0 0 12px; background: #f8fafc; font-weight: 700; color: var(--ink); width: 60px; text-align: center; }
    .phone-container input { border-radius: 0 12px 12px 0; flex-grow: 1; }
</style>

<div class="kanda-login-root">
    <div class="kanda-login-bg" aria-hidden="true"></div>
    <div class="kanda-login-scrim" aria-hidden="true"></div>

    <main class="kanda-wrap" role="main" aria-labelledby="knTitle">
        <div class="kanda-card">
            <h1 id="knTitle">Sign in to KandaNews</h1>
            <p class="muted">Use your Google, Apple, or Microsoft account to sign in.</p>

            <div id="msgError" class="error" aria-live="assertive"></div>
            <div id="msgOk" class="success" aria-live="polite"></div>

            <div id="step1" class="row">
                <button id="googleSignIn" class="btn google" aria-label="Sign in with Google"><i class="fa-brands fa-google"></i>Continue with Google</button>
                <button id="appleSignIn" class="btn apple" aria-label="Sign in with Apple"><i class="fa-brands fa-apple"></i>Continue with Apple</button>
                <button id="microsoftSignIn" class="btn microsoft" aria-label="Sign in with Microsoft"><i class="fa-brands fa-microsoft"></i>Continue with Microsoft</button>
            </div>

            <div id="step3" class="row" style="display:none">
                <h2 style="margin:.2rem 0 .4rem;color:var(--primary)">KandaNews First-time Registration!</h2>

                <label for="name">Full names
                    <input id="name" type="text" placeholder="Your full names" autocomplete="name" required>
                </label>

                <label for="phone">WhatsApp number
                    <div class="phone-container">
                        <span class="prefix">+256</span>
                        <input id="phone" type="tel" inputmode="numeric" pattern="\d{9}" maxlength="9" placeholder="e.g., 772 123456" required>
                    </div>
                    <small class="muted" aria-hidden="true">Enter the last 9 digits of your number.</small>
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
                    <span>I agree to the <a href="<?php echo esc_url(home_url('/terms')); ?>" target="_blank" rel="noopener">Terms</a> and <a href="<?php echo esc_url(home_url('/privacy')); ?>" target="_blank" rel="noopener">Privacy Policy</a></span>
                </label>

                <button id="saveProfile" class="btn primary">Register & Continue</button>
            </div>
        </div>
    </main>
</div>

<?php get_footer(); ?>