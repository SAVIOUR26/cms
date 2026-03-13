<?php
/**
 * KandaNews Africa — Portal Shared Header
 *
 * Single unified admin shell for ALL CMS tools.
 * Include at the top of every portal page (after auth.php).
 *
 * Expected variables before include:
 *   $page_title    — string (optional, defaults to 'Portal')
 *   $page_section  — string (optional): 'editions'|'content'|'subscribers'|'tools'|'settings'
 */

if (!function_exists('portal_is_logged_in')) {
    require_once __DIR__ . '/auth.php';
}

$_user       = portal_get_user();
$_username   = $_user['full_name'] ?? $_user['username'] ?? 'Admin';
$_role       = ucfirst($_user['role'] ?? 'editor');
$_initials   = mb_strtoupper(mb_substr($_username, 0, 1));
$page_title  = $page_title ?? 'Portal';
$page_section = $page_section ?? '';
$_flash      = portal_get_flash();

// Current page for active-state highlighting
$_current_page = basename($_SERVER['SCRIPT_NAME']);
$_current_path = $_SERVER['SCRIPT_NAME'];

function _nav_active(string $page): string {
    global $_current_page;
    // Compare without .php so active state works for both clean and legacy URLs
    return basename($_current_page, '.php') === basename($page, '.php') ? ' active' : '';
}
function _section_active(string $section): string {
    global $page_section;
    return $page_section === $section ? ' section-active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> — KandaNews CMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ── Reset & Base ─────────────────────────── */
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f0f2f5;
            color: #333;
            min-height: 100vh;
        }

        /* ── CSS Variables ────────────────────────── */
        :root {
            --navy:        #1e2b42;
            --navy-l:      #2a3f5f;
            --navy-xl:     #3d5a80;
            --orange:      #f05a1a;
            --orange-l:    #ff7a3d;
            --green:       #059669;
            --purple:      #7c3aed;
            --radius:      12px;
            --shadow:      0 2px 16px rgba(30,43,66,.07);
            --shadow-md:   0 6px 24px rgba(30,43,66,.10);
            --shadow-lg:   0 12px 40px rgba(30,43,66,.13);
            --sidebar-w:   260px;
            --topbar-h:    60px;
            --sidebar-bg:  #111827;
        }

        /* ── Scrollbar ────────────────────────────── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }

        a { color: var(--orange); text-decoration: none; }
        a:hover { text-decoration: underline; }

        /* ═══════════════════════════════════════════
           LAYOUT
        ═══════════════════════════════════════════ */

        /* ── Sidebar ──────────────────────────────── */
        .sidebar {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            z-index: 200;
            display: flex;
            flex-direction: column;
            transition: transform .3s cubic-bezier(.4,0,.2,1);
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            text-decoration: none;
        }
        .sidebar-logo img {
            width: 36px; height: 36px;
            border-radius: 8px;
            object-fit: contain;
        }
        .sidebar-logo .logo-icon {
            width: 36px; height: 36px;
            background: var(--orange);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #fff; font-weight: 700;
            flex-shrink: 0;
        }
        .sidebar-logo .logo-text {
            font-size: 17px;
            font-weight: 700;
            color: #fff;
            letter-spacing: .2px;
        }
        .sidebar-logo .logo-text span { color: var(--orange); }
        .sidebar-logo:hover { text-decoration: none; }

        /* Nav sections */
        .nav-section {
            padding: 20px 0 4px;
        }
        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255,255,255,.3);
            padding: 0 20px 8px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 20px;
            color: rgba(255,255,255,.6);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all .18s;
            position: relative;
            border-left: 3px solid transparent;
        }
        .nav-item:hover {
            color: #fff;
            background: rgba(255,255,255,.05);
            text-decoration: none;
        }
        .nav-item.active {
            color: #fff;
            background: rgba(240,90,26,.12);
            border-left-color: var(--orange);
        }
        .nav-item i {
            width: 18px;
            text-align: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .nav-item .badge-dot {
            margin-left: auto;
            width: 8px; height: 8px;
            background: var(--orange);
            border-radius: 50%;
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,.07);
        }
        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .sidebar-user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: var(--orange);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; font-weight: 700; color: #fff;
            flex-shrink: 0;
        }
        .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-user-info strong {
            display: block;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .sidebar-user-info small {
            color: rgba(255,255,255,.4);
            font-size: 11px;
        }
        .sidebar-logout {
            color: rgba(255,255,255,.4);
            font-size: 16px;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            transition: all .15s;
        }
        .sidebar-logout:hover {
            color: #ef4444;
            background: rgba(239,68,68,.1);
            text-decoration: none;
        }

        /* ── Top Bar ──────────────────────────────── */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 28px;
            z-index: 100;
            box-shadow: var(--shadow);
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .hamburger {
            display: none;
            background: none;
            border: none;
            color: var(--navy);
            font-size: 20px;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
        }
        .hamburger:hover { background: #f3f4f6; }
        .page-breadcrumb {
            font-size: 14px;
            color: #9ca3af;
        }
        .page-breadcrumb strong {
            color: var(--navy);
            font-weight: 600;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Quick action buttons in topbar */
        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .topbar-action-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            border: 1.5px solid #e5e7eb;
            background: #fff;
            color: var(--navy);
            cursor: pointer;
            text-decoration: none;
            transition: all .2s;
        }
        .topbar-action-btn:hover {
            border-color: var(--orange);
            color: var(--orange);
            background: rgba(240,90,26,.04);
            text-decoration: none;
        }
        .topbar-action-btn.primary {
            background: var(--orange);
            color: #fff;
            border-color: var(--orange);
        }
        .topbar-action-btn.primary:hover {
            background: var(--orange-l);
            border-color: var(--orange-l);
            color: #fff;
        }

        /* ── Main Area ────────────────────────────── */
        .main-area {
            margin-left: var(--sidebar-w);
            padding-top: var(--topbar-h);
            min-height: 100vh;
        }
        .portal-body {
            padding: 28px 28px 48px;
            max-width: 1400px;
        }

        /* ── Mobile overlay ───────────────────────── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 199;
        }
        .sidebar-overlay.open { display: block; }

        /* ── Responsive ───────────────────────────── */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 300;
            }
            .sidebar.open { transform: translateX(0); }
            .topbar { left: 0; }
            .main-area { margin-left: 0; }
            .hamburger { display: flex; }
        }

        /* ═══════════════════════════════════════════
           FLASH MESSAGES
        ═══════════════════════════════════════════ */
        .flash {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-radius: var(--radius);
            margin-bottom: 24px;
            font-size: 14px;
            font-weight: 500;
            animation: slideDown .3s ease;
        }
        @keyframes slideDown {
            from { opacity:0; transform:translateY(-10px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .flash-success { background:#ecfdf5; color:#065f46; border:1px solid #a7f3d0; }
        .flash-error   { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
        .flash-warning { background:#fffbeb; color:#92400e; border:1px solid #fde68a; }
        .flash-info    { background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; }

        /* ═══════════════════════════════════════════
           COMPONENTS
        ═══════════════════════════════════════════ */

        /* Cards */
        .card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
            margin-bottom: 24px;
            border: 1px solid #f0f0f0;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .card-header h2 {
            font-size: 17px;
            font-weight: 700;
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-header h2 i { color: var(--orange); }

        /* Stat cards */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 22px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            border: 1px solid #f0f0f0;
            transition: box-shadow .2s, transform .2s;
            text-decoration: none;
            color: inherit;
        }
        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
            text-decoration: none;
        }
        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: #fff; flex-shrink: 0;
        }
        .si-orange { background: linear-gradient(135deg, var(--orange), var(--orange-l)); }
        .si-navy   { background: linear-gradient(135deg, var(--navy), var(--navy-xl)); }
        .si-green  { background: linear-gradient(135deg, #059669, #10b981); }
        .si-purple { background: linear-gradient(135deg, #7c3aed, #a78bfa); }
        .si-blue   { background: linear-gradient(135deg, #2563eb, #60a5fa); }
        .si-pink   { background: linear-gradient(135deg, #db2777, #f472b6); }
        .stat-info { flex: 1; }
        .stat-value { font-size: 26px; font-weight: 800; color: var(--navy); line-height: 1.1; }
        .stat-label { font-size: 12px; color: #888; font-weight: 500; margin-top: 3px; }
        .stat-trend {
            font-size: 11px;
            font-weight: 600;
            margin-top: 2px;
        }
        .stat-trend.up { color: var(--green); }
        .stat-trend.neutral { color: #9ca3af; }

        /* Quick actions grid */
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .action-tile {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 22px 16px;
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-decoration: none;
            color: var(--navy);
            font-weight: 600;
            font-size: 13px;
            text-align: center;
            border: 1.5px solid #f0f0f0;
            transition: all .2s;
        }
        .action-tile:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            border-color: var(--orange);
            color: var(--orange);
            text-decoration: none;
        }
        .action-tile i {
            font-size: 26px;
            color: var(--orange);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all .2s;
            text-decoration: none;
            font-family: inherit;
            line-height: 1.4;
            white-space: nowrap;
        }
        .btn:hover { text-decoration: none; transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
        .btn-primary   { background: var(--orange); color: #fff; box-shadow: 0 3px 12px rgba(240,90,26,.25); }
        .btn-primary:hover { background: var(--orange-l); }
        .btn-secondary { background: var(--navy); color: #fff; }
        .btn-secondary:hover { background: var(--navy-l); }
        .btn-outline   { background: transparent; color: var(--navy); border: 1.5px solid #e0e0e0; }
        .btn-outline:hover { border-color: var(--navy); background: #f9fafb; color: var(--navy); }
        .btn-success   { background: #059669; color: #fff; }
        .btn-success:hover { background: #047857; }
        .btn-danger    { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-warning   { background: #d97706; color: #fff; }
        .btn-warning:hover { background: #b45309; }
        .btn-ghost     { background: transparent; color: var(--navy); border: none; box-shadow: none; }
        .btn-ghost:hover { background: #f3f4f6; }
        .btn-sm  { padding: 6px 12px; font-size: 12px; }
        .btn-lg  { padding: 14px 28px; font-size: 16px; }

        /* Forms */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        @media (max-width: 640px) { .form-row { grid-template-columns: 1fr; } }
        .form-group { margin-bottom: 20px; }
        .form-label {
            display: block;
            font-weight: 600;
            font-size: 13px;
            color: var(--navy);
            margin-bottom: 6px;
        }
        .form-label .req { color: var(--orange); margin-left: 3px; }
        .form-control {
            width: 100%;
            padding: 11px 14px;
            border: 1.5px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: border-color .2s, box-shadow .2s;
            background: #fff;
            color: #333;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--orange);
            box-shadow: 0 0 0 3px rgba(240,90,26,.1);
        }
        .form-control::placeholder { color: #aaa; }
        select.form-control { appearance: auto; }
        textarea.form-control { resize: vertical; min-height: 100px; }
        .form-hint { font-size: 12px; color: #888; margin-top: 4px; }

        /* Tables */
        .table-wrapper { overflow-x: auto; border-radius: 8px; }
        table.dt {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        table.dt th {
            background: #f9fafb;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--navy);
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        table.dt td {
            padding: 13px 16px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        table.dt tbody tr:last-child td { border-bottom: none; }
        table.dt tbody tr:hover { background: #fafbfd; }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
            letter-spacing: .3px;
        }
        .badge-published { background:#dcfce7; color:#15803d; }
        .badge-draft     { background:#fef9c3; color:#92400e; }
        .badge-archived  { background:#f3f4f6; color:#6b7280; }
        .badge-daily     { background:#eff6ff; color:#1d4ed8; }
        .badge-special   { background:#fce7f3; color:#be185d; }
        .badge-rate_card { background:#f5f3ff; color:#6d28d9; }
        .badge-university    { background:#e0f2fe; color:#0369a1; }
        .badge-corporate     { background:#f0fdf4; color:#15803d; }
        .badge-entrepreneurship { background:#fff7ed; color:#c2410c; }
        .badge-active    { background:#dcfce7; color:#15803d; }
        .badge-expired   { background:#fef2f2; color:#b91c1c; }
        .badge-pending   { background:#fef9c3; color:#92400e; }
        .badge-cancelled { background:#f3f4f6; color:#6b7280; }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        .empty-state i {
            font-size: 56px;
            margin-bottom: 16px;
            display: block;
            opacity: .4;
        }
        .empty-state h3 {
            font-size: 18px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 8px;
        }
        .empty-state p { font-size: 14px; margin-bottom: 20px; }

        /* Cover thumbnail */
        .cover-thumb {
            width: 42px; height: 54px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }
        .cover-placeholder {
            width: 42px; height: 54px;
            background: linear-gradient(135deg, var(--navy), var(--navy-xl));
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            color: rgba(255,255,255,.5);
            font-size: 14px;
        }

        /* Section header */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .section-header h1 {
            font-size: 22px;
            font-weight: 800;
            color: var(--navy);
            margin-bottom: 3px;
        }
        .section-header p {
            font-size: 14px;
            color: #888;
        }

        /* Pagination */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            margin-top: 24px;
            flex-wrap: wrap;
        }
        .pagination a, .pagination span {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            color: var(--navy);
            text-decoration: none;
            border: 1px solid #e5e7eb;
            transition: all .15s;
        }
        .pagination a:hover { background: #f3f4f6; text-decoration: none; }
        .pagination span.current { background: var(--orange); color: #fff; border-color: var(--orange); }
        .pagination span.disabled { color: #d1d5db; pointer-events: none; }

        /* Filter bar */
        .filter-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            border: 1.5px solid #e0e0e0;
            background: #fff;
            color: #666;
            cursor: pointer;
            text-decoration: none;
            transition: all .15s;
        }
        .filter-chip:hover { border-color: var(--orange); color: var(--orange); text-decoration: none; }
        .filter-chip.active { background: var(--orange); color: #fff; border-color: var(--orange); }

        @media (max-width: 768px) {
            .portal-body { padding: 20px 16px 40px; }
            .stat-grid { grid-template-columns: 1fr 1fr; }
            .actions-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .stat-grid { grid-template-columns: 1fr; }
            .actions-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════════════
     SIDEBAR
══════════════════════════════════════════════ -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<nav class="sidebar" id="sidebar">
    <!-- Logo -->
    <a href="<?php echo portal_url('index.php'); ?>" class="sidebar-logo">
        <div class="logo-icon">K</div>
        <span class="logo-text">Kanda<span>News</span> CMS</span>
    </a>

    <!-- Overview -->
    <div class="nav-section">
        <div class="nav-section-label">Overview</div>
        <a href="<?php echo portal_url('index.php'); ?>"
           class="nav-item<?php echo _nav_active('index.php'); ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="<?php echo portal_url('analytics.php'); ?>"
           class="nav-item<?php echo _nav_active('analytics.php'); ?>">
            <i class="fas fa-chart-line"></i> Analytics
        </a>
    </div>

    <!-- Content -->
    <div class="nav-section">
        <div class="nav-section-label">Content</div>
        <a href="<?php echo portal_url('editions.php'); ?>"
           class="nav-item<?php echo _nav_active('editions.php'); ?>">
            <i class="fas fa-newspaper"></i> All Editions
        </a>
        <a href="<?php echo portal_url('special-editions.php'); ?>"
           class="nav-item<?php echo _nav_active('special-editions.php'); ?>">
            <i class="fas fa-star"></i> Special Editions
        </a>
        <a href="<?php echo portal_url('upload.php'); ?>"
           class="nav-item<?php echo _nav_active('upload.php'); ?>">
            <i class="fas fa-cloud-upload-alt"></i> Upload Edition
        </a>
        <a href="<?php echo portal_url('scan-editions.php'); ?>"
           class="nav-item<?php echo _nav_active('scan-editions.php'); ?>">
            <i class="fas fa-folder-open"></i> Scan Editions
        </a>
    </div>

    <!-- Tools -->
    <div class="nav-section">
        <div class="nav-section-label">Production Tools</div>
        <a href="<?php echo portal_cms_url('build-edition.php'); ?>"
           class="nav-item<?php echo _nav_active('build-edition.php'); ?>">
            <i class="fas fa-hammer"></i> Build Edition
        </a>
        <a href="<?php echo portal_cms_url('page-editor.php'); ?>"
           class="nav-item<?php echo _nav_active('page-editor.php'); ?>">
            <i class="fas fa-file-alt"></i> Page Editor
        </a>
        <a href="<?php echo portal_cms_url('visual-page-builder.php'); ?>"
           class="nav-item<?php echo _nav_active('visual-page-builder.php'); ?>">
            <i class="fas fa-paint-brush"></i> Visual Builder
        </a>
        <a href="<?php echo portal_cms_url('pages-library.php'); ?>"
           class="nav-item<?php echo _nav_active('pages-library.php'); ?>">
            <i class="fas fa-layer-group"></i> Pages Library
        </a>
    </div>

    <!-- Engagement -->
    <div class="nav-section">
        <div class="nav-section-label">Engagement</div>
        <a href="<?php echo portal_url('polls.php'); ?>"
           class="nav-item<?php echo _nav_active('polls.php'); ?>">
            <i class="fas fa-poll"></i> Polls
        </a>
        <a href="<?php echo portal_url('events.php'); ?>"
           class="nav-item<?php echo _nav_active('events.php'); ?>">
            <i class="fas fa-calendar-alt"></i> Events
        </a>
        <a href="<?php echo portal_url('banners.php'); ?>"
           class="nav-item<?php echo _nav_active('banners.php'); ?>">
            <i class="fas fa-images"></i> Home Banners
        </a>
        <a href="<?php echo portal_url('quotes.php'); ?>"
           class="nav-item<?php echo _nav_active('quotes.php'); ?>">
            <i class="fas fa-quote-left"></i> Quotes
        </a>
        <a href="<?php echo portal_url('edition-categories.php'); ?>"
           class="nav-item<?php echo _nav_active('edition-categories.php'); ?>">
            <i class="fas fa-th-large"></i> Edition Categories
        </a>
    </div>

    <!-- Audience -->
    <div class="nav-section">
        <div class="nav-section-label">Audience</div>
        <a href="<?php echo portal_url('subscribers.php'); ?>"
           class="nav-item<?php echo _nav_active('subscribers.php'); ?>">
            <i class="fas fa-users"></i> Subscribers
        </a>
        <a href="<?php echo portal_url('subscribers.php?tab=revenue'); ?>"
           class="nav-item">
            <i class="fas fa-coins"></i> Revenue
        </a>
    </div>

    <!-- Advertising -->
    <div class="nav-section">
        <div class="nav-section-label">Advertising</div>
        <a href="<?php echo portal_url('ads.php'); ?>"
           class="nav-item<?php echo _nav_active('ads.php'); ?>">
            <i class="fas fa-bullhorn"></i> Ads Dashboard
        </a>
        <a href="<?php echo portal_url('ads.php?tab=advertisers'); ?>"
           class="nav-item">
            <i class="fas fa-building"></i> Advertisers
        </a>
        <a href="<?php echo portal_url('ads.php?tab=bookings'); ?>"
           class="nav-item">
            <i class="fas fa-calendar-check"></i> Bookings
        </a>
        <a href="<?php echo portal_url('ads.php?tab=payments'); ?>"
           class="nav-item">
            <i class="fas fa-receipt"></i> Payment Log
        </a>
    </div>

    <!-- System -->
    <div class="nav-section">
        <div class="nav-section-label">System</div>
        <a href="<?php echo portal_url('settings.php'); ?>"
           class="nav-item<?php echo _nav_active('settings.php'); ?>">
            <i class="fas fa-cog"></i> Settings
        </a>
        <a href="<?php echo portal_url('settings.php?section=integrations'); ?>"
           class="nav-item">
            <i class="fas fa-plug"></i> Integrations
        </a>
    </div>

    <!-- User footer -->
    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-user-avatar"><?php echo $_initials; ?></div>
            <div class="sidebar-user-info">
                <strong><?php echo htmlspecialchars($_username); ?></strong>
                <small><?php echo $_role; ?></small>
            </div>
            <a href="<?php echo portal_url('login.php?action=logout'); ?>"
               class="sidebar-logout" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </div>
</nav>

<!-- ══════════════════════════════════════════════
     TOP BAR
══════════════════════════════════════════════ -->
<div class="main-area">
<header class="topbar">
    <div class="topbar-left">
        <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="page-breadcrumb">
            <span>KandaNews CMS</span>
            <?php if ($page_title !== 'Dashboard'): ?>
            <span style="margin:0 6px;color:#d1d5db;">/</span>
            <strong><?php echo htmlspecialchars($page_title); ?></strong>
            <?php endif; ?>
        </div>
    </div>

    <div class="topbar-right">
        <div class="topbar-actions">
            <a href="<?php echo portal_url('upload.php'); ?>" class="topbar-action-btn">
                <i class="fas fa-plus"></i> Upload
            </a>
            <a href="<?php echo portal_cms_url('build-edition.php'); ?>" class="topbar-action-btn primary">
                <i class="fas fa-hammer"></i> Build Edition
            </a>
        </div>
    </div>
</header>

<!-- ── Main Body ─────────────────────────────── -->
<main class="portal-body">

<?php if ($_flash): ?>
<div class="flash flash-<?php echo htmlspecialchars($_flash['type']); ?>">
    <i class="fas fa-<?php echo match($_flash['type']) {
        'success' => 'check-circle',
        'error'   => 'times-circle',
        'warning' => 'exclamation-triangle',
        default   => 'info-circle',
    }; ?>"></i>
    <?php echo htmlspecialchars($_flash['message']); ?>
</div>
<?php endif; ?>
