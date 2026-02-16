<?php if (!defined('ABSPATH')) exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="icon" href="<?php echo esc_url( get_site_icon_url(32) ?: get_template_directory_uri().'/assets/img/kandanews-favicon-32x32.png'); ?>">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header class="hub-header">
  <!-- SINGLE orange layer -->
  <div class="hub-topbar">You are on <strong>KandaNews Africa</strong> — the hub.</div>

  <div class="container hub-head">
    <a class="hub-brand" href="<?php echo esc_url( home_url('/') ); ?>" aria-label="KandaNews Africa">
        <img
            class="hub-logo-img"
            src="<?php echo esc_url( get_template_directory_uri().'/assets/img/kanda-square.png'); ?>"
            alt="KandaNews"
            height="44" loading="eager" decoding="async">

        <span class="hub-title">
            KandaNews Africa <span class="hub-emoji" role="img" aria-label="Africa">🌍</span>
        </span>
    </a>



    <nav class="hub-nav" aria-label="Primary">
      <a class="nav-link hot" href="<?php echo esc_url( home_url('/about/') ); ?>">About Us</a>
      <a class="nav-link" href="#countries">Subscription</a>
      <a class="nav-link" href="#countries">Advertise</a>
      <a class="btn primary" href="<?php echo esc_url( 'https://blog.kandanews.africa/' ); ?>" target="_blank" rel="noopener noreferrer">BLOG</a>
    </nav>
  </div>
</header>

<main>
