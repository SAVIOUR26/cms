<?php
/**
 * KandaNews Africa — CMS Dashboard
 *
 * Main overview: stats, quick actions, recent editions, revenue snapshot.
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$page_title   = 'Dashboard';
$page_section = 'overview';
$user         = portal_get_user();
$db           = portal_db();

// ── Fetch statistics ─────────────────────────
$stats = [
    'total'       => 0,
    'published'   => 0,
    'drafts'      => 0,
    'special'     => 0,
    'subscribers' => 0,
    'active_subs' => 0,
];

$recent_editions    = [];
$recent_subscribers = [];

try {
    $stats['total']     = (int) $db->query("SELECT COUNT(*) FROM editions")->fetchColumn();
    $stats['published'] = (int) $db->query("SELECT COUNT(*) FROM editions WHERE status='published'")->fetchColumn();
    $stats['drafts']    = (int) $db->query("SELECT COUNT(*) FROM editions WHERE status='draft'")->fetchColumn();
    $stats['special']   = (int) $db->query("SELECT COUNT(*) FROM editions WHERE edition_type='special' AND status='published'")->fetchColumn();

    try {
        $stats['subscribers'] = (int) $db->query("SELECT COUNT(*) FROM users WHERE status='active'")->fetchColumn();
        $stats['active_subs'] = (int) $db->query("SELECT COUNT(*) FROM subscriptions WHERE status='active' AND expires_at > NOW()")->fetchColumn();
    } catch (PDOException $e) { /* tables may not exist yet */ }

    // Recent editions (last 8)
    $stmt = $db->query("
        SELECT id, title, country, edition_date, edition_type, category, status, cover_image, created_at
        FROM editions ORDER BY created_at DESC LIMIT 8
    ");
    $recent_editions = $stmt->fetchAll();

    // Recent subscribers (last 5)
    try {
        $stmt = $db->query("
            SELECT u.full_name, u.phone, u.country, s.plan, s.status, s.expires_at
            FROM subscriptions s
            JOIN users u ON u.id = s.user_id
            ORDER BY s.created_at DESC LIMIT 5
        ");
        $recent_subscribers = $stmt->fetchAll();
    } catch (PDOException $e) {}

} catch (PDOException $e) {
    // DB not ready yet — show zeros
}

$countries = portal_countries();

$cat_labels = [
    'university'      => 'University',
    'corporate'       => 'Corporate',
    'entrepreneurship'=> 'Entrepreneurship',
    'campaigns'       => 'Campaigns',
    'jobs_careers'    => 'Jobs & Careers',
    'podcasts'        => 'Podcasts',
    'episodes'        => 'Episodes',
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Welcome ────────────────────────────────── -->
<div class="section-header" style="margin-bottom:20px;">
    <div>
        <h1>Good <?php echo (date('G') < 12 ? 'morning' : (date('G') < 17 ? 'afternoon' : 'evening')); ?>,
            <?php echo htmlspecialchars(explode(' ', $_username)[0]); ?> 👋</h1>
        <p><?php echo date('l, F j, Y'); ?> &mdash; Here's your content overview</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="<?php echo portal_url('upload.php'); ?>" class="btn btn-outline">
            <i class="fas fa-cloud-upload-alt"></i> Upload
        </a>
        <a href="<?php echo portal_cms_url('build-edition.php'); ?>" class="btn btn-primary">
            <i class="fas fa-hammer"></i> Build Edition
        </a>
    </div>
</div>

<!-- ── Stat cards ─────────────────────────────── -->
<div class="stat-grid">
    <a href="<?php echo portal_url('editions.php'); ?>" class="stat-card">
        <div class="stat-icon si-navy"><i class="fas fa-newspaper"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['total']); ?></div>
            <div class="stat-label">Total Editions</div>
        </div>
    </a>
    <a href="<?php echo portal_url('editions.php?status=published'); ?>" class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-globe-africa"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['published']); ?></div>
            <div class="stat-label">Published</div>
        </div>
    </a>
    <a href="<?php echo portal_url('editions.php?status=draft'); ?>" class="stat-card">
        <div class="stat-icon si-orange"><i class="fas fa-pencil-alt"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['drafts']); ?></div>
            <div class="stat-label">Drafts</div>
        </div>
    </a>
    <a href="<?php echo portal_url('special-editions.php'); ?>" class="stat-card">
        <div class="stat-icon si-pink"><i class="fas fa-star"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['special']); ?></div>
            <div class="stat-label">Special Editions</div>
        </div>
    </a>
    <a href="<?php echo portal_url('subscribers.php'); ?>" class="stat-card">
        <div class="stat-icon si-purple"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['subscribers']); ?></div>
            <div class="stat-label">Registered Users</div>
        </div>
    </a>
    <a href="<?php echo portal_url('subscribers.php?tab=active'); ?>" class="stat-card">
        <div class="stat-icon si-blue"><i class="fas fa-id-badge"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($stats['active_subs']); ?></div>
            <div class="stat-label">Active Subscribers</div>
        </div>
    </a>
</div>

<!-- ── Quick Actions ──────────────────────────── -->
<div class="card" style="padding:20px;">
    <div class="card-header" style="margin-bottom:16px;">
        <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
    </div>
    <div class="actions-grid">
        <a href="<?php echo portal_url('upload.php'); ?>" class="action-tile">
            <i class="fas fa-cloud-upload-alt"></i>Upload Edition
        </a>
        <a href="<?php echo portal_url('upload.php?type=special&category=university'); ?>" class="action-tile">
            <i class="fas fa-graduation-cap"></i>University Edition
        </a>
        <a href="<?php echo portal_cms_url('build-edition.php'); ?>" class="action-tile">
            <i class="fas fa-hammer"></i>Build Edition
        </a>
        <a href="<?php echo portal_cms_url('page-editor.php'); ?>" class="action-tile">
            <i class="fas fa-file-alt"></i>Page Editor
        </a>
        <a href="<?php echo portal_cms_url('visual-page-builder.php'); ?>" class="action-tile">
            <i class="fas fa-paint-brush"></i>Visual Builder
        </a>
        <a href="<?php echo portal_cms_url('pages-library.php'); ?>" class="action-tile">
            <i class="fas fa-layer-group"></i>Pages Library
        </a>
        <a href="<?php echo portal_url('subscribers.php'); ?>" class="action-tile">
            <i class="fas fa-users"></i>Subscribers
        </a>
        <a href="<?php echo portal_url('settings.php'); ?>" class="action-tile">
            <i class="fas fa-cog"></i>Settings
        </a>
    </div>
</div>

<!-- ── Two columns: Recent Editions + Special Categories ─ -->
<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start;" class="two-col-grid">

    <!-- Recent Editions -->
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-clock"></i> Recent Editions</h2>
            <a href="<?php echo portal_url('editions.php'); ?>" class="btn btn-outline btn-sm">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <?php if (empty($recent_editions)): ?>
        <div class="empty-state">
            <i class="fas fa-folder-open"></i>
            <h3>No editions yet</h3>
            <p>Upload your first edition to get started.</p>
            <a href="<?php echo portal_url('upload.php'); ?>" class="btn btn-primary">
                <i class="fas fa-plus"></i> Upload Edition
            </a>
        </div>
        <?php else: ?>
        <div class="table-wrapper">
            <table class="dt">
                <thead>
                    <tr>
                        <th>Edition</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_editions as $ed): ?>
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <?php if (!empty($ed['cover_image'])): ?>
                                <img src="<?php echo portal_cms_url('uploads/covers/' . basename($ed['cover_image'])); ?>"
                                     alt="" class="cover-thumb">
                                <?php else: ?>
                                <div class="cover-placeholder"><i class="fas fa-newspaper"></i></div>
                                <?php endif; ?>
                                <div>
                                    <strong style="color:var(--navy);font-size:13px;">
                                        <?php echo htmlspecialchars($ed['title']); ?>
                                    </strong>
                                    <div style="font-size:11px;color:#9ca3af;margin-top:1px;">
                                        <?php echo strtoupper($ed['country']); ?>
                                        <?php if (!empty($ed['category'])): ?>
                                        &middot; <?php echo $cat_labels[$ed['category']] ?? $ed['category']; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td style="white-space:nowrap;color:#555;font-size:13px;">
                            <?php echo date('M j, Y', strtotime($ed['edition_date'])); ?>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $ed['edition_type']; ?>">
                                <?php echo str_replace('_', ' ', $ed['edition_type']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $ed['status']; ?>">
                                <?php echo $ed['status']; ?>
                            </span>
                        </td>
                        <td>
                            <a href="<?php echo portal_url('editions.php'); ?>"
                               class="btn btn-ghost btn-sm" title="Manage">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div>
        <!-- Special Editions by Category -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-star"></i> Special Editions</h2>
                <a href="<?php echo portal_url('special-editions.php'); ?>" class="btn btn-outline btn-sm">Manage</a>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;">
                <?php
                $cats = [
                    ['slug' => 'university',       'icon' => 'fas fa-graduation-cap', 'label' => 'University',       'color' => '#0369a1'],
                    ['slug' => 'corporate',         'icon' => 'fas fa-briefcase',      'label' => 'Corporate',        'color' => '#15803d'],
                    ['slug' => 'entrepreneurship',  'icon' => 'fas fa-rocket',         'label' => 'Entrepreneurship', 'color' => '#c2410c'],
                    ['slug' => 'campaigns',         'icon' => 'fas fa-bullhorn',       'label' => 'Campaigns',        'color' => '#7c3aed'],
                    ['slug' => 'jobs_careers',      'icon' => 'fas fa-user-tie',       'label' => 'Jobs & Careers',   'color' => '#0f766e'],
                    ['slug' => 'podcasts',          'icon' => 'fas fa-podcast',        'label' => 'Podcasts',         'color' => '#be185d'],
                ];
                foreach ($cats as $cat):
                    $cnt = 0;
                    try {
                        $s = $db->prepare("SELECT COUNT(*) FROM editions WHERE edition_type='special' AND category=? AND status='published'");
                        $s->execute([$cat['slug']]);
                        $cnt = (int) $s->fetchColumn();
                    } catch (PDOException $e) {}
                ?>
                <a href="<?php echo portal_url('special-editions.php?category=' . $cat['slug']); ?>"
                   style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:#f9fafb;border-radius:8px;text-decoration:none;border:1.5px solid #e5e7eb;transition:all .15s;"
                   onmouseover="this.style.borderColor='<?php echo $cat['color']; ?>';this.style.background='#fff';"
                   onmouseout="this.style.borderColor='#e5e7eb';this.style.background='#f9fafb';">
                    <div style="width:34px;height:34px;background:<?php echo $cat['color']; ?>18;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="<?php echo $cat['icon']; ?>" style="color:<?php echo $cat['color']; ?>;font-size:15px;"></i>
                    </div>
                    <div style="flex:1;">
                        <div style="font-size:13px;font-weight:700;color:#1e2b42;"><?php echo $cat['label']; ?></div>
                        <div style="font-size:11px;color:#9ca3af;"><?php echo $cnt; ?> published edition<?php echo $cnt !== 1 ? 's' : ''; ?></div>
                    </div>
                    <i class="fas fa-chevron-right" style="color:#d1d5db;font-size:11px;"></i>
                </a>
                <?php endforeach; ?>
            </div>
            <a href="<?php echo portal_url('upload.php?type=special'); ?>"
               class="btn btn-primary btn-sm" style="width:100%;justify-content:center;margin-top:16px;">
                <i class="fas fa-plus"></i> Add Special Edition
            </a>
        </div>

        <!-- Recent Subscribers -->
        <?php if (!empty($recent_subscribers)): ?>
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-users"></i> Recent Subscribers</h2>
                <a href="<?php echo portal_url('subscribers.php'); ?>" class="btn btn-outline btn-sm">All</a>
            </div>
            <?php foreach ($recent_subscribers as $sub): ?>
            <div style="display:flex;align-items:center;gap:10px;padding:9px 0;border-bottom:1px solid #f3f4f6;">
                <div style="width:32px;height:32px;background:var(--navy);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;flex-shrink:0;">
                    <?php echo mb_strtoupper(mb_substr($sub['full_name'] ?? 'U', 0, 1)); ?>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-weight:600;font-size:13px;color:var(--navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?php echo htmlspecialchars($sub['full_name'] ?? 'Unknown'); ?>
                    </div>
                    <div style="font-size:11px;color:#9ca3af;">
                        <?php echo strtoupper($sub['plan'] ?? ''); ?> &middot; <?php echo strtoupper($sub['country'] ?? ''); ?>
                    </div>
                </div>
                <span class="badge badge-<?php echo $sub['status']; ?>" style="font-size:10px;">
                    <?php echo $sub['status']; ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<style>
@media (max-width: 960px) { .two-col-grid { grid-template-columns: 1fr !important; } }
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
