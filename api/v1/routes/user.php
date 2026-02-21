<?php
/**
 * KandaNews API v1 — User Routes
 *
 * GET  /user/profile   — Get current user profile
 * PUT  /user/profile   — Update profile (name, email, etc.)
 */

function route_user(string $action, string $method): void {
    $user = require_auth();

    switch ("$method $action") {
        case 'GET profile':  user_get_profile($user); break;
        case 'PUT profile':  user_update_profile($user); break;
        default: json_error('Not found', 404);
    }
}

/**
 * GET /user/profile
 */
function user_get_profile(array $user): void {
    $pdo = db();

    // Get subscription status
    $stmt = $pdo->prepare("
        SELECT plan, status, starts_at, expires_at
        FROM subscriptions
        WHERE user_id = ? AND status = 'active' AND expires_at > NOW()
        ORDER BY expires_at DESC LIMIT 1
    ");
    $stmt->execute([$user['id']]);
    $sub = $stmt->fetch();

    json_success([
        'user' => [
            'id'        => (int) $user['id'],
            'phone'     => $user['phone'],
            'full_name' => $user['full_name'],
            'country'   => $user['country'],
        ],
        'subscription' => $sub ? [
            'plan'       => $sub['plan'],
            'status'     => $sub['status'],
            'starts_at'  => $sub['starts_at'],
            'expires_at' => $sub['expires_at'],
        ] : null,
    ]);
}

/**
 * PUT /user/profile
 * Body: { "full_name": "John Doe", "email": "john@example.com" }
 */
function user_update_profile(array $user): void {
    $input = json_input();

    $fields = [];
    $params = [];

    if (isset($input['full_name'])) {
        $name = trim($input['full_name']);
        if (strlen($name) < 2 || strlen($name) > 100) {
            json_error('Name must be 2-100 characters');
        }
        $fields[] = 'full_name = ?';
        $params[] = $name;
    }

    if (isset($input['email'])) {
        $email = trim($input['email']);
        if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            json_error('Invalid email address');
        }
        $fields[] = 'email = ?';
        $params[] = $email ?: null;
    }

    if (isset($input['country'])) {
        $country = strtolower(trim($input['country']));
        if (!in_array($country, ['ug', 'ke', 'ng', 'za'])) {
            json_error('Invalid country code');
        }
        $fields[] = 'country = ?';
        $params[] = $country;
    }

    if (empty($fields)) json_error('No fields to update');

    $fields[] = 'updated_at = NOW()';
    $params[] = $user['id'];

    $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
    db()->prepare($sql)->execute($params);

    // Return updated profile
    $stmt = db()->prepare("SELECT id, phone, full_name, email, country FROM users WHERE id = ?");
    $stmt->execute([$user['id']]);
    $updated = $stmt->fetch();

    json_success(['user' => $updated]);
}
