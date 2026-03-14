<?php
/**
 * KandaNews — Invite / Referral Smart Landing Page
 *
 * URL pattern:  /invite/{code}
 * Apache/nginx should rewrite /invite/* to this file.
 *
 * Behaviour:
 *  - APP_STORE_AVAILABLE=false → show "Coming Soon" waitlist page
 *  - APP_STORE_AVAILABLE=true  → redirect to Play Store / App Store
 *
 * The referral code travels through the URL so the app can pick it up
 * via deep-link or from the store referrer param.
 */

// ── Load env ──────────────────────────────────────────────────────────────────
$_envCandidates = [
    dirname(__DIR__, 2) . '/.env',
    dirname(__DIR__, 1) . '/.env',
    __DIR__ . '/../.env',
];
foreach ($_envCandidates as $_f) {
    if (is_file($_f)) {
        foreach (file($_f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
            if (!$_line || $_line[0] === '#' || strpos($_line, '=') === false) continue;
            [$_k, $_v] = explode('=', $_line, 2);
            $_ENV[trim($_k)] = trim($_v);
        }
        break;
    }
}

function invite_env(string $key, string $default = ''): string {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ── Extract code from path ────────────────────────────────────────────────────
// Supports both /invite/CODE and ?code=CODE
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$code = '';

if (preg_match('#/invite/([A-Z0-9]{4,12})#i', $path, $m)) {
    $code = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $m[1]));
} elseif (!empty($_GET['code'])) {
    $code = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $_GET['code']));
}

// ── Store availability config ─────────────────────────────────────────────────
$appAvailable  = filter_var(invite_env('APP_STORE_AVAILABLE', 'false'), FILTER_VALIDATE_BOOLEAN);
$playStoreUrl  = invite_env('PLAYSTORE_URL', '');
$appStoreUrl   = invite_env('APPSTORE_URL', '');

// ── Redirect if app is live ───────────────────────────────────────────────────
if ($appAvailable && ($playStoreUrl || $appStoreUrl)) {
    // Detect iOS vs Android via User-Agent for smart redirect
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $isIos = preg_match('/(iPhone|iPad|iPod)/i', $ua);

    $storeUrl = ($isIos && $appStoreUrl) ? $appStoreUrl : $playStoreUrl;

    // Append referral code as query param so the app can read it post-install
    if ($code) {
        $sep = str_contains($storeUrl, '?') ? '&' : '?';
        $storeUrl .= $sep . 'referral=' . urlencode($code);
    }

    header('Location: ' . $storeUrl, true, 302);
    exit;
}

// ── Coming Soon page ─────────────────────────────────────────────────────────
// Handle waitlist form submission
$waitlistSuccess = false;
$waitlistError   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email   = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL) ?? '');
    $invCode = trim($_POST['invite_code'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $waitlistError = 'Please enter a valid email address.';
    } else {
        // Store in a simple flat file (no DB dependency here).
        // Replace with DB write or mailing-list API call when ready.
        $line = date('Y-m-d H:i:s') . "\t" . $email . "\t" . ($invCode ?: '-') . PHP_EOL;
        @file_put_contents(__DIR__ . '/waitlist.tsv', $line, FILE_APPEND | LOCK_EX);
        $waitlistSuccess = true;
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KandaNews — Download the App</title>
    <meta name="description" content="Africa's digital flipping newspaper. Stay informed with KandaNews — coming to your device soon.">

    <!-- Open Graph (for WhatsApp / social previews) -->
    <meta property="og:title" content="You've been invited to KandaNews">
    <meta property="og:description" content="Africa's digital newspaper — local, pan-Africa, and built for your growth.">
    <meta property="og:image" content="<?php echo htmlspecialchars(invite_env('APP_URL', 'https://kandanews.africa')); ?>/shared/assets/img/og-invite.jpg">
    <meta property="og:url" content="<?php echo htmlspecialchars('https://' . ($_SERVER['HTTP_HOST'] ?? 'kandanews.africa') . $_SERVER['REQUEST_URI']); ?>">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
        }
        .card {
            background: #1e293b;
            border-radius: 20px;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            border: 1px solid rgba(255,255,255,.08);
            box-shadow: 0 24px 64px rgba(0,0,0,.4);
        }
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 32px;
        }
        .logo-icon {
            width: 48px; height: 48px;
            background: #f05a1a;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; font-weight: 800; color: #fff;
        }
        .logo-text { font-size: 22px; font-weight: 800; color: #fff; }
        .logo-text span { color: #f05a1a; }

        .badge {
            display: inline-block;
            background: rgba(240,90,26,.15);
            color: #f05a1a;
            border: 1px solid rgba(240,90,26,.3);
            border-radius: 20px;
            padding: 5px 14px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .5px;
            margin-bottom: 24px;
        }
        h1 {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            line-height: 1.25;
            margin-bottom: 12px;
        }
        .sub {
            font-size: 15px;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 36px;
        }

        /* Invite code display */
        .invite-code {
            display: inline-block;
            background: rgba(255,255,255,.06);
            border: 1.5px dashed rgba(255,255,255,.15);
            border-radius: 12px;
            padding: 12px 24px;
            font-family: 'Courier New', monospace;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 3px;
            color: #f8fafc;
            margin-bottom: 32px;
        }

        /* Form */
        .form-group { margin-bottom: 16px; text-align: left; }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        input[type=email], input[type=text] {
            width: 100%;
            padding: 13px 16px;
            background: rgba(255,255,255,.06);
            border: 1.5px solid rgba(255,255,255,.12);
            border-radius: 10px;
            color: #f1f5f9;
            font-size: 15px;
            transition: border-color .2s;
        }
        input:focus {
            outline: none;
            border-color: #f05a1a;
        }
        input::placeholder { color: #475569; }
        .btn {
            width: 100%;
            padding: 15px;
            background: #f05a1a;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s, transform .1s;
            margin-top: 8px;
        }
        .btn:hover { background: #ff7a3d; }
        .btn:active { transform: scale(.98); }

        .alert {
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 20px;
            text-align: left;
        }
        .alert-success { background: rgba(5,150,105,.15); color: #34d399; border: 1px solid rgba(5,150,105,.3); }
        .alert-error   { background: rgba(220,38,38,.15);  color: #f87171; border: 1px solid rgba(220,38,38,.3); }

        .features {
            display: flex;
            gap: 16px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 32px;
        }
        .feature {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #64748b;
        }
        .feature i { color: #f05a1a; font-size: 14px; }

        .footer-note {
            font-size: 12px;
            color: #475569;
            margin-top: 24px;
        }

        @media (max-width: 480px) {
            .card { padding: 36px 24px; }
            h1 { font-size: 22px; }
        }
    </style>
</head>
<body>

<div class="card">
    <!-- Logo -->
    <div class="logo">
        <div class="logo-icon">K</div>
        <div class="logo-text">Kanda<span>News</span></div>
    </div>

    <?php if ($waitlistSuccess): ?>
    <!-- ── Success state ── -->
    <div style="font-size:56px;margin-bottom:16px;">🎉</div>
    <h1>You're on the list!</h1>
    <p class="sub">We'll notify you the moment the app is available for download. Tell your friends — the more the merrier.</p>
    <?php if ($code): ?>
    <p style="font-size:13px;color:#64748b;margin-top:16px;">
        Your invite code <strong style="color:#f05a1a;"><?php echo htmlspecialchars($code); ?></strong> has been saved.
        You'll be recognised as a referral when you sign up.
    </p>
    <?php endif; ?>

    <?php else: ?>
    <!-- ── Main state ── -->
    <span class="badge"><i class="fas fa-rocket" style="margin-right:5px;"></i>Coming Soon</span>

    <?php if ($code): ?>
    <p style="font-size:13px;color:#94a3b8;margin-bottom:8px;">You've been invited with code</p>
    <div class="invite-code"><?php echo htmlspecialchars($code); ?></div>
    <?php endif; ?>

    <h1>Africa's Digital<br>Newspaper</h1>
    <p class="sub">
        KandaNews delivers local, pan-Africa news for students, professionals, and entrepreneurs —
        as an interactive flipbook, right on your phone.
    </p>

    <div class="features">
        <div class="feature"><i class="fas fa-newspaper"></i> Daily Editions</div>
        <div class="feature"><i class="fas fa-globe-africa"></i> Pan-Africa Coverage</div>
        <div class="feature"><i class="fas fa-mobile-alt"></i> Mobile-First</div>
    </div>

    <?php if (!empty($waitlistError)): ?>
    <div class="alert alert-error"><i class="fas fa-times-circle" style="margin-right:8px;"></i><?php echo htmlspecialchars($waitlistError); ?></div>
    <?php endif; ?>

    <form method="POST">
        <?php if ($code): ?>
        <input type="hidden" name="invite_code" value="<?php echo htmlspecialchars($code); ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="email">Notify me when the app launches</label>
            <input type="email" id="email" name="email"
                   placeholder="your@email.com"
                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                   required>
        </div>
        <button type="submit" class="btn">
            <i class="fas fa-bell" style="margin-right:8px;"></i>Notify Me at Launch
        </button>
    </form>

    <?php endif; ?>

    <p class="footer-note">
        Already have the app?
        <a href="https://<?php echo htmlspecialchars(parse_url(invite_env('APP_URL', 'https://kandanews.africa'), PHP_URL_HOST) ?? 'kandanews.africa'); ?>" style="color:#f05a1a;">
            Open KandaNews
        </a>
    </p>
</div>

</body>
</html>
