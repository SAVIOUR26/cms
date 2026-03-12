<?php
/**
 * KandaNews API v1 — Events Routes
 *
 * GET /events?country=ug&status=published   List upcoming/recent events
 * GET /events/{id}                          Single event detail
 */

function route_events(string $action, string $method): void {
    if ($method !== 'GET') json_error('Method not allowed', 405);

    if ($action === '' || $action === 'list') {
        events_list();
    } elseif (is_numeric($action)) {
        events_detail((int) $action);
    } else {
        json_error('Not found', 404);
    }
}

/**
 * GET /events?country=ug&status=published&limit=20
 *
 * Returns events ordered by event_date ASC (upcoming first).
 * Past events (event_date < now) are included but sorted last.
 */
function events_list(): void {
    $user    = optional_auth();
    $country = $_GET['country'] ?? ($user['country'] ?? 'ug');
    $status  = $_GET['status']  ?? 'published';
    $limit   = min(50, max(1, (int) ($_GET['limit'] ?? 20)));
    $country = strtolower(trim($country));

    if (!in_array($status, ['published', 'all'], true)) $status = 'published';

    $pdo = db();

    $where  = "country = ?";
    $params = [$country];

    if ($status !== 'all') {
        $where   .= " AND status = ?";
        $params[] = $status;
    }

    $params[] = $limit;

    $stmt = $pdo->prepare("
        SELECT id, title, description, event_date, end_date, location,
               is_online, registration_url, cover_image_url,
               country, category, status, is_free, created_at
        FROM   events
        WHERE  $where
        ORDER  BY
            CASE WHEN event_date >= NOW() THEN 0 ELSE 1 END ASC,
            event_date ASC
        LIMIT  ?
    ");
    $stmt->execute($params);
    $events = $stmt->fetchAll();

    foreach ($events as &$ev) {
        $ev['id']        = (int) $ev['id'];
        $ev['is_online'] = (bool) $ev['is_online'];
        $ev['is_free']   = (bool) $ev['is_free'];
    }
    unset($ev);

    json_success(['events' => $events]);
}

/**
 * GET /events/{id}
 */
function events_detail(int $id): void {
    $pdo  = db();
    $stmt = $pdo->prepare("
        SELECT id, title, description, event_date, end_date, location,
               is_online, registration_url, cover_image_url,
               country, category, status, is_free, created_at
        FROM   events
        WHERE  id = ? AND status = 'published'
    ");
    $stmt->execute([$id]);
    $event = $stmt->fetch();

    if (!$event) json_error('Event not found', 404);

    $event['id']        = (int) $event['id'];
    $event['is_online'] = (bool) $event['is_online'];
    $event['is_free']   = (bool) $event['is_free'];

    json_success(['event' => $event]);
}
