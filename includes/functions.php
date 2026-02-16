<?php
/**
 * KandaNews CMS — Helper Functions
 */
if (!defined('KANDA_CMS')) exit;

function getPageTitle($page = '') {
    $titles = [
        'dashboard' => 'Dashboard',
        'page-editor' => 'Page Editor',
        'visual-page-builder' => 'Visual Builder',
        'pages-library' => 'Pages Library',
        'build-edition' => 'Build Edition',
        'editions-list' => 'Editions',
        'simple-generator' => 'Quick Generator',
    ];
    return ($titles[$page] ?? 'CMS') . ' — KandaNews CMS';
}

function getDailyTheme() {
    $day = strtolower(date('l'));
    return DAILY_THEMES[$day] ?? 'KandaNews Daily';
}

function createEdition($data) {
    $db = getDatabase();
    $stmt = $db->prepare("
        INSERT INTO editions (title, edition_date, country_code, theme, page_count, status, created_by)
        VALUES (?, ?, ?, ?, ?, 'draft', ?)
    ");
    $stmt->execute([
        sanitize($data['title']),
        $data['edition_date'],
        $data['country_code'] ?? 'UG',
        $data['theme'] ?? getDailyTheme(),
        $data['page_count'] ?? DEFAULT_PAGES,
        $_SESSION['user_id'] ?? 1
    ]);
    return $db->lastInsertId();
}

function publishEdition($id, $filePath) {
    $db = getDatabase();
    $stmt = $db->prepare("
        UPDATE editions SET status = 'published', file_path = ?, published_at = NOW() WHERE id = ?
    ");
    return $stmt->execute([$filePath, $id]);
}

function getRecentActivity($limit = 20) {
    $db = getDatabase();
    $stmt = $db->prepare("
        SELECT al.*, u.username, u.full_name
        FROM activity_log al
        LEFT JOIN users u ON al.user_id = u.id
        ORDER BY al.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([(int)$limit]);
    return $stmt->fetchAll();
}

function getStats() {
    $db = getDatabase();
    $stats = [];

    try {
        $stmt = $db->query("SELECT COUNT(*) as total FROM editions WHERE status = 'published'");
        $stats['editions'] = $stmt->fetch()['total'] ?? 0;
    } catch (PDOException $e) { $stats['editions'] = 0; }

    try {
        $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE status = 'active'");
        $stats['users'] = $stmt->fetch()['total'] ?? 0;
    } catch (PDOException $e) { $stats['users'] = 0; }

    $stats['templates'] = count(glob(TEMPLATES_PATH . '*.html'));
    $stats['theme'] = getDailyTheme();

    return $stats;
}
