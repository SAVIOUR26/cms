<?php
/**
 * KandaNews Africa CMS - ULTIMATE Dashboard
 * The Future of News - October 2025
 * - KandaNews brand colors
 * - Modern design
 * - Delete edition functionality
 * - Beautiful stats and tiles
 */

define('KANDA_CMS', true);
require_once 'config.php';

requireLogin();
$user = getCurrentUser();

// Handle edition deletion
if (isset($_POST['delete_edition'])) {
    $editionName = $_POST['edition_name'];
    $editionPath = __DIR__ . '/output/' . $editionName;
    
    if (is_dir($editionPath)) {
        // Delete directory recursively
        function deleteDirectory($dir) {
            if (!file_exists($dir)) return true;
            if (!is_dir($dir)) return unlink($dir);
            foreach (scandir($dir) as $item) {
                if ($item == '.' || $item == '..') continue;
                if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) return false;
            }
            return rmdir($dir);
        }
        
        if (deleteDirectory($editionPath)) {
            $success_message = "Edition deleted successfully!";
        } else {
            $error_message = "Failed to delete edition.";
        }
    }
}

// Get statistics from file system
$pagesDir = __DIR__ . '/templates/pages/';
$outputDir = __DIR__ . '/output/';

// Count pages
$totalPages = 0;
if (is_dir($pagesDir)) {
    $files = glob($pagesDir . '*.html');
    $totalPages = count($files);
}

// Count editions
$totalEditions = 0;
$recentEditions = [];
if (is_dir($outputDir)) {
    $editions = array_diff(scandir($outputDir), array('.', '..'));
    foreach ($editions as $edition) {
        $editionPath = $outputDir . $edition;
        if (is_dir($editionPath)) {
            $totalEditions++;
            $zipFile = glob($editionPath . '/*.zip');
            $indexFile = $editionPath . '/index.html';
            $coverFile = $editionPath . '/cover.png';
            
            $recentEditions[] = [
                'name' => $edition,
                'date' => $edition,
                'path' => $editionPath,
                'zip' => !empty($zipFile) ? basename($zipFile[0]) : null,
                'has_index' => file_exists($indexFile),
                'has_cover' => file_exists($coverFile),
                'modified' => filemtime($editionPath)
            ];
        }
    }
}

// Sort by most recent
usort($recentEditions, function($a, $b) {
    return $b['modified'] - $a['modified'];
});

// Get only last 5 for dashboard
$recentEditionsDisplay = array_slice($recentEditions, 0, 5);

$page_title = 'Dashboard';
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
            background: linear-gradient(135deg, #1e2b42 0%, #2a3f5f 100%);
            color: #333;
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
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
            gap: 15px;
        }
        
        .logo img {
            height: 45px;
        }
        
        .logo-text-wrapper {
            display: flex;
            flex-direction: column;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: #1e2b42;
        }
        
        .logo-tagline {
            font-size: 11px;
            color: #f05a1a;
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: #1e2b42;
        }
        
        .user-role {
            font-size: 12px;
            color: #f05a1a;
            font-weight: 600;
        }
        
        .btn {
            padding: 10px 20px;
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
        
        .btn-secondary {
            background: #f1f2f3;
            color: #1e2b42;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        /* Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        /* Welcome Section */
        .welcome-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(135deg, #f05a1a 0%, #ff7a3d 100%);
        }
        
        .welcome-section h1 {
            font-size: 32px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #1e2b42;
        }
        
        .welcome-section p {
            color: #666;
            font-size: 16px;
        }
        
        /* Stats Bar */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #f05a1a 0%, #ff7a3d 100%);
            transform: scaleX(0);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0,0,0,0.25);
        }
        
        .stat-card:hover::before {
            transform: scaleX(1);
        }
        
        .stat-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #f05a1a;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            font-weight: 600;
        }
        
        /* Tiles Grid */
        .tiles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .tile {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            display: block;
        }
        
        .tile::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #f05a1a 0%, #ff7a3d 100%);
            transform: scaleX(0);
            transition: transform 0.3s;
        }
        
        .tile:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 50px rgba(0,0,0,0.25);
        }
        
        .tile:hover::before {
            transform: scaleX(1);
        }
        
        .tile-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .tile-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #1e2b42;
        }
        
        .tile-description {
            font-size: 14px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .tile-meta {
            font-size: 13px;
            color: #f05a1a;
            font-weight: 600;
        }
        
        /* Section Title */
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 700;
            color: white;
        }
        
        .section-link {
            font-size: 14px;
            color: #f05a1a;
            text-decoration: none;
            font-weight: 600;
            background: white;
            padding: 8px 16px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .section-link:hover {
            background: #fff5f0;
            transform: translateY(-2px);
        }
        
        /* Editions List */
        .editions-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .edition-item {
            padding: 20px 25px;
            border-bottom: 1px solid #f1f2f3;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        
        .edition-item:last-child {
            border-bottom: none;
        }
        
        .edition-item:hover {
            background: #fff5f0;
        }
        
        .edition-info {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .edition-cover {
            width: 60px;
            height: 90px;
            background: #f1f2f3;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            overflow: hidden;
        }
        
        .edition-cover img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .edition-details {
            flex: 1;
        }
        
        .edition-name {
            font-size: 16px;
            font-weight: 700;
            color: #1e2b42;
            margin-bottom: 5px;
        }
        
        .edition-meta {
            font-size: 13px;
            color: #666;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .edition-meta i {
            color: #f05a1a;
        }
        
        .edition-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .btn-small {
            padding: 8px 16px;
            font-size: 13px;
            border-radius: 6px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #f05a1a 0%, #ff7a3d 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(240, 90, 26, 0.4);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state p {
            margin-bottom: 20px;
        }
        
        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(5px);
        }
        
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .modal-header h2 {
            font-size: 22px;
            color: #1e2b42;
        }
        
        .modal-icon {
            font-size: 36px;
        }
        
        .modal-body {
            margin-bottom: 25px;
            color: #666;
            line-height: 1.6;
        }
        
        .modal-body strong {
            color: #dc3545;
        }
        
        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .tiles-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-bar {
                grid-template-columns: 1fr;
            }
            
            .edition-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .edition-info {
                width: 100%;
            }
            
            .edition-actions {
                width: 100%;
                justify-content: stretch;
            }
            
            .edition-actions .btn {
                flex: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-icon">⚠️</span>
                <h2>Delete Edition?</h2>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the edition <strong id="editionToDelete"></strong>?</p>
                <p>This action cannot be undone. All files including the ZIP will be permanently deleted.</p>
            </div>
            <div class="modal-footer">
                <button onclick="closeDeleteModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button onclick="confirmDelete()" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Edition
                </button>
            </div>
        </div>
    </div>

    <div class="header">
        <div class="header-content">
            <div class="logo">
                <img src="assets/appLogoIcon.png" alt="KandaNews Africa" onerror="this.style.display='none'">
                <div class="logo-text-wrapper">
                    <div class="logo-text">KandaNews Africa</div>
                    <div class="logo-tagline">CMS DASHBOARD</div>
                </div>
            </div>
            
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></div>
                    <div class="user-role"><?php echo ucfirst($user['role']); ?></div>
                </div>
                <a href="?logout=1" class="btn btn-secondary">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="welcome-section">
            <h1>
                <span>👋</span>
                Welcome back, <?php echo htmlspecialchars(explode(' ', $user['full_name'] ?? $user['username'])[0]); ?>!
            </h1>
            <p>🚀 The Future of News - Let's create something amazing today</p>
        </div>
        
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon">📄</div>
                <div class="stat-value"><?php echo $totalPages; ?></div>
                <div class="stat-label">Page Templates</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📰</div>
                <div class="stat-value"><?php echo $totalEditions; ?></div>
                <div class="stat-label">Total Editions</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🚀</div>
                <div class="stat-value"><?php echo !empty($recentEditions) ? date('M j', $recentEditions[0]['modified']) : 'N/A'; ?></div>
                <div class="stat-label">Last Published</div>
            </div>
        </div>
        
        <div class="tiles-grid">
            <a href="visual-page-builder.php" class="tile">
                <div class="tile-icon">🎨</div>
                <div class="tile-title">Visual Page Builder</div>
                <div class="tile-description">Build pages from scratch with drag & drop widgets. No coding required!</div>
                <div class="tile-meta">Create with widgets →</div>
            </a>
            
            <a href="pages-library.php" class="tile">
                <div class="tile-icon">📄</div>
                <div class="tile-title">Pages Library</div>
                <div class="tile-description">Browse and manage your HTML page templates. View, preview, and organize all your pages.</div>
                <div class="tile-meta"><?php echo $totalPages; ?> pages available →</div>
            </a>
            
            <a href="build-edition.php" class="tile">
                <div class="tile-icon">📰</div>
                <div class="tile-title">Build Edition</div>
                <div class="tile-description">Create new editions by arranging your pages. Drag, drop, and customize your flipbook.</div>
                <div class="tile-meta">Ready to create →</div>
            </a>
            
            <a href="editions-list.php" class="tile">
                <div class="tile-icon">📚</div>
                <div class="tile-title">Editions List</div>
                <div class="tile-description">View all your generated editions. Download ZIPs and manage your publications.</div>
                <div class="tile-meta"><?php echo $totalEditions; ?> editions created →</div>
            </a>
        </div>
        
        <div class="section-title">
            <span>📰 Recent Editions</span>
            <a href="editions-list.php" class="section-link">View All →</a>
        </div>
        
        <div class="editions-list">
            <?php if (empty($recentEditionsDisplay)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <p><strong>No editions yet!</strong></p>
                    <p>Create your first edition to get started.</p>
                    <a href="build-edition.php" class="btn btn-primary btn-small">
                        <i class="fas fa-plus"></i> Create First Edition
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($recentEditionsDisplay as $edition): ?>
                    <div class="edition-item">
                        <div class="edition-info">
                            <div class="edition-cover">
                                <?php if ($edition['has_cover']): ?>
                                    <img src="output/<?php echo htmlspecialchars($edition['name']); ?>/cover.png" alt="Cover">
                                <?php else: ?>
                                    📰
                                <?php endif; ?>
                            </div>
                            <div class="edition-details">
                                <div class="edition-name">
                                    Edition - <?php echo htmlspecialchars($edition['name']); ?>
                                </div>
                                <div class="edition-meta">
                                    <span><i class="fas fa-calendar"></i> <?php echo date('F j, Y', $edition['modified']); ?></span>
                                    <?php if ($edition['zip']): ?>
                                        <span><i class="fas fa-file-archive"></i> <?php echo htmlspecialchars($edition['zip']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="edition-actions">
                            <?php if ($edition['has_index']): ?>
                                <a href="output/<?php echo htmlspecialchars($edition['name']); ?>/index.html" target="_blank" class="btn btn-primary btn-small">
                                    <i class="fas fa-eye"></i> Preview
                                </a>
                            <?php endif; ?>
                            <?php if ($edition['zip']): ?>
                                <a href="output/<?php echo htmlspecialchars($edition['name']); ?>/<?php echo htmlspecialchars($edition['zip']); ?>" class="btn btn-success btn-small">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            <?php endif; ?>
                            <button onclick="openDeleteModal('<?php echo htmlspecialchars($edition['name'], ENT_QUOTES); ?>')" class="btn btn-danger btn-small">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="delete_edition" value="1">
        <input type="hidden" name="edition_name" id="editionNameInput">
    </form>
    
    <script>
        let editionToDelete = '';
        
        function openDeleteModal(editionName) {
            editionToDelete = editionName;
            document.getElementById('editionToDelete').textContent = editionName;
            document.getElementById('deleteModal').classList.add('show');
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
            editionToDelete = '';
        }
        
        function confirmDelete() {
            if (editionToDelete) {
                document.getElementById('editionNameInput').value = editionToDelete;
                document.getElementById('deleteForm').submit();
            }
        }
        
        // Close modal on outside click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
        
        // Close modal on ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteModal();
            }
        });
        
        // Handle logout
        <?php if (isset($_GET['logout'])): ?>
            sessionStorage.clear();
            localStorage.clear();
        <?php endif; ?>
        
        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>