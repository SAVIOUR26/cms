<?php
/**
 * KandaNews Africa — Edition Categories Management
 *
 * Manage the server-driven category tiles shown in the app's Special Editions section.
 * Changes here are reflected instantly in the app via GET /edition-categories.
 *
 * Fields: slug, label, description, icon_name, color_hex,
 *         sort_order, edition_type, country, is_active
 *
 * Actions: add / edit / toggle active / delete / reorder (sort_order)
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$pdo        = portal_db();
$page_title = 'Edition Categories';

// Ensure table exists (migration_005 may not have run on all portals)
try {
    $pdo->query("SELECT 1 FROM edition_categories LIMIT 1");
} catch (PDOException $e) {
    // Create it inline so the page doesn't crash
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS edition_categories (
            id           SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            slug         VARCHAR(50)  NOT NULL UNIQUE,
            label        VARCHAR(100) NOT NULL,
            description  VARCHAR(255) DEFAULT NULL,
            icon_name    VARCHAR(50)  NOT NULL DEFAULT 'newspaper',
            color_hex    CHAR(7)      NOT NULL DEFAULT '#F05A1A',
            sort_order   TINYINT UNSIGNED NOT NULL DEFAULT 0,
            edition_type ENUM('special','rate_card') NOT NULL DEFAULT 'special',
            country      CHAR(2)      DEFAULT NULL,
            is_active    TINYINT(1)   NOT NULL DEFAULT 1,
            created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_active_country (is_active, country)
        ) ENGINE=InnoDB
    ");
}

// ── Action handlers ──────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portal_verify_csrf()) {
        portal_flash('error', 'Invalid request. Please try again.');
        header('Location: ' . portal_url('edition-categories.php'));
        exit;
    }

    $action = $_POST['_action'] ?? '';

    // ── Save (create or update) ──────────────────────────────────────────────
    if ($action === 'save') {
        $id           = (int)($_POST['id'] ?? 0);
        $slug         = strtolower(trim(preg_replace('/[^a-z0-9_-]/', '', $_POST['slug'] ?? '')));
        $label        = trim($_POST['label'] ?? '');
        $description  = trim($_POST['description'] ?? '') ?: null;
        $icon_name    = trim($_POST['icon_name'] ?? 'newspaper') ?: 'newspaper';
        $color_hex    = trim($_POST['color_hex'] ?? '#F05A1A');
        $sort_order   = max(0, min(255, (int)($_POST['sort_order'] ?? 0)));
        $edition_type = in_array($_POST['edition_type'] ?? '', ['special','rate_card']) ? $_POST['edition_type'] : 'special';
        $country      = trim($_POST['country'] ?? '') ?: null;
        $is_active    = isset($_POST['is_active']) ? 1 : 0;

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color_hex)) $color_hex = '#F05A1A';
        if ($country) $country = strtolower($country);

        $errors = [];
        if (!$slug)  $errors[] = 'Slug is required (lowercase letters, digits, - or _).';
        if (!$label) $errors[] = 'Label is required.';

        if ($errors) {
            portal_flash('error', implode(' ', $errors));
            header('Location: ' . portal_url('edition-categories.php') . ($id ? "?edit=$id" : '?new=1'));
            exit;
        }

        try {
            if ($id > 0) {
                $pdo->prepare("
                    UPDATE edition_categories
                    SET slug = ?, label = ?, description = ?, icon_name = ?,
                        color_hex = ?, sort_order = ?, edition_type = ?,
                        country = ?, is_active = ?
                    WHERE id = ?
                ")->execute([$slug, $label, $description, $icon_name,
                             $color_hex, $sort_order, $edition_type,
                             $country, $is_active, $id]);
                portal_flash('success', "Category \"{$label}\" updated.");
            } else {
                $pdo->prepare("
                    INSERT INTO edition_categories
                        (slug, label, description, icon_name, color_hex,
                         sort_order, edition_type, country, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ")->execute([$slug, $label, $description, $icon_name,
                             $color_hex, $sort_order, $edition_type,
                             $country, $is_active]);
                portal_flash('success', "Category \"{$label}\" created.");
            }
        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), '1062')) {
                portal_flash('error', "Slug \"{$slug}\" is already in use. Choose a different slug.");
            } else {
                portal_flash('error', 'Database error: ' . $e->getMessage());
            }
        }
        header('Location: ' . portal_url('edition-categories.php'));
        exit;

    // ── Toggle active ────────────────────────────────────────────────────────
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("UPDATE edition_categories SET is_active = 1 - is_active WHERE id = ?")
                ->execute([$id]);
            portal_flash('success', 'Category status updated.');
        }
        header('Location: ' . portal_url('edition-categories.php'));
        exit;

    // ── Delete ───────────────────────────────────────────────────────────────
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $slug = $pdo->prepare("SELECT slug FROM edition_categories WHERE id = ?");
            $slug->execute([$id]);
            $row = $slug->fetch();
            $pdo->prepare("DELETE FROM edition_categories WHERE id = ?")->execute([$id]);
            portal_flash('success', 'Category' . ($row ? " \"{$row['slug']}\"" : '') . ' deleted.');
        }
        header('Location: ' . portal_url('edition-categories.php'));
        exit;
    }
}

// ── Load data ────────────────────────────────────────────────────────────────

$categories = $pdo->query("
    SELECT * FROM edition_categories ORDER BY sort_order ASC, id ASC
")->fetchAll();

$activeCount   = 0;
$inactiveCount = 0;
foreach ($categories as $c) {
    if ($c['is_active']) $activeCount++; else $inactiveCount++;
}

// ── Edit pre-fill ────────────────────────────────────────────────────────────
$edit    = null;
$openNew = isset($_GET['new']);
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM edition_categories WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch() ?: null;
}

$flash = portal_get_flash();

// Common Flutter icon suggestions
$iconSuggestions = [
    'newspaper','school','business','rocket_launch','campaign',
    'work','podcasts','play_circle','price_change','star',
    'local_hospital','sports','music_note','movie','restaurant',
    'directions_car','home','public','favorite','group',
];

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page header ──────────────────────────────────────────────────────────── -->
<div class="section-header" style="margin-bottom:20px;">
    <div>
        <h1><i class="fas fa-th-large" style="color:var(--orange);margin-right:8px;"></i>Edition Categories</h1>
        <p>Control the category tiles shown in the app's Special Editions section. Changes are live immediately.</p>
    </div>
    <button onclick="openModal(null)" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Category
    </button>
</div>

<!-- ── Flash ────────────────────────────────────────────────────────────────── -->
<?php if ($flash): ?>
<div class="flash flash-<?php echo $flash['type']; ?>" style="margin-bottom:16px;">
    <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
    <?php echo htmlspecialchars($flash['message']); ?>
</div>
<?php endif; ?>

<!-- ── Stats strip ─────────────────────────────────────────────────────────── -->
<div style="display:flex;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
    <div class="stat-card" style="flex:1;min-width:140px;cursor:default;">
        <div class="stat-icon si-green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $activeCount; ?></div>
            <div class="stat-label">Active</div>
        </div>
    </div>
    <div class="stat-card" style="flex:1;min-width:140px;cursor:default;">
        <div class="stat-icon si-orange"><i class="fas fa-pause-circle"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo $inactiveCount; ?></div>
            <div class="stat-label">Inactive</div>
        </div>
    </div>
    <div class="stat-card" style="flex:1;min-width:140px;cursor:default;">
        <div class="stat-icon si-navy"><i class="fas fa-layer-group"></i></div>
        <div class="stat-info">
            <div class="stat-value"><?php echo count($categories); ?></div>
            <div class="stat-label">Total</div>
        </div>
    </div>
</div>

<!-- ── Info tip ─────────────────────────────────────────────────────────────── -->
<div style="background:#fffbf2;border:1.5px solid #fde68a;border-radius:10px;padding:12px 16px;margin-bottom:20px;font-size:13px;color:#92400e;display:flex;gap:10px;align-items:flex-start;">
    <i class="fas fa-lightbulb" style="margin-top:2px;flex-shrink:0;"></i>
    <div>
        <strong>How this works:</strong>
        The app fetches categories via <code>GET /edition-categories?country=ug</code>.
        <strong>Sort order</strong> controls the tile sequence (lower = first).
        <strong>Slug</strong> must match the <code>category</code> column on editions in the database.
        Icon names are <a href="https://fonts.google.com/icons" target="_blank" style="color:var(--orange);">Flutter/Material icon keys</a> (snake_case).
    </div>
</div>

<!-- ── Categories table ─────────────────────────────────────────────────────── -->
<div class="card">
    <?php if (empty($categories)): ?>
    <div class="empty-state">
        <i class="fas fa-th-large"></i>
        <h3>No categories yet</h3>
        <p>Add a category to start populating the Special Editions section in the app.</p>
        <button onclick="openModal(null)" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Category
        </button>
    </div>
    <?php else: ?>
    <div class="table-wrapper">
        <table class="dt">
            <thead>
                <tr>
                    <th style="width:40px;text-align:center;">Sort</th>
                    <th style="width:48px;">Colour</th>
                    <th>Label / Slug</th>
                    <th>Icon</th>
                    <th>Type</th>
                    <th>Country</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
            <tr style="<?php echo !$c['is_active'] ? 'opacity:.55;' : ''; ?>">
                <td style="text-align:center;font-size:12px;color:#aaa;font-weight:700;">
                    <?php echo (int)$c['sort_order']; ?>
                </td>
                <td>
                    <div style="
                        width:32px;height:32px;border-radius:8px;
                        background:<?php echo htmlspecialchars($c['color_hex']); ?>;
                        display:flex;align-items:center;justify-content:center;">
                        <span style="color:#fff;font-size:10px;font-weight:700;font-family:monospace;">
                            <?php echo strtoupper(substr($c['color_hex'], 1, 3)); ?>
                        </span>
                    </div>
                </td>
                <td>
                    <div style="font-weight:700;font-size:13px;color:var(--navy);">
                        <?php echo htmlspecialchars($c['label']); ?>
                    </div>
                    <code style="font-size:11px;color:#888;"><?php echo htmlspecialchars($c['slug']); ?></code>
                    <?php if ($c['description']): ?>
                    <div style="font-size:11px;color:#aaa;margin-top:2px;">
                        <?php echo htmlspecialchars($c['description']); ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td>
                    <code style="font-size:12px;color:#555;background:#f3f4f6;padding:3px 7px;border-radius:4px;">
                        <?php echo htmlspecialchars($c['icon_name']); ?>
                    </code>
                </td>
                <td>
                    <span class="badge <?php echo $c['edition_type'] === 'rate_card' ? 'badge-special' : 'badge-published'; ?>">
                        <?php echo $c['edition_type'] === 'rate_card' ? 'Rate Card' : 'Special'; ?>
                    </span>
                </td>
                <td style="font-size:13px;color:#555;">
                    <?php
                    $countryLabels = ['ug' => '🇺🇬 UG', 'ke' => '🇰🇪 KE', 'ng' => '🇳🇬 NG', 'za' => '🇿🇦 ZA'];
                    echo $c['country'] ? ($countryLabels[$c['country']] ?? strtoupper($c['country'])) : '<span style="color:#bbb;">All</span>';
                    ?>
                </td>
                <td style="text-align:center;">
                    <?php if ($c['is_active']): ?>
                        <span class="badge badge-published">Active</span>
                    <?php else: ?>
                        <span class="badge badge-archived">Inactive</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:6px;justify-content:flex-end;flex-wrap:nowrap;">
                        <!-- Edit -->
                        <button onclick='openModal(<?php echo json_encode([
                            "id"           => (int)$c['id'],
                            "slug"         => $c['slug'],
                            "label"        => $c['label'],
                            "description"  => $c['description'] ?? '',
                            "icon_name"    => $c['icon_name'],
                            "color_hex"    => $c['color_hex'],
                            "sort_order"   => (int)$c['sort_order'],
                            "edition_type" => $c['edition_type'],
                            "country"      => $c['country'] ?? '',
                            "is_active"    => (int)$c['is_active'],
                        ]); ?>)' class="btn btn-outline btn-sm" title="Edit">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <!-- Toggle -->
                        <form method="post" style="display:inline;">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="toggle">
                            <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                            <button type="submit"
                                class="btn btn-sm <?php echo $c['is_active'] ? 'btn-warning' : 'btn-success'; ?>"
                                title="<?php echo $c['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                <i class="fas fa-<?php echo $c['is_active'] ? 'pause' : 'play'; ?>"></i>
                            </button>
                        </form>
                        <!-- Delete -->
                        <form method="post" style="display:inline;"
                              onsubmit="return confirm('Delete category «<?php echo htmlspecialchars(addslashes($c['slug'])); ?>»?\n\nEditions tagged with this slug will remain — only the category tile is removed.');">
                            <?php echo portal_csrf_field(); ?>
                            <input type="hidden" name="_action" value="delete">
                            <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
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

<!-- ══════════════════════════════════════════════════════════════════════
     ADD / EDIT MODAL
════════════════════════════════════════════════════════════════════════ -->
<div id="catModal" style="
    display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
    z-index:1000;overflow-y:auto;padding:40px 20px;">
    <div style="
        background:#fff;border-radius:16px;max-width:640px;
        margin:0 auto;box-shadow:0 20px 60px rgba(0,0,0,.2);">

        <!-- Modal header -->
        <div style="
            background:linear-gradient(135deg,var(--navy),var(--navy-l));
            padding:22px 28px;border-radius:16px 16px 0 0;
            display:flex;align-items:center;justify-content:space-between;">
            <div>
                <h2 style="color:#fff;font-size:18px;font-weight:800;margin:0;">
                    <i class="fas fa-th-large" style="color:var(--orange);margin-right:8px;"></i>
                    <span id="modalTitle">New Category</span>
                </h2>
                <p style="color:rgba(255,255,255,.6);font-size:13px;margin:4px 0 0;">
                    Appears as a tile in the app's Special Editions section
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
            <input type="hidden" name="id" id="catId" value="0">

            <!-- Label + Slug -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Label <span class="req">*</span></label>
                    <input type="text" name="label" id="fLabel" class="form-control"
                           maxlength="100" placeholder="University" required
                           oninput="autoSlug()">
                    <div class="form-hint">Display name shown on the tile</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Slug <span class="req">*</span></label>
                    <input type="text" name="slug" id="fSlug" class="form-control"
                           maxlength="50" placeholder="university" required
                           pattern="[a-z0-9_-]+"
                           title="Lowercase letters, digits, hyphens or underscores">
                    <div class="form-hint">Must match the <code>category</code> column on editions</div>
                </div>
            </div>

            <!-- Description -->
            <div class="form-group" style="margin-bottom:18px;">
                <label class="form-label">Description</label>
                <input type="text" name="description" id="fDesc" class="form-control"
                       maxlength="255" placeholder="Campus news & academic editions">
                <div class="form-hint">Short subtitle shown below the tile label (optional)</div>
            </div>

            <!-- Icon + Colour -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Flutter Icon Name</label>
                    <input type="text" name="icon_name" id="fIcon" class="form-control"
                           maxlength="50" placeholder="school" list="iconList">
                    <datalist id="iconList">
                        <?php foreach ($iconSuggestions as $ico): ?>
                        <option value="<?php echo $ico; ?>">
                        <?php endforeach; ?>
                    </datalist>
                    <div class="form-hint">
                        <a href="https://fonts.google.com/icons" target="_blank" style="color:var(--orange);">Browse Material icons</a> (use snake_case key)
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tile Colour</label>
                    <div style="display:flex;gap:8px;align-items:center;">
                        <input type="color" name="color_hex" id="fColor"
                               value="#F05A1A"
                               style="width:44px;height:44px;border:1.5px solid #e0e0e0;
                                      border-radius:8px;cursor:pointer;padding:2px;">
                        <input type="text" id="fColorText" value="#F05A1A"
                               style="flex:1;padding:11px 14px;border:1.5px solid #e0e0e0;
                                      border-radius:8px;font-size:14px;font-family:monospace;"
                               maxlength="7"
                               oninput="document.getElementById('fColor').value=this.value">
                    </div>
                    <div class="form-hint">Hex — navy #1E2B42, orange #F05A1A, blue #3B82F6</div>
                </div>
            </div>

            <!-- Sort + Type + Country -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" name="sort_order" id="fSort" class="form-control"
                           value="0" min="0" max="255">
                    <div class="form-hint">Lower = appears first (0 = top)</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Edition Type</label>
                    <select name="edition_type" id="fType" class="form-control">
                        <option value="special">Special Edition</option>
                        <option value="rate_card">Rate Card</option>
                    </select>
                </div>
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
            </div>

            <!-- Active toggle -->
            <div class="form-group" style="display:flex;align-items:center;gap:10px;margin-bottom:24px;">
                <input type="checkbox" name="is_active" id="fActive" value="1" checked
                       style="width:18px;height:18px;cursor:pointer;">
                <label for="fActive" class="form-label" style="margin:0;cursor:pointer;">
                    Active — show this category tile in the app
                </label>
            </div>

            <!-- Buttons -->
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeModal()" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Category
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
// Sync colour picker ↔ text input
document.getElementById('fColor').addEventListener('input', function () {
    document.getElementById('fColorText').value = this.value;
});

// Auto-generate slug from label (only when slug is empty)
let _slugManual = false;
document.getElementById('fSlug').addEventListener('input', function () {
    _slugManual = this.value.length > 0;
});

function autoSlug() {
    if (_slugManual) return;
    const label = document.getElementById('fLabel').value;
    const slug  = label.toLowerCase()
        .replace(/[^a-z0-9\s_-]/g, '')
        .replace(/\s+/g, '_')
        .replace(/_+/g, '_')
        .slice(0, 50);
    document.getElementById('fSlug').value = slug;
}

function openModal(data) {
    _slugManual = false;
    const modal = document.getElementById('catModal');
    if (data) {
        document.getElementById('modalTitle').textContent = 'Edit Category';
        document.getElementById('catId').value    = data.id;
        document.getElementById('fLabel').value   = data.label;
        document.getElementById('fSlug').value    = data.slug;
        document.getElementById('fDesc').value    = data.description || '';
        document.getElementById('fIcon').value    = data.icon_name   || '';
        document.getElementById('fColor').value   = data.color_hex   || '#F05A1A';
        document.getElementById('fColorText').value = data.color_hex || '#F05A1A';
        document.getElementById('fSort').value    = data.sort_order  || 0;
        document.getElementById('fType').value    = data.edition_type || 'special';
        document.getElementById('fCountry').value = data.country     || '';
        document.getElementById('fActive').checked = data.is_active == 1;
        _slugManual = true; // don't overwrite existing slug on label edit
    } else {
        document.getElementById('modalTitle').textContent = 'New Category';
        document.getElementById('catId').value    = '0';
        document.getElementById('fLabel').value   = '';
        document.getElementById('fSlug').value    = '';
        document.getElementById('fDesc').value    = '';
        document.getElementById('fIcon').value    = '';
        document.getElementById('fColor').value   = '#F05A1A';
        document.getElementById('fColorText').value = '#F05A1A';
        document.getElementById('fSort').value    = '0';
        document.getElementById('fType').value    = 'special';
        document.getElementById('fCountry').value = '';
        document.getElementById('fActive').checked = true;
    }
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    document.getElementById('fLabel').focus();
}

function closeModal() {
    document.getElementById('catModal').style.display = 'none';
    document.body.style.overflow = '';
    if (history.replaceState) {
        history.replaceState(null, '', '<?php echo portal_url('edition-categories.php'); ?>');
    }
}

document.getElementById('catModal').addEventListener('click', function (e) {
    if (e.target === this) closeModal();
});

<?php if ($edit): ?>
openModal(<?php echo json_encode([
    'id'           => (int)$edit['id'],
    'slug'         => $edit['slug'],
    'label'        => $edit['label'],
    'description'  => $edit['description'] ?? '',
    'icon_name'    => $edit['icon_name'],
    'color_hex'    => $edit['color_hex'],
    'sort_order'   => (int)$edit['sort_order'],
    'edition_type' => $edit['edition_type'],
    'country'      => $edit['country'] ?? '',
    'is_active'    => (int)$edit['is_active'],
]); ?>);
<?php elseif ($openNew): ?>
openModal(null);
<?php endif; ?>
</script>
