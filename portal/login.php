<?php
/**
 * KandaNews Africa — Portal Login
 *
 * Authenticates against `cms_admins` table in the kandan_api database.
 * Supports CSRF protection and session-based auth.
 */

require_once __DIR__ . '/includes/auth.php';

// ── Handle logout ────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    portal_logout(); // redirects to login.php
}

// ── Already logged in? redirect to dashboard ─
if (portal_is_logged_in()) {
    header('Location: ' . portal_url('index.php'));
    exit;
}

$error = '';

// ── Handle login POST ────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portal_verify_csrf()) {
        $error = 'Invalid request. Please reload and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please enter both username and password.';
        } else {
            [$ok, $result] = portal_authenticate($username, $password);
            if ($ok) {
                header('Location: ' . portal_url('index.php'));
                exit;
            }
            $error = $result; // error message string
        }
    }
}

$csrf = portal_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — KandaNews Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #1e2b42 0%, #2a3f5f 50%, #1e2b42 100%);
            background-size: 400% 400%;
            animation: bgShift 12s ease infinite;
        }
        @keyframes bgShift {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating shapes (decorative) */
        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            opacity: .06;
            pointer-events: none;
        }
        body::before {
            width: 500px; height: 500px;
            background: #f05a1a;
            top: -120px; right: -120px;
        }
        body::after {
            width: 350px; height: 350px;
            background: #f05a1a;
            bottom: -80px; left: -80px;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 24px 64px rgba(0,0,0,.35);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        /* ── Header ──────────────────── */
        .login-header {
            background: linear-gradient(135deg, #1e2b42, #2a3f5f);
            padding: 40px 32px 32px;
            text-align: center;
            color: #fff;
            position: relative;
        }
        .login-header::after {
            content: '';
            position: absolute;
            bottom: -1px; left: 0; right: 0;
            height: 20px;
            background: #fff;
            border-radius: 20px 20px 0 0;
        }
        .login-logo {
            width: 72px; height: 72px;
            margin: 0 auto 16px;
            background: rgba(240,90,26,.15);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: #f05a1a;
            backdrop-filter: blur(8px);
            border: 2px solid rgba(240,90,26,.2);
        }
        .login-header h1 {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .login-header h1 span { color: #f05a1a; }
        .login-header p {
            font-size: 14px;
            opacity: .75;
        }

        /* ── Body ────────────────────── */
        .login-body { padding: 32px 32px 12px; }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            animation: shake .4s ease;
        }
        @keyframes shake {
            0%,100% { transform:translateX(0); }
            20%     { transform:translateX(-6px); }
            40%     { transform:translateX(6px); }
            60%     { transform:translateX(-4px); }
            80%     { transform:translateX(4px); }
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .form-group { margin-bottom: 22px; }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            color: #1e2b42;
            margin-bottom: 6px;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap i {
            position: absolute;
            left: 14px; top: 50%; transform: translateY(-50%);
            color: #aaa;
            font-size: 15px;
            pointer-events: none;
            transition: color .2s;
        }
        .input-wrap input {
            width: 100%;
            padding: 14px 14px 14px 42px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 15px;
            font-family: inherit;
            transition: border-color .2s, box-shadow .2s;
            background: #fff;
        }
        .input-wrap input:focus {
            outline: none;
            border-color: #f05a1a;
            box-shadow: 0 0 0 3px rgba(240,90,26,.1);
        }
        .input-wrap input:focus + i,
        .input-wrap input:focus ~ i { color: #f05a1a; }
        .input-wrap input::placeholder { color: #bbb; }

        .toggle-pass {
            position: absolute;
            right: 14px; top: 50%; transform: translateY(-50%);
            background: none; border: none;
            color: #aaa; font-size: 15px;
            cursor: pointer;
            padding: 4px;
        }
        .toggle-pass:hover { color: #666; }

        .btn-login {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #f05a1a, #ff7a3d);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s;
            font-family: inherit;
            box-shadow: 0 4px 16px rgba(240,90,26,.3);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(240,90,26,.4);
        }
        .btn-login:active { transform: translateY(0); }

        /* ── Footer ──────────────────── */
        .login-footer {
            text-align: center;
            padding: 16px 32px 32px;
            font-size: 13px;
            color: #999;
        }
        .login-footer a {
            color: #f05a1a;
            text-decoration: none;
            font-weight: 600;
        }
        .login-footer a:hover { text-decoration: underline; }
        .login-version {
            margin-top: 12px;
            font-size: 12px;
            color: #bbb;
        }

        @media (max-width: 480px) {
            .login-card { border-radius: 16px; }
            .login-header { padding: 32px 24px 28px; }
            .login-body   { padding: 24px 24px 8px; }
            .login-footer { padding: 12px 24px 24px; }
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-newspaper"></i>
            </div>
            <h1>Kanda<span>News</span> Portal</h1>
            <p>Edition Upload &amp; Management</p>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="" autocomplete="off">
                <input type="hidden" name="_csrf" value="<?php echo $csrf; ?>">

                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <input type="text" id="username" name="username" placeholder="Enter your username"
                               required autofocus autocomplete="username"
                               value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                        <i class="fas fa-user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <input type="password" id="password" name="password" placeholder="Enter your password"
                               required autocomplete="current-password">
                        <i class="fas fa-lock"></i>
                        <button type="button" class="toggle-pass" onclick="togglePassword()" aria-label="Toggle password visibility">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>
        </div>

        <div class="login-footer">
            <p>Need help? <a href="mailto:support@kandanews.africa">Contact Support</a></p>
            <p class="login-version">KandaNews Portal v1.0 &mdash; The Future of News</p>
        </div>
    </div>

    <script>
    function togglePassword() {
        var pw   = document.getElementById('password');
        var icon = document.getElementById('toggleIcon');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            pw.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }

    document.getElementById('username').focus();
    </script>
</body>
</html>
