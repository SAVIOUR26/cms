    </main>

    <footer class="kn-footer" role="contentinfo">
        <div class="kn-footer__inner">
            <div class="kn-footer__brand">
                <img src="/shared/assets/img/kanda-square.png" alt="KandaNews" width="32" height="32">
                <span>&copy; <?php echo date('Y'); ?> KandaNews Africa &mdash; Thirdsan Enterprises Ltd</span>
            </div>
            <div class="kn-footer__links">
                <a href="https://kandanews.africa">Africa Hub</a>
                <a href="/privacy.php">Privacy</a>
                <a href="/terms.php">Terms</a>
                <a href="mailto:<?php echo h($_email); ?>"><?php echo h($_email); ?></a>
            </div>
        </div>
    </footer>

    <script src="/shared/assets/js/main.js"></script>
    <?php if (isset($extra_js)): ?>
        <script src="<?php echo h($extra_js); ?>"></script>
    <?php endif; ?>
</body>
</html>
