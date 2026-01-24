<?php
/**
 * KandaNews Africa CMS - Save Page API
 * Handles saving edited pages
 */

header('Content-Type: application/json');

// Get POST data
$code = $_POST['code'] ?? '';
$filename = $_POST['filename'] ?? '';
$mode = $_POST['mode'] ?? 'new';

// Validate inputs
if (empty($code)) {
    echo json_encode(['success' => false, 'error' => 'No code provided']);
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

// Check if file already exists (for new mode)
if ($mode === 'new' && file_exists($targetFile)) {
    // Add timestamp to make unique
    $filename = pathinfo($filename, PATHINFO_FILENAME) . '-' . time() . '.html';
    $targetFile = $pagesDir . $filename;
}

// Save the file
try {
    $result = file_put_contents($targetFile, $code);
    
    if ($result === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to write file']);
        exit;
    }
    
    // Log the save action
    $logFile = __DIR__ . '/../logs/page-edits.log';
    $logEntry = date('Y-m-d H:i:s') . " - Saved: $filename (mode: $mode)\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'filename' => $filename,
        'path' => $targetFile,
        'size' => filesize($targetFile),
        'mode' => $mode
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>