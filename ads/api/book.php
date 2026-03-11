<?php
require_once __DIR__ . '/../shared/auth.php';
require_once __DIR__ . '/../shared/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$advertiser = current_advertiser();
if (!$advertiser) {
    json_response(['error' => 'Not authenticated. Please log in.'], 401);
}

ads_session_start();

// CSRF check
$csrf = $_POST['csrf'] ?? '';
if (!$csrf || $csrf !== ($_SESSION['csrf_token'] ?? '')) {
    json_response(['error' => 'Invalid request. Please refresh and try again.'], 403);
}

$format_key   = trim($_POST['format_key'] ?? '');
$format_label = trim($_POST['format_label'] ?? '');
$start_date   = trim($_POST['start_date'] ?? '');
$days         = (int) ($_POST['days'] ?? 0);

// Validate format
$formats = AD_FORMATS;
if (!isset($formats[$format_key])) {
    json_response(['error' => 'Invalid ad format selected.']);
}

// Validate start date — must be tomorrow or later
$start    = DateTime::createFromFormat('Y-m-d', $start_date);
$tomorrow = new DateTime('tomorrow');
$tomorrow->setTime(0, 0, 0);
if (!$start || $start < $tomorrow) {
    json_response(['error' => 'Start date must be at least tomorrow.']);
}

// Validate days
if ($days < 1 || $days > 365) {
    json_response(['error' => 'Duration must be between 1 and 365 days.']);
}

// Calculate pricing
$price    = calc_price($format_key, $days);
$end_date = (clone $start)->modify('+' . ($days - 1) . ' days')->format('Y-m-d');

try {
    $db = get_db();

    $st = $db->prepare('
        INSERT INTO ads_bookings
            (advertiser_id, format_key, format_label, start_date, end_date, days,
             unit_price, subtotal, discount_pct, total_price)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $st->execute([
        $advertiser['id'],
        $format_key,
        $format_label ?: $formats[$format_key]['label'],
        $start_date,
        $end_date,
        $days,
        $price['unit_price'],
        $price['subtotal'],
        (int) $price['discount_pct'],
        $price['total'],
    ]);

    json_response(['success' => true, 'booking_id' => (int) $db->lastInsertId()]);

} catch (Exception $e) {
    error_log('ads book error: ' . $e->getMessage());
    json_response(['error' => 'Booking failed. Please try again.'], 500);
}
