<?php
/**
 * KandaNews Africa — Single Blog Post
 * Renders a markdown post by slug.
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

// ── Load post ──
$slug = $_GET['slug'] ?? '';
$post = get_post(__DIR__ . '/posts', $slug);

if (!$post) {
    http_response_code(404);
    $page_title = 'Post Not Found — KandaNews Blog';
    $page_description = 'The blog post you are looking for could not be found.';
    require_once __DIR__ . '/../shared/components/header.php';
    ?>
    <section class="kn-section text-center" style="min-height:50vh;display:flex;align-items:center;justify-content:center;">
      <div class="container">
        <h1 style="font-size:3rem;margin-bottom:1rem;">404</h1>
        <p style="color:var(--kn-muted);font-size:1.1rem;margin-bottom:2rem;">This post doesn't exist or has been removed.</p>
        <a href="/blog/" class="kn-btn kn-btn--primary"><i class="fa-solid fa-arrow-left"></i> Back to Blog</a>
      </div>
    </section>
    <?php
    require_once __DIR__ . '/../shared/components/footer.php';
    exit;
}

// ── SEO meta from post front matter ──
$page_title = h($post['title']) . ' — KandaNews Blog';
$page_description = $post['excerpt'] ?: 'Read ' . $post['title'] . ' on the KandaNews blog.';
if ($post['image']) {
    $og_image = $post['image'];
}

// Reading time
$raw = file_get_contents(__DIR__ . '/posts/' . preg_replace('/[^a-zA-Z0-9\-\.]/', '', $slug) . '.md');
$parsed = parse_front_matter($raw);
$read_time = reading_time($parsed['body']);

require_once __DIR__ . '/../shared/components/header.php';
?>

<!-- ===== ARTICLE ===== -->
<article class="kn-article kn-reveal" aria-label="<?php echo h($post['title']); ?>">

  <!-- Article Header -->
  <header class="kn-article__header">
    <div class="container">
      <a href="/blog/" class="kn-article__back"><i class="fa-solid fa-arrow-left"></i> All posts</a>

      <?php if ($post['tags']): ?>
        <span class="kn-article__tag"><?php echo h($post['tags']); ?></span>
      <?php endif; ?>

      <h1 class="kn-article__title"><?php echo h($post['title']); ?></h1>

      <div class="kn-article__meta">
        <time datetime="<?php echo h($post['date']); ?>">
          <i class="fa-regular fa-calendar"></i> <?php echo format_date($post['date']); ?>
        </time>
        <span>&middot;</span>
        <span><i class="fa-regular fa-user"></i> <?php echo h($post['author']); ?></span>
        <span>&middot;</span>
        <span><i class="fa-regular fa-clock"></i> <?php echo $read_time; ?></span>
      </div>
    </div>
  </header>

  <!-- Featured Image -->
  <?php if ($post['image']): ?>
    <div class="kn-article__hero">
      <div class="container">
        <img
          class="kn-article__hero-img"
          src="<?php echo h($post['image']); ?>"
          alt="<?php echo h($post['title']); ?>"
        >
      </div>
    </div>
  <?php endif; ?>

  <!-- Article Body -->
  <div class="kn-article__body">
    <div class="container">
      <div class="kn-prose">
        <?php echo $post['html']; ?>
      </div>
    </div>
  </div>

  <!-- Article Footer -->
  <footer class="kn-article__footer">
    <div class="container">
      <div class="kn-article__share">
        <span class="kn-article__share-label">Share this article:</span>
        <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($post['title']); ?>&url=<?php echo urlencode('https://kandanews.africa/blog/post.php?slug=' . $slug); ?>"
           target="_blank" rel="noopener" class="kn-article__share-btn" aria-label="Share on Twitter">
          <i class="fa-brands fa-x-twitter"></i>
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode('https://kandanews.africa/blog/post.php?slug=' . $slug); ?>"
           target="_blank" rel="noopener" class="kn-article__share-btn" aria-label="Share on Facebook">
          <i class="fa-brands fa-facebook-f"></i>
        </a>
        <a href="https://wa.me/?text=<?php echo urlencode($post['title'] . ' — https://kandanews.africa/blog/post.php?slug=' . $slug); ?>"
           target="_blank" rel="noopener" class="kn-article__share-btn" aria-label="Share on WhatsApp">
          <i class="fa-brands fa-whatsapp"></i>
        </a>
      </div>

      <div class="kn-article__nav">
        <a href="/blog/" class="kn-btn kn-btn--outline"><i class="fa-solid fa-arrow-left"></i> Back to Blog</a>
      </div>
    </div>
  </footer>

</article>

<?php require_once __DIR__ . '/../shared/components/footer.php'; ?>
