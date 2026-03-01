<?php
/**
 * KandaNews Africa — Editions List
 *
 * Lists all editions with status filters and publish/unpublish/archive actions.
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$page_title = 'Editions';
$db         = portal_db();
$countries  = portal_countries();

// ── Handle status toggle ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    if (!portal_verify_csrf()) {
        portal_flash('error', 'Invalid CSRF token.');
    } else {
        $id     = (int) ($_POST['edition_id'] ?? 0);
        $action = $_POST['action'];

        if ($id > 0) {
            $valid_actions = [
                'publish' => 'published',
                'unpublish' => 'draft',
                'archive' => 'archived',
                'delete' => null,
            ];

            if (array_key_exists($action, $valid_actions)) {
                try {
                    if ($action === 'delete') {
                        $stmt = $db->prepare("DELETE FROM editions WHERE id = ?");
                        $stmt->execute([$id]);
                        portal_flash('success', 'Edition deleted.');
                    } else {
                        $new_status = $valid_actions[$action];
                        $stmt = $db->prepare("UPDATE editions SET status = ? WHERE id = ?");
                        $stmt->execute([$new_status, $id]);
                        portal_flash('success', 'Edition status updated to ' . $new_status . '.');
                    }
                } catch (PDOException $e) {
                    portal_flash('error', 'Database error: ' . $e->getMessage());
                }
            }
        }
    }
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
}

// ── Filters ───────────────────────────────────
$status_filter = $_GET['status'] ?? 'all';
$type_filter   = $_GET['type'] ?? 'all';

$where  = '1=1';
$params = [];

if ($status_filter !== 'all') {
    $where .= ' AND status = ?';
    $params[] = $status_filter;
}
if ($type_filter !== 'all') {
    $where .= ' AND edition_type = ?';
    $params[] = $type_filter;
}

$stmt = $db->prepare("
    SELECT id, title, slug, country, edition_date, edition_type, category,
           status, cover_image, page_count, is_free, html_url, created_at
    FROM editions
    WHERE $where
    ORDER BY created_at DESC
");
$stmt->execute($params);
$editions = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Header ────────────────────────────── -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-size:24px; font-weight:700; color:#1e2b42; margin-bottom:4px;">
            <i class="fas fa-newspaper" style="color:#f05a1a;"></i> Editions
        </h1>
        <p style="color:#888; font-size:14px;"><?php echo count($editions); ?> edition(s) found</p>
    </div>
    <a href="<?php echo portal_url('upload.php'); ?>" class="btn btn-primary">
        <i class="fas fa-plus"></i> Upload New
    </a>
</div>

<!-- ── Filters ────────────────────────────────── -->
<div style="display:flex; gap:8px; margin-bottom:20px; flex-wrap:wrap;">
    <?php
    $statuses = ['all' => 'All', 'draft' => 'Drafts', 'published' => 'Published', 'archived' => 'Archived'];
    foreach ($statuses as $val => $label):
        $active = $status_filter === $val;
    ?>
    <a href="?status=<?php echo $val; ?>&type=<?php echo $type_filter; ?>"
       class="btn <?php echo $active ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
        <?php echo $label; ?>
    </a>
    <?php endforeach; ?>

    <span style="margin:0 8px; border-left:1px solid #ddd;"></span>

    <?php
    $types = ['all' => 'All Types', 'daily' => 'Daily', 'special' => 'Special', 'rate_card' => 'Rate Card'];
    foreach ($types as $val => $label):
        $active = $type_filter === $val;
    ?>
    <a href="?status=<?php echo $status_filter; ?>&type=<?php echo $val; ?>"
       class="btn <?php echo $active ? 'btn-primary' : 'btn-outline'; ?> btn-sm">
        <?php echo $label; ?>
    </a>
    <?php endforeach; ?>
</div>

<!-- ── Editions Table ─────────────────────────── -->
<div class="card">
    <?php if (empty($editions)): ?>
    <div style="text-align:center; padding:48px 20px; color:#999;">
        <i class="fas fa-folder-open" style="font-size:48px; color:#ddd; margin-bottom:16px; display:block;"></i>
        <p style="font-size:16px; font-weight:500;">No editions match this filter.</p>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Country</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($editions as $ed): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <?php if (!empty($ed['cover_image'])): ?>
                            <img src="<?php echo portal_cms_url($ed['cover_image']); ?>"
                                 alt="" style="width:40px; height:52px; object-fit:cover; border-radius:6px; border:1px solid #eee;">
                            <?php else: ?>
                            <div style="width:40px; height:52px; background:#f0f2f5; border-radius:6px; display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-newspaper" style="color:#ccc;"></i>
                            </div>
                            <?php endif; ?>
                            <div>
                                <strong style="color:#1e2b42;"><?php echo htmlspecialchars($ed['title']); ?></strong>
                                <?php if ($ed['is_free']): ?><span style="font-size:10px; color:#10b981; font-weight:700;"> FREE</span><?php endif; ?>
                                <div style="font-size:11px; color:#999;"><?php echo $ed['page_count']; ?> pages</div>
                            </div>
                        </div>
                    </td>
                    <td style="white-space:nowrap;"><?php echo date('M j, Y', strtotime($ed['edition_date'])); ?></td>
                    <td><span style="font-weight:600;"><?php echo strtoupper($ed['country']); ?></span></td>
                    <td><span class="badge badge-<?php echo $ed['edition_type']; ?>"><?php echo str_replace('_', ' ', $ed['edition_type']); ?></span></td>
                    <td><?php echo $ed['category'] ? ucwords(str_replace('_', ' ', $ed['category'])) : '—'; ?></td>
                    <td><span class="badge badge-<?php echo $ed['status']; ?>"><?php echo $ed['status']; ?></span></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <!-- Preview -->
                        <?php if (!empty($ed['html_url'])): ?>
                        <a href="<?php echo portal_cms_url($ed['html_url']); ?>"
                           target="_blank" class="btn btn-outline btn-sm" title="Preview">
                            <i class="fas fa-eye"></i>
                        </a>
                        <?php endif; ?>

                        <!-- Publish / Unpublish -->
                        <?php if ($ed['status'] === 'draft'): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Publish this edition? It will become visible in the app.');">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="edition_id" value="<?php echo $ed['id']; ?>">
                            <input type="hidden" name="action" value="publish">
                            <button type="submit" class="btn btn-sm" style="background:#10b981; color:#fff; border:none;" title="Publish">
                                <i class="fas fa-check-circle"></i>
                            </button>
                        </form>
                        <?php elseif ($ed['status'] === 'published'): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Unpublish this edition? It will be hidden from the app.');">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="edition_id" value="<?php echo $ed['id']; ?>">
                            <input type="hidden" name="action" value="unpublish">
                            <button type="submit" class="btn btn-outline btn-sm" title="Unpublish">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </form>
                        <?php endif; ?>

                        <!-- Archive -->
                        <?php if ($ed['status'] !== 'archived'): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Archive this edition?');">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="edition_id" value="<?php echo $ed['id']; ?>">
                            <input type="hidden" name="action" value="archive">
                            <button type="submit" class="btn btn-outline btn-sm" title="Archive">
                                <i class="fas fa-archive"></i>
                            </button>
                        </form>
                        <?php endif; ?>

                        <!-- Delete -->
                        <form method="POST" style="display:inline;" onsubmit="return confirm('DELETE this edition permanently? This cannot be undone.');">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="edition_id" value="<?php echo $ed['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-sm" style="background:#ef4444; color:#fff; border:none;" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
