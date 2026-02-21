<?php
/**
 * KandaNews API v1 — Editions Routes
 *
 * GET /editions            — List available editions
 * GET /editions/{id}       — Get single edition detail + pages
 * GET /editions/latest     — Get latest edition for user's country
 */

function route_editions(string $action, string $method): void {
    if ($method !== 'GET') json_error('Method not allowed', 405);

    // Optional auth — free editions don't require login
    $user = optional_auth();

    if ($action === '' || $action === 'list') {
        editions_list($user);
    } elseif ($action === 'latest') {
        editions_latest($user);
    } elseif (is_numeric($action)) {
        editions_detail((int) $action, $user);
    } else {
        json_error('Not found', 404);
    }
}

/**
 * GET /editions?country=ug&page=1&per_page=20
 */
function editions_list(?array $user): void {
    $country  = $_GET['country'] ?? ($user['country'] ?? 'ug');
    $page     = max(1, (int) ($_GET['page'] ?? 1));
    $perPage  = min(50, max(1, (int) ($_GET['per_page'] ?? 20)));
    $offset   = ($page - 1) * $perPage;

    $pdo = db();

    // Count total
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM editions WHERE country = ? AND status = 'published'");
    $stmt->execute([$country]);
    $total = (int) $stmt->fetch()['total'];

    // Fetch page
    $stmt = $pdo->prepare("
        SELECT id, title, slug, country, edition_date, cover_image, page_count,
               is_free, theme, created_at
        FROM editions
        WHERE country = ? AND status = 'published'
        ORDER BY edition_date DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$country, $perPage, $offset]);
    $editions = $stmt->fetchAll();

    // Check subscription for access flags
    $hasAccess = false;
    if ($user) {
        $sub = $pdo->prepare("
            SELECT id FROM subscriptions
            WHERE user_id = ? AND status = 'active' AND expires_at > NOW()
            LIMIT 1
        ");
        $sub->execute([$user['id']]);
        $hasAccess = (bool) $sub->fetch();
    }

    foreach ($editions as &$ed) {
        $ed['accessible'] = $ed['is_free'] || $hasAccess;
        $ed['id'] = (int) $ed['id'];
        $ed['page_count'] = (int) $ed['page_count'];
        $ed['is_free'] = (bool) $ed['is_free'];
    }

    json_success([
        'editions'   => $editions,
        'pagination' => [
            'page'     => $page,
            'per_page' => $perPage,
            'total'    => $total,
            'pages'    => (int) ceil($total / $perPage),
        ],
    ]);
}

/**
 * GET /editions/latest?country=ug
 */
function editions_latest(?array $user): void {
    $country = $_GET['country'] ?? ($user['country'] ?? 'ug');

    $stmt = db()->prepare("
        SELECT id, title, slug, country, edition_date, cover_image, page_count,
               is_free, theme, created_at
        FROM editions
        WHERE country = ? AND status = 'published'
        ORDER BY edition_date DESC
        LIMIT 1
    ");
    $stmt->execute([$country]);
    $edition = $stmt->fetch();

    if (!$edition) json_error('No editions available', 404);

    $edition['id'] = (int) $edition['id'];
    $edition['page_count'] = (int) $edition['page_count'];
    $edition['is_free'] = (bool) $edition['is_free'];

    json_success(['edition' => $edition]);
}

/**
 * GET /editions/{id}
 *
 * Returns full edition detail including page URLs.
 * Paid editions require an active subscription.
 */
function editions_detail(int $id, ?array $user): void {
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT id, title, slug, country, edition_date, cover_image, page_count,
               is_free, theme, description, created_at
        FROM editions WHERE id = ? AND status = 'published'
    ");
    $stmt->execute([$id]);
    $edition = $stmt->fetch();

    if (!$edition) json_error('Edition not found', 404);

    $edition['id'] = (int) $edition['id'];
    $edition['page_count'] = (int) $edition['page_count'];
    $edition['is_free'] = (bool) $edition['is_free'];

    // Access check
    $hasAccess = (bool) $edition['is_free'];
    if (!$hasAccess && $user) {
        $sub = $pdo->prepare("
            SELECT id FROM subscriptions
            WHERE user_id = ? AND status = 'active' AND expires_at > NOW()
            LIMIT 1
        ");
        $sub->execute([$user['id']]);
        $hasAccess = (bool) $sub->fetch();
    }

    if (!$hasAccess) {
        // Return edition meta but not pages
        json_success([
            'edition'    => $edition,
            'accessible' => false,
            'pages'      => [],
            'message'    => 'Subscribe to access this edition',
        ]);
        return;
    }

    // Fetch pages
    $stmt = $pdo->prepare("
        SELECT page_number, title, content_url, thumbnail_url
        FROM edition_pages
        WHERE edition_id = ?
        ORDER BY page_number ASC
    ");
    $stmt->execute([$id]);
    $pages = $stmt->fetchAll();

    json_success([
        'edition'    => $edition,
        'accessible' => true,
        'pages'      => $pages,
    ]);
}
