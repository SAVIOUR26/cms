<?php
/**
 * KandaNews Africa — Blog Listing
 * Flat-file markdown blog. No database.
 */
$COUNTRY_CODE = '';
$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$COUNTRIES = [];
require_once __DIR__ . '/../shared/includes/helpers.php';
require_once __DIR__ . '/../shared/includes/country-config.php';
require_once __DIR__ . '/includes/markdown.php';

$COUNTRY = ['name' => 'Africa', 'flag' => '🌍', 'email' => 'hello@kandanews.africa'];
$_country_name = 'Africa';
$_is_hub = true;

$page_title = 'KandaNews Blog — News, Insights & Updates from Africa';
$page_description = 'The KandaNews blog: articles on digital media, African innovation, mobile-first news, and building the future of journalism across the continent.';

require_once __DIR__ . '/../shared/components/header.php';

$posts = get_all_posts(__DIR__ . '/posts');
?>

<!-- ===== BLOG HERO ===== -->
<section class="kn-blog-hero kn-reveal" aria-label="Blog">
  <div class="container">
    <span class="kn-blog-hero__eyebrow">
      <i class="fa-solid fa-pen-nib"></i> KandaNews Blog
    </span>
    <h1 class="kn-blog-hero__title">News, Insights &amp; Updates</h1>
    <p class="kn-blog-hero__desc">Stories about building Africa's digital news future — from our team to you.</p>
  </div>
</section>


<!-- ===== POST GRID ===== -->
<section class="kn-section kn-section--alt" aria-label="Blog posts">
  <div class="container">

    <?php if (empty($posts)): ?>
      <div class="text-center" style="padding:3rem 0;">
        <p style="color:var(--kn-muted);font-size:1.1rem;">No posts yet. Check back soon!</p>
      </div>
    <?php else: ?>

      <div class="kn-posts-grid" role="list">
        <?php foreach ($posts as $post): ?>
          <article class="kn-post-card kn-reveal" role="listitem">
            <?php if ($post['image']): ?>
              <a href="/blog/post.php?slug=<?php echo urlencode($post['slug']); ?>" class="kn-post-card__img-link" aria-hidden="true" tabindex="-1">
                <img
                  class="kn-post-card__img"
                  src="<?php echo h($post['image']); ?>"
                  alt="<?php echo h($post['title']); ?>"
                  loading="lazy"
                >
              </a>
            <?php endif; ?>

            <div class="kn-post-card__body">
              <div class="kn-post-card__meta">
                <time datetime="<?php echo h($post['date']); ?>"><?php echo format_date($post['date']); ?></time>
                <?php if ($post['author']): ?>
                  <span>&middot;</span>
                  <span><?php echo h($post['author']); ?></span>
                <?php endif; ?>
                <?php if ($post['tags']): ?>
                  <span>&middot;</span>
                  <span class="kn-post-card__tag"><?php echo h($post['tags']); ?></span>
                <?php endif; ?>
              </div>

              <h2 class="kn-post-card__title">
                <a href="/blog/post.php?slug=<?php echo urlencode($post['slug']); ?>">
                  <?php echo h($post['title']); ?>
                </a>
              </h2>

              <?php if ($post['excerpt']): ?>
                <p class="kn-post-card__excerpt"><?php echo h($post['excerpt']); ?></p>
              <?php endif; ?>

              <a href="/blog/post.php?slug=<?php echo urlencode($post['slug']); ?>" class="kn-post-card__read">
                Read article <i class="fa-solid fa-arrow-right"></i>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/../shared/components/footer.php'; ?>
