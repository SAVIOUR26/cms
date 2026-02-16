<?php
if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', ['script','style','gallery','caption']);
  register_nav_menus(['primary' => __('Primary Menu','kandanews-hub')]);
});

add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style('fa', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css', [], '6.5.0');
  wp_enqueue_style('hub-base', get_template_directory_uri().'/assets/css/base.css', [], '1.0.0');
  wp_enqueue_script('hub-main', get_template_directory_uri().'/assets/js/main.js', [], '1.0.0', true);
});
