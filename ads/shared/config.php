<?php
// Load .env from /home/user/cms/.env
$envFile = '/home/user/cms/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (!$line || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
    }
}
function env(string $key, $default = null) { return $_ENV[$key] ?? getenv($key) ?: $default; }

define('DB_HOST',   env('DB_HOST', 'localhost'));
define('DB_NAME',   env('DB_NAME', 'kandan_api'));
define('DB_USER',   env('DB_USER', 'kandan_api'));
define('DB_PASS',   env('DB_PASS', ''));
define('FW_SECRET', env('FW_SECRET_KEY', ''));
define('FW_PUBLIC', env('FW_PUBLIC_KEY', ''));
define('FW_HASH',   env('FW_WEBHOOK_HASH', ''));
define('SITE_URL',  'https://ads.kandanews.africa');
define('SESSION_NAME', 'kn_ads_sess');

// Ad formats
define('AD_FORMATS', [
    'full_page'         => ['label'=>'Full Page',             'desc'=>'Entire page (540×780px)',                          'price'=>200000, 'variants'=>[]],
    'half_page'         => ['label'=>'Half Page',             'desc'=>'Half of screen (540×390px)',                       'price'=>120000, 'variants'=>[]],
    'video_60'          => ['label'=>'Video Ad (60 sec)',      'desc'=>'Auto-play within content, up to 60 seconds',       'price'=>120000, 'variants'=>[]],
    'video_30'          => ['label'=>'Video Ad (30 sec)',      'desc'=>'Auto-play within content, up to 30 seconds',       'price'=>60000,  'variants'=>[]],
    'audio_60'          => ['label'=>'Audio Ad (60 sec)',      'desc'=>'Voicenote or podcast-style, up to 60 seconds',     'price'=>60000,  'variants'=>[]],
    'audio_30'          => ['label'=>'Audio Ad (30 sec)',      'desc'=>'Voicenote or podcast-style, up to 30 seconds',     'price'=>30000,  'variants'=>[]],
    'gif_insert'        => ['label'=>'GIF Insert',             'desc'=>'Looping graphic / 4-image slider (up to 15 sec)', 'price'=>80000,  'variants'=>[]],
    'cart_ad'           => ['label'=>'Cart Ad',                'desc'=>'Product image + cart button (Cart card)',          'price'=>50000,  'variants'=>[]],
    'market_listing'    => ['label'=>'Market Listing',         'desc'=>'Kanda Market Hustle | Classified link',           'price'=>10000,  'variants'=>[]],
    'sponsored_content' => ['label'=>'Sponsored Content',      'desc'=>'5-minute documentary / native content',           'price'=>300000, 'variants'=>[]],
]);
define('DISCOUNT_WEEKLY',  0.10); // 10% for 7+ days
define('DISCOUNT_MONTHLY', 0.20); // 20% for 30+ days
