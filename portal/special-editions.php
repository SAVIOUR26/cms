<?php
/**
 * KandaNews Africa — Special Editions Manager
 *
 * CRUD interface for special editions by category.
 * These editions appear in the App's Special Editions section under
 * their respective category tiles.
 *
 * Categories: university | corporate | entrepreneurship |
 *             campaigns | jobs_careers | podcasts | episodes
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$page_title   = 'Special Editions';
$page_section = 'content';
$db           = portal_db();
$countries    = portal_countries();

$categories = [
    'university'      => ['label' => 'University',        'icon' => 'fas fa-graduation-cap', 'color' => '#0369a1'],
    'corporate'       => ['label' => 'Corporate',         'icon' => 'fas fa-briefcase',      'color' => '#15803d'],
    'entrepreneurship'=> ['label' => 'Entrepreneurship',  'icon' => 'fas fa-rocket',         'color' => '#c2410c'],
    'campaigns'       => ['label' => 'Campaigns',         'icon' => 'fas fa-bullhorn',       'color' => '#7c3aed'],
    'jobs_careers'    => ['label' => 'Jobs & Careers',    'icon' => 'fas fa-user-tie',       'color' => '#0f766e'],
    'podcasts'        => ['label' => 'Podcasts',          'icon' => 'fas fa-podcast',        'color' => '#be185d'],
    'episodes'        => ['label' => 'Episodes',          'icon' => 'fas fa-play-circle',    'color' => '#d97706'],
];

// ── Handle POST actions ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portal_verify_csrf()) {
        portal_flash('error', 'Invalid CSRF token.');
        header('Location: ' . portal_url('special-editions.php'));
        exit;
    }

    $action = $_POST['action'] ?? '';

    // Publish / unpublish / archive / delete
    if (in_array($action, ['publish', 'unpublish', 'archive', 'delete'])) {
        $id = (int) ($_POST['edition_id'] ?? 0);
        if ($id > 0) {
            try {
                if ($action === 'delete') {
                    $db->prepare("DELETE FROM editions WHERE id = ? AND edition_type = 'special'")->execute([$id]);
                    portal_flash('success', 'Edition deleted.');
                } else {
                    $map = ['publish' => 'published', 'unpublish' => 'draft', 'archive' => 'archived'];
                    $db->prepare("UPDATE editions SET status = ? WHERE id = ? AND edition_type = 'special'")
                       ->execute([$map[$action], $id]);
                    portal_flash('success', 'Edition updated.');
                }
            } catch (PDOException $e) {
                portal_flash('error', 'Database error: ' . $e->getMessage());
            }
        }
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// ── Filters ───────────────────────────────────
$cat_filter    = $_GET['category'] ?? 'all';
$status_filter = $_GET['status']   ?? 'all';
$country_filter= strtolower($_GET['country'] ?? 'all');

$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$where  = "edition_type = 'special'";
$params = [];

if ($cat_filter !== 'all') {
    $where .= ' AND category = ?';
    $params[] = $cat_filter;
}
if ($status_filter !== 'all') {
    $where .= ' AND status = ?';
    $params[] = $status_filter;
}
if ($country_filter !== 'all') {
    $where .= ' AND country = ?';
    $params[] = $country_filter;
}

try {
    $countStmt = $db->prepare("SELECT COUNT(*) FROM editions WHERE $where");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $stmt = $db->prepare("
        SELECT id, title, slug, country, edition_date, category, status, cover_image,
               is_free, page_count, html_url, created_at
        FROM editions
        WHERE $where
        ORDER BY edition_date DESC, created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $editions = $stmt->fetchAll();
} catch (PDOException $e) {
    $editions = [];
    $total    = 0;
}

$totalPages = max(1, ceil($total / $perPage));
$csrf       = portal_csrf_token();

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Header ────────────────────────────── -->
<div class="section-header">
    <div>
        <h1><i class="fas fa-star" style="color:var(--orange);margin-right:8px;"></i>Special Editions</h1>
        <p>Manage all special editions by category — they appear in the app under each category tile.</p>
    </div>
    <a href="<?php echo portal_url('upload.php?type=special'); ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Special Edition
    </a>
</div>

<!-- ── Category Overview Cards ───────────────── -->
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:28px;">
<?php foreach ($categories as $slug => $cat):
    $cnt = 0;
    try {
        $s = $db->prepare("SELECT COUNT(*) FROM editions WHERE edition_type='special' AND category=? AND status='published'");
        $s->execute([$slug]);
        $cnt = (int) $s->fetchColumn();
    } catch (PDOException $e) {}
?>
<a href="<?php echo portal_url('special-editions.php?category=' . $slug); ?>"
   style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:18px 12px;background:<?php echo ($cat_filter === $slug) ? 'var(--orange)' : '#fff'; ?>;border-radius:12px;border:2px solid <?php echo ($cat_filter === $slug) ? 'var(--orange)' : '#e5e7eb'; ?>;text-decoration:none;transition:all .15s;box-shadow:0 2px 8px rgba(0,0,0,.05);">
    <div style="width:44px;height:44px;background:<?php echo ($cat_filter === $slug) ? 'rgba(255,255,255,.25)' : $cat['color'] . '18'; ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;">
        <i class="<?php echo $cat['icon']; ?>" style="font-size:20px;color:<?php echo ($cat_filter === $slug) ? '#fff' : $cat['color']; ?>;"></i>
    </div>
    <div style="text-align:center;">
        <div style="font-size:12px;font-weight:700;color:<?php echo ($cat_filter === $slug) ? '#fff' : 'var(--navy)'; ?>;"><?php echo $cat['label']; ?></div>
        <div style="font-size:11px;color:<?php echo ($cat_filter === $slug) ? 'rgba(255,255,255,.75)' : '#9ca3af'; ?>"><?php echo $cnt; ?> live</div>
    </div>
</a>
<?php endforeach; ?>
<a href="<?php echo portal_url('special-editions.php'); ?>"
   style="display:flex;flex-direction:column;align-items:center;gap:8px;padding:18px 12px;background:<?php echo $cat_filter === 'all' ? 'var(--navy)' : '#f9fafb'; ?>;border-radius:12px;border:2px solid <?php echo $cat_filter === 'all' ? 'var(--navy)' : '#e5e7eb'; ?>;text-decoration:none;transition:all .15s;">
    <div style="width:44px;height:44px;background:<?php echo $cat_filter === 'all' ? 'rgba(255,255,255,.15)' : '#f3f4f6'; ?>;border-radius:10px;display:flex;align-items:center;justify-content:center;">
        <i class="fas fa-th" style="font-size:20px;color:<?php echo $cat_filter === 'all' ? '#fff' : '#6b7280'; ?>;"></i>
    </div>
    <div style="text-align:center;">
        <div style="font-size:12px;font-weight:700;color:<?php echo $cat_filter === 'all' ? '#fff' : 'var(--navy)'; ?>;">All</div>
        <div style="font-size:11px;color:<?php echo $cat_filter === 'all' ? 'rgba(255,255,255,.75)' : '#9ca3af'; ?>"><?php echo $total; ?> total</div>
    </div>
</a>
</div>

<!-- ── Filter Bar ─────────────────────────────── -->
<div class="filter-bar" style="margin-bottom:20px;">
    <!-- Status -->
    <?php foreach (['all' => 'All Status', 'published' => 'Published', 'draft' => 'Drafts', 'archived' => 'Archived'] as $v => $l): ?>
    <a href="<?php echo portal_url('special-editions.php?category=' . urlencode($cat_filter) . '&status=' . $v); ?>"
       class="filter-chip <?php echo $status_filter === $v ? 'active' : ''; ?>">
        <?php echo $l; ?>
    </a>
    <?php endforeach; ?>

    <span style="margin:0 4px;color:#e5e7eb;">|</span>

    <!-- Country -->
    <?php foreach (['all' => 'All Countries', 'ug' => '🇺🇬 Uganda', 'ke' => '🇰🇪 Kenya', 'ng' => '🇳🇬 Nigeria', 'za' => '🇿🇦 South Africa'] as $v => $l): ?>
    <a href="<?php echo portal_url('special-editions.php?category=' . urlencode($cat_filter) . '&status=' . urlencode($status_filter) . '&country=' . $v); ?>"
       class="filter-chip <?php echo $country_filter === $v ? 'active' : ''; ?>">
        <?php echo $l; ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- ── Editions Table ─────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h2>
            <?php if ($cat_filter !== 'all' && isset($categories[$cat_filter])): ?>
            <i class="<?php echo $categories[$cat_filter]['icon']; ?>"></i>
            <?php echo $categories[$cat_filter]['label']; ?> Editions
            <?php else: ?>
            <i class="fas fa-star"></i> All Special Editions
            <?php endif; ?>
        </h2>
        <span style="font-size:13px;color:#888;"><?php echo number_format($total); ?> edition(s)</span>
    </div>

    <?php if (empty($editions)): ?>
    <div class="empty-state">
        <i class="fas fa-star-half-alt"></i>
        <h3>No special editions found</h3>
        <p>Create your first special edition to get started.</p>
        <a href="<?php echo portal_url('upload.php?type=special' . ($cat_filter !== 'all' ? '&category=' . urlencode($cat_filter) : '')); ?>"
           class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Special Edition
        </a>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>Cover</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Date</th>
                    <th>Country</th>
                    <th>Access</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($editions as $ed): ?>
                <tr>
                    <td>
                        <?php if (!empty($ed['cover_image'])): ?>
                        <img src="<?php echo portal_cms_url('uploads/covers/' . basename($ed['cover_image'])); ?>"
                             alt="" class="cover-thumb">
                        <?php else: ?>
                        <div class="cover-placeholder"><i class="fas fa-star"></i></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong style="color:var(--navy);font-size:13px;"><?php echo htmlspecialchars($ed['title']); ?></strong>
                        <?php if ($ed['page_count'] > 0): ?>
                        <div style="font-size:11px;color:#9ca3af;margin-top:1px;"><?php echo $ed['page_count']; ?> pages</div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($ed['category']) && isset($categories[$ed['category']])): ?>
                        <span style="display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;color:<?php echo $categories[$ed['category']]['color']; ?>;">
                            <i class="<?php echo $categories[$ed['category']]['icon']; ?>"></i>
                            <?php echo $categories[$ed['category']]['label']; ?>
                        </span>
                        <?php else: ?>
                        <span style="color:#9ca3af;font-size:12px;">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;font-size:13px;color:#555;">
                        <?php echo date('M j, Y', strtotime($ed['edition_date'])); ?>
                    </td>
                    <td style="font-size:12px;font-weight:700;"><?php echo strtoupper($ed['country']); ?></td>
                    <td>
                        <?php if ($ed['is_free']): ?>
                        <span class="badge" style="background:#dcfce7;color:#15803d;">FREE</span>
                        <?php else: ?>
                        <span class="badge" style="background:#fef9c3;color:#92400e;">PAID</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $ed['status']; ?>"><?php echo $ed['status']; ?></span>
                    </td>
                    <td style="text-align:right;white-space:nowrap;">
                        <!-- Preview -->
                        <?php if (!empty($ed['html_url'])): ?>
                        <a href="<?php echo htmlspecialchars($ed['html_url']); ?>"
                           target="_blank" class="btn btn-ghost btn-sm" title="Preview in browser">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <?php endif; ?>

                        <!-- Status action -->
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="_csrf" value="<?php echo $csrf; ?>">
                            <input type="hidden" name="edition_id" value="<?php echo $ed['id']; ?>">
                            <?php if ($ed['status'] === 'published'): ?>
                            <button type="submit" name="action" value="unpublish"
                                    class="btn btn-warning btn-sm" title="Unpublish">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                            <?php else: ?>
                            <button type="submit" name="action" value="publish"
                                    class="btn btn-success btn-sm" title="Publish">
                                <i class="fas fa-globe"></i>
                            </button>
                            <?php endif; ?>
                            <button type="submit" name="action" value="delete"
                                    class="btn btn-danger btn-sm" title="Delete"
                                    data-confirm="Delete this edition permanently?">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?>
        <a href="<?php echo portal_url('special-editions.php?category=' . urlencode($cat_filter) . '&status=' . urlencode($status_filter) . '&page=' . ($page - 1)); ?>">
            <i class="fas fa-chevron-left"></i> Prev
        </a>
        <?php else: ?>
        <span class="disabled"><i class="fas fa-chevron-left"></i> Prev</span>
        <?php endif; ?>

        <?php for ($p = max(1, $page - 2); $p <= min($totalPages, $page + 2); $p++): ?>
        <?php if ($p === $page): ?>
        <span class="current"><?php echo $p; ?></span>
        <?php else: ?>
        <a href="<?php echo portal_url('special-editions.php?category=' . urlencode($cat_filter) . '&status=' . urlencode($status_filter) . '&page=' . $p); ?>">
            <?php echo $p; ?>
        </a>
        <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
        <a href="<?php echo portal_url('special-editions.php?category=' . urlencode($cat_filter) . '&status=' . urlencode($status_filter) . '&page=' . ($page + 1)); ?>">
            Next <i class="fas fa-chevron-right"></i>
        </a>
        <?php else: ?>
        <span class="disabled">Next <i class="fas fa-chevron-right"></i></span>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<!-- ── App Preview Tip ────────────────────────── -->
<div style="background:linear-gradient(135deg,#1e2b42,#2a3f5f);border-radius:var(--radius);padding:24px;color:#fff;display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
    <div style="font-size:36px;">📱</div>
    <div style="flex:1;min-width:200px;">
        <strong style="font-size:16px;">How this appears in the App</strong>
        <p style="font-size:13px;opacity:.8;margin-top:4px;">
            Special editions published here appear automatically in the KandaNews app under
            <strong>Special Editions &rarr; [Category]</strong>. Users tap a category tile to browse editions.
            Editions marked <strong>FREE</strong> are accessible without a subscription.
        </p>
    </div>
    <a href="<?php echo portal_url('upload.php?type=special'); ?>" class="btn" style="background:var(--orange);color:#fff;white-space:nowrap;">
        <i class="fas fa-plus"></i> Add Edition
    </a>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
