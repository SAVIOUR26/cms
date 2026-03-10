<?php
/**
 * KandaNews Africa CMS - Pages Library
 * Browse and manage your HTML page templates
 */

define('KANDA_CMS', true);
require_once 'config.php';

requireLogin();
$user = getCurrentUser();

// ── Recursive multi-folder page scanner ─────────────────────────────────
$pagesDir   = __DIR__ . '/templates/pages/';
$pageGroups = []; // ['Group Label' => [...page entries...]]

function scan_pages_group(string $dir, string $relPrefix = ''): array {
    $pages = [];
    if (!is_dir($dir)) return $pages;
    foreach (scandir($dir) as $file) {
        if ($file[0] === '.') continue;
        $fullPath = $dir . $file;
        if (!is_file($fullPath) || pathinfo($file, PATHINFO_EXTENSION) !== 'html') continue;
        $content = file_get_contents($fullPath);
        preg_match('/<title>(.*?)<\/title>/i', $content, $tm);
        preg_match('/data-category="(.*?)"/i', $content, $cm);
        $pages[] = [
            'filename' => $relPrefix . $file,
            'basename' => $file,
            'title'    => $tm[1] ?? pathinfo($file, PATHINFO_FILENAME),
            'category' => $cm[1] ?? 'general',
            'size'     => filesize($fullPath),
            'modified' => filemtime($fullPath),
        ];
    }
    usort($pages, fn($a, $b) => $b['modified'] - $a['modified']);
    return $pages;
}

if (is_dir($pagesDir)) {
    $rootPages = scan_pages_group($pagesDir);
    if ($rootPages) $pageGroups['General'] = $rootPages;
    foreach (scandir($pagesDir) as $folder) {
        if ($folder[0] === '.') continue;
        $folderPath = $pagesDir . $folder . '/';
        if (!is_dir($folderPath)) continue;
        $folderPages = scan_pages_group($folderPath, $folder . '/');
        if ($folderPages) {
            $pageGroups[ucwords(str_replace(['-','_'], ' ', $folder))] = $folderPages;
        }
    }
}

$totalPageCount = array_sum(array_map('count', $pageGroups));

$page_title = 'Pages Library';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - KandaNews CMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 {
            font-size: 32px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f1f3f5;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
        }
        
        .stats-bar {
            background: white;
            padding: 20px 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            display: flex;
            gap: 40px;
            align-items: center;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .stat-icon {
            font-size: 24px;
        }
        
        .stat-info {
            display: flex;
            flex-direction: column;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 13px;
            color: #666;
        }
        
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-btn {
            padding: 8px 16px;
            border-radius: 20px;
            border: 2px solid #e9ecef;
            background: white;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
        }
        
        .pages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }
        
        .page-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .page-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.12);
        }
        
        .page-preview {
            height: 200px;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #667eea;
            position: relative;
        }
        
        .page-category {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.9);
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .page-info {
            padding: 20px;
        }
        
        .page-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #333;
        }
        
        .page-meta {
            font-size: 13px;
            color: #666;
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .page-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-small {
            padding: 8px 14px;
            font-size: 13px;
            border-radius: 6px;
            flex: 1;
            text-align: center;
        }
        
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 12px;
        }
        
        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 12px;
            color: #333;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .upload-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            text-align: left;
        }
        
        .upload-info h4 {
            margin-bottom: 12px;
            color: #333;
        }
        
        .upload-info ol {
            margin-left: 20px;
            line-height: 1.8;
            color: #666;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 900px;
            max-height: 90vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .modal-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 25px;
            overflow-y: auto;
            flex: 1;
        }
        
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .preview-frame {
            width: 100%;
            height: 600px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="dashboard.php" class="logo">
                <span>📰</span>
                <span>KandaNews CMS</span>
            </a>
            <a href="dashboard.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    
    <div class="container">
        <div class="page-header">
            <h1>
                <i class="fas fa-file-code"></i>
                Pages Library
            </h1>
            <div style="display: flex; gap: 10px;">
                <button onclick="showUploadInfo()" class="btn btn-secondary">
                    <i class="fas fa-question-circle"></i> How to Add Pages
                </button>
                <a href="build-edition.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Build Edition
                </a>
            </div>
        </div>
        
        <?php
        // Flatten all pages for stats
        $allPagesFlat = array_merge(...array_values($pageGroups ?: [[]]));
        $totalSize    = array_sum(array_column($allPagesFlat, 'size'));
        ?>
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-icon">📄</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo $totalPageCount; ?></div>
                    <div class="stat-label">Total Pages</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">📂</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo count($pageGroups); ?></div>
                    <div class="stat-label">Folders</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">💾</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo number_format($totalSize / 1024, 1); ?> KB</div>
                    <div class="stat-label">Total Size</div>
                </div>
            </div>
        </div>

        <!-- Folder filter tabs -->
        <?php if ($totalPageCount > 0): ?>
        <div class="filter-bar" style="flex-wrap:wrap;gap:8px;margin-bottom:20px;">
            <button class="filter-btn active" data-folder="all" onclick="filterFolder('all',this)">
                📁 All Folders <span style="opacity:.7;">(<?php echo $totalPageCount; ?>)</span>
            </button>
            <?php foreach ($pageGroups as $label => $gPages): ?>
            <button class="filter-btn" data-folder="<?php echo htmlspecialchars($label); ?>"
                    onclick="filterFolder(<?php echo json_encode($label); ?>, this)">
                <?php
                $folderIcons = ['General'=>'📃','University'=>'🎓','Corporate'=>'💼','Campaigns'=>'📣','University'=>'🎓'];
                echo $folderIcons[$label] ?? '📂';
                ?> <?php echo htmlspecialchars($label); ?>
                <span style="opacity:.7;">(<?php echo count($gPages); ?>)</span>
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($totalPageCount === 0): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No Pages Yet!</h3>
                <p>Add HTML pages to <code>templates/pages/</code> or create subfolders to organise by edition type.</p>
                <button onclick="showUploadInfo()" class="btn btn-primary">
                    <i class="fas fa-lightbulb"></i> Quick Start Guide
                </button>
                <div class="upload-info">
                    <h4>📝 How to organise pages:</h4>
                    <ol>
                        <li>Add HTML files to <code>templates/pages/</code> for general pages</li>
                        <li>Create subfolders like <code>templates/pages/university/</code> for specific edition types</li>
                        <li>Use the Page Editor to create and save pages to any folder</li>
                        <li>Pick pages from multiple folders when building an edition</li>
                    </ol>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($pageGroups as $groupLabel => $gPages): ?>
            <div class="folder-section" data-folder="<?php echo htmlspecialchars($groupLabel); ?>" style="margin-bottom:32px;">
                <!-- Folder header -->
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;padding-bottom:10px;border-bottom:2px solid #f0f0f0;">
                    <span style="font-size:20px;"><?php echo $folderIcons[$groupLabel] ?? '📂'; ?></span>
                    <div>
                        <div style="font-size:15px;font-weight:700;color:#1e2b42;"><?php echo htmlspecialchars($groupLabel); ?></div>
                        <div style="font-size:12px;color:#888;"><?php echo count($gPages); ?> page<?php echo count($gPages) !== 1 ? 's' : ''; ?> &nbsp;·&nbsp;
                            <code style="font-size:11px;background:#f3f4f6;padding:1px 5px;border-radius:4px;">templates/pages/<?php echo $groupLabel === 'General' ? '' : strtolower(str_replace(' ', '-', $groupLabel)) . '/'; ?></code>
                        </div>
                    </div>
                    <div style="margin-left:auto;">
                        <a href="page-editor.php?folder=<?php echo urlencode(strtolower(str_replace(' ','-',$groupLabel))); ?>"
                           class="btn btn-primary btn-small" style="font-size:12px;padding:5px 12px;">
                            <i class="fas fa-plus"></i> New Page Here
                        </a>
                    </div>
                </div>

                <div class="pages-grid">
                    <?php foreach ($gPages as $page): ?>
                    <div class="page-card" data-folder="<?php echo htmlspecialchars($groupLabel); ?>"
                         data-category="<?php echo htmlspecialchars($page['category']); ?>">
                        <div class="page-preview">
                            <?php
                            $catIcons = ['cover'=>'📰','article'=>'📄','ad'=>'📢','interactive'=>'🎮','general'=>'📃'];
                            echo $catIcons[$page['category']] ?? '📃';
                            ?>
                            <span class="page-category"><?php echo htmlspecialchars($page['category']); ?></span>
                        </div>
                        <div class="page-info">
                            <div class="page-title"><?php echo htmlspecialchars($page['title']); ?></div>
                            <div style="font-size:10px;color:#aaa;margin-bottom:4px;">
                                <i class="fas fa-folder" style="margin-right:3px;"></i><?php echo htmlspecialchars($page['filename']); ?>
                            </div>
                            <div class="page-meta">
                                <span><i class="fas fa-file"></i> <?php echo number_format($page['size'] / 1024, 1); ?> KB</span>
                                <span><i class="fas fa-clock"></i> <?php echo date('M j', $page['modified']); ?></span>
                            </div>
                            <div class="page-actions">
                                <button onclick="previewPage('<?php echo htmlspecialchars($page['filename']); ?>')" class="btn btn-secondary btn-small">
                                    <i class="fas fa-eye"></i> Preview
                                </button>
                                <a href="page-editor.php?file=<?php echo urlencode($page['filename']); ?>" class="btn btn-primary btn-small">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Preview Modal -->
    <div id="previewModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Page Preview</h3>
                <button class="modal-close" onclick="closePreview()">×</button>
            </div>
            <div class="modal-body">
                <iframe id="previewFrame" class="preview-frame"></iframe>
            </div>
        </div>
    </div>
    
    <script>
        function filterFolder(folder, btn) {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.querySelectorAll('.folder-section').forEach(sec => {
                sec.style.display = (folder === 'all' || sec.dataset.folder === folder) ? '' : 'none';
            });
        }

        // Legacy: kept for compatibility with old filter buttons if any remain
        function filterPages(category) {
            const cards = document.querySelectorAll('.page-card');
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            if (event && event.target) event.target.classList.add('active');
            cards.forEach(card => {
                card.style.display = (category === 'all' || card.dataset.category === category) ? 'block' : 'none';
            });
        }
        
        function previewPage(filename) {
            const modal = document.getElementById('previewModal');
            const frame = document.getElementById('previewFrame');
            frame.src = 'templates/pages/' + filename;
            modal.classList.add('active');
        }
        
        function closePreview() {
            document.getElementById('previewModal').classList.remove('active');
        }
        
        function showUploadInfo() {
            alert('📝 How to Add Pages:\n\n1. Create HTML pages using Claude AI\n2. Save them to: templates/pages/\n3. Refresh this page\n4. Start building editions!\n\nTip: Add data-category="cover" to your HTML to categorize pages automatically.');
        }
    </script>
</body>
</html>