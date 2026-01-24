<?php
/**
 * KandaNews Africa CMS - Pages Library
 * Browse and manage your HTML page templates
 */

define('KANDA_CMS', true);
require_once 'config.php';

requireLogin();
$user = getCurrentUser();

// Get all template pages from the templates/pages/ directory
$pagesDir = __DIR__ . '/templates/pages/';
$pages = [];

if (is_dir($pagesDir)) {
    $files = scandir($pagesDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
            $filePath = $pagesDir . $file;
            $content = file_get_contents($filePath);
            
            // Extract title from HTML (if exists)
            preg_match('/<title>(.*?)<\/title>/i', $content, $titleMatch);
            $title = $titleMatch[1] ?? pathinfo($file, PATHINFO_FILENAME);
            
            // Extract category from filename or content
            preg_match('/data-category="(.*?)"/i', $content, $catMatch);
            $category = $catMatch[1] ?? 'general';
            
            $pages[] = [
                'filename' => $file,
                'title' => $title,
                'category' => $category,
                'size' => filesize($filePath),
                'modified' => filemtime($filePath)
            ];
        }
    }
}

// Sort by most recent
usort($pages, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

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
        
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-icon">📄</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo count($pages); ?></div>
                    <div class="stat-label">Total Pages</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">📁</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo number_format(array_sum(array_column($pages, 'size')) / 1024, 1); ?> KB</div>
                    <div class="stat-label">Total Size</div>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon">🎨</div>
                <div class="stat-info">
                    <div class="stat-value"><?php echo count(array_unique(array_column($pages, 'category'))); ?></div>
                    <div class="stat-label">Categories</div>
                </div>
            </div>
        </div>
        
        <div class="filter-bar">
            <button class="filter-btn active" onclick="filterPages('all')">All Pages</button>
            <button class="filter-btn" onclick="filterPages('cover')">📰 Covers</button>
            <button class="filter-btn" onclick="filterPages('article')">📄 Articles</button>
            <button class="filter-btn" onclick="filterPages('ad')">📢 Ads</button>
            <button class="filter-btn" onclick="filterPages('interactive')">🎮 Interactive</button>
        </div>
        
        <?php if (empty($pages)): ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No Pages Yet!</h3>
                <p>Start creating beautiful HTML pages with Claude AI</p>
                <button onclick="showUploadInfo()" class="btn btn-primary">
                    <i class="fas fa-lightbulb"></i> Learn How to Add Pages
                </button>
                
                <div class="upload-info">
                    <h4>📝 Quick Start Guide:</h4>
                    <ol>
                        <li>Open Claude AI and ask it to create a beautiful HTML page</li>
                        <li>Save the HTML code to <code>templates/pages/your-page-name.html</code></li>
                        <li>Refresh this page to see it appear!</li>
                        <li>Start building your edition</li>
                    </ol>
                </div>
            </div>
        <?php else: ?>
            <div class="pages-grid">
                <?php foreach ($pages as $page): ?>
                    <div class="page-card" data-category="<?php echo htmlspecialchars($page['category']); ?>">
                        <div class="page-preview">
                            <?php 
                            $icons = [
                                'cover' => '📰',
                                'article' => '📄',
                                'ad' => '📢',
                                'interactive' => '🎮',
                                'general' => '📃'
                            ];
                            echo $icons[$page['category']] ?? '📃';
                            ?>
                            <span class="page-category"><?php echo htmlspecialchars($page['category']); ?></span>
                        </div>
                        <div class="page-info">
                            <div class="page-title"><?php echo htmlspecialchars($page['title']); ?></div>
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
        function filterPages(category) {
            const cards = document.querySelectorAll('.page-card');
            const buttons = document.querySelectorAll('.filter-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            cards.forEach(card => {
                if (category === 'all' || card.dataset.category === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
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