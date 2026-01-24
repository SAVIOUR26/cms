<?php
define('KANDA_CMS', true);
require_once 'config.php';

requireLogin();

$edition_id = $_GET['edition_id'] ?? 0;

if (!$edition_id) {
    die("No edition ID");
}

try {
    $db = getDatabase();
    $stmt = $db->prepare("SELECT * FROM editions WHERE id = ?");
    $stmt->execute([$edition_id]);
    $edition = $stmt->fetch();
    
    if (!$edition) {
        die("Edition not found");
    }
    
    // Get ZIP path
    $zipPath = $edition['file_path'];
    
    if (!$zipPath || !file_exists($zipPath)) {
        // Generate path from date
        $dateFolder = date('Y-m-d', strtotime($edition['edition_date']));
        $zipPath = OUTPUT_PATH . "edition-{$dateFolder}.zip";
    }
    
    if (file_exists($zipPath)) {
        // Download ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . basename($zipPath) . '"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);
        exit;
    } else {
        // Show output folder instead
        $outputFolder = OUTPUT_PATH . date('Y-m-d', strtotime($edition['edition_date']));
        $indexFile = $outputFolder . '/index.html';
        
        if (file_exists($indexFile)) {
            // Redirect to view edition
            $relativePath = 'output/' . date('Y-m-d', strtotime($edition['edition_date'])) . '/index.html';
            header("Location: {$relativePath}");
            exit;
        }
    }
    
    die("Edition files not found. Please regenerate.");
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>