<?php
/**
 * KandaNews Africa CMS - Page Editor
 * Edit page templates with live preview
 */

define('KANDA_CMS', true);
require_once 'config.php';

requireLogin();
$user = getCurrentUser();

// Get page to edit
$pageFile = $_GET['file'] ?? '';
$pagesDir = __DIR__ . '/templates/pages/';
$pageContent = '';
$pageExists = false;

if ($pageFile && file_exists($pagesDir . $pageFile)) {
    $pageContent = file_get_contents($pagesDir . $pageFile);
    $pageExists = true;
}

$page_title = 'Page Editor';
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
            overflow-x: hidden;
        }
        
        .header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .header-content {
            max-width: 100%;
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
            font-size: 20px;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
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
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-secondary {
            background: #f1f3f5;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
        }
        
        .editor-container {
            display: grid;
            grid-template-columns: 350px 1fr 450px;
            height: calc(100vh - 70px);
            gap: 0;
        }
        
        /* LEFT PANEL - TOOLS */
        .tools-panel {
            background: white;
            border-right: 1px solid #e9ecef;
            overflow-y: auto;
            padding: 20px;
        }
        
        .panel-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .tool-section {
            margin-bottom: 25px;
        }
        
        .tool-section-title {
            font-size: 13px;
            font-weight: 700;
            color: #667eea;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 6px;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
            font-family: monospace;
        }
        
        .color-picker {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-top: 8px;
        }
        
        .color-option {
            width: 100%;
            height: 40px;
            border-radius: 6px;
            border: 2px solid #e9ecef;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .color-option:hover {
            transform: scale(1.1);
            border-color: #667eea;
        }
        
        .quick-action {
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 6px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
        }
        
        .quick-action:hover {
            background: #e9ecef;
        }
        
        /* CENTER PANEL - CODE EDITOR */
        .code-panel {
            background: #1e1e1e;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .code-header {
            background: #2d2d2d;
            padding: 12px 20px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .code-title {
            font-size: 14px;
            font-weight: 600;
        }
        
        .code-actions {
            display: flex;
            gap: 10px;
        }
        
        .code-btn {
            background: #3e3e3e;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .code-btn:hover {
            background: #4e4e4e;
        }
        
        .code-editor {
            flex: 1;
            overflow: auto;
        }
        
        #codeTextarea {
            width: 100%;
            height: 100%;
            background: #1e1e1e;
            color: #d4d4d4;
            border: none;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.6;
            resize: none;
            outline: none;
        }
        
        /* RIGHT PANEL - PREVIEW */
        .preview-panel {
            background: white;
            border-left: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
        }
        
        .preview-header {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .preview-title {
            font-size: 14px;
            font-weight: 700;
        }
        
        .device-toggle {
            display: flex;
            gap: 5px;
        }
        
        .device-btn {
            padding: 6px 10px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s;
        }
        
        .device-btn.active {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .preview-container {
            flex: 1;
            padding: 20px;
            background: #f8f9fa;
            overflow: auto;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        
        .preview-frame-wrapper {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s;
        }
        
        .preview-frame-wrapper.desktop {
            width: 100%;
            max-width: 461px;
        }
        
        .preview-frame-wrapper.mobile {
            width: 375px;
        }
        
        #previewFrame {
            width: 100%;
            height: 600px;
            border: none;
            display: block;
        }
        
        /* SAVE MODAL */
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
            max-width: 500px;
            padding: 30px;
        }
        
        .modal-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .save-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .save-option {
            padding: 15px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .save-option:hover {
            border-color: #667eea;
            background: #f8f9fa;
        }
        
        .save-option.selected {
            border-color: #667eea;
            background: #f0f3ff;
        }
        
        .option-title {
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .option-desc {
            font-size: 13px;
            color: #666;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .notification {
            position: fixed;
            top: 90px;
            right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            display: none;
            align-items: center;
            gap: 10px;
            z-index: 1001;
        }
        
        .notification.show {
            display: flex;
            animation: slideIn 0.3s;
        }
        
        .notification.success {
            border-left: 4px solid #28a745;
        }
        
        .notification.error {
            border-left: 4px solid #dc3545;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <a href="pages-library.php" class="logo">
                <span>✏️</span>
                <span>Page Editor</span>
            </a>
            <div class="header-actions">
                <button onclick="autoPreview()" class="btn btn-secondary">
                    <i class="fas fa-eye"></i> Preview
                </button>
                <button onclick="openSaveModal()" class="btn btn-success">
                    <i class="fas fa-save"></i> Save Page
                </button>
                <a href="pages-library.php" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Close
                </a>
            </div>
        </div>
    </div>
    
    <div class="editor-container">
        <!-- LEFT: TOOLS PANEL -->
        <div class="tools-panel">
            <div class="panel-title">
                <i class="fas fa-tools"></i>
                Quick Edit Tools
            </div>
            
            <div class="tool-section">
                <div class="tool-section-title">📝 Text Content</div>
                
                <div class="form-group">
                    <label>Find Text</label>
                    <input type="text" id="findText" placeholder="Enter text to find...">
                </div>
                
                <div class="form-group">
                    <label>Replace With</label>
                    <input type="text" id="replaceText" placeholder="Enter replacement...">
                </div>
                
                <button onclick="findReplace()" class="btn btn-primary" style="width: 100%; margin-bottom: 10px;">
                    <i class="fas fa-exchange-alt"></i> Find & Replace
                </button>
            </div>
            
            <div class="tool-section">
                <div class="tool-section-title">🎨 Colors</div>
                
                <div class="form-group">
                    <label>Background Color</label>
                    <div class="color-picker">
                        <div class="color-option" style="background: #667eea;" onclick="changeColor('background', '#667eea')"></div>
                        <div class="color-option" style="background: #764ba2;" onclick="changeColor('background', '#764ba2')"></div>
                        <div class="color-option" style="background: #ff6b6b;" onclick="changeColor('background', '#ff6b6b')"></div>
                        <div class="color-option" style="background: #28a745;" onclick="changeColor('background', '#28a745')"></div>
                        <div class="color-option" style="background: #17a2b8;" onclick="changeColor('background', '#17a2b8')"></div>
                        <div class="color-option" style="background: #ffc107;" onclick="changeColor('background', '#ffc107')"></div>
                        <div class="color-option" style="background: #6c757d;" onclick="changeColor('background', '#6c757d')"></div>
                        <div class="color-option" style="background: #ffffff; border-color: #333;" onclick="changeColor('background', '#ffffff')"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Custom Color</label>
                    <input type="color" id="customColor" style="height: 40px;">
                    <button onclick="applyCustomColor()" class="btn btn-secondary" style="width: 100%; margin-top: 8px;">Apply Custom</button>
                </div>
            </div>
            
            <div class="tool-section">
                <div class="tool-section-title">🖼️ Images & Media</div>
                
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" id="imageUrl" placeholder="https://example.com/image.jpg">
                    <button onclick="replaceImage()" class="btn btn-secondary" style="width: 100%; margin-top: 8px;">Replace Image</button>
                </div>
                
                <div class="form-group">
                    <label>Video Embed URL</label>
                    <input type="text" id="videoUrl" placeholder="https://youtube.com/embed/...">
                    <button onclick="replaceVideo()" class="btn btn-secondary" style="width: 100%; margin-top: 8px;">Replace Video</button>
                </div>
            </div>
            
            <div class="tool-section">
                <div class="tool-section-title">⚡ Quick Actions</div>
                
                <div class="quick-action" onclick="formatCode()">
                    <i class="fas fa-indent"></i>
                    Format Code
                </div>
                
                <div class="quick-action" onclick="addEmoji()">
                    <i class="fas fa-smile"></i>
                    Add Emoji
                </div>
                
                <div class="quick-action" onclick="minifyCode()">
                    <i class="fas fa-compress"></i>
                    Minify Code
                </div>
            </div>
        </div>
        
        <!-- CENTER: CODE EDITOR -->
        <div class="code-panel">
            <div class="code-header">
                <div class="code-title">
                    📄 <?php echo htmlspecialchars($pageFile ?: 'New Page'); ?>
                </div>
                <div class="code-actions">
                    <button class="code-btn" onclick="undoEdit()">
                        <i class="fas fa-undo"></i> Undo
                    </button>
                    <button class="code-btn" onclick="selectAllCode()">
                        <i class="fas fa-check-square"></i> Select All
                    </button>
                </div>
            </div>
            <div class="code-editor">
                <textarea id="codeTextarea" spellcheck="false"><?php echo htmlspecialchars($pageContent); ?></textarea>
            </div>
        </div>
        
        <!-- RIGHT: PREVIEW PANEL -->
        <div class="preview-panel">
            <div class="preview-header">
                <div class="preview-title">👁️ Live Preview</div>
                <div class="device-toggle">
                    <button class="device-btn active" onclick="setDevice('desktop')">
                        <i class="fas fa-desktop"></i>
                    </button>
                    <button class="device-btn" onclick="setDevice('mobile')">
                        <i class="fas fa-mobile-alt"></i>
                    </button>
                </div>
            </div>
            <div class="preview-container">
                <div class="preview-frame-wrapper desktop" id="frameWrapper">
                    <iframe id="previewFrame"></iframe>
                </div>
            </div>
        </div>
    </div>
    
    <!-- SAVE MODAL -->
    <div id="saveModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">💾 Save Your Page</div>
            
            <div class="save-options">
                <div class="save-option selected" data-mode="new">
                    <div class="option-title">✨ Save as New Page</div>
                    <div class="option-desc">Create a new version without changing the original</div>
                </div>
                
                <div class="save-option" data-mode="update">
                    <div class="option-title">♻️ Update Original</div>
                    <div class="option-desc">Overwrite the existing page file</div>
                </div>
            </div>
            
            <div class="form-group" id="newNameGroup">
                <label>New File Name</label>
                <input type="text" id="newFileName" placeholder="my-edited-page.html">
                <small style="display: block; margin-top: 5px; color: #666;">Will be saved as: templates/pages/your-name.html</small>
            </div>
            
            <div class="modal-actions">
                <button onclick="closeSaveModal()" class="btn btn-secondary">Cancel</button>
                <button onclick="savePage()" class="btn btn-success">
                    <i class="fas fa-save"></i> Save Page
                </button>
            </div>
        </div>
    </div>
    
    <!-- NOTIFICATION -->
    <div id="notification" class="notification">
        <i class="fas fa-check-circle"></i>
        <span id="notificationText"></span>
    </div>
    
    <script>
        const codeTextarea = document.getElementById('codeTextarea');
        const previewFrame = document.getElementById('previewFrame');
        const originalFileName = <?php echo json_encode($pageFile); ?>;
        
        let saveMode = 'new';
        let codeHistory = [codeTextarea.value];
        let historyIndex = 0;
        
        // Auto-preview on load
        window.addEventListener('load', () => {
            autoPreview();
        });
        
        // Auto-preview on code change (debounced)
        let previewTimeout;
        codeTextarea.addEventListener('input', () => {
            clearTimeout(previewTimeout);
            previewTimeout = setTimeout(() => {
                autoPreview();
                saveHistory();
            }, 1000);
        });
        
        function autoPreview() {
            const code = codeTextarea.value;
            const blob = new Blob([code], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            previewFrame.src = url;
        }
        
        function saveHistory() {
            historyIndex++;
            codeHistory[historyIndex] = codeTextarea.value;
            codeHistory = codeHistory.slice(0, historyIndex + 1);
        }
        
        function undoEdit() {
            if (historyIndex > 0) {
                historyIndex--;
                codeTextarea.value = codeHistory[historyIndex];
                autoPreview();
            }
        }
        
        function selectAllCode() {
            codeTextarea.select();
        }
        
        function findReplace() {
            const find = document.getElementById('findText').value;
            const replace = document.getElementById('replaceText').value;
            
            if (!find) {
                showNotification('Please enter text to find', 'error');
                return;
            }
            
            const code = codeTextarea.value;
            const regex = new RegExp(find, 'gi');
            const newCode = code.replace(regex, replace);
            
            if (code === newCode) {
                showNotification('Text not found', 'error');
            } else {
                codeTextarea.value = newCode;
                autoPreview();
                saveHistory();
                showNotification('Text replaced successfully!', 'success');
            }
        }
        
        function changeColor(type, color) {
            const code = codeTextarea.value;
            
            if (type === 'background') {
                // Replace gradient or solid background colors
                const newCode = code.replace(
                    /background:\s*(?:linear-gradient\([^)]+\)|#[0-9a-f]{3,6}|[a-z]+)/gi,
                    `background: ${color}`
                );
                codeTextarea.value = newCode;
            }
            
            autoPreview();
            saveHistory();
            showNotification('Color changed!', 'success');
        }
        
        function applyCustomColor() {
            const color = document.getElementById('customColor').value;
            changeColor('background', color);
        }
        
        function replaceImage() {
            const url = document.getElementById('imageUrl').value;
            if (!url) {
                showNotification('Please enter an image URL', 'error');
                return;
            }
            
            const code = codeTextarea.value;
            // Replace first image src
            const newCode = code.replace(/src="[^"]*"/i, `src="${url}"`);
            codeTextarea.value = newCode;
            autoPreview();
            saveHistory();
            showNotification('Image replaced!', 'success');
        }
        
        function replaceVideo() {
            const url = document.getElementById('videoUrl').value;
            if (!url) {
                showNotification('Please enter a video URL', 'error');
                return;
            }
            
            const code = codeTextarea.value;
            // Replace iframe src
            const newCode = code.replace(/<iframe[^>]*src="[^"]*"/i, `<iframe src="${url}"`);
            codeTextarea.value = newCode;
            autoPreview();
            saveHistory();
            showNotification('Video replaced!', 'success');
        }
        
        function formatCode() {
            // Basic HTML formatting
            let code = codeTextarea.value;
            code = code.replace(/></g, '>\n<');
            codeTextarea.value = code;
            showNotification('Code formatted!', 'success');
        }
        
        function addEmoji() {
            const emojis = ['😊', '🎉', '🚀', '💪', '✨', '👍', '📰', '🔥', '💯', '🌟'];
            const emoji = prompt('Choose emoji:\n' + emojis.join(' ')) || emojis[0];
            codeTextarea.value += emoji;
            showNotification('Emoji added!', 'success');
        }
        
        function minifyCode() {
            let code = codeTextarea.value;
            code = code.replace(/\s+/g, ' ').trim();
            codeTextarea.value = code;
            showNotification('Code minified!', 'success');
        }
        
        function setDevice(device) {
            const wrapper = document.getElementById('frameWrapper');
            const buttons = document.querySelectorAll('.device-btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.closest('.device-btn').classList.add('active');
            
            wrapper.className = 'preview-frame-wrapper ' + device;
        }
        
        function openSaveModal() {
            document.getElementById('saveModal').classList.add('active');
            
            // Suggest filename
            if (originalFileName) {
                const baseName = originalFileName.replace('.html', '');
                document.getElementById('newFileName').value = baseName + '-edited.html';
            } else {
                document.getElementById('newFileName').value = 'my-new-page.html';
            }
        }
        
        function closeSaveModal() {
            document.getElementById('saveModal').classList.remove('active');
        }
        
        // Save option selection
        document.querySelectorAll('.save-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.save-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                saveMode = this.dataset.mode;
                
                const newNameGroup = document.getElementById('newNameGroup');
                if (saveMode === 'new') {
                    newNameGroup.style.display = 'block';
                } else {
                    newNameGroup.style.display = 'none';
                }
            });
        });
        
        async function savePage() {
            const code = codeTextarea.value;
            let fileName;
            
            if (saveMode === 'new') {
                fileName = document.getElementById('newFileName').value;
                if (!fileName) {
                    showNotification('Please enter a file name', 'error');
                    return;
                }
                if (!fileName.endsWith('.html')) {
                    fileName += '.html';
                }
            } else {
                fileName = originalFileName;
            }
            
            // Send to server
            const formData = new FormData();
            formData.append('code', code);
            formData.append('filename', fileName);
            formData.append('mode', saveMode);
            
            try {
                const response = await fetch('api/save-page.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('✅ Page saved successfully!', 'success');
                    closeSaveModal();
                    
                    // Redirect to pages library after 1 second
                    setTimeout(() => {
                        window.location.href = 'pages-library.php';
                    }, 1500);
                } else {
                    showNotification('❌ Error: ' + result.error, 'error');
                }
            } catch (error) {
                showNotification('❌ Failed to save page', 'error');
                console.error(error);
            }
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const text = document.getElementById('notificationText');
            
            text.textContent = message;
            notification.className = 'notification show ' + type;
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>