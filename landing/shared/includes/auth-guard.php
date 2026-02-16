<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_strict_mode', 1);
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireAuth($loginUrl = '/login.php') {
    if (!isLoggedIn()) {
        header('Location: ' . $loginUrl);
        exit;
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id'        => $_SESSION['user_id'],
        'name'      => $_SESSION['user_name'] ?? 'User',
        'email'     => $_SESSION['user_email'] ?? '',
        'phone'     => $_SESSION['user_phone'] ?? '',
        'whatsapp'  => $_SESSION['user_whatsapp'] ?? '',
        'category'  => $_SESSION['user_category'] ?? '',
        'org'       => $_SESSION['user_org'] ?? '',
        'country'   => $_SESSION['user_country'] ?? '',
    ];
}

function generateCSRF() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRF($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
