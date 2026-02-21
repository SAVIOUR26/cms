<?php
/**
 * KandaNews API v1 — Front Controller
 *
 * All requests to /api/v1/* are routed through this file.
 * No framework, no sessions — stateless JSON API with JWT auth.
 *
 * Endpoints:
 *   POST /auth/request-otp      Send OTP to phone
 *   POST /auth/verify-otp       Verify OTP → get tokens
 *   POST /auth/refresh           Refresh access token
 *
 *   GET  /user/profile           Get current user
 *   PUT  /user/profile           Update profile
 *
 *   GET  /editions               List editions
 *   GET  /editions/latest        Latest edition
 *   GET  /editions/{id}          Edition detail + pages
 *
 *   GET  /subscribe/plans        Available plans
 *   GET  /subscribe/status       Subscription status
 *   POST /subscribe/initiate     Start payment
 *   POST /subscribe/verify       Verify payment
 *
 *   POST /webhooks/flutterwave   Flutterwave callback
 *   POST /webhooks/dpo           DPO callback
 *
 *   POST /auth/register          Complete first-time registration
 *
 *   GET  /editions/today         Today's edition
 *
 *   GET  /misc/quote             Quote of the day
 */

// ── Bootstrap ──
header('Content-Type: application/json; charset=utf-8');

// Load config
$config = require __DIR__ . '/config/app.php';

// Error handling
if ($config['app_debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // Never leak errors in JSON API
} else {
    error_reporting(0);
}

set_exception_handler(function (Throwable $e) use ($config) {
    http_response_code(500);
    $body = ['ok' => false, 'error' => 'Internal server error'];
    if ($config['app_debug']) {
        $body['debug'] = $e->getMessage();
        $body['trace'] = $e->getTraceAsString();
    }
    echo json_encode($body);
    error_log("[API ERROR] " . $e->getMessage() . "\n" . $e->getTraceAsString());
    exit;
});

// Load helpers
require __DIR__ . '/helpers/response.php';
require __DIR__ . '/helpers/jwt.php';
require __DIR__ . '/helpers/otp.php';
require __DIR__ . '/config/database.php';
require __DIR__ . '/middleware/auth.php';

// ── CORS ──
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 86400');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Parse route ──
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($requestUri, PHP_URL_PATH);

// Strip /api/v1 prefix
$path = preg_replace('#^/api/v1/?#', '', $path);
$path = trim($path, '/');

$segments = $path ? explode('/', $path, 3) : [];
$resource = $segments[0] ?? '';
$action   = $segments[1] ?? '';
$extra    = $segments[2] ?? '';
$method   = $_SERVER['REQUEST_METHOD'];

// ── Route dispatch ──
switch ($resource) {
    case 'auth':
        require __DIR__ . '/routes/auth.php';
        route_auth($action, $method);
        break;

    case 'user':
        require __DIR__ . '/routes/user.php';
        route_user($action, $method);
        break;

    case 'editions':
        require __DIR__ . '/routes/editions.php';
        route_editions($action, $method);
        break;

    case 'subscribe':
        require __DIR__ . '/routes/subscribe.php';
        route_subscribe($action, $method);
        break;

    case 'webhooks':
        require __DIR__ . '/routes/webhooks.php';
        route_webhooks($action, $method);
        break;

    case 'misc':
        require __DIR__ . '/routes/misc.php';
        route_misc($action, $method);
        break;

    case '':
        json_success([
            'api'     => 'KandaNews API',
            'version' => 'v1',
            'status'  => 'operational',
        ]);
        break;

    default:
        json_error('Not found', 404);
}
