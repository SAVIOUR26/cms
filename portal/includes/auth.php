<?php
/**
 * KandaNews Africa — Portal Auth Helper
 *
 * Provides authentication utilities and database connections
 * for the Upload Portal. Connects to `kandan_api` for edition
 * data and to `kandan_api`.`cms_admins` for admin authentication.
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

date_default_timezone_set('Africa/Kampala');

// ──────────────────────────────────────────────
// .env loader (mirrors api/v1/config/app.php)
// ──────────────────────────────────────────────
$_portal_env_loaded = false;
$_portal_env_file   = dirname(__DIR__, 2) . '/.env';

if (is_file($_portal_env_file)) {
    foreach (file($_portal_env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $_line) {
        $_line = trim($_line);
        if ($_line === '' || $_line[0] === '#') continue;
        if (strpos($_line, '=') === false) continue;
        [$_k, $_v] = explode('=', $_line, 2);
        $_ENV[trim($_k)] = trim($_v);
    }
    $_portal_env_loaded = true;
}

function portal_env(string $key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// ──────────────────────────────────────────────
// Database: kandan_api (editions, cms_admins)
// ──────────────────────────────────────────────
function portal_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $host    = portal_env('DB_HOST', 'localhost');
    $dbname  = portal_env('DB_NAME', 'kandan_api');
    $user    = portal_env('DB_USER', 'kandan_api');
    $pass    = portal_env('DB_PASS', '');
    $charset = portal_env('DB_CHARSET', 'utf8mb4');

    $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}

// ──────────────────────────────────────────────
// CSRF helpers
// ──────────────────────────────────────────────
function portal_csrf_token(): string {
    if (empty($_SESSION['portal_csrf'])) {
        $_SESSION['portal_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['portal_csrf'];
}

function portal_csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="' . portal_csrf_token() . '">';
}

function portal_verify_csrf(): bool {
    $token = $_POST['_csrf'] ?? $_GET['_csrf'] ?? '';
    if (empty($token) || empty($_SESSION['portal_csrf'])) return false;
    return hash_equals($_SESSION['portal_csrf'], $token);
}

// ──────────────────────────────────────────────
// Auth functions
// ──────────────────────────────────────────────

/**
 * Check if the current session is authenticated as a portal admin.
 */
function portal_is_logged_in(): bool {
    return !empty($_SESSION['portal_admin_id']);
}

/**
 * Redirect to login page if not authenticated.
 */
function portal_require_login(): void {
    if (!portal_is_logged_in()) {
        header('Location: ' . portal_url('login.php'));
        exit;
    }
}

/**
 * Return the current admin user row, or null.
 */
function portal_get_user(): ?array {
    if (!portal_is_logged_in()) return null;

    // Cache in session to avoid repeated queries
    if (!empty($_SESSION['portal_admin_data'])) {
        return $_SESSION['portal_admin_data'];
    }

    try {
        $stmt = portal_db()->prepare(
            "SELECT id, username, full_name, role, status, last_login
             FROM cms_admins WHERE id = ? AND status = 'active' LIMIT 1"
        );
        $stmt->execute([$_SESSION['portal_admin_id']]);
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['portal_admin_data'] = $user;
            return $user;
        }
    } catch (PDOException $e) {
        // fall through
    }

    // user deleted / suspended since login — force logout
    portal_logout();
    return null;
}

/**
 * Destroy the portal session and redirect to login.
 */
function portal_logout(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: ' . portal_url('login.php'));
    exit;
}

/**
 * Attempt to authenticate an admin.
 * Returns [true, $user] on success, [false, $error] on failure.
 */
function portal_authenticate(string $username, string $password): array {
    try {
        $db   = portal_db();
        $stmt = $db->prepare(
            "SELECT id, username, password, full_name, role, status
             FROM cms_admins WHERE username = ? LIMIT 1"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            return [false, 'Invalid username or password.'];
        }

        if ($user['status'] !== 'active') {
            return [false, 'Your account has been suspended. Contact an administrator.'];
        }

        // Support both hashed and plaintext (legacy) passwords
        $valid = password_verify($password, $user['password'])
              || ($password === $user['password']);

        if (!$valid) {
            return [false, 'Invalid username or password.'];
        }

        // Update last_login
        $db->prepare("UPDATE cms_admins SET last_login = NOW() WHERE id = ?")
           ->execute([$user['id']]);

        // Set session
        $_SESSION['portal_admin_id']   = $user['id'];
        $_SESSION['portal_admin_data'] = [
            'id'        => $user['id'],
            'username'  => $user['username'],
            'full_name' => $user['full_name'],
            'role'      => $user['role'],
            'status'    => $user['status'],
        ];

        // Regenerate session id to prevent fixation
        session_regenerate_id(true);

        return [true, $user];
    } catch (PDOException $e) {
        return [false, 'Database error. Please try again later.'];
    }
}

// ──────────────────────────────────────────────
// URL helpers
// ──────────────────────────────────────────────

function portal_base_url(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'cms.kandanews.africa';
    return $scheme . '://' . $host . '/portal';
}

function portal_url(string $path = ''): string {
    return portal_base_url() . '/' . ltrim($path, '/');
}

function portal_cms_url(string $path = ''): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'cms.kandanews.africa';
    return $scheme . '://' . $host . '/' . ltrim($path, '/');
}

// ──────────────────────────────────────────────
// Misc helpers
// ──────────────────────────────────────────────

function portal_sanitize(string $str): string {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

function portal_slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

function portal_flash(string $type, string $message): void {
    $_SESSION['portal_flash'] = ['type' => $type, 'message' => $message];
}

function portal_get_flash(): ?array {
    if (isset($_SESSION['portal_flash'])) {
        $flash = $_SESSION['portal_flash'];
        unset($_SESSION['portal_flash']);
        return $flash;
    }
    return null;
}

/**
 * Country labels used throughout the portal.
 */
function portal_countries(): array {
    return [
        'UG' => 'Uganda',
        'KE' => 'Kenya',
        'NG' => 'Nigeria',
        'ZA' => 'South Africa',
    ];
}
