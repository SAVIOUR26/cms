<?php
/**
 * KandaNews Africa — Portal Shared Header
 *
 * Include at the top of every portal page (after auth.php).
 * Provides HTML <head>, top navigation bar, and responsive sidebar.
 *
 * Expected variables before include:
 *   $page_title  — string (optional, defaults to 'Portal')
 */

if (!function_exists('portal_is_logged_in')) {
    require_once __DIR__ . '/auth.php';
}

$_user       = portal_get_user();
$_username   = $_user['full_name'] ?? $_user['username'] ?? 'Admin';
$_role       = ucfirst($_user['role'] ?? 'editor');
$_initials   = mb_strtoupper(mb_substr($_username, 0, 1));
$page_title  = $page_title ?? 'Portal';
$_flash      = portal_get_flash();

// Current page for nav highlighting
$_current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> — KandaNews Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* ── Variables (CSS custom props) ─────────── */
        :root {
            --navy:    #1e2b42;
            --navy-light: #2a3f5f;
            --orange:  #f05a1a;
            --orange-light: #ff7a3d;
            --radius:  12px;
            --shadow:  0 4px 24px rgba(30,43,66,.08);
            --shadow-lg: 0 10px 40px rgba(30,43,66,.12);
            --sidebar-w: 260px;
            --topbar-h: 64px;
        }

        a { color: var(--orange); text-decoration: none; }
        a:hover { text-decoration: underline; }

        /* ── Top Navigation Bar ───────────────────── */
        .portal-topbar {
            position: fixed; top: 0; left: 0; right: 0;
            height: var(--topbar-h);
            background: var(--navy);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            z-index: 1000;
            box-shadow: 0 2px 12px rgba(0,0,0,.15);
        }
        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            text-decoration: none;
        }
        .topbar-brand:hover { text-decoration: none; }
        .topbar-brand span.accent { color: var(--orange); }
        .topbar-brand img { height: 36px; }
        .hamburger {
            display: none;
            background: none;
            border: none;
            color: #fff;
            font-size: 22px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 6px;
        }
        .hamburger:hover { background: rgba(255,255,255,.1); }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .topbar-nav {
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .topbar-nav a {
            color: rgba(255,255,255,.7);
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            transition: all .2s;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .topbar-nav a:hover,
        .topbar-nav a.active {
            color: #fff;
            background: rgba(255,255,255,.1);
        }
        .topbar-nav a.active { background: rgba(240,90,26,.25); color: var(--orange); }

        /* User dropdown */
        .user-menu {
            position: relative;
        }
        .user-menu-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 8px;
            background: rgba(255,255,255,.08);
            border: none;
            color: #fff;
            font-size: 14px;
            font-weight: 500;
            transition: all .2s;
        }
        .user-menu-trigger:hover { background: rgba(255,255,255,.15); }
        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--orange);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            color: #fff;
        }
        .user-dropdown {
            display: none;
            position: absolute;
            right: 0; top: calc(100% + 8px);
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            min-width: 200px;
            overflow: hidden;
            z-index: 1001;
            border: 1px solid #e5e7eb;
        }
        .user-dropdown.open { display: block; }
        .user-dropdown-header {
            padding: 16px;
            border-bottom: 1px solid #f0f0f0;
            background: #fafafa;
        }
        .user-dropdown-header strong { display: block; color: var(--navy); font-size: 14px; }
        .user-dropdown-header small  { color: #888; font-size: 12px; }
        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            color: #333;
            font-size: 14px;
            transition: background .15s;
        }
        .user-dropdown a:hover { background: #f5f5f5; text-decoration: none; }
        .user-dropdown a i { width: 16px; text-align: center; color: #888; }
        .user-dropdown a.logout-link { color: #dc3545; }
        .user-dropdown a.logout-link i { color: #dc3545; }

        /* ── Sidebar (mobile overlay) ─────────────── */
        .portal-sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.4);
            z-index: 1100;
        }
        .portal-sidebar-overlay.open { display: block; }
        .portal-sidebar {
            position: fixed;
            top: 0; left: -280px; bottom: 0;
            width: 280px;
            background: var(--navy);
            z-index: 1101;
            transition: left .3s ease;
            padding-top: 20px;
            overflow-y: auto;
        }
        .portal-sidebar.open { left: 0; }
        .sidebar-close {
            position: absolute;
            top: 16px; right: 16px;
            background: none;
            border: none;
            color: rgba(255,255,255,.6);
            font-size: 20px;
            cursor: pointer;
        }
        .sidebar-brand {
            padding: 12px 24px 24px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,.1);
            margin-bottom: 12px;
        }
        .sidebar-brand span.accent { color: var(--orange); }
        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            color: rgba(255,255,255,.7);
            font-size: 15px;
            font-weight: 500;
            transition: all .2s;
            text-decoration: none;
        }
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            color: #fff;
            background: rgba(255,255,255,.08);
        }
        .sidebar-nav a.active { border-left: 3px solid var(--orange); }
        .sidebar-nav a i { width: 20px; text-align: center; }

        /* ── Main content area ────────────────────── */
        .portal-body {
            padding-top: calc(var(--topbar-h) + 24px);
            padding-left: 24px;
            padding-right: 24px;
            padding-bottom: 40px;
            max-width: 1360px;
            margin: 0 auto;
        }

        /* ── Flash messages ───────────────────────── */
        .flash-message {
            padding: 14px 20px;
            border-radius: var(--radius);
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: flashIn .3s ease;
        }
        @keyframes flashIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
        .flash-success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
        .flash-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
        .flash-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
        .flash-info    { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }

        /* ── Cards ────────────────────────────────── */
        .card {
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            padding: 24px;
            margin-bottom: 24px;
        }
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .card-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-header h2 i { color: var(--orange); }

        /* ── Buttons ──────────────────────────────── */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
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
        }
        .btn:hover { text-decoration: none; transform: translateY(-1px); }
        .btn-primary   { background: var(--orange); color: #fff; box-shadow: 0 4px 12px rgba(240,90,26,.25); }
        .btn-primary:hover { background: var(--orange-light); box-shadow: 0 6px 18px rgba(240,90,26,.35); }
        .btn-secondary { background: var(--navy); color: #fff; box-shadow: 0 4px 12px rgba(30,43,66,.2); }
        .btn-secondary:hover { background: var(--navy-light); }
        .btn-outline   { background: transparent; color: var(--navy); border: 2px solid #e0e0e0; }
        .btn-outline:hover { border-color: var(--navy); background: #f9fafb; }
        .btn-success   { background: #059669; color: #fff; }
        .btn-success:hover { background: #047857; }
        .btn-danger    { background: #dc2626; color: #fff; }
        .btn-danger:hover { background: #b91c1c; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .btn-lg { padding: 14px 28px; font-size: 16px; }

        /* ── Forms ────────────────────────────────── */
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 14px;
            color: var(--navy);
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e0e0e0;
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

        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: #f9fafb;
            border-radius: 8px;
            cursor: pointer;
        }
        .form-check input[type="checkbox"] {
            width: 20px; height: 20px;
            accent-color: var(--orange);
            cursor: pointer;
        }
        .form-check label {
            margin: 0;
            cursor: pointer;
            font-weight: 600;
            color: var(--navy);
        }

        /* ── Tables ───────────────────────────────── */
        .table-wrapper { overflow-x: auto; }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        table.data-table th {
            background: #f9fafb;
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--navy);
            border-bottom: 2px solid #e5e7eb;
            white-space: nowrap;
        }
        table.data-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        table.data-table tbody tr:hover { background: #fafbfc; }

        /* ── Badges ───────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }
        .badge-published { background: #ecfdf5; color: #065f46; }
        .badge-draft     { background: #fef3c7; color: #92400e; }
        .badge-archived  { background: #f3f4f6; color: #6b7280; }
        .badge-daily     { background: #eff6ff; color: #1e40af; }
        .badge-special   { background: #fef2f2; color: #991b1b; }
        .badge-rate_card { background: #f5f3ff; color: #5b21b6; }

        /* ── Pagination ───────────────────────────── */
        .pagination {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            margin-top: 24px;
        }
        .pagination a, .pagination span {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            color: var(--navy);
            text-decoration: none;
            border: 1px solid #e5e7eb;
            transition: all .15s;
        }
        .pagination a:hover { background: #f3f4f6; border-color: #d1d5db; text-decoration: none; }
        .pagination span.current {
            background: var(--orange);
            color: #fff;
            border-color: var(--orange);
        }
        .pagination span.disabled {
            color: #ccc;
            pointer-events: none;
        }

        /* ── Stat cards ───────────────────────────── */
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
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform .2s;
        }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-icon {
            width: 52px; height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fff;
        }
        .stat-icon.orange  { background: linear-gradient(135deg, var(--orange), var(--orange-light)); }
        .stat-icon.navy    { background: linear-gradient(135deg, var(--navy), var(--navy-light)); }
        .stat-icon.green   { background: linear-gradient(135deg, #059669, #10b981); }
        .stat-icon.purple  { background: linear-gradient(135deg, #7c3aed, #a78bfa); }
        .stat-info { flex: 1; }
        .stat-value { font-size: 28px; font-weight: 700; color: var(--navy); line-height: 1.2; }
        .stat-label { font-size: 13px; color: #888; font-weight: 500; margin-top: 2px; }

        /* ── Quick Actions ────────────────────────── */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }
        .quick-action {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            padding: 24px 16px;
            background: #fff;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            text-decoration: none;
            color: var(--navy);
            font-weight: 600;
            font-size: 14px;
            transition: all .2s;
        }
        .quick-action:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            text-decoration: none;
        }
        .quick-action i {
            font-size: 28px;
            color: var(--orange);
        }

        /* ── Responsive ───────────────────────────── */
        @media (max-width: 768px) {
            .topbar-nav { display: none; }
            .hamburger  { display: block; }
            .portal-body { padding-left: 16px; padding-right: 16px; }
            .stat-grid { grid-template-columns: 1fr 1fr; }
            .quick-actions { grid-template-columns: 1fr 1fr; }
            table.data-table { font-size: 13px; }
            table.data-table th, table.data-table td { padding: 8px 10px; }
        }
        @media (max-width: 480px) {
            .stat-grid { grid-template-columns: 1fr; }
            .quick-actions { grid-template-columns: 1fr; }
            .user-menu-trigger span.user-name-text { display: none; }
        }
    </style>
</head>
<body>

<!-- ── Top Bar ───────────────────────────────── -->
<header class="portal-topbar">
    <div class="topbar-left">
        <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>
        <a href="<?php echo portal_url('index.php'); ?>" class="topbar-brand">
            <img src="<?php echo portal_cms_url('assets/appLogoIcon.png'); ?>" alt="KandaNews" onerror="this.style.display='none'">
            Kanda<span class="accent">News</span>
        </a>
    </div>

    <div class="topbar-right">
        <nav class="topbar-nav">
            <a href="<?php echo portal_url('index.php'); ?>" class="<?php echo $_current_page === 'index.php' ? 'active' : ''; ?>">
                <i class="fas fa-th-large"></i> Dashboard
            </a>
            <a href="<?php echo portal_url('editions.php'); ?>" class="<?php echo $_current_page === 'editions.php' ? 'active' : ''; ?>">
                <i class="fas fa-newspaper"></i> Editions
            </a>
            <a href="<?php echo portal_url('upload.php'); ?>" class="<?php echo $_current_page === 'upload.php' ? 'active' : ''; ?>">
                <i class="fas fa-cloud-upload-alt"></i> Upload
            </a>
            <a href="<?php echo portal_cms_url('build-edition.php'); ?>">
                <i class="fas fa-hammer"></i> Build Edition
            </a>
        </nav>

        <div class="user-menu">
            <button class="user-menu-trigger" onclick="toggleUserMenu()">
                <div class="user-avatar"><?php echo $_initials; ?></div>
                <span class="user-name-text"><?php echo htmlspecialchars($_username); ?></span>
                <i class="fas fa-chevron-down" style="font-size:10px;opacity:.6;"></i>
            </button>
            <div class="user-dropdown" id="userDropdown">
                <div class="user-dropdown-header">
                    <strong><?php echo htmlspecialchars($_username); ?></strong>
                    <small><?php echo $_role; ?></small>
                </div>
                <a href="<?php echo portal_url('index.php'); ?>"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="<?php echo portal_url('upload.php'); ?>"><i class="fas fa-cloud-upload-alt"></i> Upload Edition</a>
                <a href="<?php echo portal_url('login.php?action=logout'); ?>" class="logout-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </div>
</header>

<!-- ── Mobile Sidebar ────────────────────────── -->
<div class="portal-sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<nav class="portal-sidebar" id="sidebar">
    <button class="sidebar-close" onclick="toggleSidebar()">&times;</button>
    <div class="sidebar-brand">
        Kanda<span class="accent">News</span> Portal
    </div>
    <div class="sidebar-nav">
        <a href="<?php echo portal_url('index.php'); ?>" class="<?php echo $_current_page === 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-th-large"></i> Dashboard
        </a>
        <a href="<?php echo portal_url('editions.php'); ?>" class="<?php echo $_current_page === 'editions.php' ? 'active' : ''; ?>">
            <i class="fas fa-newspaper"></i> Editions
        </a>
        <a href="<?php echo portal_url('upload.php'); ?>" class="<?php echo $_current_page === 'upload.php' ? 'active' : ''; ?>">
            <i class="fas fa-cloud-upload-alt"></i> Upload Edition
        </a>
        <a href="<?php echo portal_cms_url('build-edition.php'); ?>">
            <i class="fas fa-hammer"></i> Build Edition
        </a>
        <a href="<?php echo portal_url('login.php?action=logout'); ?>">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<!-- ── Main Body ─────────────────────────────── -->
<main class="portal-body">

<?php if ($_flash): ?>
<div class="flash-message flash-<?php echo htmlspecialchars($_flash['type']); ?>">
    <i class="fas fa-<?php
        echo match($_flash['type']) {
            'success' => 'check-circle',
            'error'   => 'exclamation-circle',
            'warning' => 'exclamation-triangle',
            default   => 'info-circle',
        };
    ?>"></i>
    <?php echo htmlspecialchars($_flash['message']); ?>
</div>
<?php endif; ?>
