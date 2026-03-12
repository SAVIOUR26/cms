<?php
/**
 * KandaNews API v1 — Edition Categories Route
 *
 * GET /edition-categories?country=ug
 *   Returns ordered, active categories for the given country.
 *   country=NULL rows are shown in all countries.
 */

function route_categories(string $action, string $method): void {
    if ($method !== 'GET') json_error('Method not allowed', 405);

    $user    = optional_auth();
    $country = $_GET['country'] ?? ($user['country'] ?? 'ug');
    $country = strtolower(trim($country));

    $pdo  = db();
    $stmt = $pdo->prepare("
        SELECT id, slug, label, description, icon_name, color_hex,
               edition_type, sort_order
        FROM   edition_categories
        WHERE  is_active = 1
          AND  (country IS NULL OR country = ?)
        ORDER  BY sort_order ASC, id ASC
    ");
    $stmt->execute([$country]);
    $categories = $stmt->fetchAll();

    // Cast numeric fields
    foreach ($categories as &$cat) {
        $cat['id']         = (int) $cat['id'];
        $cat['sort_order'] = (int) $cat['sort_order'];
    }
    unset($cat);

    json_success(['categories' => $categories]);
}
