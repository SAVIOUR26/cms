<?php
/**
 * KandaNews Africa — Upload Edition
 *
 * Form to register / upload a new edition into the `editions` table
 * of the kandan_api database.
 *
 * File handling:
 *  - Cover image  -> /home/user/cms/uploads/covers/
 *  - HTML edition -> /home/user/cms/output/<slug>/  (extracted if ZIP or single HTML)
 *  - ZIP file     -> /home/user/cms/output/<slug>.zip
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$page_title = 'Upload Edition';
$db         = portal_db();
$countries  = portal_countries();
$errors     = [];

// Paths
$covers_dir = dirname(__DIR__) . '/uploads/covers';
$output_dir = dirname(__DIR__) . '/output';

// Ensure directories exist
if (!is_dir($covers_dir)) @mkdir($covers_dir, 0755, true);
if (!is_dir($output_dir)) @mkdir($output_dir, 0755, true);

// ── Handle form submission ───────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portal_verify_csrf()) {
        $errors[] = 'Invalid CSRF token. Please reload and try again.';
    } else {
        // Collect fields
        $title        = trim($_POST['title'] ?? '');
        $edition_date = trim($_POST['edition_date'] ?? '');
        $country      = strtolower(trim($_POST['country'] ?? 'ug'));
        $edition_type = trim($_POST['edition_type'] ?? 'daily');
        $is_free      = isset($_POST['is_free']) ? 1 : 0;
        $theme        = trim($_POST['theme'] ?? '');
        $description  = trim($_POST['description'] ?? '');
        $status       = trim($_POST['status'] ?? 'draft');
        $page_count   = max(0, (int)($_POST['page_count'] ?? 0));

        // Validation
        if ($title === '')        $errors[] = 'Title is required.';
        if ($edition_date === '') $errors[] = 'Edition date is required.';
        if (!in_array($edition_type, ['daily', 'special', 'rate_card'])) $errors[] = 'Invalid edition type.';
        if (!in_array($status, ['draft', 'published', 'archived'])) $errors[] = 'Invalid status.';
        if (!array_key_exists(strtoupper($country), $countries)) $errors[] = 'Invalid country.';

        // Generate slug
        $slug = portal_slugify($title . '-' . $edition_date);

        // Check unique slug
        $stmt = $db->prepare("SELECT id FROM editions WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            // Append random suffix
            $slug .= '-' . substr(bin2hex(random_bytes(3)), 0, 6);
        }

        // ── Cover image upload ────────────────
        $cover_path = null;
        if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $cover_file = $_FILES['cover_image'];
            $cover_ext  = strtolower(pathinfo($cover_file['name'], PATHINFO_EXTENSION));
            $allowed_img = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($cover_ext, $allowed_img)) {
                $errors[] = 'Cover image must be JPG, PNG, GIF, or WebP.';
            } elseif ($cover_file['size'] > 10 * 1024 * 1024) {
                $errors[] = 'Cover image must be under 10 MB.';
            } else {
                $cover_name = $slug . '-cover.' . $cover_ext;
                $cover_dest = $covers_dir . '/' . $cover_name;
                if (move_uploaded_file($cover_file['tmp_name'], $cover_dest)) {
                    $cover_path = 'uploads/covers/' . $cover_name;
                } else {
                    $errors[] = 'Failed to save cover image.';
                }
            }
        }

        // ── HTML file upload ──────────────────
        $html_url = null;
        if (!empty($_FILES['html_file']['name']) && $_FILES['html_file']['error'] === UPLOAD_ERR_OK) {
            $html_file = $_FILES['html_file'];
            $html_ext  = strtolower(pathinfo($html_file['name'], PATHINFO_EXTENSION));

            if (!in_array($html_ext, ['html', 'htm', 'zip'])) {
                $errors[] = 'HTML file must be .html, .htm, or .zip';
            } elseif ($html_file['size'] > 50 * 1024 * 1024) {
                $errors[] = 'HTML file must be under 50 MB.';
            } else {
                $edition_dir = $output_dir . '/' . $slug;
                if (!is_dir($edition_dir)) @mkdir($edition_dir, 0755, true);

                if ($html_ext === 'zip') {
                    // Extract ZIP to edition directory
                    $zip = new ZipArchive();
                    if ($zip->open($html_file['tmp_name']) === true) {
                        $zip->extractTo($edition_dir);
                        $zip->close();
                        // Check for index.html
                        if (file_exists($edition_dir . '/index.html')) {
                            $html_url = 'output/' . $slug . '/index.html';
                        } else {
                            // Look for any HTML file
                            $html_files = glob($edition_dir . '/*.html');
                            if (!empty($html_files)) {
                                $first = basename($html_files[0]);
                                $html_url = 'output/' . $slug . '/' . $first;
                            } else {
                                $html_url = 'output/' . $slug . '/';
                            }
                        }
                    } else {
                        $errors[] = 'Failed to extract ZIP file.';
                    }
                } else {
                    // Single HTML file
                    $dest = $edition_dir . '/index.html';
                    if (move_uploaded_file($html_file['tmp_name'], $dest)) {
                        $html_url = 'output/' . $slug . '/index.html';
                    } else {
                        $errors[] = 'Failed to save HTML file.';
                    }
                }
            }
        }

        // ── ZIP file upload (optional separate) ─
        $zip_url = null;
        if (!empty($_FILES['zip_file']['name']) && $_FILES['zip_file']['error'] === UPLOAD_ERR_OK) {
            $zip_file = $_FILES['zip_file'];
            $zip_ext  = strtolower(pathinfo($zip_file['name'], PATHINFO_EXTENSION));

            if ($zip_ext !== 'zip') {
                $errors[] = 'ZIP file must be a .zip archive.';
            } elseif ($zip_file['size'] > 50 * 1024 * 1024) {
                $errors[] = 'ZIP file must be under 50 MB.';
            } else {
                $zip_dest = $output_dir . '/' . $slug . '.zip';
                if (move_uploaded_file($zip_file['tmp_name'], $zip_dest)) {
                    $zip_url = 'output/' . $slug . '.zip';
                } else {
                    $errors[] = 'Failed to save ZIP file.';
                }
            }
        }

        // ── Insert into database ─────────────
        if (empty($errors)) {
            try {
                $stmt = $db->prepare(
                    "INSERT INTO editions
                        (title, slug, country, edition_date, edition_type, cover_image, html_url, zip_url,
                         page_count, is_free, theme, description, status, created_at)
                     VALUES
                        (:title, :slug, :country, :edition_date, :edition_type, :cover_image, :html_url, :zip_url,
                         :page_count, :is_free, :theme, :description, :status, NOW())"
                );
                $stmt->execute([
                    ':title'        => $title,
                    ':slug'         => $slug,
                    ':country'      => $country,
                    ':edition_date' => $edition_date,
                    ':edition_type' => $edition_type,
                    ':cover_image'  => $cover_path,
                    ':html_url'     => $html_url,
                    ':zip_url'      => $zip_url,
                    ':page_count'   => $page_count,
                    ':is_free'      => $is_free,
                    ':theme'        => $theme ?: null,
                    ':description'  => $description ?: null,
                    ':status'       => $status,
                ]);

                $new_id = $db->lastInsertId();
                portal_flash('success', 'Edition "' . $title . '" uploaded successfully!');
                header('Location: ' . portal_url('editions.php'));
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page Header ────────────────────────────── -->
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
    <div>
        <h1 style="font-size:24px; font-weight:700; color:#1e2b42; margin-bottom:4px;">
            <i class="fas fa-cloud-upload-alt" style="color:#f05a1a;"></i> Upload New Edition
        </h1>
        <p style="color:#888; font-size:14px;">Register a new edition for the KandaNews app.</p>
    </div>
    <a href="<?php echo portal_url('editions.php'); ?>" class="btn btn-outline">
        <i class="fas fa-arrow-left"></i> Back to Editions
    </a>
</div>

<!-- ── Errors ─────────────────────────────────── -->
<?php if (!empty($errors)): ?>
<div class="flash-message flash-error" style="flex-direction:column; align-items:flex-start;">
    <strong style="display:flex; align-items:center; gap:6px; margin-bottom:6px;">
        <i class="fas fa-exclamation-circle"></i> Please fix the following:
    </strong>
    <ul style="margin:0; padding-left:20px; font-size:14px;">
        <?php foreach ($errors as $err): ?>
        <li><?php echo htmlspecialchars($err); ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- ── Upload Form ────────────────────────────── -->
<form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
    <?php echo portal_csrf_field(); ?>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
        <!-- Left column -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> Edition Details</h2>
            </div>

            <div class="form-group">
                <label for="title">Title *</label>
                <input type="text" id="title" name="title" class="form-control" required
                       placeholder="e.g., KandaNews Daily Edition"
                       value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="edition_date">Edition Date *</label>
                <input type="date" id="edition_date" name="edition_date" class="form-control" required
                       value="<?php echo htmlspecialchars($_POST['edition_date'] ?? date('Y-m-d')); ?>">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label for="country">Country *</label>
                    <select id="country" name="country" class="form-control" required>
                        <?php foreach ($countries as $code => $name): ?>
                        <option value="<?php echo $code; ?>"
                            <?php echo (strtoupper($_POST['country'] ?? 'UG') === $code) ? 'selected' : ''; ?>>
                            <?php echo $name; ?> (<?php echo $code; ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="edition_type">Edition Type *</label>
                    <select id="edition_type" name="edition_type" class="form-control" required>
                        <option value="daily"     <?php echo ($_POST['edition_type'] ?? 'daily') === 'daily' ? 'selected' : ''; ?>>Daily</option>
                        <option value="special"   <?php echo ($_POST['edition_type'] ?? '') === 'special' ? 'selected' : ''; ?>>Special</option>
                        <option value="rate_card"  <?php echo ($_POST['edition_type'] ?? '') === 'rate_card' ? 'selected' : ''; ?>>Rate Card</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div class="form-group">
                    <label for="page_count">Page Count</label>
                    <input type="number" id="page_count" name="page_count" class="form-control" min="0" max="200"
                           placeholder="0" value="<?php echo htmlspecialchars($_POST['page_count'] ?? '0'); ?>">
                </div>
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="draft"     <?php echo ($_POST['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="published"  <?php echo ($_POST['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="archived"   <?php echo ($_POST['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="is_free" name="is_free" value="1"
                        <?php echo !empty($_POST['is_free']) ? 'checked' : ''; ?>>
                    <label for="is_free">Free Edition (no subscription required)</label>
                </div>
            </div>

            <div class="form-group">
                <label for="theme">Theme / Tag</label>
                <input type="text" id="theme" name="theme" class="form-control"
                       placeholder="e.g., Money Moves Monday, Tech Tuesday"
                       value="<?php echo htmlspecialchars($_POST['theme'] ?? ''); ?>">
                <div class="form-hint">Optional tag or theme for the edition.</div>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" class="form-control" rows="3"
                          placeholder="Brief description of this edition..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
            </div>
        </div>

        <!-- Right column — file uploads -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-image"></i> Cover Image</h2>
                </div>
                <div class="form-group">
                    <label for="cover_image">Upload Cover Image</label>
                    <input type="file" id="cover_image" name="cover_image" class="form-control"
                           accept=".jpg,.jpeg,.png,.gif,.webp">
                    <div class="form-hint">JPG, PNG, GIF, or WebP. Max 10 MB. Recommended: 400x520px.</div>
                </div>
                <div id="coverPreview" style="display:none; margin-top:12px; text-align:center;">
                    <img id="coverImg" src="" alt="Cover preview"
                         style="max-width:200px; max-height:260px; border-radius:10px; border:2px solid #eee; box-shadow:0 4px 16px rgba(0,0,0,.1);">
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-file-code"></i> Edition Files</h2>
                </div>
                <div class="form-group">
                    <label for="html_file">HTML Edition File *</label>
                    <input type="file" id="html_file" name="html_file" class="form-control"
                           accept=".html,.htm,.zip">
                    <div class="form-hint">Single .html file or a .zip containing the edition. Max 50 MB.</div>
                </div>

                <div class="form-group">
                    <label for="zip_file">Downloadable ZIP (optional)</label>
                    <input type="file" id="zip_file" name="zip_file" class="form-control"
                           accept=".zip">
                    <div class="form-hint">Optional .zip for offline download in the app. Max 50 MB.</div>
                </div>
            </div>

            <!-- Submit -->
            <div class="card" style="background:linear-gradient(135deg, #1e2b42, #2a3f5f); color:#fff;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                    <i class="fas fa-rocket" style="font-size:24px; color:#f05a1a;"></i>
                    <div>
                        <strong style="font-size:16px;">Ready to upload?</strong>
                        <p style="font-size:13px; opacity:.7; margin-top:2px;">Review your details before submitting.</p>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width:100%; justify-content:center;" id="submitBtn">
                    <i class="fas fa-cloud-upload-alt"></i> Upload Edition
                </button>
            </div>
        </div>
    </div>
</form>

<script>
/* ── Cover image preview ──────────────────── */
document.getElementById('cover_image').addEventListener('change', function(e) {
    var file = e.target.files[0];
    if (!file) { document.getElementById('coverPreview').style.display = 'none'; return; }
    var reader = new FileReader();
    reader.onload = function(ev) {
        document.getElementById('coverImg').src = ev.target.result;
        document.getElementById('coverPreview').style.display = 'block';
    };
    reader.readAsDataURL(file);
});

/* ── File size validation ─────────────────── */
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    var maxSize = 50 * 1024 * 1024; // 50 MB
    var coverMax = 10 * 1024 * 1024; // 10 MB
    var inputs = [
        { el: document.getElementById('html_file'), max: maxSize, label: 'HTML file' },
        { el: document.getElementById('zip_file'),  max: maxSize, label: 'ZIP file' },
        { el: document.getElementById('cover_image'), max: coverMax, label: 'Cover image' },
    ];
    for (var i = 0; i < inputs.length; i++) {
        var file = inputs[i].el.files[0];
        if (file && file.size > inputs[i].max) {
            e.preventDefault();
            alert(inputs[i].label + ' exceeds the maximum file size (' + Math.round(inputs[i].max / 1048576) + ' MB).');
            return;
        }
    }

    // Disable button to prevent double submit
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
});
</script>

<style>
/* Responsive override for upload form columns */
@media (max-width: 900px) {
    #uploadForm > div { grid-template-columns: 1fr !important; }
}
</style>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
