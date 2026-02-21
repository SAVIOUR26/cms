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
            'id'          => (int) $user['id'],
            'phone'       => $user['phone'],
            'full_name'   => $user['full_name'],
            'first_name'  => $user['first_name'],
            'surname'     => $user['surname'],
            'age'         => $user['age'] !== null ? (int) $user['age'] : null,
            'role'        => $user['role'],
            'role_detail' => $user['role_detail'],
            'avatar_url'  => $user['avatar_url'],
            'country'     => $user['country'],
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
 * Body: { "first_name": "John", "surname": "Doe", "email": "john@example.com", ... }
 */
function user_update_profile(array $user): void {
    $input = json_input();

    $fields = [];
    $params = [];

    if (isset($input['first_name'])) {
        $firstName = trim($input['first_name']);
        if (strlen($firstName) < 2 || strlen($firstName) > 50) {
            json_error('First name must be 2-50 characters');
        }
        $fields[] = 'first_name = ?';
        $params[] = $firstName;
    }

    if (isset($input['surname'])) {
        $surname = trim($input['surname']);
        if (strlen($surname) < 2 || strlen($surname) > 50) {
            json_error('Surname must be 2-50 characters');
        }
        $fields[] = 'surname = ?';
        $params[] = $surname;
    }

    // Update full_name when first_name or surname changes
    if (isset($input['first_name']) || isset($input['surname'])) {
        $newFirst   = isset($input['first_name']) ? trim($input['first_name']) : ($user['first_name'] ?? '');
        $newSurname = isset($input['surname'])    ? trim($input['surname'])    : ($user['surname'] ?? '');
        if ($newFirst && $newSurname) {
            $fields[] = 'full_name = ?';
            $params[] = $newFirst . ' ' . $newSurname;
        }
    } elseif (isset($input['full_name'])) {
        // Legacy full_name support (only if first_name/surname not provided)
        $name = trim($input['full_name']);
        if (strlen($name) < 2 || strlen($name) > 100) {
            json_error('Name must be 2-100 characters');
        }
        $fields[] = 'full_name = ?';
        $params[] = $name;
    }

    if (isset($input['age'])) {
        $age = (int) $input['age'];
        if ($age < 13 || $age > 120) {
            json_error('Age must be between 13 and 120');
        }
        $fields[] = 'age = ?';
        $params[] = $age;
    }

    if (isset($input['role'])) {
        $role = trim($input['role']);
        $validRoles = ['student', 'professional', 'entrepreneur'];
        if (!in_array($role, $validRoles, true)) {
            json_error('Role must be one of: ' . implode(', ', $validRoles));
        }
        $fields[] = 'role = ?';
        $params[] = $role;
    }

    if (isset($input['role_detail'])) {
        $roleDetail = trim($input['role_detail']);
        if (strlen($roleDetail) < 2 || strlen($roleDetail) > 200) {
            json_error('Role detail must be 2-200 characters');
        }
        $fields[] = 'role_detail = ?';
        $params[] = $roleDetail;
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
    $stmt = db()->prepare("
        SELECT id, phone, full_name, first_name, surname, age, role, role_detail,
               avatar_url, email, country
        FROM users WHERE id = ?
    ");
    $stmt->execute([$user['id']]);
    $updated = $stmt->fetch();

    $updated['id']  = (int) $updated['id'];
    $updated['age'] = $updated['age'] !== null ? (int) $updated['age'] : null;

    json_success(['user' => $updated]);
}
