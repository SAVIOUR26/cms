<?php if (!defined('ABSPATH')) exit; $cfg = kxn_brand_config(); ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="<?php echo esc_url( get_site_icon_url(32) ?: get_template_directory_uri().'/assets/img/kandanews-favicon-32x32.png' ); ?>" type="image/png">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header role="banner" class="kxn-header">
  <div class="container header-grid">
    <!-- Left: Logo -->
    <a class="logo-wrap" href="<?php echo esc_url( home_url('/') ); ?>" aria-label="Home">
      <img class="logo-img" src="<?php echo esc_url( get_template_directory_uri().'/assets/img/kanda-square.png' ); ?>" alt="KandaNews">
    </a>

    <!-- Center: Brand + Country -->
    <div class="centerbrand" aria-label="Site">
      <div class="title">
        <?php echo esc_html($cfg['brand']); ?>
        <span class="country">
          <span class="flag-inline" role="img" aria-label="<?php echo esc_attr($cfg['country']); ?> flag">
            <?php echo esc_html($cfg['flag']); ?>
          </span>
          <?php echo esc_html($cfg['country']); ?>
        </span>
      </div>
    </div>

    <!-- Right: Actions -->
    <nav class="nav-actions" aria-label="<?php esc_attr_e('Actions','kandanews'); ?>">
      <a class="btn darkghost" href="<?php echo esc_url($cfg['links']['switch_country']); ?>">
        <i class="fa-solid fa-globe" aria-hidden="true"></i> Switch Country
      </a>
      <a class="btn primary" href="<?php echo esc_url($cfg['links']['login']); ?>">Read Now</a>
      <a class="btn darkghost" href="<?php echo esc_url($cfg['links']['login']); ?>">Login / Register</a>
    </nav>
  </div>

  <!-- Subbar in orange -->
  <div class="subbar subbar-accent">Daily stories. Flipped to inspire. Just a tap away.</div>
</header>

<main>
