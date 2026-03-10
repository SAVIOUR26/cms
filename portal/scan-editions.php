<?php
/**
 * KandaNews Africa — Scan & Import Editions
 *
 * Scans the output/ folder for edition directories not yet registered
 * in the database, and allows bulk-importing them.
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$page_title   = 'Scan Editions';
$page_section = 'content';

$db         = portal_db();
$outputPath = dirname(__DIR__) . '/output';
$cmsBaseUrl = rtrim(portal_env('CMS_URL', 'https://cms.kandanews.africa'), '/');

// ── Handle import action ──────────────────────────────────────────────────
$import_results = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['import'])) {
    if (!portal_verify_csrf()) {
        portal_flash('error', 'Security token mismatch.');
        header('Location: ' . portal_url('scan-editions.php'));
        exit;
    }

    $dirs   = (array) ($_POST['dirs'] ?? []);
    $ok = $fail = 0;

    foreach ($dirs as $dirName) {
        $dirName = basename($dirName); // safety
        $dirPath = $outputPath . '/' . $dirName;
        if (!is_dir($dirPath) || !file_exists($dirPath . '/index.html')) continue;

        // Determine metadata from form overrides or guess from folder name
        $title    = portal_sanitize($_POST['title_'   . $dirName] ?? '');
        $date     = portal_sanitize($_POST['date_'    . $dirName] ?? '');
        $country  = portal_sanitize($_POST['country_' . $dirName] ?? 'ug');
        $type     = portal_sanitize($_POST['type_'    . $dirName] ?? 'daily');
        $category = portal_sanitize($_POST['cat_'     . $dirName] ?? '');
        $isFree   = (int) ($_POST['free_' . $dirName] ?? 0);

        if ($title === '') $title = ucwords(str_replace(['-', '_'], ' ', $dirName));
        if ($date === '')  $date  = preg_match('/^\d{4}-\d{2}-\d{2}/', $dirName) ? substr($dirName, 0, 10) : date('Y-m-d');

        // Build slug
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $title . '-' . $date), '-'));

        // Find ZIP if exists
        $zips    = glob($dirPath . '/*.zip') ?: [];
        $zipFile = $zips ? basename($zips[0]) : null;

        // Find cover
        $coverFile = null;
        foreach (['cover.jpg', 'cover.png', 'cover.jpeg'] as $cf) {
            if (file_exists($dirPath . '/' . $cf)) { $coverFile = $cf; break; }
        }

        $htmlUrl  = $cmsBaseUrl . '/output/' . $dirName . '/index.html';
        $zipUrl   = $zipFile ? $cmsBaseUrl . '/output/' . $dirName . '/' . $zipFile : null;
        $coverUrl = $coverFile ? $cmsBaseUrl . '/output/' . $dirName . '/' . $coverFile : null;

        // Count pages in HTML
        $pageCount = 0;
        $html = file_get_contents($dirPath . '/index.html');
        if ($html) $pageCount = substr_count($html, 'class="swiper-slide"');

        try {
            $stmt = $db->prepare(
                "INSERT IGNORE INTO editions
                 (title, slug, country, edition_date, edition_type, category, cover_image,
                  html_url, zip_url, page_count, is_free, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', NOW())"
            );
            $stmt->execute([
                $title, $slug, $country, $date, $type,
                $category ?: null, $coverUrl,
                $htmlUrl, $zipUrl, $pageCount, $isFree,
            ]);
            if ($stmt->rowCount() > 0) {
                $import_results[] = ['ok' => true,  'dir' => $dirName, 'msg' => "Imported as draft: $title"];
                $ok++;
            } else {
                $import_results[] = ['ok' => false, 'dir' => $dirName, 'msg' => "Skipped — slug already exists ($slug)"];
            }
        } catch (PDOException $e) {
            $import_results[] = ['ok' => false, 'dir' => $dirName, 'msg' => 'DB error: ' . $e->getMessage()];
            $fail++;
        }
    }

    if ($ok > 0) portal_flash('success', "$ok edition(s) imported as drafts. Review and publish from All Editions.");
    if ($fail > 0) portal_flash('error', "$fail edition(s) failed to import.");
    header('Location: ' . portal_url('scan-editions.php'));
    exit;
}

// ── Scan output folder ────────────────────────────────────────────────────
$registered = [];
try {
    $rows = $db->query("SELECT html_url FROM editions")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($rows as $url) {
        // Extract dirname from URL like .../output/2025-11-03/index.html
        if (preg_match('#/output/([^/]+)/#', $url, $m)) {
            $registered[$m[1]] = true;
        }
    }
} catch (PDOException $e) {}

$unregistered = [];
$alreadyIn    = [];

if (is_dir($outputPath)) {
    foreach (scandir($outputPath) as $item) {
        if ($item[0] === '.') continue;
        $itemPath = $outputPath . '/' . $item;
        if (!is_dir($itemPath) || !file_exists($itemPath . '/index.html')) continue;

        if (isset($registered[$item])) {
            $alreadyIn[] = $item;
        } else {
            $zips    = glob($itemPath . '/*.zip') ?: [];
            $covers  = [];
            foreach (['cover.jpg','cover.png','cover.jpeg'] as $cf) {
                if (file_exists($itemPath . '/' . $cf)) { $covers[] = $cf; break; }
            }
            $guessDate = preg_match('/^(\d{4}-\d{2}-\d{2})/', $item, $dm) ? $dm[1] : date('Y-m-d');
            $unregistered[] = [
                'dir'        => $item,
                'zip'        => $zips ? basename($zips[0]) : null,
                'cover'      => $covers ? $covers[0] : null,
                'guess_date' => $guessDate,
                'guess_type' => (stripos($item, 'rate') !== false ? 'rate_card'
                              : (stripos($item, 'special') !== false ? 'special' : 'daily')),
                'mtime'      => filemtime($itemPath),
            ];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Header ─────────────────────────────── -->
<div class="section-header">
    <div>
        <h1><i class="fas fa-folder-open" style="color:var(--orange);margin-right:8px;"></i>Scan & Import Editions</h1>
        <p>Detect edition folders in <code>output/</code> not yet registered in the database.</p>
    </div>
    <div style="display:flex;gap:10px;">
        <a href="<?php echo portal_url('editions.php'); ?>" class="btn btn-sm btn-outline">
            <i class="fas fa-list"></i> All Editions
        </a>
    </div>
</div>

<!-- ── Stats row ───────────────────────────────── -->
<div class="stat-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:28px;">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(240,90,26,.12);color:var(--orange);">
            <i class="fas fa-folder"></i>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?php echo count($unregistered) + count($alreadyIn); ?></div>
            <div class="stat-label">Total output/ folders</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fef9c3;color:#92400e;">
            <i class="fas fa-exclamation-circle"></i>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?php echo count($unregistered); ?></div>
            <div class="stat-label">Not yet imported</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7;color:#15803d;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-body">
            <div class="stat-value"><?php echo count($alreadyIn); ?></div>
            <div class="stat-label">Already in database</div>
        </div>
    </div>
</div>

<!-- ── Unregistered editions ───────────────────── -->
<?php if (empty($unregistered)): ?>
<div class="card">
    <div style="text-align:center;padding:40px;">
        <i class="fas fa-check-circle" style="font-size:48px;color:#10b981;margin-bottom:16px;display:block;"></i>
        <h3 style="color:var(--navy);margin-bottom:8px;">All folders are registered</h3>
        <p style="color:#888;">Every edition in <code>output/</code> is already in the database.</p>
    </div>
</div>
<?php else: ?>

<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-download"></i> <?php echo count($unregistered); ?> Unregistered Edition<?php echo count($unregistered) !== 1 ? 's' : ''; ?></h2>
        <div style="display:flex;gap:8px;">
            <button type="button" onclick="selectAll(true)"
                    style="padding:6px 14px;border:1.5px solid var(--orange);border-radius:6px;background:#fff;color:var(--orange);font-size:13px;font-weight:600;cursor:pointer;">
                Select All
            </button>
            <button type="button" onclick="selectAll(false)"
                    style="padding:6px 14px;border:1.5px solid #d1d5db;border-radius:6px;background:#fff;color:#555;font-size:13px;font-weight:600;cursor:pointer;">
                Deselect All
            </button>
        </div>
    </div>
    <p style="font-size:13px;color:#888;margin-bottom:20px;">
        These folders exist in <code>output/</code> but are not yet in the database. Fill in the details and click <strong>Import Selected</strong>.
        Imported editions are saved as <strong>drafts</strong> — you can review and publish them from All Editions.
    </p>

    <form method="POST" action="">
        <?php echo portal_csrf_field(); ?>

        <?php foreach ($unregistered as $idx => $item): ?>
        <?php
        $d     = $item['dir'];
        $guess = ucwords(str_replace(['-', '_'], ' ', $d));
        $gType = $item['guess_type'];
        ?>
        <div class="import-card" id="card-<?php echo $idx; ?>" style="border:1.5px solid #e5e7eb;border-radius:12px;padding:20px;margin-bottom:16px;transition:border-color .15s;">
            <div style="display:flex;align-items:flex-start;gap:16px;">

                <!-- Checkbox -->
                <input type="checkbox" name="dirs[]" value="<?php echo htmlspecialchars($d); ?>"
                       id="chk-<?php echo $idx; ?>" class="edition-checkbox"
                       style="width:18px;height:18px;margin-top:4px;accent-color:var(--orange);cursor:pointer;"
                       onchange="highlightCard(<?php echo $idx; ?>, this.checked)">

                <!-- Folder info -->
                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;flex-wrap:wrap;">
                        <span style="font-size:14px;font-weight:700;color:var(--navy);">
                            <i class="fas fa-folder-open" style="color:var(--orange);margin-right:5px;"></i>
                            <?php echo htmlspecialchars($d); ?>
                        </span>
                        <span class="badge badge-draft">draft</span>
                        <?php if ($item['zip']): ?>
                        <span class="badge" style="background:#eff6ff;color:#1d4ed8;">
                            <i class="fas fa-file-archive"></i> ZIP found
                        </span>
                        <?php endif; ?>
                        <span style="font-size:12px;color:#aaa;margin-left:auto;">
                            <i class="fas fa-clock"></i> <?php echo date('M j, Y', $item['mtime']); ?>
                        </span>
                    </div>

                    <!-- Editable fields (2-col grid) -->
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="import-fields-grid">
                        <div>
                            <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:4px;">Title</label>
                            <input type="text" name="title_<?php echo htmlspecialchars($d); ?>"
                                   value="<?php echo htmlspecialchars($guess); ?>"
                                   style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;"
                                   placeholder="Edition title">
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:4px;">Date</label>
                            <input type="date" name="date_<?php echo htmlspecialchars($d); ?>"
                                   value="<?php echo htmlspecialchars($item['guess_date']); ?>"
                                   style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;">
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:4px;">Country</label>
                            <select name="country_<?php echo htmlspecialchars($d); ?>"
                                    style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;background:#fff;">
                                <option value="ug">🇺🇬 Uganda</option>
                                <option value="ke">🇰🇪 Kenya</option>
                                <option value="ng">🇳🇬 Nigeria</option>
                                <option value="za">🇿🇦 South Africa</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:4px;">Edition Type</label>
                            <select name="type_<?php echo htmlspecialchars($d); ?>"
                                    style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;background:#fff;"
                                    onchange="toggleCatField(this, '<?php echo $idx; ?>')">
                                <option value="daily"     <?php echo $gType==='daily'     ? 'selected' : ''; ?>>Daily Edition</option>
                                <option value="special"   <?php echo $gType==='special'   ? 'selected' : ''; ?>>Special Edition</option>
                                <option value="rate_card" <?php echo $gType==='rate_card' ? 'selected' : ''; ?>>Rate Card</option>
                            </select>
                        </div>
                        <div id="cat-row-<?php echo $idx; ?>" style="<?php echo $gType==='special' ? '' : 'display:none;'; ?>">
                            <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:4px;">Category</label>
                            <select name="cat_<?php echo htmlspecialchars($d); ?>"
                                    style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;background:#fff;">
                                <option value="">— none —</option>
                                <option value="university">University</option>
                                <option value="corporate">Corporate</option>
                                <option value="entrepreneurship">Entrepreneurship</option>
                                <option value="campaigns">Campaigns</option>
                                <option value="jobs_careers">Jobs & Careers</option>
                                <option value="podcasts">Podcasts</option>
                                <option value="episodes">Episodes</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:12px;font-weight:600;color:#555;display:block;margin-bottom:4px;">Access</label>
                            <select name="free_<?php echo htmlspecialchars($d); ?>"
                                    style="width:100%;padding:8px 10px;border:1.5px solid #e5e7eb;border-radius:6px;font-size:13px;background:#fff;">
                                <option value="1">Free (public)</option>
                                <option value="0">Subscribers only</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:8px;">
            <button type="submit" name="import" value="1"
                    style="padding:12px 28px;background:var(--orange);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:8px;">
                <i class="fas fa-download"></i> Import Selected Editions
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- ── Already registered ──────────────────────── -->
<?php if ($alreadyIn): ?>
<div class="card">
    <div class="card-header">
        <h2><i class="fas fa-check-circle" style="color:#10b981;"></i> Already in Database (<?php echo count($alreadyIn); ?>)</h2>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
        <?php foreach ($alreadyIn as $a): ?>
        <span style="padding:5px 12px;background:#f0fdf4;border:1px solid #a7f3d0;border-radius:20px;font-size:12px;color:#065f46;font-family:monospace;">
            <?php echo htmlspecialchars($a); ?>
        </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── How to add edition folders tip ──────────── -->
<div class="card" style="background:linear-gradient(135deg,#1e2b42,#2a3f5f);color:#fff;margin-top:8px;">
    <h3 style="margin-bottom:12px;"><i class="fas fa-lightbulb" style="color:var(--orange);"></i> How folder-based editions work</h3>
    <p style="font-size:13px;opacity:.85;line-height:1.7;">
        Any folder inside <code style="background:rgba(255,255,255,.15);padding:2px 6px;border-radius:4px;">output/</code>
        that contains an <code style="background:rgba(255,255,255,.15);padding:2px 6px;border-radius:4px;">index.html</code>
        is recognised as an edition.<br>
        You can <strong>drop edition folders directly</strong> into <code>output/</code> via FTP/SSH and they will
        appear here for import.<br>
        Use descriptive folder names like <code>2025-11-03</code>, <code>university-special-nov</code>, or <code>rate-card-2025</code>
        — the importer guesses the date and type automatically.
    </p>
</div>

<style>
.import-card { transition: border-color .15s, background .15s; }
.import-card.selected { border-color: var(--orange) !important; background: #fffaf7; }
@media (max-width: 640px) { .import-fields-grid { grid-template-columns: 1fr !important; } }
</style>

<script>
function selectAll(val) {
    document.querySelectorAll('.edition-checkbox').forEach((cb, i) => {
        cb.checked = val;
        highlightCard(i, val);
    });
}
function highlightCard(idx, checked) {
    const card = document.getElementById('card-' + idx);
    if (card) card.classList.toggle('selected', checked);
}
function toggleCatField(sel, idx) {
    const row = document.getElementById('cat-row-' + idx);
    if (row) row.style.display = sel.value === 'special' ? '' : 'none';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
