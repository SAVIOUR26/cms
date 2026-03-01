<?php
/**
 * KandaNews API v1 — Subscription Routes
 *
 * GET  /subscribe/status      — Current subscription status
 * GET  /subscribe/plans       — Available plans & pricing
 * POST /subscribe/initiate    — Start a payment (Flutterwave / DPO)
 * POST /subscribe/verify      — Verify a payment reference
 */

function route_subscribe(string $action, string $method): void {
    switch ("$method $action") {
        case 'GET status':   subscribe_status();   break;
        case 'GET plans':    subscribe_plans();     break;
        case 'POST initiate': subscribe_initiate(); break;
        case 'POST verify':  subscribe_verify();    break;
        default: json_error('Not found', 404);
    }
}

/**
 * GET /subscribe/status
 */
function subscribe_status(): void {
    $user = require_auth();
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT id, plan, status, payment_provider, starts_at, expires_at, amount, currency
        FROM subscriptions
        WHERE user_id = ? AND status IN ('active', 'pending')
        ORDER BY expires_at DESC LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $sub = $stmt->fetch();

    if (!$sub || ($sub['status'] === 'active' && strtotime($sub['expires_at']) < time())) {
        json_success([
            'subscribed' => false,
            'subscription' => null,
        ]);
        return;
    }

    json_success([
        'subscribed' => $sub['status'] === 'active',
        'subscription' => [
            'id'               => (int) $sub['id'],
            'plan'             => $sub['plan'],
            'status'           => $sub['status'],
            'payment_provider' => $sub['payment_provider'],
            'starts_at'        => $sub['starts_at'],
            'expires_at'       => $sub['expires_at'],
            'amount'           => (float) $sub['amount'],
            'currency'         => $sub['currency'],
        ],
    ]);
}

/**
 * GET /subscribe/plans?country=ug
 */
function subscribe_plans(): void {
    global $config;

    $country = strtolower($_GET['country'] ?? 'ug');
    $pricing = $config['pricing'][$country] ?? $config['pricing']['ug'];

    json_success([
        'country'  => $country,
        'currency' => $pricing['currency'],
        'plans'    => [
            [
                'id'       => 'daily',
                'label'    => 'Daily',
                'price'    => $pricing['daily'],
                'duration' => '1 day',
                'days'     => 1,
            ],
            [
                'id'       => 'weekly',
                'label'    => 'Weekly',
                'price'    => $pricing['weekly'],
                'duration' => '7 days',
                'days'     => 7,
                'popular'  => true,
            ],
            [
                'id'       => 'monthly',
                'label'    => 'Monthly',
                'price'    => $pricing['monthly'],
                'duration' => '30 days',
                'days'     => 30,
            ],
        ],
    ]);
}

/**
 * POST /subscribe/initiate
 * Body: { "plan": "weekly", "provider": "flutterwave", "country": "ug" }
 *
 * Creates a pending subscription and returns the payment link/reference.
 */
function subscribe_initiate(): void {
    global $config;
    $user = require_auth();
    $input = json_input();

    $plan     = $input['plan'] ?? '';
    $provider = $input['provider'] ?? 'flutterwave';
    $country  = strtolower($input['country'] ?? $user['country'] ?? 'ug');

    // Validate plan
    $pricing = $config['pricing'][$country] ?? $config['pricing']['ug'];
    $plans = ['daily' => 1, 'weekly' => 7, 'monthly' => 30];
    if (!isset($plans[$plan])) json_error('Invalid plan. Choose daily, weekly, or monthly.');

    $amount   = $pricing[$plan];
    $currency = $pricing['currency'];
    $days     = $plans[$plan];
    $txRef    = 'KN-' . strtoupper($country) . '-' . $user['id'] . '-' . time();

    // Create pending subscription
    $pdo = db();
    $stmt = $pdo->prepare("
        INSERT INTO subscriptions (user_id, plan, status, payment_provider, payment_ref, amount, currency, starts_at, expires_at)
        VALUES (?, ?, 'pending', ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? DAY))
    ");
    $stmt->execute([$user['id'], $plan, $provider, $txRef, $amount, $currency, $days]);
    $subId = (int) $pdo->lastInsertId();

    // Build payment link based on provider
    if ($provider === 'flutterwave') {
        $paymentData = flutterwave_init($user, $txRef, $amount, $currency, $plan);
    } elseif ($provider === 'dpo') {
        $paymentData = dpo_init($user, $txRef, $amount, $currency, $plan);
    } else {
        json_error('Invalid payment provider. Choose flutterwave or dpo.');
    }

    json_success([
        'subscription_id' => $subId,
        'payment_ref'     => $txRef,
        'amount'          => $amount,
        'currency'        => $currency,
        'provider'        => $provider,
        'link'            => $paymentData['link'] ?? null,
        'payment'         => $paymentData,
    ]);
}

/**
 * POST /subscribe/verify
 * Body: { "payment_ref": "KN-UG-1-...", "transaction_id": "..." }
 */
function subscribe_verify(): void {
    $user = require_auth();
    $input = json_input();
    // Accept both naming conventions from clients
    $ref  = $input['payment_ref'] ?? $input['reference'] ?? '';
    $txId = $input['transaction_id'] ?? $input['tx_id'] ?? '';

    if (!$ref) json_error('Payment reference is required');

    $pdo = db();
    $stmt = $pdo->prepare("
        SELECT id, plan, payment_provider, amount, currency, status
        FROM subscriptions WHERE payment_ref = ? AND user_id = ?
    ");
    $stmt->execute([$ref, $user['id']]);
    $sub = $stmt->fetch();

    if (!$sub) json_error('Subscription not found', 404);
    if ($sub['status'] === 'active') {
        json_success(['status' => 'already_active', 'message' => 'Subscription is already active']);
        return;
    }

    // Verify with payment provider
    $verified = false;
    if ($sub['payment_provider'] === 'flutterwave' && $txId) {
        $verified = flutterwave_verify($txId, (float) $sub['amount'], $sub['currency']);
    } elseif ($sub['payment_provider'] === 'dpo' && $txId) {
        $verified = dpo_verify($txId);
    }

    if ($verified) {
        $pdo->prepare("UPDATE subscriptions SET status = 'active', payment_tx_id = ? WHERE id = ?")
            ->execute([$txId, $sub['id']]);

        json_success(['status' => 'active', 'message' => 'Subscription activated successfully']);
    } else {
        json_success(['status' => 'pending', 'message' => 'Payment not yet confirmed']);
    }
}

// ─────────────────────────────────────────────
//  Payment Provider Helpers
// ─────────────────────────────────────────────

function flutterwave_init(array $user, string $ref, float $amount, string $currency, string $plan): array {
    global $config;

    $secret = $config['fw_secret_key'];
    $redirectUrl = $config['api_url'] . '/subscribe/callback?provider=flutterwave&ref=' . urlencode($ref);

    $payload = [
        'tx_ref'          => $ref,
        'amount'          => $amount,
        'currency'        => $currency,
        'redirect_url'    => $redirectUrl,
        'payment_options' => 'mobilemoney,card',
        'customer'        => [
            'phone_number' => $user['phone'],
            'name'         => $user['full_name'] ?? 'KandaNews User',
        ],
        'customizations'  => [
            'title'       => 'KandaNews ' . ucfirst($plan) . ' Plan',
            'description' => 'KandaNews subscription — ' . $plan . ' access',
            'logo'        => $config['app_url'] . '/shared/assets/img/kanda-square.png',
        ],
    ];

    // Call Flutterwave Standard Payment API to get a hosted checkout link
    $ch = curl_init('https://api.flutterwave.com/v3/payments');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer $secret",
            'Content-Type: application/json',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $resp = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $link = $resp['data']['link'] ?? null;
    if (!$link) {
        // Fallback: return payment data for client-side initialization
        return [
            'method'     => 'flutterwave_standard',
            'public_key' => $config['fw_public_key'],
            'tx_ref'     => $ref,
            'amount'     => $amount,
            'currency'   => $currency,
            'link'       => null,
        ];
    }

    return [
        'method' => 'redirect',
        'link'   => $link,
        'tx_ref' => $ref,
    ];
}

function flutterwave_verify(string $txId, float $expectedAmount, string $expectedCurrency): bool {
    global $config;
    $secret = $config['fw_secret_key'];
    if (!$secret) return false;

    $ch = curl_init("https://api.flutterwave.com/v3/transactions/$txId/verify");
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER     => ["Authorization: Bearer $secret"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $resp = json_decode(curl_exec($ch), true);
    curl_close($ch);

    if (($resp['status'] ?? '') !== 'success') return false;

    $data = $resp['data'] ?? [];
    return ($data['status'] ?? '') === 'successful'
        && (float) ($data['amount'] ?? 0) >= $expectedAmount
        && ($data['currency'] ?? '') === $expectedCurrency;
}

function dpo_init(array $user, string $ref, float $amount, string $currency, string $plan): array {
    global $config;

    $companyToken = $config['dpo_company_token'];
    $serviceType  = $config['dpo_service_type'];
    $redirectUrl  = $config['api_url'] . '/subscribe/callback?provider=dpo&ref=' . urlencode($ref);

    // Build DPO createToken XML request
    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <API3G>
        <CompanyToken>' . htmlspecialchars($companyToken) . '</CompanyToken>
        <Request>createToken</Request>
        <Transaction>
            <PaymentAmount>' . number_format($amount, 2, '.', '') . '</PaymentAmount>
            <PaymentCurrency>' . htmlspecialchars($currency) . '</PaymentCurrency>
            <CompanyRef>' . htmlspecialchars($ref) . '</CompanyRef>
            <RedirectURL>' . htmlspecialchars($redirectUrl) . '</RedirectURL>
            <BackURL>' . htmlspecialchars($redirectUrl) . '&cancelled=1</BackURL>
            <CompanyRefUnique>1</CompanyRefUnique>
            <PTL>30</PTL>
        </Transaction>
        <Services>
            <Service>
                <ServiceType>' . htmlspecialchars($serviceType) . '</ServiceType>
                <ServiceDescription>KandaNews ' . htmlspecialchars(ucfirst($plan)) . ' Plan</ServiceDescription>
                <ServiceDate>' . date('Y/m/d H:i') . '</ServiceDate>
            </Service>
        </Services>
    </API3G>';

    $ch = curl_init('https://secure.3gdirectpay.com/API/v6/');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $xml,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/xml'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);

    // Extract TransToken from DPO response
    $transToken = null;
    if (preg_match('/<TransToken>(.*?)<\/TransToken>/', $resp, $matches)) {
        $transToken = $matches[1];
    }

    if ($transToken) {
        return [
            'method'      => 'redirect',
            'link'        => 'https://secure.3gdirectpay.com/payv3.php?ID=' . $transToken,
            'tx_ref'      => $ref,
            'trans_token'  => $transToken,
        ];
    }

    // Fallback if token creation failed
    return [
        'method'     => 'dpo_redirect',
        'tx_ref'     => $ref,
        'amount'     => $amount,
        'currency'   => $currency,
        'link'       => null,
    ];
}

function dpo_verify(string $txId): bool {
    global $config;
    $token = $config['dpo_company_token'];
    if (!$token) return false;

    $xml = '<?xml version="1.0" encoding="utf-8"?>
    <API3G>
        <CompanyToken>' . htmlspecialchars($token) . '</CompanyToken>
        <Request>verifyToken</Request>
        <TransactionToken>' . htmlspecialchars($txId) . '</TransactionToken>
    </API3G>';

    $ch = curl_init('https://secure.3gdirectpay.com/API/v6/');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $xml,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/xml'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);

    // Check for success status code (000)
    return preg_match('/<Result>000<\/Result>/', $resp) === 1;
}
