<?php
/**
 * KandaNews Africa CMS - Save Visual Page API
 * Saves pages created with the visual builder
 */

header('Content-Type: application/json');

// Get POST data
$html = $_POST['html'] ?? '';
$filename = $_POST['filename'] ?? '';
$category = $_POST['category'] ?? 'custom';

// Validate inputs
if (empty($html)) {
    echo json_encode(['success' => false, 'error' => 'No HTML provided']);
    exit;
}

if (empty($filename)) {
    echo json_encode(['success' => false, 'error' => 'No filename provided']);
    exit;
}

// Sanitize filename
$filename = basename($filename);
if (!preg_match('/^[a-z0-9_-]+\.html$/i', $filename)) {
    echo json_encode(['success' => false, 'error' => 'Invalid filename format']);
    exit;
}

// Set target directory
$pagesDir = __DIR__ . '/../templates/pages/';
$targetFile = $pagesDir . $filename;

// Check if directory exists
if (!is_dir($pagesDir)) {
    if (!mkdir($pagesDir, 0777, true)) {
        echo json_encode(['success' => false, 'error' => 'Could not create templates directory']);
        exit;
    }
}

// Check if file already exists
if (file_exists($targetFile)) {
    // Add timestamp to make unique
    $filename = pathinfo($filename, PATHINFO_FILENAME) . '-' . time() . '.html';
    $targetFile = $pagesDir . $filename;
}

// Save the file
try {
    $result = file_put_contents($targetFile, $html);
    
    if ($result === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to write file']);
        exit;
    }
    
    // Log the save action
    $logFile = __DIR__ . '/../logs/visual-pages.log';
    $logEntry = date('Y-m-d H:i:s') . " - Saved: $filename (category: $category)\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'filename' => $filename,
        'path' => $targetFile,
        'size' => filesize($targetFile),
        'category' => $category
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>