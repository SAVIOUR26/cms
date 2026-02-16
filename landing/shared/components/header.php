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
$_is_hub = ($COUNTRY_CODE ?? '') === '' || !isset($COUNTRIES[$COUNTRY_CODE ?? '']);
$_login_url = '/login.php';
$_hub_url = 'https://kandanews.africa';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo h($_brand . ' ' . $_country_name); ?> — The Future of News. Daily interactive flipbook editions for students, professionals and entrepreneurs.">
    <title><?php echo h($_brand . ' ' . $_country_name); ?> — The Future of News</title>
    <link rel="icon" type="image/png" href="/shared/assets/img/kanda-square.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="/shared/assets/css/base.css">
    <?php if (isset($extra_css)): ?>
        <link rel="stylesheet" href="<?php echo h($extra_css); ?>">
    <?php endif; ?>
</head>
<body>
    <header class="kn-header" role="banner">
        <div class="kn-header__inner">
            <a href="/" class="kn-header__logo" aria-label="<?php echo h($_brand); ?> home">
                <img src="/shared/assets/img/kanda-square.png" alt="<?php echo h($_brand); ?>" width="48" height="48">
            </a>
            <div class="kn-header__brand">
                <span class="kn-header__name"><?php echo h($_brand); ?></span>
                <span class="kn-header__country"><?php echo $_flag; ?> <?php echo h($_country_name); ?></span>
            </div>
            <nav class="kn-header__actions" aria-label="Main navigation">
                <a href="<?php echo h($_hub_url); ?>/#countries" class="kn-header__link">Switch Country</a>
                <a href="/#subscribe" class="kn-header__link kn-header__link--cta">Subscribe</a>
                <a href="<?php echo h($_login_url); ?>" class="kn-header__link kn-header__link--login">Login / Register</a>
            </nav>
            <button class="kn-header__burger" aria-label="Menu" aria-expanded="false" type="button">
                <i class="fa-solid fa-bars"></i>
            </button>
        </div>
        <div class="kn-subbar">
            Daily stories. Flipped to inspire. Just a tap away.
        </div>
    </header>
    <main>
