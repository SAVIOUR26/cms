<?php
/**
 * KandaNews API v1 — Auth Routes
 *
 * POST /auth/request-otp   — Send OTP to phone
 * POST /auth/verify-otp    — Verify OTP, get tokens
 * POST /auth/refresh        — Refresh access token
 */

function route_auth(string $action, string $method): void {
    switch ("$method $action") {
        case 'POST request-otp':  auth_request_otp(); break;
        case 'POST verify-otp':   auth_verify_otp();  break;
        case 'POST refresh':      auth_refresh();      break;
        default: json_error('Not found', 404);
    }
}

/**
 * POST /auth/request-otp
 * Body: { "phone": "+256...", "country": "ug" }
 */
function auth_request_otp(): void {
    $input = json_input();
    $country = $input['country'] ?? 'ug';
    $phone = $input['phone'] ?? '';

    if (!$phone) json_error('Phone number is required');

    $phone = normalize_phone($phone, $country);

    // Validate format (E.164: + followed by 7-15 digits)
    if (!preg_match('/^\+\d{7,15}$/', $phone)) {
        json_error('Invalid phone number format');
    }

    // Rate limit
    if (!otp_rate_ok($phone)) {
        json_error('Too many OTP requests. Try again later.', 429);
    }

    // Generate, store, send
    $code = otp_generate();
    otp_store($phone, $code);
    $sent = otp_send_sms($phone, $code);

    if (!$sent) {
        json_error('Failed to send SMS. Please try again.', 502);
    }

    json_success([
        'message' => 'OTP sent successfully',
        'phone'   => substr($phone, 0, 4) . '****' . substr($phone, -3),
        'ttl'     => 300,
    ]);
}

/**
 * POST /auth/verify-otp
 * Body: { "phone": "+256...", "code": "123456", "country": "ug" }
 *
 * If the phone doesn't have an account, one is created automatically.
 * Returns JWT tokens.
 */
function auth_verify_otp(): void {
    $input = json_input();
    $country = $input['country'] ?? 'ug';
    $phone = normalize_phone($input['phone'] ?? '', $country);
    $code  = $input['code'] ?? '';

    if (!$phone || !$code) json_error('Phone and code are required');

    if (!otp_verify($phone, $code)) {
        json_error('Invalid or expired code', 401);
    }

    // Find or create user
    $pdo = db();
    $stmt = $pdo->prepare("SELECT id, phone, full_name, country FROM users WHERE phone = ?");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if (!$user) {
        // Auto-register
        $stmt = $pdo->prepare("
            INSERT INTO users (phone, country, status, created_at)
            VALUES (?, ?, 'active', NOW())
        ");
        $stmt->execute([$phone, $country]);
        $userId = (int) $pdo->lastInsertId();
        $user = ['id' => $userId, 'phone' => $phone, 'full_name' => null, 'country' => $country];
    }

    // Issue tokens
    $tokens = jwt_create_tokens((int) $user['id'], $phone, $user['country'] ?? $country);

    json_success([
        'tokens'    => $tokens,
        'user'      => [
            'id'        => (int) $user['id'],
            'phone'     => $user['phone'],
            'full_name' => $user['full_name'],
            'country'   => $user['country'],
        ],
        'is_new'    => empty($user['full_name']),
    ]);
}

/**
 * POST /auth/refresh
 * Body: { "refresh_token": "..." }
 */
function auth_refresh(): void {
    $input = json_input();
    $token = $input['refresh_token'] ?? '';
    if (!$token) json_error('Refresh token is required');

    $payload = jwt_decode($token);
    if (!$payload || ($payload['type'] ?? '') !== 'refresh') {
        json_error('Invalid or expired refresh token', 401);
    }

    // Verify user still exists
    $stmt = db()->prepare("SELECT id, phone, country FROM users WHERE id = ? AND status = 'active'");
    $stmt->execute([$payload['sub']]);
    $user = $stmt->fetch();

    if (!$user) json_error('User not found', 401);

    $tokens = jwt_create_tokens((int) $user['id'], $user['phone'], $user['country']);
    json_success(['tokens' => $tokens]);
}
