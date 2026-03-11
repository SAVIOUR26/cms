<?php
// Expected variables (set by caller before including):
// $page_title  string  — <title> content
// $active_nav  string  — 'home'|'login'|'register'|'dashboard'
// $__adv       array|null — current advertiser (or null if guest)
$page_title = $page_title ?? 'KandaNews Ads';
$active_nav = $active_nav ?? 'home';
$__adv      = $__adv ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Advertise with KandaNews — Reach 10,000+ verified subscribers in Uganda and across Africa.">
    <title><?= h($page_title) ?> | KandaNews Ads</title>

    <!-- Preconnect -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Ads CSS -->
    <link rel="stylesheet" href="/assets/css/ads.css">

    <style>
        :root {
            --kn-navy: #1e2b42;
            --kn-navy-light: #2a3f5f;
            --kn-navy-dark: #0f172a;
            --kn-orange: #f05a1a;
            --kn-orange-hover: #ff7b48;
            --kn-orange-light: rgba(240,90,26,0.12);
            --kn-white: #ffffff;
            --kn-light: #f8fafc;
            --kn-gray-100: #f1f5f9;
            --kn-gray-200: #e5e7eb;
            --kn-gray-400: #94a3b8;
            --kn-gray-500: #64748b;
            --kn-gray-600: #475569;
            --kn-gray-700: #334155;
            --kn-ink: #0f172a;
            --kn-muted: #64748b;
            --kn-success: #059669;
            --kn-danger: #dc2626;
            --kn-warning: #d97706;
            --kn-font: "Inter", system-ui, -apple-system, sans-serif;
            --kn-radius: 14px;
            --kn-radius-sm: 10px;
            --kn-radius-lg: 20px;
            --kn-radius-full: 999px;
            --kn-shadow: 0 8px 30px rgba(0,0,0,0.06);
            --kn-shadow-md: 0 12px 40px rgba(0,0,0,0.08);
            --kn-shadow-orange: 0 8px 24px rgba(240,90,26,0.3);
            --kn-header-h: 68px;
            --kn-container: 1200px;
            --kn-ease: cubic-bezier(0.4,0,0.2,1);
        }
    </style>
</head>
<body>

<!-- ========== NAVIGATION ========== -->
<header class="kn-header" id="kn-header">
    <nav class="kn-nav container">
        <!-- Logo -->
        <a href="/index.php" class="kn-logo" aria-label="KandaNews Ads Home">
            <span class="kn-logo-icon"><i class="fa-solid fa-newspaper"></i></span>
            <span class="kn-logo-text">
                <span class="kn-logo-brand">KandaNews</span>
                <span class="kn-logo-sub">Ads Portal</span>
            </span>
        </a>

        <!-- Desktop nav links -->
        <ul class="kn-nav-links" id="kn-nav-links">
            <li><a href="/index.php" class="kn-nav-link<?= $active_nav === 'home' ? ' active' : '' ?>">
                <i class="fa-solid fa-house"></i> Home
            </a></li>
            <li><a href="/index.php#formats" class="kn-nav-link">
                <i class="fa-solid fa-table-list"></i> Ad Formats
            </a></li>
            <?php if ($__adv): ?>
                <li><a href="/dashboard.php" class="kn-nav-link<?= $active_nav === 'dashboard' ? ' active' : '' ?>">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a></li>
                <li><a href="/book.php" class="kn-nav-link">
                    <i class="fa-solid fa-calendar-plus"></i> Book Ad
                </a></li>
            <?php endif; ?>
        </ul>

        <!-- Auth actions -->
        <div class="kn-nav-actions">
            <?php if ($__adv): ?>
                <span class="kn-nav-user">
                    <i class="fa-solid fa-circle-user"></i>
                    <?= h($__adv['company_name']) ?>
                </span>
                <a href="/logout.php" class="kn-btn kn-btn-outline kn-btn-sm">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            <?php else: ?>
                <a href="/login.php" class="kn-btn kn-btn-outline kn-btn-sm<?= $active_nav === 'login' ? ' active' : '' ?>">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </a>
                <a href="/register.php" class="kn-btn kn-btn-primary kn-btn-sm<?= $active_nav === 'register' ? ' active' : '' ?>">
                    <i class="fa-solid fa-user-plus"></i> Register
                </a>
            <?php endif; ?>
        </div>

        <!-- Hamburger (mobile) -->
        <button class="kn-hamburger" id="kn-hamburger" aria-label="Toggle menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
    </nav>
</header>

<!-- Mobile nav overlay -->
<div class="kn-mobile-nav" id="kn-mobile-nav" aria-hidden="true">
    <div class="kn-mobile-nav-inner">
        <ul>
            <li><a href="/index.php"><i class="fa-solid fa-house"></i> Home</a></li>
            <li><a href="/index.php#formats"><i class="fa-solid fa-table-list"></i> Ad Formats</a></li>
            <?php if ($__adv): ?>
                <li><a href="/dashboard.php"><i class="fa-solid fa-gauge-high"></i> Dashboard</a></li>
                <li><a href="/book.php"><i class="fa-solid fa-calendar-plus"></i> Book Ad</a></li>
                <li><a href="/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            <?php else: ?>
                <li><a href="/login.php"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
                <li><a href="/register.php"><i class="fa-solid fa-user-plus"></i> Register</a></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<main class="kn-main">
