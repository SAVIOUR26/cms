<?php
/**
 * KandaNews Africa CMS - Simple Generator
 * Assembles pages into a beautiful flipbook and creates ZIP
 */

define('KANDA_CMS', true);
require_once 'config.php';

requireLogin();
$user = getCurrentUser();

$page_title = 'Generate Edition';
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .generator-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 800px;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .edition-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #666;
        }
        
        .info-value {
            color: #333;
            font-weight: 500;
        }
        
        .progress-section {
            margin-bottom: 30px;
        }
        
        .progress-bar {
            width: 100%;
            height: 40px;
            background: #e9ecef;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            margin-bottom: 15px;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.5s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }
        
        .status-text {
            text-align: center;
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
            min-height: 28px;
        }
        
        .log-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            max-height: 300px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.8;
        }
        
        .log-entry {
            padding: 5px 0;
            color: #333;
        }
        
        .log-entry.success {
            color: #28a745;
        }
        
        .log-entry.error {
            color: #dc3545;
        }
        
        .log-entry.info {
            color: #17a2b8;
        }
        
        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 14px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
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
            background: #6c757d;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="generator-container">
        <div class="header">
            <h1>🚀 Edition Generator</h1>
            <p>Creating your stunning flipbook edition</p>
        </div>
        
        <div class="content">
            <div class="edition-info" id="editionInfo">
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">Loading...</span>
                </div>
            </div>
            
            <div class="progress-section">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill">0%</div>
                </div>
                <div class="status-text" id="statusText">Ready to generate...</div>
            </div>
            
            <div class="log-container" id="logContainer">
                <div class="log-entry">Waiting to start generation...</div>
            </div>
            
            <div class="actions">
                <button id="btnGenerate" class="btn btn-primary" onclick="startGeneration()">
                    <i class="fas fa-rocket"></i> Start Generation
                </button>
                <a href="build-edition.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Builder
                </a>
                <a href="#" id="btnDownload" class="btn btn-success hidden">
                    <i class="fas fa-download"></i> Download ZIP
                </a>
            </div>
        </div>
    </div>
    
    <script>
        let editionData = null;
        
        // Load edition data from localStorage
        window.addEventListener('DOMContentLoaded', function() {
            const data = localStorage.getItem('editionData');
            if (!data) {
                alert('No edition data found. Please build an edition first.');
                window.location.href = 'build-edition.php';
                return;
            }
            
            editionData = JSON.parse(data);
            displayEditionInfo();
        });
        
        function displayEditionInfo() {
            const infoHtml = `
                <div class="info-row">
                    <span class="info-label">Title:</span>
                    <span class="info-value">${editionData.title}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Date:</span>
                    <span class="info-value">${new Date(editionData.date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'})}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Pages:</span>
                    <span class="info-value">${editionData.pages.length} pages</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Theme:</span>
                    <span class="info-value">${editionData.theme || 'N/A'}</span>
                </div>
            `;
            document.getElementById('editionInfo').innerHTML = infoHtml;
        }
        
        function log(message, type = 'info') {
            const container = document.getElementById('logContainer');
            const entry = document.createElement('div');
            entry.className = `log-entry ${type}`;
            entry.textContent = `[${new Date().toLocaleTimeString()}] ${message}`;
            container.appendChild(entry);
            container.scrollTop = container.scrollHeight;
        }
        
        function setProgress(percent, text) {
            document.getElementById('progressFill').style.width = percent + '%';
            document.getElementById('progressFill').textContent = percent + '%';
            document.getElementById('statusText').textContent = text;
        }
        
        async function startGeneration() {
            const btn = document.getElementById('btnGenerate');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            
            try {
                // Step 1: Initialize
                setProgress(10, '📋 Initializing generation...');
                log('Starting edition generation', 'info');
                await sleep(500);
                
                // Step 2: Load pages
                setProgress(30, '📄 Loading page templates...');
                log(`Loading ${editionData.pages.length} pages`, 'info');
                await sleep(800);
                
                // Step 3: Generate flipbook
                setProgress(50, '🎨 Building flipbook structure...');
                log('Creating flipbook HTML...', 'info');
                await sleep(1000);
                
                // Step 4: Call backend to generate
                setProgress(70, '🔧 Generating files...');
                log('Calling backend generator...', 'info');
                
                const response = await fetch('api/generate-edition.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(editionData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    setProgress(90, '✅ Finalizing...');
                    log('✅ Edition generated successfully!', 'success');
                    log(`Output: ${result.output_path}`, 'success');
                    log(`ZIP: ${result.zip_path}`, 'success');
                    log(`Total pages: ${result.total_pages}`, 'info');
                    await sleep(500);
                    
                    // Step 5: Complete
                    setProgress(100, '🎉 Edition Ready!');
                    log('═══════════════════════════════', 'success');
                    log('✨ GENERATION COMPLETE!', 'success');
                    log('Edition is ready for download', 'success');
                    log('═══════════════════════════════', 'success');
                    
                    // Show download button
                    const downloadBtn = document.getElementById('btnDownload');
                    downloadBtn.classList.remove('hidden');
                    downloadBtn.href = result.zip_url;
                    
                    btn.innerHTML = '<i class="fas fa-check"></i> Complete!';
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-success');
                    
                } else {
                    throw new Error(result.error || 'Generation failed');
                }
                
            } catch (error) {
                log('❌ Error: ' + error.message, 'error');
                setProgress(0, '❌ Generation failed');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-redo"></i> Retry Generation';
            }
        }
        
        function sleep(ms) {
            return new Promise(resolve => setTimeout(resolve, ms));
        }
        
        // Auto-start after 2 seconds
        setTimeout(() => {
            log('Auto-starting generation in 2 seconds...', 'info');
            setTimeout(() => startGeneration(), 2000);
        }, 100);
    </script>
</body>
</html>