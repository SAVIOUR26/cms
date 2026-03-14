<?php
/**
 * KandaNews API v1 — Referral Routes
 *
 * GET  /referral/code    — Get (or auto-create) current user's referral code + invite URL
 * GET  /referral/stats   — Referral count and recent invitees
 */

function route_referral(string $action, string $method): void {
    $user = require_auth();

    switch ("$method $action") {
        case 'GET code':  referral_get_code($user);  break;
        case 'GET stats': referral_get_stats($user); break;
        default: json_error('Not found', 404);
    }
}

/**
 * GET /referral/code
 *
 * Returns the user's unique referral code and a ready-to-share invite URL.
 * The code is created lazily on first request — no setup needed by the user.
 */
function referral_get_code(array $user): void {
    $pdo = db();

    $stmt = $pdo->prepare("SELECT code FROM referral_codes WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();

    if (!$row) {
        $code = referral_generate_code($pdo);
        $pdo->prepare("INSERT INTO referral_codes (user_id, code) VALUES (?, ?)")
            ->execute([$user['id'], $code]);
    } else {
        $code = $row['code'];
    }

    $baseUrl = rtrim(env('APP_URL', 'https://kandanews.africa'), '/');
    $inviteUrl = $baseUrl . '/invite/' . $code;

    json_success([
        'code'       => $code,
        'invite_url' => $inviteUrl,
        'share_text' => 'Join me on KandaNews — Africa\'s digital newspaper! Get the app: ' . $inviteUrl,
    ]);
}

/**
 * GET /referral/stats
 *
 * Returns total referrals, last-30-day count, and a list of recent invitees.
 */
function referral_get_stats(array $user): void {
    $pdo = db();

    // Ensure code exists so we can return it
    $stmt = $pdo->prepare("SELECT code FROM referral_codes WHERE user_id = ?");
    $stmt->execute([$user['id']]);
    $codeRow = $stmt->fetch();
    $code = $codeRow['code'] ?? null;

    // Aggregate counts
    $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN r.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS last_30_days
        FROM referrals r
        WHERE r.referrer_id = ?
    ");
    $stmt->execute([$user['id']]);
    $counts = $stmt->fetch();

    // Recent invitees (name masked for privacy — show first name + country)
    $stmt = $pdo->prepare("
        SELECT u.first_name, u.country, r.created_at
        FROM referrals r
        JOIN users u ON u.id = r.referred_user_id
        WHERE r.referrer_id = ?
        ORDER BY r.created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user['id']]);
    $recent = $stmt->fetchAll();

    $baseUrl = rtrim(env('APP_URL', 'https://kandanews.africa'), '/');

    json_success([
        'code'         => $code,
        'invite_url'   => $code ? $baseUrl . '/invite/' . $code : null,
        'total'        => (int) ($counts['total'] ?? 0),
        'last_30_days' => (int) ($counts['last_30_days'] ?? 0),
        'recent'       => array_map(fn($r) => [
            'name'      => $r['first_name'] ?? 'Someone',
            'country'   => $r['country'],
            'joined_at' => $r['created_at'],
        ], $recent),
    ]);
}

/**
 * Generate a unique 8-character referral code.
 * Excludes O/0/I/1 to avoid visual confusion.
 */
function referral_generate_code(PDO $pdo): string {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    for ($attempt = 0; $attempt < 10; $attempt++) {
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        $stmt = $pdo->prepare("SELECT id FROM referral_codes WHERE code = ?");
        $stmt->execute([$code]);
        if (!$stmt->fetch()) return $code;
    }

    throw new \RuntimeException('Failed to generate a unique referral code after 10 attempts');
}
