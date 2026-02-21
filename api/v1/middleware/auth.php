<?php
/**
 * KandaNews API v1 — Auth Middleware
 *
 * Validates JWT Bearer token from the Authorization header.
 * Sets $GLOBALS['auth_user'] on success.
 */

function require_auth(): array {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    if (!preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
        json_error('Authentication required', 401);
    }

    $payload = jwt_decode($m[1]);
    if (!$payload || ($payload['type'] ?? '') !== 'access') {
        json_error('Invalid or expired token', 401);
    }

    // Fetch user from DB to ensure they still exist and are active
    $stmt = db()->prepare("
        SELECT id, phone, full_name, first_name, surname, age, role, role_detail,
               avatar_url, country, status
        FROM users WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$payload['sub']]);
    $user = $stmt->fetch();

    if (!$user) {
        json_error('User not found or deactivated', 401);
    }

    $GLOBALS['auth_user'] = $user;
    return $user;
}

/**
 * Optional auth — sets user if token present, otherwise null.
 */
function optional_auth(): ?array {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/^Bearer\s+(.+)$/i', $header, $m)) return null;

    $payload = jwt_decode($m[1]);
    if (!$payload || ($payload['type'] ?? '') !== 'access') return null;

    $stmt = db()->prepare("
        SELECT id, phone, full_name, first_name, surname, age, role, role_detail,
               avatar_url, country, status
        FROM users WHERE id = ? AND status = 'active'
    ");
    $stmt->execute([$payload['sub']]);
    $user = $stmt->fetch();

    if ($user) $GLOBALS['auth_user'] = $user;
    return $user;
}
