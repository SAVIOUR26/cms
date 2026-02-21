<?php
/**
 * KandaNews Africa — Portal Dashboard
 *
 * Overview page: stat cards, quick actions, recent editions.
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$page_title = 'Dashboard';
$user       = portal_get_user();
$db         = portal_db();

// ── Fetch stats ──────────────────────────────
$total_editions    = 0;
$published_count   = 0;
$draft_count       = 0;
$subscriber_count  = 0;
$recent_editions   = [];

try {
    // Total editions
    $stmt = $db->query("SELECT COUNT(*) FROM editions");
    $total_editions = (int) $stmt->fetchColumn();

    // Published
    $stmt = $db->query("SELECT COUNT(*) FROM editions WHERE status = 'published'");
    $published_count = (int) $stmt->fetchColumn();

    // Drafts
    $stmt = $db->query("SELECT COUNT(*) FROM editions WHERE status = 'draft'");
    $draft_count = (int) $stmt->fetchColumn();

    // Subscribers (if table exists)
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
        $subscriber_count = (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        $subscriber_count = 0;
    }

    // Recent editions (last 10)
    $stmt = $db->query(
        "SELECT id, title, slug, country, edition_date, edition_type, status, cover_image, created_at
         FROM editions ORDER BY created_at DESC LIMIT 10"
    );
    $recent_editions = $stmt->fetchAll();
} catch (PDOException $e) {
    // Tables might not exist yet
}

// Country map for display
$countries = portal_countries();

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Welcome Header ─────────────────────────── -->
<div style="margin-bottom:28px;">
    <h1 style="font-size:28px; font-weight:700; color:#1e2b42; margin-bottom:4px;">
        Welcome back, <?php echo htmlspecialchars($user['full_name'] ?? $user['username'] ?? 'Admin'); ?>
    </h1>
    <p style="color:#888; font-size:15px;">
        Here is what is happening with your editions today, <?php echo date('l, F j, Y'); ?>.
    </p>
</div>

<!-- ── Stat Cards ─────────────────────────────── -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon navy"><i class="fas fa-newspaper"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($total_editions); ?></div>
            <div class="stat-label">Total Editions</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($published_count); ?></div>
            <div class="stat-label">Published</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fas fa-pencil-alt"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($draft_count); ?></div>
            <div class="stat-label">Drafts</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($subscriber_count); ?></div>
            <div class="stat-label">Subscribers</div>
        </div>
    </div>
</div>

<!-- ── Quick Actions ──────────────────────────── -->
<div class="quick-actions">
    <a class="quick-action" href="<?php echo portal_url('upload.php'); ?>">
        <i class="fas fa-cloud-upload-alt"></i>
        Upload Edition
    </a>
    <a class="quick-action" href="<?php echo portal_cms_url('build-edition.php'); ?>">
        <i class="fas fa-hammer"></i>
        Build Edition
    </a>
    <a class="quick-action" href="<?php echo portal_url('editions.php'); ?>">
        <i class="fas fa-list"></i>
        View Editions
    </a>
    <a class="quick-action" href="<?php echo portal_url('editions.php?status=published'); ?>">
        <i class="fas fa-globe-africa"></i>
        Published
    </a>
</div>

<!-- ── Recent Editions ────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-clock"></i> Recent Editions</h2>
        <a href="<?php echo portal_url('editions.php'); ?>" class="btn btn-outline btn-sm">
            View All <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <?php if (empty($recent_editions)): ?>
    <div style="text-align:center; padding:48px 20px; color:#999;">
        <i class="fas fa-folder-open" style="font-size:48px; color:#ddd; margin-bottom:16px; display:block;"></i>
        <p style="font-size:16px; font-weight:500; margin-bottom:8px;">No editions yet</p>
        <p style="font-size:14px;">Upload your first edition to get started.</p>
        <a href="<?php echo portal_url('upload.php'); ?>" class="btn btn-primary" style="margin-top:16px;">
            <i class="fas fa-plus"></i> Upload Edition
        </a>
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
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_editions as $ed): ?>
                <tr>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px;">
                            <?php if (!empty($ed['cover_image'])): ?>
                            <img src="<?php echo portal_cms_url('uploads/covers/' . basename($ed['cover_image'])); ?>"
                                 alt="" style="width:40px; height:52px; object-fit:cover; border-radius:6px; border:1px solid #eee;">
                            <?php else: ?>
                            <div style="width:40px; height:52px; background:#f0f2f5; border-radius:6px; display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-newspaper" style="color:#ccc;"></i>
                            </div>
                            <?php endif; ?>
                            <div>
                                <strong style="color:#1e2b42;"><?php echo htmlspecialchars($ed['title']); ?></strong>
                            </div>
                        </div>
                    </td>
                    <td style="white-space:nowrap;"><?php echo date('M j, Y', strtotime($ed['edition_date'])); ?></td>
                    <td>
                        <span style="font-weight:600;"><?php echo strtoupper($ed['country']); ?></span>
                        <span style="font-size:12px; color:#888;"><?php echo $countries[strtoupper($ed['country'])] ?? ''; ?></span>
                    </td>
                    <td><span class="badge badge-<?php echo $ed['edition_type']; ?>"><?php echo str_replace('_', ' ', $ed['edition_type']); ?></span></td>
                    <td><span class="badge badge-<?php echo $ed['status']; ?>"><?php echo $ed['status']; ?></span></td>
                    <td style="text-align:right; white-space:nowrap;">
                        <a href="<?php echo portal_url('edit-edition.php?id=' . $ed['id']); ?>" class="btn btn-outline btn-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <?php if (!empty($ed['slug'])): ?>
                        <a href="<?php echo portal_cms_url('output/' . $ed['slug'] . '/index.html'); ?>"
                           target="_blank" class="btn btn-outline btn-sm" title="Preview">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
