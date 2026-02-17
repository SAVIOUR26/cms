<?php if (!defined('ABSPATH')) exit; $cfg = kxn_brand_config(); ?>
</main>

<footer class="kxn-footer">
  <div class="container footer-inner">

    <div class="footer-info-row">
      To learn more about us or to review our Terms and Privacy Policy, visit the <a href="https://kandanews.africa/" class="footer-mail" target="_blank" rel="noopener">KandaNews Hub</a>.
    </div>

    <hr class="footer-divider" style="height: 1px; border: none; background-color: white; width: 100%; margin: 1rem 0;">

    <div class="footer-text">
      &copy; 2025 <?php echo esc_html($cfg['brand'].' '.$cfg['country']); ?> | 
      A subsidiary of KandaNews Africa CO. • 
      <a class="footer-mail" href="mailto:<?php echo antispambot($cfg['email']); ?>">
        <?php echo antispambot($cfg['email']); ?>
      </a>
    </div>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>