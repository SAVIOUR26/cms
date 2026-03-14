<?php
/**
 * KandaNews Africa — SDUI Card Builder
 *
 * Visual card builder for Special Editions.
 * Lets the portal operator configure every visual property of the
 * special edition card that appears in the Flutter app — without
 * touching any app code or releasing an update.
 *
 * GET  ?id={edition_id}  — Load builder for that edition
 * POST ?id={edition_id}  — Save card_config JSON to DB
 */

require_once __DIR__ . '/includes/auth.php';
portal_require_login();

$db = portal_db();

// ── Load edition ──────────────────────────────────────────────────
$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . portal_url('special-editions.php'));
    exit;
}

$stmt = $db->prepare("
    SELECT id, title, slug, country, edition_date, category, status,
           cover_image, is_free, html_url, edition_type, card_config
    FROM editions
    WHERE id = ? AND edition_type IN ('special','rate_card')
");
$stmt->execute([$id]);
$edition = $stmt->fetch();

if (!$edition) {
    header('Location: ' . portal_url('special-editions.php'));
    exit;
}

// ── Preset library (mirrors docs/sdui-spec.md) ───────────────────
$presets = [
    'university' => [
        'label' => 'University', 'icon_fa' => 'fas fa-graduation-cap',
        'config' => [
            'version' => 1, 'preset' => 'university',
            'card' => ['layout' => 'full_bleed', 'background' => ['type' => 'gradient', 'colors' => ['#1e2b42','#3B82F6'], 'angle' => 135], 'cover_treatment' => 'overlay'],
            'badge' => ['text' => 'UNIVERSITY', 'bg_color' => '#3B82F6', 'text_color' => '#FFFFFF', 'icon' => 'school'],
            'typography' => ['title_color' => '#FFFFFF', 'subtitle_color' => 'rgba(255,255,255,0.80)', 'accent_color' => '#FCD34D'],
            'cta' => ['label' => 'Read Edition', 'bg_color' => '#FCD34D', 'text_color' => '#1e2b42'],
        ],
    ],
    'corporate' => [
        'label' => 'Corporate', 'icon_fa' => 'fas fa-briefcase',
        'config' => [
            'version' => 1, 'preset' => 'corporate',
            'card' => ['layout' => 'full_bleed', 'background' => ['type' => 'gradient', 'colors' => ['#1E2B42','#374151'], 'angle' => 160], 'cover_treatment' => 'overlay'],
            'badge' => ['text' => 'CORPORATE', 'bg_color' => '#1E2B42', 'text_color' => '#F59E0B', 'icon' => 'business'],
            'typography' => ['title_color' => '#FFFFFF', 'subtitle_color' => 'rgba(255,255,255,0.75)', 'accent_color' => '#F59E0B'],
            'cta' => ['label' => 'Read Edition', 'bg_color' => '#F59E0B', 'text_color' => '#1e2b42'],
        ],
    ],
    'entrepreneurship' => [
        'label' => 'Entrepreneurship', 'icon_fa' => 'fas fa-rocket',
        'config' => [
            'version' => 1, 'preset' => 'entrepreneurship',
            'card' => ['layout' => 'full_bleed', 'background' => ['type' => 'gradient', 'colors' => ['#c2410c','#F05A1A'], 'angle' => 135], 'cover_treatment' => 'overlay'],
            'badge' => ['text' => 'STARTUP', 'bg_color' => '#F05A1A', 'text_color' => '#FFFFFF', 'icon' => 'rocket_launch'],
            'typography' => ['title_color' => '#FFFFFF', 'subtitle_color' => 'rgba(255,255,255,0.80)', 'accent_color' => '#FCD34D'],
            'cta' => ['label' => 'Read Edition', 'bg_color' => '#FCD34D', 'text_color' => '#1e2b42'],
        ],
    ],
    'campaigns' => [
        'label' => 'Campaigns', 'icon_fa' => 'fas fa-bullhorn',
        'config' => [
            'version' => 1, 'preset' => 'campaigns',
            'card' => ['layout' => 'full_bleed', 'background' => ['type' => 'gradient', 'colors' => ['#7c3aed','#EF4444'], 'angle' => 145], 'cover_treatment' => 'overlay'],
            'badge' => ['text' => 'CAMPAIGN', 'bg_color' => '#EF4444', 'text_color' => '#FFFFFF', 'icon' => 'campaign'],
            'typography' => ['title_color' => '#FFFFFF', 'subtitle_color' => 'rgba(255,255,255,0.80)', 'accent_color' => '#FBBF24'],
            'cta' => ['label' => 'Read Now', 'bg_color' => '#FBBF24', 'text_color' => '#1e2b42'],
        ],
    ],
    'jobs_careers' => [
        'label' => 'Jobs & Careers', 'icon_fa' => 'fas fa-user-tie',
        'config' => [
            'version' => 1, 'preset' => 'jobs_careers',
            'card' => ['layout' => 'full_bleed', 'background' => ['type' => 'gradient', 'colors' => ['#065f46','#10B981'], 'angle' => 135], 'cover_treatment' => 'overlay'],
            'badge' => ['text' => 'CAREERS', 'bg_color' => '#10B981', 'text_color' => '#FFFFFF', 'icon' => 'work'],
            'typography' => ['title_color' => '#FFFFFF', 'subtitle_color' => 'rgba(255,255,255,0.80)', 'accent_color' => '#FFFFFF'],
            'cta' => ['label' => 'See Opportunities', 'bg_color' => '#FFFFFF', 'text_color' => '#065f46'],
        ],
    ],
    'podcasts' => [
        'label' => 'Podcasts', 'icon_fa' => 'fas fa-podcast',
        'config' => [
            'version' => 1, 'preset' => 'podcasts',
            'card' => ['layout' => 'full_bleed', 'background' => ['type' => 'gradient', 'colors' => ['#4c1d95','#8B5CF6'], 'angle' => 150], 'cover_treatment' => 'overlay'],
            'badge' => ['text' => 'PODCAST', 'bg_color' => '#8B5CF6', 'text_color' => '#FFFFFF', 'icon' => 'podcasts'],
            'typography' => ['title_color' => '#FFFFFF', 'subtitle_color' => 'rgba(255,255,255,0.80)', 'accent_color' => '#FCD34D'],
            'cta' => ['label' => 'Listen Now', 'bg_color' => '#FCD34D', 'text_color' => '#1e2b42'],
        ],
    ],
    'culture' => [
        'label' => 'Culture', 'icon_fa' => 'fas fa-music',
        'config' => [
            'version' => 1, 'preset' => 'culture',
            'card' => ['layout' => 'full_bleed', 'background' => ['type' => 'gradient', 'colors' => ['#78350f','#B45309'], 'angle' => 135], 'cover_treatment' => 'overlay'],
            'badge' => ['text' => 'CULTURE', 'bg_color' => '#8B4513', 'text_color' => '#FCD34D', 'icon' => 'music_note'],
            'typography' => ['title_color' => '#FCD34D', 'subtitle_color' => 'rgba(255,255,255,0.80)', 'accent_color' => '#FCD34D'],
            'cta' => ['label' => 'Explore', 'bg_color' => '#FCD34D', 'text_color' => '#78350f'],
        ],
    ],
    'health' => [
        'label' => 'Health', 'icon_fa' => 'fas fa-heart-pulse',
        'config' => [
            'version' => 1, 'preset' => 'health',
            'card' => ['layout' => 'full_bleed', 'background' => ['type' => 'gradient', 'colors' => ['#065f46','#059669'], 'angle' => 135], 'cover_treatment' => 'overlay'],
            'badge' => ['text' => 'HEALTH', 'bg_color' => '#059669', 'text_color' => '#FFFFFF', 'icon' => 'favorite'],
            'typography' => ['title_color' => '#FFFFFF', 'subtitle_color' => 'rgba(255,255,255,0.80)', 'accent_color' => '#FFFFFF'],
            'cta' => ['label' => 'Read Edition', 'bg_color' => '#FFFFFF', 'text_color' => '#065f46'],
        ],
    ],
    'elections' => [
        'label' => 'Elections', 'icon_fa' => 'fas fa-landmark',
        'config' => [
            'version' => 1, 'preset' => 'elections',
            'card' => ['layout' => 'full_bleed', 'background' => ['type' => 'gradient', 'colors' => ['#1e2b42','#DC2626'], 'angle' => 135], 'cover_treatment' => 'overlay'],
            'badge' => ['text' => 'ELECTIONS', 'bg_color' => '#DC2626', 'text_color' => '#FFFFFF', 'icon' => 'how_to_vote'],
            'typography' => ['title_color' => '#FFFFFF', 'subtitle_color' => 'rgba(255,255,255,0.80)', 'accent_color' => '#FBBF24'],
            'cta' => ['label' => 'Read Coverage', 'bg_color' => '#FBBF24', 'text_color' => '#1e2b42'],
        ],
    ],
];

// ── Handle Save ───────────────────────────────────────────────────
$saved   = false;
$saveErr = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!portal_verify_csrf()) {
        $saveErr = 'Invalid CSRF token.';
    } else {
        $raw = $_POST['card_config_json'] ?? '';
        $decoded = json_decode($raw, true);

        if (!$decoded || !isset($decoded['version'])) {
            $saveErr = 'Invalid card config JSON. Please try again.';
        } else {
            // Ensure version is set
            $decoded['version'] = 1;
            $json = json_encode($decoded, JSON_UNESCAPED_UNICODE);
            try {
                $db->prepare("UPDATE editions SET card_config = ? WHERE id = ? AND edition_type IN ('special','rate_card')")
                   ->execute([$json, $id]);
                $edition['card_config'] = $json;
                $saved = true;
            } catch (PDOException $e) {
                $saveErr = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// ── Parse current config ──────────────────────────────────────────
$currentConfig = null;
if (!empty($edition['card_config'])) {
    $currentConfig = json_decode($edition['card_config'], true);
}

// Default to entrepreneurship preset if nothing saved yet
if (!$currentConfig) {
    // Try to match existing category to a preset
    $matchPreset = $edition['category'] ?? 'entrepreneurship';
    $currentConfig = $presets[$matchPreset]['config'] ?? $presets['entrepreneurship']['config'];
}

$currentConfigJson = json_encode($currentConfig, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
$csrf = portal_csrf_token();
$page_title   = 'Design Card — ' . htmlspecialchars($edition['title']);
$page_section = 'content';

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Page-level styles ──────────────────────────────────────── -->
<style>
.sdui-layout {
    display: grid;
    grid-template-columns: 380px 1fr 320px;
    gap: 24px;
    align-items: start;
}
@media (max-width: 1100px) {
    .sdui-layout { grid-template-columns: 1fr; }
}

/* ── Controls panel ── */
.sdui-panel {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}
.sdui-panel-header {
    background: var(--navy);
    color: #fff;
    padding: 14px 18px;
    font-size: 13px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sdui-panel-body { padding: 18px; }

.field-group { margin-bottom: 18px; }
.field-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6b7280;
    margin-bottom: 6px;
}
.field-input {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 13px;
    color: var(--navy);
    background: #fafafa;
    transition: border-color .15s;
}
.field-input:focus { outline: none; border-color: var(--orange); background: #fff; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.color-row { display: flex; align-items: center; gap: 8px; }
.color-swatch {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid #e5e7eb;
    cursor: pointer;
    flex-shrink: 0;
}

/* ── Preset grid ── */
.preset-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
    margin-bottom: 4px;
}
.preset-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    padding: 10px 6px;
    border: 2px solid #e5e7eb;
    border-radius: 10px;
    background: #fafafa;
    cursor: pointer;
    font-size: 10px;
    font-weight: 700;
    color: #374151;
    transition: all .15s;
    text-align: center;
}
.preset-btn:hover { border-color: var(--orange); background: #fff7ed; }
.preset-btn.active { border-color: var(--orange); background: #fff7ed; color: var(--orange); }
.preset-btn i { font-size: 16px; margin-bottom: 2px; }

/* ── Layout options ── */
.layout-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}
.layout-opt {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 8px 4px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    background: #fafafa;
    cursor: pointer;
    font-size: 10px;
    font-weight: 600;
    color: #6b7280;
    transition: all .15s;
    text-align: center;
}
.layout-opt:hover { border-color: var(--orange); }
.layout-opt.active { border-color: var(--orange); background: #fff7ed; color: var(--orange); }
.layout-icon {
    width: 44px;
    height: 28px;
    border-radius: 4px;
    background: #e5e7eb;
    position: relative;
    overflow: hidden;
}

/* ── Phone preview ── */
.phone-wrap {
    display: flex;
    justify-content: center;
    padding: 20px 0;
    position: sticky;
    top: 80px;
}
.phone-frame {
    width: 280px;
    height: 560px;
    background: #0f172a;
    border-radius: 36px;
    padding: 12px 10px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.4), inset 0 0 0 1px rgba(255,255,255,0.08);
    position: relative;
}
.phone-screen {
    width: 100%;
    height: 100%;
    background: #1a1a2e;
    border-radius: 26px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.phone-status {
    background: #0f172a;
    padding: 8px 16px 4px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 9px;
    color: #fff;
    font-weight: 700;
    flex-shrink: 0;
}
.phone-content {
    flex: 1;
    padding: 8px;
    overflow-y: auto;
    background: #f1f5f9;
}
.phone-section-label {
    font-size: 10px;
    font-weight: 800;
    color: #1e2b42;
    padding: 6px 4px 4px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

/* ── Card preview (simulates Flutter SpecialEditionCard) ── */
.card-preview {
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.18);
    height: 160px;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    cursor: pointer;
    margin-bottom: 8px;
    transition: transform .15s;
}
.card-preview:hover { transform: scale(1.01); }
.card-preview-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
}
.card-preview-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.70) 0%, rgba(0,0,0,0.05) 60%);
}
.card-preview-content {
    position: relative;
    z-index: 2;
    padding: 10px 12px 12px;
}
.card-preview-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 6px;
}
.card-preview-title {
    font-size: 13px;
    font-weight: 800;
    line-height: 1.3;
    margin-bottom: 3px;
}
.card-preview-sub {
    font-size: 9px;
    opacity: 0.75;
    margin-bottom: 8px;
}
.card-preview-cta {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* ── JSON panel ── */
.json-output {
    font-family: 'Courier New', monospace;
    font-size: 11px;
    line-height: 1.6;
    background: #0f172a;
    color: #a5f3fc;
    padding: 16px;
    border-radius: 8px;
    overflow-x: auto;
    max-height: 360px;
    overflow-y: auto;
    white-space: pre;
}

/* ── Save bar ── */
.save-bar {
    position: sticky;
    bottom: 0;
    background: #fff;
    border-top: 1px solid #e5e7eb;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin: 0 -18px -18px;
}
</style>

<!-- ── Page Header ────────────────────────────────────────────── -->
<div class="section-header" style="margin-bottom:24px;">
    <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;">
            <a href="<?php echo portal_url('special-editions.php'); ?>" style="color:var(--orange);font-size:13px;text-decoration:none;">
                <i class="fas fa-arrow-left"></i> Special Editions
            </a>
        </div>
        <h1><i class="fas fa-palette" style="color:var(--orange);margin-right:8px;"></i>Design Card</h1>
        <p style="margin-top:4px;font-size:13px;color:#6b7280;">
            <?php echo htmlspecialchars($edition['title']); ?>
            &nbsp;·&nbsp;
            <span class="badge badge-<?php echo $edition['status']; ?>"><?php echo $edition['status']; ?></span>
            &nbsp;·&nbsp;
            <strong style="color:var(--navy);"><?php echo strtoupper($edition['country']); ?></strong>
        </p>
    </div>
</div>

<?php if ($saved): ?>
<div class="alert alert-success" style="margin-bottom:20px;">
    <i class="fas fa-check-circle"></i>
    Card config saved. Changes are live in the API immediately.
</div>
<?php elseif ($saveErr): ?>
<div class="alert alert-error" style="margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($saveErr); ?>
</div>
<?php endif; ?>

<!-- ── 3-column layout ───────────────────────────────────────── -->
<div class="sdui-layout">

    <!-- ── Col 1: Controls ─────────────────────────────────── -->
    <div>
        <form method="POST" id="sdui-form">
            <input type="hidden" name="_csrf" value="<?php echo $csrf; ?>">
            <input type="hidden" name="card_config_json" id="card-config-input"
                   value="<?php echo htmlspecialchars(json_encode($currentConfig)); ?>">

            <!-- Presets -->
            <div class="sdui-panel" style="margin-bottom:16px;">
                <div class="sdui-panel-header">
                    <i class="fas fa-swatchbook"></i> Theme Presets
                </div>
                <div class="sdui-panel-body">
                    <p style="font-size:12px;color:#6b7280;margin-bottom:12px;">Start with a preset — then customise any detail below.</p>
                    <div class="preset-grid">
                        <?php foreach ($presets as $key => $preset): ?>
                        <button type="button" class="preset-btn <?php echo ($currentConfig['preset'] ?? '') === $key ? 'active' : ''; ?>"
                                data-preset="<?php echo htmlspecialchars(json_encode($preset['config'])); ?>"
                                onclick="applyPreset(this)">
                            <i class="<?php echo $preset['icon_fa']; ?>"></i>
                            <?php echo $preset['label']; ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Layout -->
            <div class="sdui-panel" style="margin-bottom:16px;">
                <div class="sdui-panel-header">
                    <i class="fas fa-layout"></i> Card Layout
                </div>
                <div class="sdui-panel-body">
                    <div class="layout-grid" id="layout-grid">
                        <?php
                        $layouts = [
                            'full_bleed' => ['label' => 'Full Bleed', 'desc' => 'Cover fills card'],
                            'split'      => ['label' => 'Split',      'desc' => 'Image + text side'],
                            'compact'    => ['label' => 'Compact',    'desc' => 'List style'],
                            'hero'       => ['label' => 'Hero',       'desc' => 'Large immersive'],
                        ];
                        $activeLayout = $currentConfig['card']['layout'] ?? 'full_bleed';
                        foreach ($layouts as $lk => $lv):
                        ?>
                        <div class="layout-opt <?php echo $activeLayout === $lk ? 'active' : ''; ?>"
                             data-layout="<?php echo $lk; ?>"
                             onclick="setLayout(this)">
                            <div class="layout-icon"></div>
                            <span><?php echo $lv['label']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Background -->
            <div class="sdui-panel" style="margin-bottom:16px;">
                <div class="sdui-panel-header">
                    <i class="fas fa-fill-drip"></i> Background
                </div>
                <div class="sdui-panel-body">
                    <div class="field-group">
                        <label class="field-label">Type</label>
                        <select class="field-input" id="bg-type" onchange="syncConfig()">
                            <?php
                            $bgType = $currentConfig['card']['background']['type'] ?? 'gradient';
                            foreach (['gradient','solid','image','image_overlay'] as $bt):
                            ?>
                            <option value="<?php echo $bt; ?>" <?php echo $bgType === $bt ? 'selected' : ''; ?>>
                                <?php echo ucfirst(str_replace('_',' ',$bt)); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Color From</label>
                        <div class="color-row">
                            <input type="color" class="color-swatch" id="bg-color-1"
                                   value="<?php echo $currentConfig['card']['background']['colors'][0] ?? '#1e2b42'; ?>"
                                   oninput="syncConfig()">
                            <input type="text" class="field-input" id="bg-color-1-hex"
                                   value="<?php echo $currentConfig['card']['background']['colors'][0] ?? '#1e2b42'; ?>"
                                   oninput="syncColorFromText('bg-color-1','bg-color-1-hex')" maxlength="7">
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Color To</label>
                        <div class="color-row">
                            <input type="color" class="color-swatch" id="bg-color-2"
                                   value="<?php echo $currentConfig['card']['background']['colors'][1] ?? '#3B82F6'; ?>"
                                   oninput="syncConfig()">
                            <input type="text" class="field-input" id="bg-color-2-hex"
                                   value="<?php echo $currentConfig['card']['background']['colors'][1] ?? '#3B82F6'; ?>"
                                   oninput="syncColorFromText('bg-color-2','bg-color-2-hex')" maxlength="7">
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Gradient Angle: <span id="angle-val"><?php echo $currentConfig['card']['background']['angle'] ?? 135; ?></span>°</label>
                        <input type="range" min="0" max="360" step="5"
                               id="bg-angle"
                               value="<?php echo $currentConfig['card']['background']['angle'] ?? 135; ?>"
                               oninput="document.getElementById('angle-val').textContent=this.value; syncConfig();"
                               style="width:100%;accent-color:var(--orange);">
                    </div>

                    <div class="field-group">
                        <label class="field-label">Cover Treatment</label>
                        <select class="field-input" id="cover-treatment" onchange="syncConfig()">
                            <?php
                            $ct = $currentConfig['card']['cover_treatment'] ?? 'overlay';
                            foreach (['none','overlay','blur_bottom'] as $cto):
                            ?>
                            <option value="<?php echo $cto; ?>" <?php echo $ct === $cto ? 'selected' : ''; ?>>
                                <?php echo ucfirst(str_replace('_',' ',$cto)); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Badge -->
            <div class="sdui-panel" style="margin-bottom:16px;">
                <div class="sdui-panel-header">
                    <i class="fas fa-tag"></i> Badge / Label
                </div>
                <div class="sdui-panel-body">
                    <div class="field-group">
                        <label class="field-label">Badge Text (max 14 chars)</label>
                        <input type="text" class="field-input" id="badge-text" maxlength="14"
                               value="<?php echo htmlspecialchars($currentConfig['badge']['text'] ?? 'SPECIAL'); ?>"
                               oninput="syncConfig()">
                    </div>
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Background</label>
                            <div class="color-row">
                                <input type="color" class="color-swatch" id="badge-bg"
                                       value="<?php echo $currentConfig['badge']['bg_color'] ?? '#3B82F6'; ?>"
                                       oninput="syncConfig()">
                                <input type="text" class="field-input" id="badge-bg-hex"
                                       value="<?php echo $currentConfig['badge']['bg_color'] ?? '#3B82F6'; ?>"
                                       oninput="syncColorFromText('badge-bg','badge-bg-hex')" maxlength="7">
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Text Color</label>
                            <div class="color-row">
                                <input type="color" class="color-swatch" id="badge-text-color"
                                       value="<?php echo $currentConfig['badge']['text_color'] ?? '#FFFFFF'; ?>"
                                       oninput="syncConfig()">
                                <input type="text" class="field-input" id="badge-tc-hex"
                                       value="<?php echo $currentConfig['badge']['text_color'] ?? '#FFFFFF'; ?>"
                                       oninput="syncColorFromText('badge-text-color','badge-tc-hex')" maxlength="7">
                            </div>
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Flutter Icon Key</label>
                        <input type="text" class="field-input" id="badge-icon"
                               value="<?php echo htmlspecialchars($currentConfig['badge']['icon'] ?? 'star'); ?>"
                               oninput="syncConfig()" placeholder="e.g. school, work, podcasts">
                        <div style="font-size:10px;color:#9ca3af;margin-top:4px;">Common: school · business · work · podcasts · campaign · rocket_launch · favorite · star</div>
                    </div>
                </div>
            </div>

            <!-- Typography -->
            <div class="sdui-panel" style="margin-bottom:16px;">
                <div class="sdui-panel-header">
                    <i class="fas fa-font"></i> Typography
                </div>
                <div class="sdui-panel-body">
                    <div class="field-group">
                        <label class="field-label">Title Color</label>
                        <div class="color-row">
                            <input type="color" class="color-swatch" id="title-color"
                                   value="<?php echo $currentConfig['typography']['title_color'] ?? '#FFFFFF'; ?>"
                                   oninput="syncConfig()">
                            <input type="text" class="field-input" id="title-color-hex"
                                   value="<?php echo $currentConfig['typography']['title_color'] ?? '#FFFFFF'; ?>"
                                   oninput="syncColorFromText('title-color','title-color-hex')" maxlength="7">
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Accent Color</label>
                        <div class="color-row">
                            <input type="color" class="color-swatch" id="accent-color"
                                   value="<?php echo $currentConfig['typography']['accent_color'] ?? '#FCD34D'; ?>"
                                   oninput="syncConfig()">
                            <input type="text" class="field-input" id="accent-color-hex"
                                   value="<?php echo $currentConfig['typography']['accent_color'] ?? '#FCD34D'; ?>"
                                   oninput="syncColorFromText('accent-color','accent-color-hex')" maxlength="7">
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA -->
            <div class="sdui-panel" style="margin-bottom:16px;">
                <div class="sdui-panel-header">
                    <i class="fas fa-hand-pointer"></i> Call to Action Button
                </div>
                <div class="sdui-panel-body">
                    <div class="field-group">
                        <label class="field-label">Button Label (max 20 chars)</label>
                        <input type="text" class="field-input" id="cta-label" maxlength="20"
                               value="<?php echo htmlspecialchars($currentConfig['cta']['label'] ?? 'Read Edition'); ?>"
                               oninput="syncConfig()">
                    </div>
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Button Background</label>
                            <div class="color-row">
                                <input type="color" class="color-swatch" id="cta-bg"
                                       value="<?php echo $currentConfig['cta']['bg_color'] ?? '#FCD34D'; ?>"
                                       oninput="syncConfig()">
                                <input type="text" class="field-input" id="cta-bg-hex"
                                       value="<?php echo $currentConfig['cta']['bg_color'] ?? '#FCD34D'; ?>"
                                       oninput="syncColorFromText('cta-bg','cta-bg-hex')" maxlength="7">
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Button Text Color</label>
                            <div class="color-row">
                                <input type="color" class="color-swatch" id="cta-text"
                                       value="<?php echo $currentConfig['cta']['text_color'] ?? '#1e2b42'; ?>"
                                       oninput="syncConfig()">
                                <input type="text" class="field-input" id="cta-text-hex"
                                       value="<?php echo $currentConfig['cta']['text_color'] ?? '#1e2b42'; ?>"
                                       oninput="syncColorFromText('cta-text','cta-text-hex')" maxlength="7">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save -->
            <button type="submit" class="btn btn-primary" style="width:100%;padding:14px;font-size:15px;">
                <i class="fas fa-save"></i> Save Card Config
            </button>
        </form>
    </div>

    <!-- ── Col 2: Phone Preview ────────────────────────────── -->
    <div>
        <div class="phone-wrap">
            <div>
                <div style="text-align:center;font-size:12px;font-weight:700;color:#6b7280;margin-bottom:12px;text-transform:uppercase;letter-spacing:0.5px;">
                    Live Preview
                </div>
                <div class="phone-frame">
                    <div class="phone-screen">
                        <!-- Status bar -->
                        <div class="phone-status">
                            <span>9:41</span>
                            <span><i class="fas fa-signal" style="font-size:8px;"></i> &nbsp;<i class="fas fa-battery-three-quarters" style="font-size:8px;"></i></span>
                        </div>

                        <!-- App content mock -->
                        <div class="phone-content">
                            <!-- App header strip -->
                            <div style="display:flex;align-items:center;gap:8px;padding:6px 4px 10px;border-bottom:1px solid #e2e8f0;margin-bottom:10px;">
                                <div style="width:24px;height:24px;background:var(--navy);border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                    <span style="font-size:8px;color:#f05a1a;font-weight:900;">KN</span>
                                </div>
                                <span style="font-size:11px;font-weight:800;color:var(--navy);">KandaNews Africa</span>
                            </div>

                            <div class="phone-section-label">Special Editions</div>

                            <!-- THE CARD PREVIEW -->
                            <div class="card-preview" id="card-preview">
                                <div class="card-preview-bg" id="preview-bg"></div>
                                <div class="card-preview-overlay" id="preview-overlay"></div>
                                <div class="card-preview-content">
                                    <div class="card-preview-badge" id="preview-badge">
                                        <i class="fas fa-star" id="preview-badge-icon" style="font-size:8px;"></i>
                                        <span id="preview-badge-text">SPECIAL</span>
                                    </div>
                                    <div class="card-preview-title" id="preview-title">
                                        <?php echo htmlspecialchars(mb_substr($edition['title'], 0, 50)); ?>
                                    </div>
                                    <div class="card-preview-sub" id="preview-sub">
                                        <?php echo date('M j, Y', strtotime($edition['edition_date'])); ?>
                                        &nbsp;·&nbsp; <?php echo $edition['page_count']; ?> pages
                                    </div>
                                    <div class="card-preview-cta" id="preview-cta">Read Edition</div>
                                </div>
                            </div>

                            <!-- Filler cards to simulate app -->
                            <div class="phone-section-label" style="margin-top:8px;">More Editions</div>
                            <?php for ($i = 0; $i < 2; $i++): ?>
                            <div style="height:72px;background:#e2e8f0;border-radius:10px;margin-bottom:6px;"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <div style="text-align:center;font-size:11px;color:#9ca3af;margin-top:10px;">
                    Simulated Flutter card view
                </div>
            </div>
        </div>
    </div>

    <!-- ── Col 3: JSON Output ──────────────────────────────── -->
    <div>
        <div class="sdui-panel">
            <div class="sdui-panel-header">
                <i class="fas fa-code"></i> Generated JSON
            </div>
            <div class="sdui-panel-body" style="padding:12px;">
                <p style="font-size:11px;color:#6b7280;margin-bottom:8px;">
                    This exact JSON is saved to <code>editions.card_config</code>
                    and returned by the API to the Flutter app.
                </p>
                <div class="json-output" id="json-output"><?php echo htmlspecialchars($currentConfigJson); ?></div>
                <button type="button" onclick="copyJson()"
                        style="margin-top:10px;width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;background:#f9fafb;font-size:12px;cursor:pointer;color:#374151;">
                    <i class="fas fa-copy"></i> Copy JSON
                </button>
            </div>
        </div>

        <!-- API preview tip -->
        <div style="background:linear-gradient(135deg,#1e2b42,#2a3f5f);border-radius:12px;padding:18px;color:#fff;margin-top:16px;">
            <div style="font-size:13px;font-weight:700;margin-bottom:8px;">
                <i class="fas fa-bolt" style="color:var(--orange);margin-right:6px;"></i>
                Live in API immediately
            </div>
            <p style="font-size:12px;opacity:.8;line-height:1.5;margin-bottom:12px;">
                Once saved, the Flutter app receives this config on its next
                <code style="background:rgba(255,255,255,.1);padding:1px 4px;border-radius:3px;font-size:11px;">GET /editions?type=special</code>
                request. No app release needed.
            </p>
            <div style="font-size:11px;opacity:.6;">API endpoint returns <code style="background:rgba(255,255,255,.1);padding:1px 4px;border-radius:3px;">card_config</code> as a decoded object.</div>
        </div>

        <!-- Edition info -->
        <div class="sdui-panel" style="margin-top:16px;">
            <div class="sdui-panel-header">
                <i class="fas fa-info-circle"></i> Edition Details
            </div>
            <div class="sdui-panel-body">
                <table style="width:100%;font-size:12px;border-collapse:collapse;">
                    <?php
                    $rows = [
                        'ID'      => '#' . $edition['id'],
                        'Status'  => ucfirst($edition['status']),
                        'Country' => strtoupper($edition['country']),
                        'Date'    => date('M j, Y', strtotime($edition['edition_date'])),
                        'Pages'   => $edition['page_count'] ?: '—',
                        'Access'  => $edition['is_free'] ? 'Free' : 'Paid / Subscription',
                    ];
                    foreach ($rows as $k => $v):
                    ?>
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:6px 4px;color:#6b7280;font-weight:600;width:80px;"><?php echo $k; ?></td>
                        <td style="padding:6px 4px;color:var(--navy);font-weight:700;"><?php echo htmlspecialchars($v); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <?php if (!empty($edition['html_url'])): ?>
                <a href="<?php echo htmlspecialchars($edition['html_url']); ?>" target="_blank"
                   class="btn btn-ghost" style="width:100%;margin-top:12px;text-align:center;font-size:12px;">
                    <i class="fas fa-external-link-alt"></i> Preview Edition
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- ── JavaScript ─────────────────────────────────────────────── -->
<script>
// Build config object from all form fields
function buildConfig() {
    return {
        version: 1,
        preset: document.querySelector('.preset-btn.active')?.dataset.preset
               ? JSON.parse(document.querySelector('.preset-btn.active').dataset.preset).preset
               : 'custom',
        card: {
            layout: document.querySelector('.layout-opt.active')?.dataset.layout || 'full_bleed',
            background: {
                type:   document.getElementById('bg-type').value,
                colors: [
                    document.getElementById('bg-color-1').value,
                    document.getElementById('bg-color-2').value,
                ],
                angle: parseInt(document.getElementById('bg-angle').value),
            },
            cover_treatment: document.getElementById('cover-treatment').value,
        },
        badge: {
            text:       document.getElementById('badge-text').value.toUpperCase(),
            bg_color:   document.getElementById('badge-bg').value,
            text_color: document.getElementById('badge-text-color').value,
            icon:       document.getElementById('badge-icon').value.trim(),
        },
        typography: {
            title_color:    document.getElementById('title-color').value,
            subtitle_color: 'rgba(255,255,255,0.80)',
            accent_color:   document.getElementById('accent-color').value,
        },
        cta: {
            label:      document.getElementById('cta-label').value,
            bg_color:   document.getElementById('cta-bg').value,
            text_color: document.getElementById('cta-text').value,
        },
    };
}

// Sync config → form input → preview → JSON panel
function syncConfig() {
    const config = buildConfig();

    // Update hidden form input
    document.getElementById('card-config-input').value = JSON.stringify(config);

    // Update JSON panel
    document.getElementById('json-output').textContent = JSON.stringify(config, null, 2);

    // Update hex text inputs to match color pickers
    syncHexFromPicker('bg-color-1', 'bg-color-1-hex');
    syncHexFromPicker('bg-color-2', 'bg-color-2-hex');
    syncHexFromPicker('badge-bg', 'badge-bg-hex');
    syncHexFromPicker('badge-text-color', 'badge-tc-hex');
    syncHexFromPicker('title-color', 'title-color-hex');
    syncHexFromPicker('accent-color', 'accent-color-hex');
    syncHexFromPicker('cta-bg', 'cta-bg-hex');
    syncHexFromPicker('cta-text', 'cta-text-hex');

    // Update live preview
    updatePreview(config);
}

function syncHexFromPicker(pickerId, textId) {
    const picker = document.getElementById(pickerId);
    const text   = document.getElementById(textId);
    if (picker && text) text.value = picker.value;
}

function syncColorFromText(pickerId, textId) {
    const text   = document.getElementById(textId);
    const picker = document.getElementById(pickerId);
    if (picker && text && /^#[0-9A-Fa-f]{6}$/.test(text.value)) {
        picker.value = text.value;
        syncConfig();
    }
}

// Update the phone mockup card preview
function updatePreview(config) {
    const bg     = document.getElementById('preview-bg');
    const badge  = document.getElementById('preview-badge');
    const btext  = document.getElementById('preview-badge-text');
    const title  = document.getElementById('preview-title');
    const sub    = document.getElementById('preview-sub');
    const cta    = document.getElementById('preview-cta');

    // Background
    const bgCfg = config.card.background;
    if (bgCfg.type === 'gradient') {
        bg.style.background = `linear-gradient(${bgCfg.angle}deg, ${bgCfg.colors[0]}, ${bgCfg.colors[1] || bgCfg.colors[0]})`;
    } else if (bgCfg.type === 'solid') {
        bg.style.background = bgCfg.colors[0];
    }

    // Badge
    badge.style.background = config.badge.bg_color;
    badge.style.color       = config.badge.text_color;
    btext.textContent       = config.badge.text;

    // Typography
    title.style.color = config.typography.title_color;
    sub.style.color   = config.typography.subtitle_color || 'rgba(255,255,255,0.75)';

    // CTA
    cta.style.background = config.cta.bg_color;
    cta.style.color      = config.cta.text_color;
    cta.textContent      = config.cta.label;
}

// Apply a preset
function applyPreset(btn) {
    const preset = JSON.parse(btn.dataset.preset);

    // Mark active
    document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    // Apply to form fields
    const bg = preset.card.background;
    document.getElementById('bg-type').value     = bg.type;
    document.getElementById('bg-color-1').value  = bg.colors[0] || '#1e2b42';
    document.getElementById('bg-color-2').value  = bg.colors[1] || bg.colors[0];
    document.getElementById('bg-angle').value     = bg.angle || 135;
    document.getElementById('angle-val').textContent = bg.angle || 135;
    document.getElementById('cover-treatment').value = preset.card.cover_treatment || 'overlay';

    document.getElementById('badge-text').value         = preset.badge.text;
    document.getElementById('badge-bg').value           = preset.badge.bg_color;
    document.getElementById('badge-text-color').value   = preset.badge.text_color;
    document.getElementById('badge-icon').value         = preset.badge.icon;
    document.getElementById('title-color').value        = preset.typography.title_color;
    document.getElementById('accent-color').value       = preset.typography.accent_color;
    document.getElementById('cta-label').value          = preset.cta.label;
    document.getElementById('cta-bg').value             = preset.cta.bg_color;
    document.getElementById('cta-text').value           = preset.cta.text_color;

    // Set layout
    const layout = preset.card.layout || 'full_bleed';
    document.querySelectorAll('.layout-opt').forEach(el => {
        el.classList.toggle('active', el.dataset.layout === layout);
    });

    syncConfig();
}

// Set layout option
function setLayout(el) {
    document.querySelectorAll('.layout-opt').forEach(e => e.classList.remove('active'));
    el.classList.add('active');
    syncConfig();
}

// Copy JSON to clipboard
function copyJson() {
    const text = document.getElementById('json-output').textContent;
    navigator.clipboard.writeText(text).then(() => {
        const btn = event.currentTarget;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(() => { btn.innerHTML = '<i class="fas fa-copy"></i> Copy JSON'; }, 2000);
    });
}

// Init on load
document.addEventListener('DOMContentLoaded', () => {
    const config = <?php echo json_encode($currentConfig, JSON_UNESCAPED_UNICODE); ?>;
    updatePreview(config);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
