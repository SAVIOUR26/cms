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

$company_name     = trim($_POST['company_name'] ?? '');
$contact_name     = trim($_POST['contact_name'] ?? '');
$email            = trim(strtolower($_POST['email'] ?? ''));
$phone            = trim($_POST['phone'] ?? '');
$country          = trim($_POST['country'] ?? 'Uganda');
$password         = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if (!$company_name || !$contact_name || !$email || !$phone || !$password) {
    json_response(['error' => 'All fields are required.']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['error' => 'Please enter a valid email address.']);
}
if (strlen($password) < 8) {
    json_response(['error' => 'Password must be at least 8 characters.']);
}
if ($password !== $password_confirm) {
    json_response(['error' => 'Passwords do not match.']);
}

try {
    $db = get_db();

    $st = $db->prepare('SELECT id FROM ads_advertisers WHERE email = ?');
    $st->execute([$email]);
    if ($st->fetch()) {
        json_response(['error' => 'An account with this email already exists. Please log in.']);
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $st = $db->prepare('
        INSERT INTO ads_advertisers (company_name, contact_name, email, phone, country, password)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $st->execute([$company_name, $contact_name, $email, $phone, $country, $hash]);

    ads_login([
        'id'           => (int) $db->lastInsertId(),
        'company_name' => $company_name,
        'contact_name' => $contact_name,
        'email'        => $email,
        'phone'        => $phone,
        'country'      => $country,
    ]);

    json_response(['success' => true]);

} catch (Exception $e) {
    error_log('ads register error: ' . $e->getMessage());
    json_response(['error' => 'Registration failed. Please try again.'], 500);
}
