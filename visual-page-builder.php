<?php
/**
 * KandaNews Africa CMS - Visual Page Builder
 * Drag & Drop Widget-Based Page Builder
 */

define('KANDA_CMS', true);
require_once 'config.php';

requireLogin();
$user = getCurrentUser();

$page_title = 'Visual Page Builder';
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
            overflow: hidden;
        }
        
        .builder-container {
            display: grid;
            grid-template-columns: 280px 1fr 320px;
            height: 100vh;
        }
        
        /* LEFT SIDEBAR - WIDGETS */
        .widgets-panel {
            background: white;
            border-right: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
        }
        
        .panel-header {
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .panel-title {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .panel-subtitle {
            font-size: 13px;
            color: #666;
        }
        
        .widgets-list {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        .widget-category {
            margin-bottom: 25px;
        }
        
        .category-title {
            font-size: 12px;
            font-weight: 700;
            color: #667eea;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        
        .widget-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            cursor: grab;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .widget-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .widget-item:active {
            cursor: grabbing;
        }
        
        .widget-icon {
            font-size: 24px;
        }
        
        .widget-info {
            flex: 1;
        }
        
        .widget-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 2px;
        }
        
        .widget-desc {
            font-size: 11px;
            color: #666;
        }
        
        /* CENTER - CANVAS */
        .canvas-panel {
            background: #e9ecef;
            display: flex;
            flex-direction: column;
        }
        
        .canvas-header {
            background: white;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .canvas-info {
            font-size: 14px;
            color: #666;
        }
        
        .canvas-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
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
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #333;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .canvas-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow: auto;
        }
        
        /* THE PAGE CANVAS - FIXED SIZE! */
        #pageCanvas {
            width: 461px;
            height: 600px;
            background: white;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            position: relative;
            overflow: hidden;
        }
        
        #pageCanvas.drag-over {
            outline: 3px dashed #667eea;
            outline-offset: -10px;
        }
        
        /* WIDGETS ON CANVAS */
        .canvas-widget {
            position: absolute;
            border: 2px solid transparent;
            cursor: move;
            transition: border-color 0.2s;
        }
        
        .canvas-widget:hover {
            border-color: #667eea;
        }
        
        .canvas-widget.selected {
            border-color: #667eea;
            box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
        }
        
        .canvas-widget .widget-controls {
            position: absolute;
            top: -30px;
            right: 0;
            display: none;
            gap: 5px;
        }
        
        .canvas-widget.selected .widget-controls {
            display: flex;
        }
        
        .widget-control-btn {
            background: #667eea;
            color: white;
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .widget-control-btn:hover {
            background: #764ba2;
        }
        
        .resize-handle {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 15px;
            height: 15px;
            background: #667eea;
            cursor: se-resize;
            display: none;
        }
        
        .canvas-widget.selected .resize-handle {
            display: block;
        }
        
        /* RIGHT SIDEBAR - PROPERTIES */
        .properties-panel {
            background: white;
            border-left: 1px solid #e9ecef;
            display: flex;
            flex-direction: column;
        }
        
        .properties-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }
        
        .property-group {
            margin-bottom: 20px;
        }
        
        .property-label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            color: #333;
        }
        
        .property-input {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .property-input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        textarea.property-input {
            min-height: 100px;
            resize: vertical;
            font-family: inherit;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .empty-icon {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
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
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 2px solid #e9ecef;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            display: none;
            z-index: 1001;
        }
        
        .notification.show {
            display: block;
            animation: slideIn 0.3s;
        }
        
        .notification.success {
            border-left: 4px solid #28a745;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="builder-container">
        <!-- LEFT: WIDGETS LIBRARY -->
        <div class="widgets-panel">
            <div class="panel-header">
                <div class="panel-title">🎨 Widgets</div>
                <div class="panel-subtitle">Drag onto canvas</div>
            </div>
            
            <div class="widgets-list">
                <div class="widget-category">
                    <div class="category-title">📝 Content</div>
                    
                    <div class="widget-item" draggable="true" data-widget-type="text">
                        <div class="widget-icon">📝</div>
                        <div class="widget-info">
                            <div class="widget-name">Text</div>
                            <div class="widget-desc">Add text content</div>
                        </div>
                    </div>
                    
                    <div class="widget-item" draggable="true" data-widget-type="heading">
                        <div class="widget-icon">📰</div>
                        <div class="widget-info">
                            <div class="widget-name">Heading</div>
                            <div class="widget-desc">Large title text</div>
                        </div>
                    </div>
                </div>
                
                <div class="widget-category">
                    <div class="category-title">🖼️ Media</div>
                    
                    <div class="widget-item" draggable="true" data-widget-type="image">
                        <div class="widget-icon">🖼️</div>
                        <div class="widget-info">
                            <div class="widget-name">Image</div>
                            <div class="widget-desc">Add an image</div>
                        </div>
                    </div>
                    
                    <div class="widget-item" draggable="true" data-widget-type="video">
                        <div class="widget-icon">🎥</div>
                        <div class="widget-info">
                            <div class="widget-name">Video</div>
                            <div class="widget-desc">Embed video</div>
                        </div>
                    </div>
                    
                    <div class="widget-item" draggable="true" data-widget-type="slider">
                        <div class="widget-icon">🎞️</div>
                        <div class="widget-info">
                            <div class="widget-name">Image Slider</div>
                            <div class="widget-desc">Multiple images</div>
                        </div>
                    </div>
                </div>
                
                <div class="widget-category">
                    <div class="category-title">📐 Layout</div>
                    
                    <div class="widget-item" draggable="true" data-widget-type="header">
                        <div class="widget-icon">📋</div>
                        <div class="widget-info">
                            <div class="widget-name">Header</div>
                            <div class="widget-desc">Page header</div>
                        </div>
                    </div>
                    
                    <div class="widget-item" draggable="true" data-widget-type="footer">
                        <div class="widget-icon">🔖</div>
                        <div class="widget-info">
                            <div class="widget-name">Footer</div>
                            <div class="widget-desc">Page footer</div>
                        </div>
                    </div>
                    
                    <div class="widget-item" draggable="true" data-widget-type="box">
                        <div class="widget-icon">⬜</div>
                        <div class="widget-info">
                            <div class="widget-name">Box</div>
                            <div class="widget-desc">Container box</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- CENTER: CANVAS -->
        <div class="canvas-panel">
            <div class="canvas-header">
                <div class="canvas-info">
                    <strong>Page Size:</strong> 461px × 600px (Fixed)
                </div>
                <div class="canvas-actions">
                    <button onclick="clearCanvas()" class="btn btn-secondary">
                        <i class="fas fa-trash"></i> Clear
                    </button>
                    <button onclick="previewPage()" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> Preview
                    </button>
                    <button onclick="savePage()" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Page
                    </button>
                </div>
            </div>
            
            <div class="canvas-wrapper">
                <div id="pageCanvas">
                    <!-- Widgets will be dropped here -->
                </div>
            </div>
        </div>
        
        <!-- RIGHT: PROPERTIES -->
        <div class="properties-panel">
            <div class="panel-header">
                <div class="panel-title">⚙️ Properties</div>
                <div class="panel-subtitle">Edit selected widget</div>
            </div>
            
            <div class="properties-content" id="propertiesContent">
                <div class="empty-state">
                    <div class="empty-icon">🎯</div>
                    <p>Select a widget to edit its properties</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- SAVE MODAL -->
    <div id="saveModal" class="modal">
        <div class="modal-content">
            <div class="modal-title">💾 Save Your Page</div>
            
            <div class="form-group">
                <label>Page Name</label>
                <input type="text" id="pageName" placeholder="my-awesome-page">
                <small style="display: block; margin-top: 5px; color: #666;">Will be saved as: my-awesome-page.html</small>
            </div>
            
            <div class="form-group">
                <label>Category</label>
                <select id="pageCategory">
                    <option value="cover">Cover</option>
                    <option value="article">Article</option>
                    <option value="ad">Advertisement</option>
                    <option value="interactive">Interactive</option>
                </select>
            </div>
            
            <div class="modal-actions">
                <button onclick="closeSaveModal()" class="btn btn-secondary">Cancel</button>
                <button onclick="savePageToFile()" class="btn btn-success">
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </div>
    </div>
    
    <!-- NOTIFICATION -->
    <div id="notification" class="notification"></div>
    
    <script>
        let selectedWidget = null;
        let widgets = [];
        let widgetIdCounter = 0;
        let isDragging = false;
        let isResizing = false;
        let dragStartX, dragStartY, widgetStartX, widgetStartY;
        
        // Initialize drag and drop
        document.querySelectorAll('.widget-item').forEach(item => {
            item.addEventListener('dragstart', handleDragStart);
        });
        
        const canvas = document.getElementById('pageCanvas');
        canvas.addEventListener('dragover', handleDragOver);
        canvas.addEventListener('drop', handleDrop);
        canvas.addEventListener('dragleave', handleDragLeave);
        
        function handleDragStart(e) {
            e.dataTransfer.setData('widgetType', e.target.dataset.widgetType);
        }
        
        function handleDragOver(e) {
            e.preventDefault();
            canvas.classList.add('drag-over');
        }
        
        function handleDragLeave(e) {
            canvas.classList.remove('drag-over');
        }
        
        function handleDrop(e) {
            e.preventDefault();
            canvas.classList.remove('drag-over');
            
            const widgetType = e.dataTransfer.getData('widgetType');
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            addWidget(widgetType, x, y);
        }
        
        function addWidget(type, x, y) {
            const widgetId = 'widget-' + (++widgetIdCounter);
            const widget = {
                id: widgetId,
                type: type,
                x: Math.max(0, Math.min(x, 461 - 100)),
                y: Math.max(0, Math.min(y, 600 - 50)),
                width: getDefaultWidth(type),
                height: getDefaultHeight(type),
                content: getDefaultContent(type),
                style: getDefaultStyle(type)
            };
            
            widgets.push(widget);
            renderWidget(widget);
            selectWidget(widgetId);
        }
        
        function getDefaultWidth(type) {
            switch(type) {
                case 'header': return 461;
                case 'footer': return 461;
                case 'heading': return 400;
                case 'text': return 380;
                case 'image': return 300;
                case 'video': return 380;
                case 'slider': return 380;
                case 'box': return 200;
                default: return 300;
            }
        }
        
        function getDefaultHeight(type) {
            switch(type) {
                case 'header': return 60;
                case 'footer': return 50;
                case 'heading': return 60;
                case 'text': return 100;
                case 'image': return 200;
                case 'video': return 250;
                case 'slider': return 250;
                case 'box': return 150;
                default: return 100;
            }
        }
        
        function getDefaultContent(type) {
            switch(type) {
                case 'header': return 'Page Header';
                case 'footer': return 'Page Footer';
                case 'heading': return 'Your Headline Here';
                case 'text': return 'Your text content goes here. Double-click to edit.';
                case 'image': return 'https://via.placeholder.com/300x200?text=Image';
                case 'video': return 'https://youtube.com/embed/dQw4w9WgXcQ';
                case 'slider': return 'https://via.placeholder.com/300x200?text=Slide+1|https://via.placeholder.com/300x200?text=Slide+2';
                case 'box': return '';
                default: return 'Content';
            }
        }
        
        function getDefaultStyle(type) {
            switch(type) {
                case 'header': return { background: 'linear-gradient(135deg, #667eea, #764ba2)', color: 'white', fontSize: '18px', fontWeight: 'bold', padding: '15px' };
                case 'footer': return { background: '#f8f9fa', color: '#333', fontSize: '12px', padding: '10px', textAlign: 'center' };
                case 'heading': return { fontSize: '32px', fontWeight: 'bold', color: '#333' };
                case 'text': return { fontSize: '16px', color: '#333', lineHeight: '1.6' };
                case 'box': return { background: '#f8f9fa', border: '2px solid #e9ecef', borderRadius: '8px' };
                default: return {};
            }
        }
        
        function renderWidget(widget) {
            const el = document.createElement('div');
            el.className = 'canvas-widget';
            el.id = widget.id;
            el.style.left = widget.x + 'px';
            el.style.top = widget.y + 'px';
            el.style.width = widget.width + 'px';
            el.style.height = widget.height + 'px';
            
            // Apply custom styles
            for (let prop in widget.style) {
                el.style[prop] = widget.style[prop];
            }
            
            // Add content based on type
            el.innerHTML = getWidgetHTML(widget);
            
            // Add controls
            el.innerHTML += `
                <div class="widget-controls">
                    <button class="widget-control-btn" onclick="deleteWidget('${widget.id}')" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <div class="resize-handle"></div>
            `;
            
            // Add event listeners
            el.addEventListener('mousedown', (e) => {
                if (e.target.classList.contains('resize-handle')) {
                    startResize(e, widget.id);
                } else if (!e.target.classList.contains('widget-control-btn')) {
                    startDrag(e, widget.id);
                }
            });
            
            el.addEventListener('dblclick', () => {
                selectWidget(widget.id);
            });
            
            canvas.appendChild(el);
        }
        
        function getWidgetHTML(widget) {
            switch(widget.type) {
                case 'text':
                case 'heading':
                case 'header':
                case 'footer':
                    return `<div style="padding: 10px">${widget.content}</div>`;
                case 'image':
                    return `<img src="${widget.content}" style="width: 100%; height: 100%; object-fit: cover;">`;
                case 'video':
                    return `<iframe src="${widget.content}" style="width: 100%; height: 100%; border: none;"></iframe>`;
                case 'slider':
                    const images = widget.content.split('|');
                    return `<img src="${images[0]}" style="width: 100%; height: 100%; object-fit: cover;">`;
                case 'box':
                    return '';
                default:
                    return widget.content;
            }
        }
        
        function startDrag(e, widgetId) {
            if (e.button !== 0) return;
            isDragging = true;
            selectWidget(widgetId);
            
            dragStartX = e.clientX;
            dragStartY = e.clientY;
            const widget = widgets.find(w => w.id === widgetId);
            widgetStartX = widget.x;
            widgetStartY = widget.y;
            
            document.addEventListener('mousemove', drag);
            document.addEventListener('mouseup', stopDrag);
            e.preventDefault();
        }
        
        function drag(e) {
            if (!isDragging || !selectedWidget) return;
            
            const dx = e.clientX - dragStartX;
            const dy = e.clientY - dragStartY;
            const widget = widgets.find(w => w.id === selectedWidget);
            
            widget.x = Math.max(0, Math.min(widgetStartX + dx, 461 - widget.width));
            widget.y = Math.max(0, Math.min(widgetStartY + dy, 600 - widget.height));
            
            const el = document.getElementById(selectedWidget);
            el.style.left = widget.x + 'px';
            el.style.top = widget.y + 'px';
        }
        
        function stopDrag() {
            isDragging = false;
            document.removeEventListener('mousemove', drag);
            document.removeEventListener('mouseup', stopDrag);
        }
        
        function startResize(e, widgetId) {
            isResizing = true;
            selectWidget(widgetId);
            
            dragStartX = e.clientX;
            dragStartY = e.clientY;
            const widget = widgets.find(w => w.id === widgetId);
            const startWidth = widget.width;
            const startHeight = widget.height;
            
            const resize = (e) => {
                if (!isResizing) return;
                const dx = e.clientX - dragStartX;
                const dy = e.clientY - dragStartY;
                
                widget.width = Math.max(50, Math.min(startWidth + dx, 461 - widget.x));
                widget.height = Math.max(30, Math.min(startHeight + dy, 600 - widget.y));
                
                const el = document.getElementById(widgetId);
                el.style.width = widget.width + 'px';
                el.style.height = widget.height + 'px';
            };
            
            const stopResize = () => {
                isResizing = false;
                document.removeEventListener('mousemove', resize);
                document.removeEventListener('mouseup', stopResize);
            };
            
            document.addEventListener('mousemove', resize);
            document.addEventListener('mouseup', stopResize);
            e.stopPropagation();
            e.preventDefault();
        }
        
        function selectWidget(widgetId) {
            // Deselect all
            document.querySelectorAll('.canvas-widget').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Select this one
            const el = document.getElementById(widgetId);
            if (el) {
                el.classList.add('selected');
                selectedWidget = widgetId;
                showProperties(widgetId);
            }
        }
        
        function showProperties(widgetId) {
            const widget = widgets.find(w => w.id === widgetId);
            if (!widget) return;
            
            const props = document.getElementById('propertiesContent');
            props.innerHTML = `
                <div class="property-group">
                    <label class="property-label">Content</label>
                    <textarea class="property-input" id="prop-content">${widget.content}</textarea>
                </div>
                
                <div class="property-group">
                    <label class="property-label">Width</label>
                    <input type="number" class="property-input" id="prop-width" value="${widget.width}">
                </div>
                
                <div class="property-group">
                    <label class="property-label">Height</label>
                    <input type="number" class="property-input" id="prop-height" value="${widget.height}">
                </div>
                
                <div class="property-group">
                    <label class="property-label">X Position</label>
                    <input type="number" class="property-input" id="prop-x" value="${widget.x}">
                </div>
                
                <div class="property-group">
                    <label class="property-label">Y Position</label>
                    <input type="number" class="property-input" id="prop-y" value="${widget.y}">
                </div>
                
                <button onclick="applyProperties()" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-check"></i> Apply Changes
                </button>
            `;
        }
        
        function applyProperties() {
            if (!selectedWidget) return;
            
            const widget = widgets.find(w => w.id === selectedWidget);
            widget.content = document.getElementById('prop-content').value;
            widget.width = parseInt(document.getElementById('prop-width').value);
            widget.height = parseInt(document.getElementById('prop-height').value);
            widget.x = parseInt(document.getElementById('prop-x').value);
            widget.y = parseInt(document.getElementById('prop-y').value);
            
            // Re-render widget
            const el = document.getElementById(selectedWidget);
            el.style.left = widget.x + 'px';
            el.style.top = widget.y + 'px';
            el.style.width = widget.width + 'px';
            el.style.height = widget.height + 'px';
            el.innerHTML = getWidgetHTML(widget) + el.querySelector('.widget-controls').outerHTML + el.querySelector('.resize-handle').outerHTML;
            
            showNotification('✅ Properties updated!');
        }
        
        function deleteWidget(widgetId) {
            if (confirm('Delete this widget?')) {
                widgets = widgets.filter(w => w.id !== widgetId);
                document.getElementById(widgetId).remove();
                selectedWidget = null;
                document.getElementById('propertiesContent').innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">🎯</div>
                        <p>Select a widget to edit</p>
                    </div>
                `;
            }
        }
        
        function clearCanvas() {
            if (confirm('Clear all widgets?')) {
                widgets = [];
                canvas.innerHTML = '';
                selectedWidget = null;
            }
        }
        
        function previewPage() {
            const html = generateHTML();
            const blob = new Blob([html], {type: 'text/html'});
            const url = URL.createObjectURL(blob);
            window.open(url, '_blank');
        }
        
        function savePage() {
            document.getElementById('saveModal').classList.add('active');
        }
        
        function closeSaveModal() {
            document.getElementById('saveModal').classList.remove('active');
        }
        
        async function savePageToFile() {
            const name = document.getElementById('pageName').value;
            const category = document.getElementById('pageCategory').value;
            
            if (!name) {
                alert('Please enter a page name');
                return;
            }
            
            const html = generateHTML();
            const filename = name + '.html';
            
            const formData = new FormData();
            formData.append('html', html);
            formData.append('filename', filename);
            formData.append('category', category);
            
            try {
                const response = await fetch('api/save-visual-page.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showNotification('✅ Page saved successfully!');
                    closeSaveModal();
                    
                    setTimeout(() => {
                        window.location.href = 'pages-library.php';
                    }, 1500);
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                alert('Failed to save page');
            }
        }
        
        function generateHTML() {
            let html = `<!DOCTYPE html>
<html lang="en" data-category="custom">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KandaNews Page</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            width: 461px; 
            height: 600px; 
            overflow: hidden;
            position: relative;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
    </style>
</head>
<body>
`;
            
            widgets.forEach(widget => {
                html += `    <div style="position: absolute; left: ${widget.x}px; top: ${widget.y}px; width: ${widget.width}px; height: ${widget.height}px; `;
                
                for (let prop in widget.style) {
                    html += `${prop.replace(/([A-Z])/g, '-$1').toLowerCase()}: ${widget.style[prop]}; `;
                }
                
                html += `">`;
                html += getWidgetHTML(widget);
                html += `</div>\n`;
            });
            
            html += `</body>
</html>`;
            
            return html;
        }
        
        function showNotification(message) {
            const notif = document.getElementById('notification');
            notif.textContent = message;
            notif.classList.add('show', 'success');
            
            setTimeout(() => {
                notif.classList.remove('show');
            }, 3000);
        }
        
        // Click outside canvas to deselect
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.canvas-widget') && !e.target.closest('.properties-panel')) {
                document.querySelectorAll('.canvas-widget').forEach(el => {
                    el.classList.remove('selected');
                });
                selectedWidget = null;
            }
        });
    </script>
</body>
</html>