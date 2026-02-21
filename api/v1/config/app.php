<?php
/**
 * KandaNews API v1 — Configuration
 *
 * Reads settings from environment or falls back to defaults.
 * NEVER commit real credentials — use .env on the server.
 */

// Load .env if available
$envFile = dirname(__DIR__, 2) . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line[0] === '#') continue;
        if (strpos($line, '=') === false) continue;
        [$key, $val] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($val);
    }
}

function env(string $key, $default = null) {
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

return [
    // ── App ──
    'app_env'   => env('APP_ENV', 'production'),
    'app_debug' => env('APP_DEBUG', 'false') === 'true',
    'app_url'   => env('APP_URL', 'https://kandanews.africa'),
    'api_url'   => env('API_URL', 'https://kandanews.africa/api/v1'),

    // ── Database ──
    'db_host'    => env('DB_HOST', 'localhost'),
    'db_name'    => env('DB_NAME', 'kandan_api'),
    'db_user'    => env('DB_USER', 'kandan_api'),
    'db_pass'    => env('DB_PASS', ''),
    'db_charset' => env('DB_CHARSET', 'utf8mb4'),

    // ── JWT Auth ──
    'jwt_secret'   => env('JWT_SECRET', 'CHANGE-ME-IN-PRODUCTION-' . md5(__DIR__)),
    'jwt_ttl'      => (int) env('JWT_TTL', 86400 * 30),       // 30 days
    'jwt_refresh'  => (int) env('JWT_REFRESH_TTL', 86400 * 90), // 90 days

    // ── OTP ──
    'otp_length'  => 6,
    'otp_ttl'     => 300, // 5 minutes
    'otp_max_attempts' => 5,

    // ── SMS (Africa's Talking) ──
    'at_api_key'   => env('AT_API_KEY', ''),
    'at_username'  => env('AT_USERNAME', 'sandbox'),
    'at_sender_id' => env('AT_SENDER_ID', 'KandaNews'),

    // ── Payments: Flutterwave ──
    'fw_public_key'    => env('FW_PUBLIC_KEY', ''),
    'fw_secret_key'    => env('FW_SECRET_KEY', ''),
    'fw_webhook_hash'  => env('FW_WEBHOOK_HASH', ''),

    // ── Payments: DPO ──
    'dpo_company_token' => env('DPO_COMPANY_TOKEN', ''),
    'dpo_service_type'  => env('DPO_SERVICE_TYPE', ''),

    // ── Editions ──
    'editions_path' => env('EDITIONS_PATH', dirname(__DIR__, 2) . '/editions'),
    'editions_url'  => env('EDITIONS_URL', 'https://ug.kandanews.africa/editions'),

    // ── Subscription pricing (UGX) ──
    'pricing' => [
        'ug' => ['currency' => 'UGX', 'daily' => 500,   'weekly' => 2500,    'monthly' => 7500],
        'ke' => ['currency' => 'KES', 'daily' => 20,    'weekly' => 100,     'monthly' => 300],
        'ng' => ['currency' => 'NGN', 'daily' => 100,   'weekly' => 500,     'monthly' => 1500],
        'za' => ['currency' => 'ZAR', 'daily' => 5,     'weekly' => 25,      'monthly' => 70],
    ],

    // ── CORS ──
    'cors_origins' => ['*'], // Restrict in production
];
