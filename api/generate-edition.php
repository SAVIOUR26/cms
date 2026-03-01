<?php
error_reporting(0);
header('Content-Type: application/json');

/**
 * Connect to the kandan_api database so generated editions are
 * automatically registered with draft status for proof-checking.
 */
function apiDb(): ?PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $envFile = dirname(__DIR__) . '/.env';
    $env = [];
    if (is_file($envFile)) {
        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (!$line || $line[0] === '#' || strpos($line, '=') === false) continue;
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim($v);
        }
    }

    try {
        $pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=%s',
                $env['DB_HOST'] ?? 'localhost',
                $env['DB_NAME'] ?? 'kandan_api',
                $env['DB_CHARSET'] ?? 'utf8mb4'),
            $env['DB_USER'] ?? 'kandan_api',
            $env['DB_PASS'] ?? '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("[CMS] Cannot connect to API DB: " . $e->getMessage());
        return null;
    }
}

/**
 * Register a generated edition in the API database as draft.
 * Returns the new edition ID or null on failure.
 */
function registerEdition(array $meta): ?int {
    $db = apiDb();
    if (!$db) return null;

    try {
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($meta['title']));
        $slug = trim($slug, '-') . '-' . $meta['date'];

        // Ensure unique slug
        $stmt = $db->prepare("SELECT id FROM editions WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . substr(bin2hex(random_bytes(3)), 0, 6);
        }

        $stmt = $db->prepare("
            INSERT INTO editions
                (title, slug, country, edition_date, edition_type, category,
                 html_url, zip_url, page_count, is_free, theme, description, status, created_at)
            VALUES
                (:title, :slug, :country, :edition_date, :edition_type, :category,
                 :html_url, :zip_url, :page_count, :is_free, :theme, :description, 'draft', NOW())
        ");
        $stmt->execute([
            ':title'        => $meta['title'],
            ':slug'         => $slug,
            ':country'      => $meta['country'] ?? 'ug',
            ':edition_date' => $meta['date'],
            ':edition_type' => $meta['edition_type'] ?? 'daily',
            ':category'     => $meta['category'] ?? null,
            ':html_url'     => $meta['html_url'],
            ':zip_url'      => $meta['zip_url'] ?? null,
            ':page_count'   => $meta['page_count'] ?? 0,
            ':is_free'      => $meta['is_free'] ?? 1,
            ':theme'        => $meta['theme'] ?? null,
            ':description'  => $meta['description'] ?? null,
        ]);
        return (int) $db->lastInsertId();
    } catch (PDOException $e) {
        error_log("[CMS] Failed to register edition: " . $e->getMessage());
        return null;
    }
}

function extractParts($html) {
    $css = '';
    $body = '';
    
    // Extract CSS
    if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $html, $matches)) {
        $css = implode("\n", $matches[1]);
    }
    
    // Extract body - keep everything as-is
    if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
        $body = $matches[1];
    } else {
        $body = preg_replace('/<!\s*DOCTYPE[^>]*>|<\/?html[^>]*>|<head[^>]*>.*?<\/head>|<style[^>]*>.*?<\/style>/is', '', $html);
    }
    
    return ['css' => trim($css), 'body' => trim($body)];
}

function scopeCSS($css, $scope) {
    // Remove comments
    $css = preg_replace('/\/\*.*?\*\//s', '', $css);
    
    $output = '';
    
    // Extract and preserve @keyframes
    preg_match_all('/@keyframes\s+([^{]+)\{((?:[^{}]+|\{[^}]*\})*)\}/s', $css, $keyframes, PREG_SET_ORDER);
    foreach ($keyframes as $kf) {
        $output .= $kf[0] . "\n";
        $css = str_replace($kf[0], '', $css);
    }
    
    // Process regular CSS rules
    preg_match_all('/([^{]+)\{([^}]*)\}/s', $css, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $selectors = trim($match[1]);
        $declarations = trim($match[2]);
        
        if (empty($declarations)) continue;
        if (strpos($selectors, '@') === 0) continue;
        
        // Scope each selector
        $selectorList = array_map('trim', explode(',', $selectors));
        $scopedSelectors = [];
        
        foreach ($selectorList as $selector) {
            if (empty($selector)) continue;
            
            // Add scope prefix
            $scopedSelectors[] = '.' . $scope . ' ' . $selector;
        }
        
        $output .= implode(', ', $scopedSelectors) . ' { ' . $declarations . " }\n";
    }
    
    return $output;
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) throw new Exception('Invalid input');
    
    $title = $input['title'] ?? 'Daily Edition';
    $includeDate = $input['includeDate'] ?? true;
    $date = $input['date'] ?? date('Y-m-d');
    $pages = $input['pages'] ?? [];
    
    if (empty($pages)) throw new Exception('No pages provided');
    
    // Format display text based on includeDate
    if ($includeDate && $date) {
        $displayText = $title . ' - ' . date('F j, Y', strtotime($date));
    } else {
        $displayText = $title;
    }
    
    $allCSS = '';
    $allSlides = '';
    $allThumbs = '';
    
    foreach ($pages as $idx => $page) {
        $filename = $page['filename'] ?? '';
        if ($filename[0] !== '/') {
            $filename = '../templates/pages/' . $filename;
        }
        
        if (!file_exists($filename)) {
            throw new Exception("File not found: " . basename($filename));
        }
        
        $parts = extractParts(file_get_contents($filename));
        
        // Scope CSS with page class
        $scopedCSS = scopeCSS($parts['css'], "page-{$idx}");
        $allCSS .= "/* Page {$idx} */\n" . $scopedCSS . "\n";
        
        // Main slide - keep body exactly as template designed it
        $allSlides .= "<div class=\"swiper-slide page-{$idx}\">{$parts['body']}</div>\n";
        
        // Thumbnail - same content, smaller
        $allThumbs .= "<div class=\"swiper-slide\"><div class=\"thumb-page page-{$idx}\">{$parts['body']}</div><span class=\"thumb-num\">" . ($idx + 1) . "</span></div>\n";
    }
    
    $total = count($pages);
    
$html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title}</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #1e2b42;
    color: #fff;
    overflow: hidden;
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.header {
    text-align: center;
    padding: 12px 20px;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    background: #e5e7eb;
    border-bottom: 1px solid #d1d5db;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
}

.logo { 
    height: 28px;
    object-fit: contain;
    margin-right: 12px;
}

.container {
    flex: 1;
    display: flex;
    padding: 20px;
    gap: 20px;
    overflow: hidden;
    position: relative;
}

.fullscreen-prompt {
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.85);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    cursor: pointer;
    animation: fadeIn 0.3s;
}

.fullscreen-prompt.hidden {
    display: none;
}

.fs-message {
    text-align: center;
    color: white;
}

.fs-message i {
    font-size: 48px;
    color: #f05a1a;
    margin-bottom: 15px;
    animation: pulse 2s infinite;
}

.fs-message p {
    font-size: 18px;
    font-weight: 600;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.1); opacity: 0.8; }
}

.sidebar {
    width: 150px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.thumbs {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 5px;
}

.thumbs::-webkit-scrollbar {
    width: 4px;
}

.thumbs::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.05);
    border-radius: 2px;
}

.thumbs::-webkit-scrollbar-thumb {
    background: rgba(240,90,26,0.6);
    border-radius: 2px;
}

.thumbs .swiper-slide {
    height: 110px !important;
    margin-bottom: 12px;
    cursor: pointer;
    border-radius: 6px;
    overflow: hidden;
    border: 2px solid rgba(255,255,255,0.1);
    transition: all 0.3s;
    background: rgba(0,0,0,0.4);
    position: relative;
}

.thumbs .swiper-slide:hover {
    border-color: rgba(240,90,26,0.8);
    transform: scale(1.05);
}

.thumbs .swiper-slide-thumb-active {
    border-color: #f05a1a;
    box-shadow: 0 0 20px rgba(240,90,26,0.6);
    transform: scale(1.08);
}

.thumb-page {
    width: 461px;
    height: 600px;
    transform: scale(0.19);
    transform-origin: top left;
    pointer-events: none;
}

.thumb-num {
    position: absolute;
    top: 5px;
    right: 5px;
    background: rgba(240,90,26,0.95);
    color: white;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.viewer {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.main-swiper {
    width: 100%;
    max-width: 490px;
    height: 640px;
}

.main-swiper .swiper-slide {
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.page-label {
    position: absolute;
    bottom: -40px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 13px;
    font-weight: 600;
    color: rgba(255,255,255,0.7);
    padding: 6px 14px;
    background: rgba(0,0,0,0.3);
    border-radius: 20px;
    backdrop-filter: blur(10px);
}

.swiper-slide > * {
    border: 2px solid #f05a1a;
    border-radius: 8px;
    box-shadow: 
        0 25px 60px rgba(0,0,0,0.5),
        0 10px 30px rgba(240,90,26,0.2),
        0 0 0 1px rgba(240,90,26,0.1);
}

/* Scroll indicator for scrollable pages */
.page-wrapper.scrollable {
    position: relative;
}

.page-wrapper.scrollable::after {
    content: '↓';
    position: absolute;
    bottom: 15px;
    left: 50%;
    transform: translateX(-50%);
    width: 40px;
    height: 40px;
    background: rgba(240, 90, 26, 0.9);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 700;
    pointer-events: none;
    animation: pulseArrow 2s ease-in-out infinite;
    box-shadow: 0 4px 15px rgba(240, 90, 26, 0.5);
    z-index: 100;
}

@keyframes pulseArrow {
    0%, 100% { 
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
    50% { 
        transform: translateX(-50%) translateY(8px);
        opacity: 0.7;
    }
}

/* Hide indicator when scrolled near bottom */
.page-wrapper.scrollable.scrolled::after {
    display: none;
}

.controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    padding: 15px 20px;
    background: rgba(255,255,255,.08);
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(255,255,255,.15);
}

.btn {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg,#f05a1a,#ff7a3d);
    color: #fff;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(240,90,26,.4);
}

.btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(240,90,26,.6);
}

.btn:active:not(:disabled) {
    transform: translateY(0);
}

.btn:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.btn.muted { 
    background: linear-gradient(135deg,#666,#888);
}

.page-num {
    font-size: 15px;
    font-weight: 700;
    color: #fff;
    min-width: 70px;
    text-align: center;
    padding: 8px 16px;
    background: rgba(240,90,26,0.15);
    border-radius: 8px;
    border: 1px solid rgba(240,90,26,0.3);
}

@media (max-width: 768px) {
    .container { 
        padding: 10px;
        padding-bottom: 5px;
        justify-content: center;
    }
    .sidebar { display: none; }
    .viewer {
        padding: 0;
        width: 100%;
        max-width: 100%;
    }
    .main-swiper { 
        height: calc(100% - 20px);
        max-width: calc(100vw - 24px);
        margin: 0 auto;
    }
    .page-label {
        bottom: -35px;
        font-size: 12px;
    }
}

/* Page-specific CSS */
{$allCSS}
</style>
</head>
<body>
<div class="header">
<img src="appLogoIcon.png" alt="KandaNews Africa" class="logo" 
     onerror="this.onerror=null; this.src='../assets/appLogoIcon.png'; this.onerror=function(){this.src='../../assets/appLogoIcon.png'; this.onerror=function(){this.style.display='none'}}">
<span>{$displayText}</span>
</div>

<div class="container">
<div class="fullscreen-prompt" id="fs-prompt">
    <div class="fs-message">
        <i class="fas fa-expand"></i>
        <p>Tap anywhere to enter fullscreen</p>
    </div>
</div>

<div class="sidebar">
<div class="swiper thumbs">
<div class="swiper-wrapper">
{$allThumbs}
</div>
</div>
</div>

<div class="viewer">
<div class="swiper main-swiper">
<div class="swiper-wrapper">
{$allSlides}
</div>
</div>
</div>
</div>

<div class="controls">
<button class="btn" id="sound"><i class="fas fa-volume-up"></i></button>
<button class="btn" id="prev"><i class="fas fa-chevron-left"></i></button>
<span class="page-num" id="num">1 / {$total}</span>
<button class="btn" id="next"><i class="fas fa-chevron-right"></i></button>
<button class="btn" id="full"><i class="fas fa-expand"></i></button>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
let soundOn = true;
const audio = new Audio('turn.mp3');
audio.volume = 0.3;

const thumbSwiper = new Swiper('.thumbs', {
    direction: 'vertical',
    slidesPerView: 'auto',
    freeMode: true,
    watchSlidesProgress: true
});

const mainSwiper = new Swiper('.main-swiper', {
    effect: 'flip',
    flipEffect: {
        slideShadows: true,
        limitRotation: true
    },
    speed: 600,
    keyboard: { enabled: true },
    thumbs: { swiper: thumbSwiper },
    on: {
        slideChange: function() {
            const current = this.activeIndex + 1;
            const total = this.slides.length;
            document.getElementById('num').textContent = current + ' / ' + total;
            document.getElementById('prev').disabled = current === 1;
            document.getElementById('next').disabled = current === total;
            
            if (soundOn) {
                audio.currentTime = 0;
                audio.play().catch(() => {});
            }
            
            if (navigator.vibrate) navigator.vibrate(10);
        }
    }
});

document.getElementById('prev').onclick = () => mainSwiper.slidePrev();
document.getElementById('next').onclick = () => mainSwiper.slideNext();
document.getElementById('sound').onclick = function() {
    soundOn = !soundOn;
    this.classList.toggle('muted');
    this.querySelector('i').classList.toggle('fa-volume-up');
    this.querySelector('i').classList.toggle('fa-volume-mute');
};

let isFullscreen = false;
document.getElementById('full').onclick = function() {
    if (!isFullscreen) {
        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen();
        }
        isFullscreen = true;
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
        }
        isFullscreen = false;
    }
};

document.getElementById('prev').disabled = true;

// Handle scrollable page indicators
function updateScrollIndicators() {
    document.querySelectorAll('.page-wrapper.scrollable').forEach(page => {
        page.addEventListener('scroll', function() {
            const scrollTop = this.scrollTop;
            const scrollHeight = this.scrollHeight;
            const clientHeight = this.clientHeight;
            
            // Hide indicator when scrolled near bottom (within 50px)
            if (scrollTop + clientHeight >= scrollHeight - 50) {
                this.classList.add('scrolled');
            } else {
                this.classList.remove('scrolled');
            }
        });
        
        // Trigger initial check
        page.dispatchEvent(new Event('scroll'));
    });
}

// Check on page change
mainSwiper.on('slideChange', function() {
    setTimeout(updateScrollIndicators, 100);
});

// Initial check
updateScrollIndicators();

// Fullscreen on first interaction (browsers require user action)
let hasInteracted = false;
const fsPrompt = document.getElementById('fs-prompt');

function enterFullscreen() {
    if (!hasInteracted) {
        hasInteracted = true;
        fsPrompt.classList.add('hidden');
        
        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen().catch(() => {});
        } else if (document.documentElement.webkitRequestFullscreen) {
            document.documentElement.webkitRequestFullscreen().catch(() => {});
        }
    }
}

// Trigger fullscreen on any interaction
fsPrompt.addEventListener('click', enterFullscreen);
fsPrompt.addEventListener('touchstart', enterFullscreen);

console.log('KandaNews - {$total} pages loaded');
</script>
</body>
</html>
HTML;

    // Output directory - use date if provided, otherwise use sanitized title
    $dirName = ($includeDate && $date) ? $date : str_replace(' ', '_', strtolower($title));
    $outputDir = '../output/' . $dirName . '/';
    if (!is_dir($outputDir)) mkdir($outputDir, 0755, true);
    
    file_put_contents($outputDir . 'index.html', $html);
    
    // Copy assets to output folder
    if (file_exists('../assets/turn.mp3')) {
        copy('../assets/turn.mp3', $outputDir . 'turn.mp3');
    }
    
    // Copy logo
    if (file_exists('../assets/appLogoIcon.png')) {
        copy('../assets/appLogoIcon.png', $outputDir . 'appLogoIcon.png');
    } elseif (file_exists('assets/appLogoIcon.png')) {
        copy('assets/appLogoIcon.png', $outputDir . 'appLogoIcon.png');
    }
    
    // Create ZIP with proper name
    // Create ZIP with proper name
    $zipName = str_replace(' ', '_', $title);
    if ($includeDate && $date) {
        $zipName .= '_' . $date;
    }
    $zipName .= '.zip';
    $zipFile = $outputDir . $zipName;
    $zip = new ZipArchive();
    
    if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
        $zip->addFile($outputDir . 'index.html', 'index.html');
        
        if (file_exists($outputDir . 'turn.mp3')) {
            $zip->addFile($outputDir . 'turn.mp3', 'turn.mp3');
        }
        
        if (file_exists($outputDir . 'appLogoIcon.png')) {
            $zip->addFile($outputDir . 'appLogoIcon.png', 'appLogoIcon.png');
        }
        
        $zip->close();
    }
    
    // ── Auto-register in API database as draft ──
    $country     = $input['country'] ?? 'ug';
    $editionType = $input['edition_type'] ?? 'daily';
    $category    = $input['category'] ?? null;
    $isFree      = $input['is_free'] ?? 1;
    $theme       = $input['theme'] ?? null;
    $description = $input['description'] ?? null;

    $baseUrl  = 'output/' . $dirName . '/';
    $htmlPath = $baseUrl . 'index.html';
    $zipPath  = $baseUrl . $zipName;

    $editionId = registerEdition([
        'title'        => $title,
        'date'         => $date ?: date('Y-m-d'),
        'country'      => $country,
        'edition_type' => $editionType,
        'category'     => $category,
        'html_url'     => $htmlPath,
        'zip_url'      => $zipPath,
        'page_count'   => $total,
        'is_free'      => $isFree,
        'theme'        => $theme,
        'description'  => $description,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Generated successfully' . ($editionId ? ' (registered as draft #' . $editionId . ')' : ''),
        'pages' => $total,
        'zip' => $zipName,
        'edition_id' => $editionId,
        'status' => 'draft',
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>