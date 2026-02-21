<?php
/**
 * KandaNews API v1 — JWT Helper (HMAC-SHA256, no external deps)
 *
 * Tokens are stateless — the native app stores them and sends
 * Authorization: Bearer <token> with every request.
 */

function jwt_encode(array $payload): string {
    global $config;
    $secret = $config['jwt_secret'];

    $header  = base64url_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
    $payload = base64url_encode(json_encode($payload));
    $sig     = base64url_encode(hash_hmac('sha256', "$header.$payload", $secret, true));

    return "$header.$payload.$sig";
}

function jwt_decode(string $token): ?array {
    global $config;
    $secret = $config['jwt_secret'];

    $parts = explode('.', $token);
    if (count($parts) !== 3) return null;

    [$header, $payload, $sig] = $parts;
    $expected = base64url_encode(hash_hmac('sha256', "$header.$payload", $secret, true));

    if (!hash_equals($expected, $sig)) return null;

    $data = json_decode(base64url_decode($payload), true);
    if (!is_array($data)) return null;

    // Check expiry
    if (isset($data['exp']) && $data['exp'] < time()) return null;

    return $data;
}

/**
 * Create an access + refresh token pair for a user.
 */
function jwt_create_tokens(int $user_id, string $phone, string $country): array {
    global $config;
    $now = time();

    $access = jwt_encode([
        'sub'     => $user_id,
        'phone'   => $phone,
        'country' => $country,
        'iat'     => $now,
        'exp'     => $now + $config['jwt_ttl'],
        'type'    => 'access',
    ]);

    $refresh = jwt_encode([
        'sub'  => $user_id,
        'iat'  => $now,
        'exp'  => $now + $config['jwt_refresh'],
        'type' => 'refresh',
    ]);

    return [
        'access_token'  => $access,
        'refresh_token' => $refresh,
        'expires_in'    => $config['jwt_ttl'],
        'token_type'    => 'Bearer',
    ];
}

// ── Base64url (RFC 7515) ──

function base64url_encode(string $data): string {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode(string $data): string {
    return base64_decode(strtr($data, '-_', '+/'));
}
