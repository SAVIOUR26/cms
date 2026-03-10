<?php
/**
 * KandaNews Africa — Shared Header Component
 * Auto-detects country from subdomain, adapts branding.
 *
 * Required: Include country-config.php before this file.
 */
$_brand = 'KandaNews';
$_country_name = $COUNTRY['name'] ?? 'Africa';
$_flag = $COUNTRY['flag'] ?? '🌍';
$_email = $COUNTRY['email'] ?? 'hello@kandanews.africa';
$_is_hub = ($_is_hub ?? false) || ($COUNTRY_CODE ?? '') === '' || !isset($COUNTRIES[$COUNTRY_CODE ?? '']);
$_hub_url = 'https://kandanews.africa';
$_page_title = isset($page_title) ? $page_title : h($_brand . ' ' . $_country_name) . ' — The Future of News';
$_page_desc = isset($page_description) ? $page_description : 'Africa\'s first digital flipping newspaper. Daily interactive editions for students, professionals and entrepreneurs — mobile-first, fast, and built to help you grow.';
$_og_image = isset($og_image) ? $og_image : '/shared/assets/img/kanda-icon.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $_page_title; ?></title>
    <meta name="description" content="<?php echo h($_page_desc); ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo h($_page_title); ?>">
    <meta property="og:description" content="<?php echo h($_page_desc); ?>">
    <meta property="og:image" content="<?php echo h($_og_image); ?>">
    <meta property="og:site_name" content="KandaNews Africa">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo h($_page_title); ?>">
    <meta name="twitter:description" content="<?php echo h($_page_desc); ?>">
    <meta name="twitter:image" content="<?php echo h($_og_image); ?>">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NewsMediaOrganization",
        "name": "KandaNews Africa",
        "url": "https://kandanews.africa",
        "logo": "https://kandanews.africa/shared/assets/img/kanda-icon.png",
        "description": "Africa's first digital flipping newspaper"
    }
    </script>

    <!-- Favicon — KandaNews icon -->
    <link rel="icon" type="image/png" sizes="192x192" href="/shared/assets/img/kanda-icon.png">
    <link rel="icon" type="image/png" sizes="32x32"  href="/shared/assets/img/kanda-icon.png">
    <link rel="apple-touch-icon"                      href="/shared/assets/img/kanda-icon.png">

    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="/shared/assets/css/base.css">
    <?php if (isset($extra_css)): ?>
        <link rel="stylesheet" href="<?php echo h($extra_css); ?>">
    <?php endif; ?>
</head>
<body>
    <header class="kn-header" role="banner">
        <div class="kn-header__inner">
            <a href="<?php echo $_is_hub ? '/' : h($_hub_url); ?>" class="kn-header__logo" aria-label="<?php echo h($_brand); ?> home">
                <img src="/shared/assets/img/kanda-icon.png" alt="<?php echo h($_brand); ?>" width="44" height="44" style="border-radius:10px;">
            </a>
            <div class="kn-header__brand">
                <span class="kn-header__name">
                    <?php echo h($_brand); ?>
                    <span class="kn-header__flag" aria-label="<?php echo h($_country_name); ?>"><?php echo $_flag; ?></span>
                </span>
                <span class="kn-header__country"><?php echo h($_country_name); ?></span>
            </div>
            <nav class="kn-header__nav" id="main-nav" aria-label="Main navigation">
                <?php if (!$_is_hub): ?>
                    <a href="<?php echo h($_hub_url); ?>/#countries" class="kn-header__link">Countries</a>
                <?php else: ?>
                    <a href="#countries" class="kn-header__link">Countries</a>
                <?php endif; ?>
                <a href="<?php echo h($_hub_url); ?>/about.php" class="kn-header__link">About Us</a>
                <a href="<?php echo h($_hub_url); ?>/blog/" class="kn-header__link">Blog</a>
                <a href="#download" class="kn-header__link kn-header__link--cta">
                    <i class="fa-solid fa-download"></i> Download App
                </a>
            </nav>
            <button class="kn-header__burger" aria-label="Open menu" aria-expanded="false" aria-controls="mobile-nav" type="button">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
        <nav class="kn-mobile-nav" id="mobile-nav" aria-label="Mobile navigation">
            <?php if (!$_is_hub): ?>
                <a href="<?php echo h($_hub_url); ?>/#countries" class="kn-mobile-nav__link">Countries</a>
            <?php else: ?>
                <a href="#countries" class="kn-mobile-nav__link">Countries</a>
            <?php endif; ?>
            <a href="<?php echo h($_hub_url); ?>/about.php" class="kn-mobile-nav__link">About Us</a>
            <a href="<?php echo h($_hub_url); ?>/blog/" class="kn-mobile-nav__link">Blog</a>
            <a href="#download" class="kn-mobile-nav__link kn-mobile-nav__link--cta">
                <i class="fa-solid fa-download"></i> Download App
            </a>
        </nav>
    </header>
    <main>

