<?php
/**
 * KandaNews — Login / Register
 * Pure PHP, no WordPress.
 */
require_once __DIR__ . '/shared/includes/helpers.php';
require_once __DIR__ . '/shared/includes/country-config.php';
require_once __DIR__ . '/shared/includes/auth-guard.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    header('Location: /dashboard.php');
    exit;
}

$phone_prefix = $COUNTRY['phone_prefix'] ?? '+256';
$phone_digits = $COUNTRY['phone_digits'] ?? 9;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to KandaNews <?php echo h($COUNTRY['name']); ?>">
    <title>Sign In — KandaNews <?php echo h($COUNTRY['name']); ?></title>
    <link rel="icon" type="image/png" href="/shared/assets/img/kanda-square.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous">
    <style>
        :root {
            --primary: #1e2b42;
            --accent: #f05a1a;
            --accent-hover: #ff7b48;
            --ink: #0f172a;
            --muted: #64748b;
            --border: #e5e7eb;
            --light: #f9f9f9;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; height: 100%; }
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, Arial, sans-serif;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #000;
        }
        .bg {
            position: fixed; inset: 0;
            background: #000 url('/shared/assets/img/login-bg.gif') center/cover no-repeat;
            opacity: 0.30; filter: grayscale(25%);
        }
        .scrim {
            position: fixed; inset: 0;
            background: linear-gradient(180deg, rgba(0,0,0,0.25), rgba(0,0,0,0.50));
        }
        .wrap {
            position: relative; z-index: 10;
            width: min(92vw, 520px);
            margin: auto;
        }
        .card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.35);
            padding: 28px;
        }
        .logo-row {
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 1.2rem;
        }
        .logo-row img { width: 44px; height: 44px; border-radius: 10px; }
        .logo-row span { font-weight: 900; font-size: 1.3rem; color: var(--primary); }
        h1 { margin: 0 0 0.3rem; color: var(--primary); font-size: 1.6rem; }
        .muted { color: var(--muted); font-size: 0.92rem; }
        label { display: grid; gap: 6px; font-weight: 700; color: var(--ink); margin-bottom: 12px; }
        input[type=text], input[type=tel] {
            padding: 12px; border: 1px solid var(--border);
            border-radius: 12px; font: inherit; width: 100%;
        }
        input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(30,43,66,0.15); }
        .row { display: grid; gap: 10px; margin: 14px 0; }
        .btn {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            padding: 13px 16px; border-radius: 12px; border: 2px solid var(--primary);
            font-weight: 700; color: var(--primary); background: #fff;
            cursor: pointer; font-size: 0.95rem; width: 100%;
            transition: transform 0.1s, background 0.15s;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn.primary { background: var(--accent); border-color: var(--accent); color: #fff; }
        .btn.primary:hover { background: var(--accent-hover); border-color: var(--accent-hover); }
        .btn.google { background: #DB4437; border-color: #DB4437; color: #fff; }
        .btn.apple { background: #000; border-color: #000; color: #fff; }
        .btn.microsoft { background: #00A4EF; border-color: #00A4EF; color: #fff; }
        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 18px 0; color: var(--muted); font-size: 0.85rem;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: var(--border);
        }
        .alert {
            padding: 10px 14px; border-radius: 10px; font-size: 0.9rem;
            margin-bottom: 12px; display: none;
        }
        .alert--error { color: #b91c1c; background: #fee2e2; border: 1px solid #fecaca; }
        .alert--success { color: #065f46; background: #d1fae5; border: 1px solid #a7f3d0; }
        .choices { display: flex; gap: 12px; flex-wrap: wrap; }
        .choices label { display: flex; align-items: center; gap: 8px; font-weight: 800; margin-bottom: 0; }
        .phone-row { display: flex; align-items: center; }
        .phone-row .prefix {
            padding: 12px 0; border: 1px solid var(--border); border-right: none;
            border-radius: 12px 0 0 12px; background: var(--light);
            font-weight: 700; color: var(--ink); width: 65px; text-align: center;
        }
        .phone-row input { border-radius: 0 12px 12px 0; flex: 1; }
        small.muted { color: var(--muted); font-size: 0.8rem; }
        .terms { display: flex; gap: 8px; align-items: flex-start; margin-bottom: 16px; }
        .terms input { margin-top: 4px; }
        .terms a { color: var(--accent); text-decoration: underline; }
        .back-link {
            display: block; text-align: center; margin-top: 12px;
            color: rgba(255,255,255,0.7); font-size: 0.85rem;
        }
        .back-link:hover { color: #fff; }
    </style>
</head>
<body>
    <div class="bg" aria-hidden="true"></div>
    <div class="scrim" aria-hidden="true"></div>

    <div class="wrap">
        <div class="card" role="main" aria-labelledby="loginTitle">
            <div class="logo-row">
                <img src="/shared/assets/img/kanda-square.png" alt="KandaNews">
                <span>KandaNews <?php echo $COUNTRY['flag']; ?></span>
            </div>

            <h1 id="loginTitle">Sign in to KandaNews</h1>
            <p class="muted">Use your Google, Apple, or Microsoft account to sign in.</p>

            <div id="msgError" class="alert alert--error" aria-live="assertive"></div>
            <div id="msgOk" class="alert alert--success" aria-live="polite"></div>

            <!-- Step 1: Social login buttons -->
            <div id="step1" class="row">
                <button class="btn google" id="googleSignIn" aria-label="Sign in with Google">
                    <i class="fa-brands fa-google"></i> Continue with Google
                </button>
                <button class="btn apple" id="appleSignIn" aria-label="Sign in with Apple">
                    <i class="fa-brands fa-apple"></i> Continue with Apple
                </button>
                <button class="btn microsoft" id="microsoftSignIn" aria-label="Sign in with Microsoft">
                    <i class="fa-brands fa-microsoft"></i> Continue with Microsoft
                </button>
            </div>

            <!-- Step 2: First-time registration (hidden initially) -->
            <div id="step3" class="row" style="display:none">
                <h2 style="margin: 0.2rem 0 0.6rem; color: var(--primary); font-size: 1.3rem;">
                    Welcome! Complete your profile
                </h2>

                <label for="name">Full names
                    <input id="name" type="text" placeholder="Your full names" autocomplete="name" required>
                </label>

                <label for="phone">WhatsApp number
                    <div class="phone-row">
                        <span class="prefix"><?php echo h($phone_prefix); ?></span>
                        <input id="phone" type="tel" inputmode="numeric" pattern="\d{<?php echo $phone_digits; ?>}" maxlength="<?php echo $phone_digits; ?>" placeholder="e.g. 772 123456" required>
                    </div>
                    <small class="muted">Enter the last <?php echo $phone_digits; ?> digits of your number.</small>
                </label>

                <div role="group" aria-labelledby="catLabel">
                    <div id="catLabel" style="font-weight: 800; margin-bottom: 6px;">I am a</div>
                    <div class="choices">
                        <label><input type="radio" name="cat" value="professional" required> Professional</label>
                        <label><input type="radio" name="cat" value="student"> University Student</label>
                        <label><input type="radio" name="cat" value="entrepreneur"> Entrepreneur</label>
                    </div>
                </div>

                <label for="org" style="margin-top: 8px;">University / Company Name
                    <input id="org" type="text" placeholder="e.g. Makerere University or MTN <?php echo h($COUNTRY['name']); ?>" required>
                </label>

                <div class="terms">
                    <input id="terms" type="checkbox" required>
                    <span>I agree to the <a href="/terms.php" target="_blank" rel="noopener">Terms</a> and <a href="/privacy.php" target="_blank" rel="noopener">Privacy Policy</a></span>
                </div>

                <button id="saveProfile" class="btn primary">Register &amp; Continue</button>
            </div>
        </div>

        <a href="/" class="back-link">&larr; Back to KandaNews <?php echo h($COUNTRY['name']); ?></a>
    </div>
</body>
</html>
