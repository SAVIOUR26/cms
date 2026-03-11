<?php
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

ads_session_start();

// CSRF check
$csrf = $_POST['csrf'] ?? '';
if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    json_response(['error' => 'Invalid request. Please refresh and try again.'], 403);
}

$email    = trim(strtolower($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    json_response(['error' => 'Email and password are required.']);
}

try {
    $db = get_db();

    $st = $db->prepare('SELECT * FROM ads_advertisers WHERE email = ? AND status = "active"');
    $st->execute([$email]);
    $advertiser = $st->fetch();

    if (!$advertiser || !password_verify($password, $advertiser['password'])) {
        json_response(['error' => 'Incorrect email or password.']);
    }

    ads_login($advertiser);

    json_response(['success' => true]);

} catch (Exception $e) {
    error_log('ads login error: ' . $e->getMessage());
    json_response(['error' => 'Login failed. Please try again.'], 500);
}
