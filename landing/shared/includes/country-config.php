<?php
$COUNTRIES = [
    'ug' => [
        'name' => 'Uganda',
        'flag' => "\xF0\x9F\x87\xBA\xF0\x9F\x87\xAC",
        'currency' => 'UGX',
        'phone_prefix' => '+256',
        'phone_digits' => 9,
        'timezone' => 'Africa/Kampala',
        'email' => 'hello@ug.kandanews.africa',
        'whatsapp' => '+256700000000',
        'plans' => [
            'daily'   => ['amount' => 500,  'label' => 'UGX 500 / day'],
            'weekly'  => ['amount' => 2500, 'label' => 'UGX 2,500 / week'],
            'monthly' => ['amount' => 7500, 'label' => 'UGX 7,500 / month'],
        ],
    ],
    'ke' => [
        'name' => 'Kenya',
        'flag' => "\xF0\x9F\x87\xB0\xF0\x9F\x87\xAA",
        'currency' => 'KES',
        'phone_prefix' => '+254',
        'phone_digits' => 9,
        'timezone' => 'Africa/Nairobi',
        'email' => 'hello@ke.kandanews.africa',
        'whatsapp' => '+254700000000',
        'plans' => [
            'daily'   => ['amount' => 20,  'label' => 'KES 20 / day'],
            'weekly'  => ['amount' => 100, 'label' => 'KES 100 / week'],
            'monthly' => ['amount' => 300, 'label' => 'KES 300 / month'],
        ],
    ],
    'za' => [
        'name' => 'South Africa',
        'flag' => "\xF0\x9F\x87\xBF\xF0\x9F\x87\xA6",
        'currency' => 'ZAR',
        'phone_prefix' => '+27',
        'phone_digits' => 9,
        'timezone' => 'Africa/Johannesburg',
        'email' => 'hello@za.kandanews.africa',
        'whatsapp' => '+27600000000',
        'plans' => [
            'daily'   => ['amount' => 5,   'label' => 'ZAR 5 / day'],
            'weekly'  => ['amount' => 25,  'label' => 'ZAR 25 / week'],
            'monthly' => ['amount' => 75,  'label' => 'ZAR 75 / month'],
        ],
    ],
    'ng' => [
        'name' => 'Nigeria',
        'flag' => "\xF0\x9F\x87\xB3\xF0\x9F\x87\xAC",
        'currency' => 'NGN',
        'phone_prefix' => '+234',
        'phone_digits' => 10,
        'timezone' => 'Africa/Lagos',
        'email' => 'hello@ng.kandanews.africa',
        'whatsapp' => '+234800000000',
        'plans' => [
            'daily'   => ['amount' => 200,  'label' => 'NGN 200 / day'],
            'weekly'  => ['amount' => 1000, 'label' => 'NGN 1,000 / week'],
            'monthly' => ['amount' => 3000, 'label' => 'NGN 3,000 / month'],
        ],
    ],
];

// Auto-detect country from subdomain
function detectCountry() {
    global $COUNTRIES;
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if (preg_match('/^([a-z]{2})\./', $host, $m)) {
        $code = strtolower($m[1]);
        if (isset($COUNTRIES[$code])) return $code;
    }
    return 'ug'; // default
}

$COUNTRY_CODE = detectCountry();
$COUNTRY = $COUNTRIES[$COUNTRY_CODE];
