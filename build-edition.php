<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Build Edition - KandaNews Africa</title>
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
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        /* Header */
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .logo img {
            height: 50px;
        }
        
        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: #1e2b42;
        }
        
        .logo-tagline {
            font-size: 12px;
            color: #f05a1a;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* Dashboard Button */
        .dashboard-btn {
            background: linear-gradient(135deg, #1e2b42 0%, #2a3f5f 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(30, 43, 66, 0.3);
            white-space: nowrap;
        }

        .dashboard-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(30, 43, 66, 0.5);
        }

        .dashboard-btn i {
            font-size: 16px;
        }
        
        /* Stats */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .stat-card {
            background: #f1f2f3;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            min-width: 80px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #f05a1a;
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            font-weight: 600;
        }
        
        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }
        
        /* Page Selector */
        .page-selector {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e2b42;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: #f05a1a;
        }
        
        .available-pages {
            max-height: 440px;
            overflow-y: auto;
            border: 2px dashed #e0e0e0;
            border-radius: 10px;
            padding: 0;
            margin-bottom: 20px;
        }

        /* Folder tabs */
        .folder-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 12px 14px;
            border-bottom: 2px solid #f0f0f0;
            background: #fafafa;
            border-radius: 10px 10px 0 0;
            position: sticky;
            top: 0;
            z-index: 5;
        }
        .folder-tab {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1.5px solid #d1d5db;
            background: #fff;
            color: #555;
            transition: all .15s;
            user-select: none;
        }
        .folder-tab:hover { border-color: #f05a1a; color: #f05a1a; }
        .folder-tab.active { background: #f05a1a; border-color: #f05a1a; color: #fff; }

        /* Folder group header */
        .folder-group { display: none; padding: 8px 14px; }
        .folder-group.visible { display: block; }
        .folder-group-header {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: #f05a1a;
            padding: 4px 0 8px;
            border-bottom: 1px dashed #f0e0d8;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .folder-group-header .folder-count {
            background: #fff5f0;
            color: #f05a1a;
            font-size: 10px;
            padding: 1px 7px;
            border-radius: 10px;
            border: 1px solid #ffd4c2;
        }

        .page-item {
            background: #f1f2f3;
            padding: 12px 14px;
            margin-bottom: 8px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-item:hover {
            background: #fff5f0;
            border-left: 4px solid #f05a1a;
            transform: translateX(5px);
        }

        .page-item-name {
            font-weight: 600;
            color: #1e2b42;
        }
        
        .page-item-icon {
            color: #f05a1a;
        }
        
        /* Canvas */
        .canvas {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            min-height: 500px;
        }
        
        .canvas-area {
            border: 3px dashed #f05a1a;
            border-radius: 10px;
            padding: 20px;
            min-height: 400px;
            background: #fff5f0;
        }
        
        .canvas-placeholder {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .canvas-placeholder i {
            font-size: 48px;
            color: #f05a1a;
            margin-bottom: 15px;
        }
        
        .selected-page {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #f05a1a;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .selected-page-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .selected-page-number {
            background: #f05a1a;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }
        
        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 12px;
        }
        
        .remove-btn:hover {
            background: #c82333;
            transform: scale(1.05);
        }
        
        /* Settings Panel */
        .settings-panel {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            color: #1e2b42;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #f05a1a;
            box-shadow: 0 0 0 3px rgba(240, 90, 26, 0.1);
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f1f2f3;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .checkbox-wrapper:hover {
            background: #fff5f0;
        }
        
        .checkbox-wrapper input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #f05a1a;
        }
        
        .checkbox-wrapper label {
            margin: 0;
            cursor: pointer;
            font-weight: 600;
            color: #1e2b42;
        }
        
        .generate-btn {
            width: 100%;
            background: linear-gradient(135deg, #f05a1a 0%, #ff7a3d 100%);
            color: white;
            border: none;
            padding: 18px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(240, 90, 26, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .generate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(240, 90, 26, 0.5);
        }
        
        .generate-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .status-message {
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
        }
        
        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status-loading {
            background: #fff5f0;
            color: #f05a1a;
            border: 1px solid #f05a1a;
        }
        
        .download-btn {
            display: none;
            width: 100%;
            background: #28a745;
            color: white;
            border: none;
            padding: 18px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
            text-align: center;
            text-decoration: none;
        }
        
        .download-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        @media (max-width: 968px) {
            .main-content {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-right {
                width: 100%;
                flex-direction: column;
            }

            .stats {
                width: 100%;
            }

            .dashboard-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <img src="assets/appLogoIcon.png" alt="KandaNews Africa" onerror="this.style.display='none'">
                <div>
                    <div class="logo-text">KandaNews Africa</div>
                    <div class="logo-tagline">BUILD EDITION</div>
                </div>
            </div>
            <div class="header-right">
                <div class="stats">
                    <div class="stat-card">
                        <div class="stat-value" id="available-count">0</div>
                        <div class="stat-label">Available</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value" id="selected-count">0</div>
                        <div class="stat-label">Selected</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">461×600</div>
                        <div class="stat-label">Page Size</div>
                    </div>
                </div>
                <a href="index.php" class="dashboard-btn">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </div>
        </div>
        
        <div class="main-content">
            <div>
                <div class="page-selector">
                    <div class="section-title">
                        <i class="fas fa-file-alt"></i>
                        Available Pages
                    </div>
                    <div class="available-pages" id="available-pages">
                        <?php
                        // ── Multi-folder page scanner ───────────────────
                        $pagesDir = __DIR__ . '/templates/pages/';
                        $pageGroups = []; // ['Group Label' => [['filename'=>'rel/path.html','name'=>'display name'], ...]]

                        if (is_dir($pagesDir)) {
                            // Root-level HTML files → "General" group
                            $rootFiles = [];
                            foreach (scandir($pagesDir) as $f) {
                                if ($f[0] === '.') continue;
                                if (pathinfo($f, PATHINFO_EXTENSION) === 'html') {
                                    $rootFiles[] = ['filename' => $f, 'name' => str_replace(['-', '_', '.html'], [' ', ' ', ''], $f)];
                                }
                            }
                            if ($rootFiles) $pageGroups['General'] = $rootFiles;

                            // Subfolders → each folder becomes a group
                            foreach (scandir($pagesDir) as $folder) {
                                if ($folder[0] === '.') continue;
                                // Skip files — only enter actual directories
                                if (pathinfo($folder, PATHINFO_EXTENSION) !== '') continue;
                                $folderPath = $pagesDir . $folder . '/';
                                if (!is_dir($folderPath)) continue;
                                $folderPages = [];
                                foreach (scandir($folderPath) as $f) {
                                    if ($f[0] === '.') continue;
                                    if (pathinfo($f, PATHINFO_EXTENSION) === 'html') {
                                        $folderPages[] = [
                                            'filename' => $folder . '/' . $f,
                                            'name' => str_replace(['-', '_', '.html'], [' ', ' ', ''], $f),
                                        ];
                                    }
                                }
                                if ($folderPages) {
                                    $pageGroups[ucwords(str_replace(['-','_'], ' ', $folder))] = $folderPages;
                                }
                            }
                        }

                        $totalPages = array_sum(array_map('count', $pageGroups));

                        if ($totalPages === 0):
                        ?>
                        <div style="text-align:center;padding:40px;color:#999;">
                            <i class="fas fa-folder-open" style="font-size:48px;margin-bottom:15px;display:block;"></i>
                            No pages found in <code>templates/pages/</code><br>
                            <small>Add HTML files or create subfolders to organise page sets.</small>
                        </div>
                        <?php else: ?>

                        <!-- Folder tabs -->
                        <div class="folder-tabs" id="folder-tabs">
                            <span class="folder-tab active" data-group="all" onclick="filterGroup('all', this)">
                                All <span style="opacity:.7;">(<?php echo $totalPages; ?>)</span>
                            </span>
                            <?php foreach ($pageGroups as $label => $pages): ?>
                            <span class="folder-tab" data-group="<?php echo htmlspecialchars($label); ?>"
                                  onclick="filterGroup(<?php echo json_encode($label); ?>, this)">
                                <i class="fas fa-folder" style="font-size:10px;margin-right:4px;"></i>
                                <?php echo htmlspecialchars($label); ?>
                                <span style="opacity:.7;">(<?php echo count($pages); ?>)</span>
                            </span>
                            <?php endforeach; ?>
                        </div>

                        <!-- Page groups -->
                        <?php foreach ($pageGroups as $label => $pages): ?>
                        <div class="folder-group visible" data-group="<?php echo htmlspecialchars($label); ?>">
                            <div class="folder-group-header">
                                <i class="fas fa-folder-open"></i>
                                <?php echo htmlspecialchars($label); ?>
                                <span class="folder-count"><?php echo count($pages); ?></span>
                            </div>
                            <?php foreach ($pages as $page): ?>
                            <div class="page-item" data-filename="<?php echo htmlspecialchars($page['filename']); ?>"
                                 data-group="<?php echo htmlspecialchars($label); ?>" onclick="addPage(this)">
                                <span class="page-item-name"><?php echo htmlspecialchars($page['name']); ?></span>
                                <span class="page-item-icon"><i class="fas fa-plus-circle"></i></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endforeach; ?>

                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="canvas" style="margin-top: 30px;">
                    <div class="section-title">
                        <i class="fas fa-layer-group"></i>
                        Edition Canvas
                        <span style="margin-left:auto;font-size:14px;color:#666;font-weight:normal;" id="page-count-label">0 pages</span>
                    </div>
                    <div class="canvas-area" id="canvas">
                        <div class="canvas-placeholder">
                            <i class="fas fa-mouse-pointer"></i>
                            <h3 style="color:#1e2b42;margin-bottom:10px;">Click pages to add them here</h3>
                            <p>Build your edition by selecting pages from the left</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="settings-panel">
                <div class="section-title">
                    <i class="fas fa-cog"></i>
                    Edition Settings
                </div>
                
                <div class="form-group">
                    <label for="edition-title">📰 Edition Title</label>
                    <input type="text" id="edition-title" placeholder="e.g., Daily Edition, Rate Card 2025" value="Daily Edition">
                </div>
                
                <div class="form-group">
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="include-date" checked>
                        <label for="include-date">📅 Include Date</label>
                    </div>
                </div>
                
                <div class="form-group" id="date-field">
                    <label for="edition-date">Edition Date</label>
                    <input type="date" id="edition-date" value="<?php echo date('Y-m-d'); ?>">
                </div>
                
                <div class="form-group">
                    <label for="country">🌍 Country</label>
                    <select id="country">
                        <option value="ug">Uganda 🇺🇬</option>
                        <option value="ke">Kenya 🇰🇪</option>
                        <option value="za">South Africa 🇿🇦</option>
                    </select>
                </div>
                
                <button class="generate-btn" id="generate-btn" onclick="generateEdition()">
                    <i class="fas fa-magic"></i>
                    Generate Edition
                </button>
                
                <a class="download-btn" id="download-btn" href="#" download>
                    <i class="fas fa-download"></i> Download Edition ZIP
                </a>
                
                <div class="status-message" id="status-message"></div>
            </div>
        </div>
    </div>
    
    <script>
        let selectedPages = [];
        
        document.getElementById('include-date').addEventListener('change', function() {
            document.getElementById('date-field').style.display = this.checked ? 'block' : 'none';
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            const availablePages = document.querySelectorAll('.page-item').length;
            document.getElementById('available-count').textContent = availablePages;
        });
        
        // ── Folder filtering ──────────────────────────────────
        function filterGroup(group, tabEl) {
            // Update active tab
            document.querySelectorAll('.folder-tab').forEach(t => t.classList.remove('active'));
            tabEl.classList.add('active');

            // Show/hide groups
            document.querySelectorAll('.folder-group').forEach(g => {
                if (group === 'all' || g.dataset.group === group) {
                    g.classList.add('visible');
                } else {
                    g.classList.remove('visible');
                }
            });
        }

        function addPage(element) {
            const filename = element.dataset.filename;
            if (selectedPages.some(p => p.filename === filename)) {
                showStatus('Page already added!', 'error');
                return;
            }
            const group = element.dataset.group || '';
            const rawName = element.querySelector('.page-item-name').textContent.trim();
            const displayName = group && group !== 'General' ? group + ' › ' + rawName : rawName;
            selectedPages.push({
                filename: filename,
                name: displayName,
            });
            updateCanvas();
            updateStats();
        }
        
        function removePage(index) {
            selectedPages.splice(index, 1);
            updateCanvas();
            updateStats();
        }
        
        function updateCanvas() {
            const canvas = document.getElementById('canvas');
            if (selectedPages.length === 0) {
                canvas.innerHTML = `
                    <div class="canvas-placeholder">
                        <i class="fas fa-mouse-pointer"></i>
                        <h3 style="color:#1e2b42;margin-bottom:10px;">Click pages to add them here</h3>
                        <p>Build your edition by selecting pages from the left</p>
                    </div>
                `;
            } else {
                canvas.innerHTML = selectedPages.map((page, index) => `
                    <div class="selected-page">
                        <div class="selected-page-info">
                            <div class="selected-page-number">${index + 1}</div>
                            <span>${page.name}</span>
                        </div>
                        <button class="remove-btn" onclick="removePage(${index})">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                `).join('');
            }
        }
        
        function updateStats() {
            document.getElementById('selected-count').textContent = selectedPages.length;
            document.getElementById('page-count-label').textContent = 
                selectedPages.length + (selectedPages.length === 1 ? ' page' : ' pages');
        }
        
        async function generateEdition() {
            if (selectedPages.length === 0) {
                showStatus('Please add at least one page!', 'error');
                return;
            }
            
            const title = document.getElementById('edition-title').value.trim();
            if (!title) {
                showStatus('Please enter an edition title!', 'error');
                return;
            }
            
            const btn = document.getElementById('generate-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            showStatus('Generating your stunning edition...', 'loading');
            
            const data = {
                title: title,
                date: document.getElementById('include-date').checked ? 
                      document.getElementById('edition-date').value : null,
                includeDate: document.getElementById('include-date').checked,
                country: document.getElementById('country').value,
                pages: selectedPages
            };
            
            try {
                const response = await fetch('api/generate-edition.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showStatus(`✨ Edition generated successfully! ${result.pages} pages created.`, 'success');
                    const downloadBtn = document.getElementById('download-btn');
                    downloadBtn.href = result.download_url || 'output/' + (result.zip || 'edition.zip');
                    downloadBtn.style.display = 'block';
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-magic"></i> Generate Edition';
                } else {
                    throw new Error(result.error || 'Generation failed');
                }
            } catch (error) {
                showStatus('Error: ' + error.message, 'error');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-magic"></i> Generate Edition';
            }
        }
        
        function showStatus(message, type) {
            const statusEl = document.getElementById('status-message');
            statusEl.textContent = message;
            statusEl.className = 'status-message status-' + type;
            statusEl.style.display = 'block';
            if (type !== 'loading') {
                setTimeout(() => { statusEl.style.display = 'none'; }, 5000);
            }
        }
    </script>
</body>
</html>