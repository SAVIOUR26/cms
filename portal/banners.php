<?php
/**
 * KandaNews Africa — Home Banners Management
 *
 * Manage the server-driven info carousel banners shown on the app home screen.
 * Supports create / edit / toggle active / delete.
 * Shows impression and click analytics per banner.
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$pdo        = portal_db();
$page_title = 'Home Banners';

// ── Action handlers ─────────────────────────────────────────────────────────

$error   = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portal_verify_csrf()) {
        $error = 'Invalid request. Please try again.';
    } else {
        $action = $_POST['_action'] ?? '';

        // ── Create / Edit ──────────────────────────────────────────────────
        if ($action === 'save') {
            $id           = (int)($_POST['id'] ?? 0);
            $title        = trim($_POST['title'] ?? '');
            $subtitle     = trim($_POST['subtitle'] ?? '') ?: null;
            $action_url   = trim($_POST['action_url'] ?? '') ?: null;
            $action_label = trim($_POST['action_label'] ?? '') ?: null;
            $bg_color_hex = trim($_POST['bg_color_hex'] ?? '#1E2B42');
            $icon_name    = trim($_POST['icon_name'] ?? '') ?: null;
            $country      = trim($_POST['country'] ?? '') ?: null;
            $sort_order   = max(0, (int)($_POST['sort_order'] ?? 0));
            $starts_at    = trim($_POST['starts_at'] ?? '') ?: null;
            $ends_at      = trim($_POST['ends_at'] ?? '') ?: null;
            $is_active    = isset($_POST['is_active']) ? 1 : 0;

            // Validate colour format
            if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $bg_color_hex)) {
                $bg_color_hex = '#1E2B42';
            }

            if (!$title) {
                $error = 'Title is required.';
            } else {
                try {
                    if ($id > 0) {
                        // Update existing
                        $stmt = $pdo->prepare("
                            UPDATE home_banners
                            SET title        = ?,
                                subtitle     = ?,
                                action_url   = ?,
                                action_label = ?,
                                bg_color_hex = ?,
                                icon_name    = ?,
                                country      = ?,
                                sort_order   = ?,
                                starts_at    = ?,
                                ends_at      = ?,
                                is_active    = ?
                            WHERE id = ?
                        ");
                        $stmt->execute([
                            $title, $subtitle, $action_url, $action_label,
                            $bg_color_hex, $icon_name, $country,
                            $sort_order, $starts_at, $ends_at, $is_active, $id,
                        ]);
                        portal_flash('success', 'Banner updated successfully.');
                    } else {
                        // Insert new
                        $stmt = $pdo->prepare("
                            INSERT INTO home_banners
                                (title, subtitle, action_url, action_label,
                                 bg_color_hex, icon_name, country, sort_order,
                                 starts_at, ends_at, is_active)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $stmt->execute([
                            $title, $subtitle, $action_url, $action_label,
                            $bg_color_hex, $icon_name, $country,
                            $sort_order, $starts_at, $ends_at, $is_active,
                        ]);
                        portal_flash('success', 'Banner created successfully.');
                    }
                    header('Location: ' . portal_url('banners.php'));
                    exit;
                } catch (PDOException $e) {
                    $error = 'Database error: ' . $e->getMessage();
                }
            }

        // ── Toggle active ──────────────────────────────────────────────────
        } elseif ($action === 'toggle') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare("UPDATE home_banners SET is_active = 1 - is_active WHERE id = ?")
                    ->execute([$id]);
                portal_flash('success', 'Banner status updated.');
            }
            header('Location: ' . portal_url('banners.php'));
            exit;

        // ── Delete ─────────────────────────────────────────────────────────
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $pdo->prepare("DELETE FROM home_banners WHERE id = ?")->execute([$id]);
                portal_flash('success', 'Banner deleted.');
            }
            header('Location: ' . portal_url('banners.php'));
            exit;
        }
    }
}

// ── Fetch banners ────────────────────────────────────────────────────────────

$banners = $pdo->query("
    SELECT id, title, subtitle, action_url, action_label,
           bg_color_hex, icon_name, country, is_active,
           sort_order, starts_at, ends_at, created_at,
           COALESCE(impression_count, 0) AS impression_count,
           COALESCE(click_count, 0)      AS click_count
    FROM   home_banners
    ORDER  BY sort_order ASC, id ASC
")->fetchAll();

// ── Edit prefill ─────────────────────────────────────────────────────────────

$edit = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    foreach ($banners as $b) {
        if ((int)$b['id'] === $editId) { $edit = $b; break; }
    }
}

// ── Stats ────────────────────────────────────────────────────────────────────

$totalBanners     = count($banners);
$activeBanners    = count(array_filter($banners, fn($b) => $b['is_active']));
$totalImpressions = array_sum(array_column($banners, 'impression_count'));
$totalClicks      = array_sum(array_column($banners, 'click_count'));
$overallCtr       = $totalImpressions > 0
    ? round(($totalClicks / $totalImpressions) * 100, 1)
    : 0;

require_once __DIR__ . '/includes/header.php';
?>

<div class="section-header">
    <div>
        <h1><i class="fas fa-images" style="color:var(--orange);margin-right:8px;"></i>Home Banners</h1>
        <p>Manage the info carousel shown on the app home screen. Quote of the Day always appears first; banners follow in sort order.</p>
    </div>
    <button class="btn btn-primary" onclick="openModal()">
        <i class="fas fa-plus"></i> New Banner
    </button>
</div>

<?php if ($error): ?>
<div class="flash flash-error"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<!-- ── Stats ──────────────────────────────────────────────────────────────── -->
<div class="stat-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
    <div class="stat-card">
        <div class="stat-icon si-navy"><i class="fas fa-images"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $totalBanners; ?></div>
            <div class="stat-label">Total Banners</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $activeBanners; ?></div>
            <div class="stat-label">Active Now</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-blue"><i class="fas fa-eye"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($totalImpressions); ?></div>
            <div class="stat-label">Total Impressions</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon si-orange"><i class="fas fa-hand-pointer"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo number_format($totalClicks); ?></div>
            <div class="stat-label">Total Clicks</div>
            <div class="stat-trend <?php echo $overallCtr >= 2 ? 'up' : 'neutral'; ?>">
                CTR <?php echo $overallCtr; ?>%
            </div>
        </div>
    </div>
</div>

<!-- ── Banners table ───────────────────────────────────────────────────────── -->
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-list"></i> All Banners</h2>
        <span style="font-size:13px;color:#888;">Ordered by sort position · Quote of the Day always precedes these</span>
    </div>

    <?php if (empty($banners)): ?>
    <div class="empty-state">
        <i class="fas fa-images"></i>
        <h3>No banners yet</h3>
        <p>Create your first banner to start populating the home screen carousel.</p>
        <button class="btn btn-primary" onclick="openModal()"><i class="fas fa-plus"></i> New Banner</button>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Banner</th>
                    <th>Country</th>
                    <th>Schedule</th>
                    <th style="text-align:center;">Impressions</th>
                    <th style="text-align:center;">Clicks</th>
                    <th style="text-align:center;">CTR</th>
                    <th style="text-align:center;">Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($banners as $b):
                $ctr = $b['impression_count'] > 0
                    ? round(($b['click_count'] / $b['impression_count']) * 100, 1)
                    : 0;
                $countries = portal_countries();
                $countryLabel = $b['country']
                    ? ($countries[strtoupper($b['country'])] ?? strtoupper($b['country']))
                    : 'All Countries';
            ?>
            <tr>
                <td style="color:#888;font-size:12px;"><?php echo (int)$b['id']; ?></td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <!-- Colour swatch -->
                        <div style="
                            width:36px;height:36px;border-radius:8px;flex-shrink:0;
                            background:<?php echo htmlspecialchars($b['bg_color_hex']); ?>;
                            display:flex;align-items:center;justify-content:center;
                            color:#fff;font-size:14px;">
                            <i class="fas fa-bullhorn"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;color:var(--navy);font-size:14px;">
                                <?php echo htmlspecialchars($b['title']); ?>
                            </div>
                            <?php if ($b['subtitle']): ?>
                            <div style="font-size:12px;color:#888;margin-top:2px;">
                                <?php echo htmlspecialchars(mb_strimwidth($b['subtitle'], 0, 60, '…')); ?>
                            </div>
                            <?php endif; ?>
                            <?php if ($b['action_url']): ?>
                            <div style="font-size:11px;color:var(--orange);margin-top:2px;">
                                <i class="fas fa-link" style="font-size:9px;"></i>
                                <?php echo htmlspecialchars($b['action_label'] ?? $b['action_url']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </td>
                <td>
                    <span style="font-size:12px;color:#555;">
                        <?php echo htmlspecialchars($countryLabel); ?>
                    </span>
                </td>
                <td style="font-size:12px;color:#888;">
                    <?php if ($b['starts_at'] || $b['ends_at']): ?>
                        <?php if ($b['starts_at']): ?>
                        <div>From: <?php echo date('d M Y', strtotime($b['starts_at'])); ?></div>
                        <?php endif; ?>
                        <?php if ($b['ends_at']): ?>
                        <div>To: <?php echo date('d M Y', strtotime($b['ends_at'])); ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#bbb;">Always</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:center;font-weight:700;color:var(--navy);">
                    <?php echo number_format($b['impression_count']); ?>
                </td>
                <td style="text-align:center;font-weight:700;color:var(--navy);">
                    <?php echo number_format($b['click_count']); ?>
                </td>
                <td style="text-align:center;">
                    <span style="font-size:13px;font-weight:700;color:<?php echo $ctr >= 3 ? 'var(--green)' : ($ctr >= 1 ? 'var(--orange)' : '#9ca3af'); ?>">
                        <?php echo $ctr; ?>%
                    </span>
                </td>
                <td style="text-align:center;">
                    <?php if ($b['is_active']): ?>
                        <span class="badge badge-published">Active</span>
                    <?php else: ?>
                        <span class="badge badge-archived">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:nowrap;">
                        <a href="?edit=<?php echo (int)$b['id']; ?>"
                           class="btn btn-outline btn-sm" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <form method="post" style="display:inline;">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="toggle">
                            <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                            <button type="submit"
                                class="btn btn-sm <?php echo $b['is_active'] ? 'btn-warning' : 'btn-success'; ?>"
                                title="<?php echo $b['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                <i class="fas fa-<?php echo $b['is_active'] ? 'pause' : 'play'; ?>"></i>
                            </button>
                        </form>
                        <form method="post" style="display:inline;"
                              onsubmit="return confirm('Delete this banner? This cannot be undone.');">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$b['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- ═══════════════════════════════════════════════════════════════════════════
     CREATE / EDIT MODAL
══════════════════════════════════════════════════════════════════════════════ -->
<div id="bannerModal" style="
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
    z-index:1000;overflow-y:auto;padding:40px 20px;">
    <div style="
        background:#fff;border-radius:16px;max-width:600px;
        margin:0 auto;padding:0;box-shadow:0 20px 60px rgba(0,0,0,.2);">

        <!-- Modal header -->
        <div style="
            background:linear-gradient(135deg,var(--navy),var(--navy-l));
            padding:24px 28px;border-radius:16px 16px 0 0;
            display:flex;align-items:center;justify-content:space-between;">
            <div>
                <h2 style="color:#fff;font-size:18px;font-weight:800;margin:0;">
                    <i class="fas fa-images" style="color:var(--orange);margin-right:8px;"></i>
                    <span id="modalTitle">New Banner</span>
                </h2>
                <p style="color:rgba(255,255,255,.6);font-size:13px;margin:4px 0 0;">
                    Appears in the home screen info carousel after the Quote of the Day
                </p>
            </div>
            <button onclick="closeModal()" style="
                background:rgba(255,255,255,.15);border:none;color:#fff;
                width:36px;height:36px;border-radius:50%;cursor:pointer;
                font-size:16px;display:flex;align-items:center;justify-content:center;">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Modal body -->
        <form method="post" style="padding:28px;">
            <?php echo portal_csrf_field(); ?>
            <input type="hidden" name="_action" value="save">
            <input type="hidden" name="id" id="bannerId" value="0">

            <!-- Title + Subtitle -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Title <span class="req">*</span></label>
                    <input type="text" name="title" id="fTitle" class="form-control"
                           placeholder="New here? Start here" maxlength="255" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Subtitle</label>
                    <input type="text" name="subtitle" id="fSubtitle" class="form-control"
                           placeholder="Short supporting text" maxlength="255">
                </div>
            </div>

            <!-- CTA URL + Label -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Action URL</label>
                    <input type="text" name="action_url" id="fActionUrl" class="form-control"
                           placeholder="/special-editions or https://...">
                    <div class="form-hint">Use /path for in-app links or full https:// for external</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Action Label</label>
                    <input type="text" name="action_label" id="fActionLabel" class="form-control"
                           placeholder="Explore" maxlength="100">
                    <div class="form-hint">Button text shown on the banner</div>
                </div>
            </div>

            <!-- Background colour + Icon -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Background Colour</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="color" name="bg_color_hex" id="fBgColor"
                               value="#1E2B42"
                               style="width:44px;height:44px;border:1.5px solid #e0e0e0;
                                      border-radius:8px;cursor:pointer;padding:2px;">
                        <input type="text" id="fBgColorText"
                               value="#1E2B42"
                               style="flex:1;padding:11px 14px;border:1.5px solid #e0e0e0;
                                      border-radius:8px;font-size:14px;font-family:monospace;"
                               maxlength="7"
                               oninput="document.getElementById('fBgColor').value=this.value">
                    </div>
                    <div class="form-hint">Hex code — navy #1E2B42, orange #F05A1A</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Icon Name</label>
                    <input type="text" name="icon_name" id="fIconName" class="form-control"
                           placeholder="play_circle">
                    <div class="form-hint">Flutter icon key: play_circle, campaign, star, work…</div>
                </div>
            </div>

            <!-- Country + Sort order -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <select name="country" id="fCountry" class="form-control">
                        <option value="">All Countries</option>
                        <?php foreach (portal_countries() as $code => $label): ?>
                        <option value="<?php echo strtolower($code); ?>">
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" id="fSortOrder" class="form-control"
                           value="0" min="0" max="255">
                    <div class="form-hint">Lower numbers appear first (0 = first)</div>
                </div>
            </div>

            <!-- Schedule -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Show From</label>
                    <input type="datetime-local" name="starts_at" id="fStartsAt"
                           class="form-control">
                    <div class="form-hint">Leave blank for no start restriction</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Show Until</label>
                    <input type="datetime-local" name="ends_at" id="fEndsAt"
                           class="form-control">
                    <div class="form-hint">Leave blank for no expiry</div>
                </div>
            </div>

            <!-- Active toggle -->
            <div class="form-group" style="display:flex;align-items:center;gap:10px;">
                <input type="checkbox" name="is_active" id="fIsActive"
                       value="1" checked
                       style="width:18px;height:18px;cursor:pointer;">
                <label for="fIsActive" class="form-label" style="margin:0;cursor:pointer;">
                    Active — show this banner in the carousel immediately
                </label>
            </div>

            <!-- Buttons -->
            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px;">
                <button type="button" onclick="closeModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Banner
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
// Sync colour picker with text input
document.getElementById('fBgColor').addEventListener('input', function () {
    document.getElementById('fBgColorText').value = this.value;
});

function openModal(data) {
    const modal = document.getElementById('bannerModal');
    if (data) {
        document.getElementById('modalTitle').textContent  = 'Edit Banner';
        document.getElementById('bannerId').value          = data.id;
        document.getElementById('fTitle').value            = data.title;
        document.getElementById('fSubtitle').value         = data.subtitle  || '';
        document.getElementById('fActionUrl').value        = data.action_url   || '';
        document.getElementById('fActionLabel').value      = data.action_label || '';
        document.getElementById('fBgColor').value          = data.bg_color_hex || '#1E2B42';
        document.getElementById('fBgColorText').value      = data.bg_color_hex || '#1E2B42';
        document.getElementById('fIconName').value         = data.icon_name    || '';
        document.getElementById('fCountry').value          = data.country      || '';
        document.getElementById('fSortOrder').value        = data.sort_order   || 0;
        document.getElementById('fStartsAt').value         = data.starts_at    ? data.starts_at.slice(0,16) : '';
        document.getElementById('fEndsAt').value           = data.ends_at      ? data.ends_at.slice(0,16)   : '';
        document.getElementById('fIsActive').checked       = data.is_active == 1;
    } else {
        document.getElementById('modalTitle').textContent  = 'New Banner';
        document.getElementById('bannerId').value          = '0';
        document.getElementById('fTitle').value            = '';
        document.getElementById('fSubtitle').value         = '';
        document.getElementById('fActionUrl').value        = '';
        document.getElementById('fActionLabel').value      = '';
        document.getElementById('fBgColor').value          = '#1E2B42';
        document.getElementById('fBgColorText').value      = '#1E2B42';
        document.getElementById('fIconName').value         = '';
        document.getElementById('fCountry').value          = '';
        document.getElementById('fSortOrder').value        = '0';
        document.getElementById('fStartsAt').value         = '';
        document.getElementById('fEndsAt').value           = '';
        document.getElementById('fIsActive').checked       = true;
    }
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('bannerModal').style.display = 'none';
    document.body.style.overflow = '';
    // Clear edit param from URL without page reload
    if (history.replaceState) {
        history.replaceState(null, '', '<?php echo portal_url('banners.php'); ?>');
    }
}

// Close on backdrop click
document.getElementById('bannerModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});

<?php if ($edit): ?>
// Auto-open modal for edit
openModal(<?php echo json_encode([
    'id'           => $edit['id'],
    'title'        => $edit['title'],
    'subtitle'     => $edit['subtitle'],
    'action_url'   => $edit['action_url'],
    'action_label' => $edit['action_label'],
    'bg_color_hex' => $edit['bg_color_hex'],
    'icon_name'    => $edit['icon_name'],
    'country'      => $edit['country'],
    'sort_order'   => $edit['sort_order'],
    'starts_at'    => $edit['starts_at'],
    'ends_at'      => $edit['ends_at'],
    'is_active'    => $edit['is_active'],
]); ?>);
<?php endif; ?>
</script>
