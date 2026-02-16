<?php
/* Template Name: Kanda — Africa Hub */
get_header(); ?>
<main style="max-width:1180px;margin:40px auto;padding:0 20px">
  <?php while ( have_posts() ) : the_post(); ?>
    <!-- Your special hero/sections here -->
    <?php the_content(); ?>
  <?php endwhile; ?>
</main>
<?php get_footer(); ?>
