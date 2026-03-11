<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function ads_session_start(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

function current_advertiser(): ?array {
    ads_session_start();
    if (empty($_SESSION['advertiser_id'])) return null;
    try {
        $db = get_db();
        $st = $db->prepare('SELECT id, company_name, contact_name, email, phone, country FROM ads_advertisers WHERE id = ? AND status = "active"');
        $st->execute([$_SESSION['advertiser_id']]);
        return $st->fetch() ?: null;
    } catch (Exception $e) { return null; }
}

function require_ads_auth(): array {
    $a = current_advertiser();
    if (!$a) { header('Location: /login.php?next=' . urlencode($_SERVER['REQUEST_URI'])); exit; }
    return $a;
}

function ads_login(array $advertiser): void {
    ads_session_start();
    session_regenerate_id(true);
    $_SESSION['advertiser_id'] = $advertiser['id'];
}

function ads_logout(): void {
    ads_session_start();
    session_destroy();
}
