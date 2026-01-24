<?php
/**
 * KandaNews Africa CMS - Edition Generator API
 * Backend script that assembles pages and creates flipbook ZIP
 */

header('Content-Type: application/json');

// Get POST data
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid request data']);
    exit;
}

// Extract edition data
$title = $data['title'] ?? 'KandaNews Edition';
$date = $data['date'] ?? date('Y-m-d');
$theme = $data['theme'] ?? '';
$country = $data['country'] ?? 'ug';
$pages = $data['pages'] ?? [];

if (empty($pages)) {
    echo json_encode(['success' => false, 'error' => 'No pages provided']);
    exit;
}

// Create output directory
$outputDir = __DIR__ . '/../output/' . date('Y-m-d', strtotime($date));
if (!file_exists($outputDir)) {
    mkdir($outputDir, 0777, true);
}

// Generate the flipbook HTML
$flipbookHtml = generateFlipbookHTML($title, $date, $theme, $country, $pages);
file_put_contents($outputDir . '/index.html', $flipbookHtml);

// Copy page files
$pagesDir = __DIR__ . '/../templates/pages/';
foreach ($pages as $index => $page) {
    $sourceFile = $pagesDir . $page['filename'];
    if (file_exists($sourceFile)) {
        $destFile = $outputDir . '/page-' . ($index + 1) . '.html';
        copy($sourceFile, $destFile);
    }
}

// Generate CSS
$css = generateCSS();
file_put_contents($outputDir . '/styles.css', $css);

// Generate JavaScript
$js = generateJS(count($pages));
file_put_contents($outputDir . '/script.js', $js);

// Create cover image placeholder (you can replace this with actual image generation)
createCoverImage($outputDir, $title, $date);

// Create ZIP file
$zipPath = createZIP($outputDir, $date);

// Return success response
echo json_encode([
    'success' => true,
    'output_path' => $outputDir,
    'zip_path' => $zipPath,
    'zip_url' => str_replace(__DIR__ . '/..', '', $zipPath),
    'total_pages' => count($pages)
]);

// ============================================
// HELPER FUNCTIONS
// ============================================

function generateFlipbookHTML($title, $date, $theme, $country, $pages) {
    $countryNames = [
        'ug' => 'Uganda 🇺🇬',
        'ke' => 'Kenya 🇰🇪',
        'za' => 'South Africa 🇿🇦'
    ];
    $countryName = $countryNames[$country] ?? 'Uganda 🇺🇬';
    
    $formattedDate = date('F j, Y', strtotime($date));
    $totalPages = count($pages);
    
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title} - KandaNews Africa</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="kanda-header">
        <div class="header-content">
            <div class="logo">
                <span class="logo-icon">📰</span>
                <span class="logo-text">KandaNews Africa</span>
            </div>
            <div class="header-info">
                <div class="edition-title">{$title}</div>
                <div class="edition-meta">
                    <span>{$countryName}</span>
                    <span>•</span>
                    <span>{$formattedDate}</span>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Flipbook Container -->
    <div class="flipbook-container">
        <div id="flipbook">
HTML;
    
    // Add each page
    foreach ($pages as $index => $page) {
        $pageNum = $index + 1;
        $html .= <<<HTML
            <div class="page" data-page="{$pageNum}">
                <iframe src="page-{$pageNum}.html" class="page-frame" frameborder="0"></iframe>
            </div>
HTML;
    }
    
    $html .= <<<HTML
        </div>
    </div>
    
    <!-- Controls -->
    <div class="controls">
        <button id="first-btn" class="control-btn" title="First Page">
            <i class="fas fa-step-backward"></i>
        </button>
        <button id="prev-btn" class="control-btn" title="Previous">
            <i class="fas fa-chevron-left"></i>
        </button>
        <span id="page-info">Page 1 of {$totalPages}</span>
        <button id="next-btn" class="control-btn" title="Next">
            <i class="fas fa-chevron-right"></i>
        </button>
        <button id="last-btn" class="control-btn" title="Last Page">
            <i class="fas fa-step-forward"></i>
        </button>
    </div>
    
    <!-- Footer -->
    <footer class="kanda-footer">
        <div class="footer-content">
            <div class="footer-left">
                <span>🚀 The Future of News</span>
                <span>•</span>
                <span>Powered by AI</span>
            </div>
            <div class="footer-right">
                <a href="https://kandanews.africa">kandanews.africa</a>
                <span>•</span>
                <span>+256 772 253804</span>
            </div>
        </div>
    </footer>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/turn.js/3/turn.min.js"></script>
    <script src="script.js"></script>
</body>
</html>
HTML;
    
    return $html;
}

function generateCSS() {
    return <<<CSS
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    overflow-x: hidden;
}

/* Header */
.kanda-header {
    background: white;
    box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
    border-bottom: 3px solid #667eea;
}

.header-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    display: flex;
    align-items: center;
    gap: 12px;
}

.logo-icon {
    font-size: 32px;
}

.logo-text {
    font-size: 24px;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.header-info {
    text-align: right;
}

.edition-title {
    font-size: 18px;
    font-weight: 700;
    color: #333;
}

.edition-meta {
    font-size: 14px;
    color: #666;
    margin-top: 4px;
}

/* Flipbook Container */
.flipbook-container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
    min-height: calc(100vh - 200px);
}

#flipbook {
    width: 922px;
    height: 600px;
    margin: 0 auto;
    box-shadow: 0 10px 40px rgba(0,0,0,0.2);
}

.page {
    background: white;
    width: 461px;
    height: 600px;
    overflow: hidden;
}

.page-frame {
    width: 100%;
    height: 100%;
    border: none;
}

/* Controls */
.controls {
    text-align: center;
    padding: 30px 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
}

.control-btn {
    padding: 12px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 16px;
}

.control-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.control-btn:active {
    transform: translateY(0);
}

#page-info {
    font-weight: 700;
    color: #333;
    font-size: 16px;
    padding: 0 15px;
}

/* Footer */
.kanda-footer {
    background: #2d3748;
    color: white;
    padding: 20px;
    text-align: center;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 14px;
}

.footer-left, .footer-right {
    display: flex;
    gap: 10px;
    align-items: center;
}

.footer-right a {
    color: #667eea;
    text-decoration: none;
    font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
    #flipbook {
        width: 100%;
        height: auto;
    }
    
    .header-content {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .footer-content {
        flex-direction: column;
        gap: 10px;
    }
    
    .controls {
        flex-wrap: wrap;
    }
}
CSS;
}

function generateJS($totalPages) {
    return <<<JS
\$(document).ready(function() {
    // Initialize flipbook
    \$('#flipbook').turn({
        width: 922,
        height: 600,
        autoCenter: true,
        duration: 1000,
        gradients: true,
        acceleration: true,
        pages: {$totalPages}
    });
    
    // Update page info
    \$('#flipbook').bind('turned', function(event, page, pageObject) {
        \$('#page-info').text('Page ' + page + ' of {$totalPages}');
    });
    
    // Control buttons
    \$('#first-btn').click(function() {
        \$('#flipbook').turn('page', 1);
    });
    
    \$('#prev-btn').click(function() {
        \$('#flipbook').turn('previous');
    });
    
    \$('#next-btn').click(function() {
        \$('#flipbook').turn('next');
    });
    
    \$('#last-btn').click(function() {
        \$('#flipbook').turn('page', {$totalPages});
    });
    
    // Keyboard navigation
    \$(document).keydown(function(e) {
        if (e.keyCode == 37) \$('#flipbook').turn('previous');
        if (e.keyCode == 39) \$('#flipbook').turn('next');
        if (e.keyCode == 36) \$('#flipbook').turn('page', 1);
        if (e.keyCode == 35) \$('#flipbook').turn('page', {$totalPages});
    });
    
    // Touch swipe for mobile
    var startX = 0;
    \$('#flipbook').on('touchstart', function(e) {
        startX = e.originalEvent.touches[0].pageX;
    });
    
    \$('#flipbook').on('touchend', function(e) {
        var endX = e.originalEvent.changedTouches[0].pageX;
        if (startX - endX > 50) \$('#flipbook').turn('next');
        if (endX - startX > 50) \$('#flipbook').turn('previous');
    });
});
JS;
}

function createCoverImage($outputDir, $title, $date) {
    // Create a simple cover.png placeholder
    // In production, you'd use GD or ImageMagick to create a real cover
    $coverHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            margin: 0;
            padding: 0;
            width: 461px;
            height: 600px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            color: white;
        }
        .cover-content {
            text-align: center;
            padding: 40px;
        }
        h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }
        p {
            font-size: 24px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="cover-content">
        <h1>📰</h1>
        <h1>{$title}</h1>
        <p>{$date}</p>
    </div>
</body>
</html>
HTML;
    
    file_put_contents($outputDir . '/cover.html', $coverHtml);
}

function createZIP($sourceDir, $date) {
    $zipFilename = 'kandanews-edition-' . $date . '.zip';
    $zipPath = dirname($sourceDir) . '/' . $zipFilename;
    
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($sourceDir) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        
        $zip->close();
        return $zipPath;
    }
    
    return false;
}
?>