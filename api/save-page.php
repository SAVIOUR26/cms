<?php
/**
 * KandaNews Africa CMS - Save Page API
 * Handles saving edited pages with subfolder support.
 * Accepts filename as:
 *   "my-page.html"            → templates/pages/my-page.html
 *   "university/my-page.html" → templates/pages/university/my-page.html
 */

header('Content-Type: application/json');

$code     = $_POST['code']     ?? '';
$filename = trim($_POST['filename'] ?? '');
$folder   = trim($_POST['folder']   ?? '');   // optional explicit folder
$mode     = $_POST['mode']     ?? 'new';

if (empty($code)) {
    echo json_encode(['success' => false, 'error' => 'No code provided']);
    exit;
}
if (empty($filename)) {
    echo json_encode(['success' => false, 'error' => 'No filename provided']);
    exit;
}

// ── Parse optional subfolder from filename (e.g. "university/page.html") ──
$subfolder = '';
if (strpos($filename, '/') !== false) {
    $parts     = explode('/', $filename, 2);
    $subfolder = $parts[0];
    $filename  = $parts[1];
}
// Explicit folder param overrides
if ($folder !== '') {
    $subfolder = $folder;
}

// ── Validate subfolder name (letters, numbers, hyphens, underscores only) ──
if ($subfolder !== '' && !preg_match('/^[a-z0-9_-]+$/i', $subfolder)) {
    echo json_encode(['success' => false, 'error' => 'Invalid folder name — use letters, numbers, hyphens, underscores only']);
    exit;
}

// ── Validate filename ────────────────────────────────────────────────────
$filename = basename($filename);
if (!preg_match('/^[a-z0-9_-]+\.html$/i', $filename)) {
    echo json_encode(['success' => false, 'error' => 'Invalid filename — use letters, numbers, hyphens, underscores, .html only']);
    exit;
}

// ── Build target path ────────────────────────────────────────────────────
$pagesBase  = realpath(__DIR__ . '/../templates') . '/pages/';
$targetDir  = $subfolder !== '' ? $pagesBase . $subfolder . '/' : $pagesBase;
$relPath    = $subfolder !== '' ? $subfolder . '/' . $filename : $filename;
$targetFile = $targetDir . $filename;

// Security: ensure target is still inside pagesBase
if (!str_starts_with(realpath($targetDir . '.') ?: $targetDir, $pagesBase)) {
    echo json_encode(['success' => false, 'error' => 'Invalid path — directory traversal not allowed']);
    exit;
}

// ── Create directory if needed ───────────────────────────────────────────
if (!is_dir($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        echo json_encode(['success' => false, 'error' => 'Could not create folder: ' . $subfolder]);
        exit;
    }
}

// ── Avoid overwriting in "new" mode ─────────────────────────────────────
if ($mode === 'new' && file_exists($targetFile)) {
    $filename   = pathinfo($filename, PATHINFO_FILENAME) . '-' . time() . '.html';
    $targetFile = $targetDir . $filename;
    $relPath    = $subfolder !== '' ? $subfolder . '/' . $filename : $filename;
}

// ── Save ─────────────────────────────────────────────────────────────────
try {
    $result = file_put_contents($targetFile, $code);
    if ($result === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to write file']);
        exit;
    }

    $logFile  = __DIR__ . '/../logs/page-edits.log';
    $logEntry = date('Y-m-d H:i:s') . " - Saved: $relPath (mode: $mode)\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND);

    echo json_encode([
        'success'  => true,
        'filename' => $filename,
        'folder'   => $subfolder ?: null,
        'rel_path' => $relPath,
        'size'     => filesize($targetFile),
        'mode'     => $mode,
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
