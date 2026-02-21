<?php
/**
 * KandaNews API v1 — Auth Routes
 *
 * POST /auth/request-otp   — Send OTP to phone
 * POST /auth/verify-otp    — Verify OTP, get tokens
 * POST /auth/refresh        — Refresh access token
 * POST /auth/register       — Complete first-time registration
 */

function route_auth(string $action, string $method): void {
    switch ("$method $action") {
        case 'POST request-otp':  auth_request_otp(); break;
        case 'POST verify-otp':   auth_verify_otp();  break;
        case 'POST refresh':      auth_refresh();      break;
        case 'POST register':     auth_register();     break;
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

    // ── Test account bypass (phone: 0772253804 / OTP: 202026) ──
    $testPhone = '+256772253804';
    $testCode  = '202026';

    if ($phone === $testPhone) {
        otp_store($phone, $testCode);
        json_success([
            'message' => 'OTP sent successfully',
            'phone'   => substr($phone, 0, 4) . '****' . substr($phone, -3),
            'ttl'     => 300,
        ]);
        return;
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
 * A user is considered "new" if first_name is NULL (registration incomplete).
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
    $stmt = $pdo->prepare("
        SELECT id, phone, full_name, first_name, surname, age, role, role_detail, country
        FROM users WHERE phone = ?
    ");
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
        $user = [
            'id'          => $userId,
            'phone'       => $phone,
            'full_name'   => null,
            'first_name'  => null,
            'surname'     => null,
            'age'         => null,
            'role'        => null,
            'role_detail' => null,
            'country'     => $country,
        ];
    }

    // Issue tokens
    $tokens = jwt_create_tokens((int) $user['id'], $phone, $user['country'] ?? $country);

    json_success([
        'tokens'    => $tokens,
        'user'      => [
            'id'          => (int) $user['id'],
            'phone'       => $user['phone'],
            'full_name'   => $user['full_name'],
            'first_name'  => $user['first_name'],
            'surname'     => $user['surname'],
            'age'         => $user['age'] !== null ? (int) $user['age'] : null,
            'role'        => $user['role'],
            'role_detail' => $user['role_detail'],
            'country'     => $user['country'],
        ],
        'is_new'    => empty($user['first_name']),
    ]);
}

/**
 * POST /auth/register
 * Body: { "first_name": "John", "surname": "Doe", "age": 25, "role": "student", "role_detail": "Makerere University" }
 *
 * Completes first-time registration for a newly created user.
 * Requires JWT auth.
 */
function auth_register(): void {
    $user = require_auth();
    $input = json_input();

    // Validate first_name
    $firstName = trim($input['first_name'] ?? '');
    if (strlen($firstName) < 2 || strlen($firstName) > 50) {
        json_error('First name must be 2-50 characters');
    }

    // Validate surname
    $surname = trim($input['surname'] ?? '');
    if (strlen($surname) < 2 || strlen($surname) > 50) {
        json_error('Surname must be 2-50 characters');
    }

    // Validate age
    if (!isset($input['age'])) {
        json_error('Age is required');
    }
    $age = (int) $input['age'];
    if ($age < 13 || $age > 120) {
        json_error('Age must be between 13 and 120');
    }

    // Validate role
    $role = trim($input['role'] ?? '');
    $validRoles = ['student', 'professional', 'entrepreneur'];
    if (!in_array($role, $validRoles, true)) {
        json_error('Role must be one of: ' . implode(', ', $validRoles));
    }

    // Validate role_detail
    $roleDetail = trim($input['role_detail'] ?? '');
    if (strlen($roleDetail) < 2 || strlen($roleDetail) > 200) {
        json_error('Role detail must be 2-200 characters');
    }

    // Build full name
    $fullName = $firstName . ' ' . $surname;

    // Update user record
    $stmt = db()->prepare("
        UPDATE users
        SET first_name = ?, surname = ?, full_name = ?, age = ?, role = ?, role_detail = ?, updated_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$firstName, $surname, $fullName, $age, $role, $roleDetail, $user['id']]);

    // Fetch updated user
    $stmt = db()->prepare("
        SELECT id, phone, full_name, first_name, surname, age, role, role_detail, avatar_url, country
        FROM users WHERE id = ?
    ");
    $stmt->execute([$user['id']]);
    $updated = $stmt->fetch();

    $updated['id']  = (int) $updated['id'];
    $updated['age'] = $updated['age'] !== null ? (int) $updated['age'] : null;

    json_success(['user' => $updated]);
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
