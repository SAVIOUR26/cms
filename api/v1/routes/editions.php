<?php
/**
 * KandaNews API v1 — Editions Routes
 *
 * GET /editions            — List available editions
 * GET /editions/latest     — Get latest edition for user's country
 * GET /editions/today      — Get today's edition (or most recent)
 * GET /editions/{id}       — Get single edition detail + pages
 */

function route_editions(string $action, string $method): void {
    if ($method !== 'GET') json_error('Method not allowed', 405);

    // Optional auth — free editions don't require login
    $user = optional_auth();

    if ($action === '' || $action === 'list') {
        editions_list($user);
    } elseif ($action === 'latest') {
        editions_latest($user);
    } elseif ($action === 'today') {
        editions_today($user);
    } elseif (is_numeric($action)) {
        editions_detail((int) $action, $user);
    } else {
        json_error('Not found', 404);
    }
}

/**
 * GET /editions?country=ug&page=1&per_page=20&type=special&category=university
 */
function editions_list(?array $user): void {
    $country  = $_GET['country'] ?? ($user['country'] ?? 'ug');
    $page     = max(1, (int) ($_GET['page'] ?? 1));
    $perPage  = min(50, max(1, (int) ($_GET['per_page'] ?? 20)));
    $offset   = ($page - 1) * $perPage;
    $type     = $_GET['type'] ?? null;
    $category = $_GET['category'] ?? null;

    $pdo = db();

    // Build WHERE clause dynamically for type/category filters
    $where  = "country = ? AND status = 'published'";
    $params = [$country];

    if ($type) {
        $where .= " AND edition_type = ?";
        $params[] = $type;
    }
    if ($category) {
        $where .= " AND category = ?";
        $params[] = $category;
    }

    // Count total
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM editions WHERE $where");
    $stmt->execute($params);
    $total = (int) $stmt->fetch()['total'];

    // Fetch page
    $fetchParams = array_merge($params, [$perPage, $offset]);
    $stmt = $pdo->prepare("
        SELECT id, title, slug, country, edition_date, cover_image, page_count,
               is_free, theme, html_url, zip_url, edition_type, category, created_at
        FROM editions
        WHERE $where
        ORDER BY edition_date DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($fetchParams);
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
               is_free, theme, html_url, zip_url, edition_type, created_at
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
 * GET /editions/today?country=ug
 *
 * Returns today's edition, or the most recent published edition if
 * no edition matches today's date exactly.
 */
function editions_today(?array $user): void {
    $country = $_GET['country'] ?? ($user['country'] ?? 'ug');
    $today = date('Y-m-d');

    $pdo = db();

    // Try today's edition first
    $stmt = $pdo->prepare("
        SELECT id, title, slug, country, edition_date, cover_image, page_count,
               is_free, theme, html_url, zip_url, edition_type, created_at
        FROM editions
        WHERE country = ? AND status = 'published' AND edition_date = ?
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$country, $today]);
    $edition = $stmt->fetch();

    // Fall back to most recent published edition
    if (!$edition) {
        $stmt = $pdo->prepare("
            SELECT id, title, slug, country, edition_date, cover_image, page_count,
                   is_free, theme, html_url, zip_url, edition_type, created_at
            FROM editions
            WHERE country = ? AND status = 'published'
            ORDER BY edition_date DESC
            LIMIT 1
        ");
        $stmt->execute([$country]);
        $edition = $stmt->fetch();
    }

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
               is_free, theme, description, html_url, zip_url, edition_type, created_at
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
