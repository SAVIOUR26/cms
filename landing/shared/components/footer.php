    </main>

    <footer class="kn-footer" role="contentinfo">
        <div class="kn-footer__inner">
            <div class="kn-footer__col">
                <div class="kn-footer__brand">
                    <img src="/shared/assets/img/kanda-square.png" alt="KandaNews" width="36" height="36">
                    <div>
                        <strong>KandaNews Africa</strong>
                        <span>Thirdsan Enterprises Ltd</span>
                    </div>
                </div>
                <p class="kn-footer__tagline">Africa's first digital flipping newspaper. Tap to Know. Tap to Grow.</p>
            </div>
            <div class="kn-footer__col">
                <h4 class="kn-footer__heading">Quick Links</h4>
                <a href="https://kandanews.africa">Africa Hub</a>
                <a href="https://ug.kandanews.africa">Uganda</a>
                <a href="https://kandanews.africa/blog/">Blog</a>
                <a href="mailto:<?php echo h($_email); ?>">Contact Us</a>
            </div>
            <div class="kn-footer__col">
                <h4 class="kn-footer__heading">Legal</h4>
                <a href="/privacy.php">Privacy Policy</a>
                <a href="/terms.php">Terms of Service</a>
            </div>
        </div>
        <div class="kn-footer__bottom">
            <span>&copy; <?php echo date('Y'); ?> KandaNews Africa &mdash; Thirdsan Enterprises Ltd. All rights reserved.</span>
        </div>
    </footer>

    <script src="/shared/assets/js/main.js"></script>
    <?php if (isset($extra_js)): ?>
        <script src="<?php echo h($extra_js); ?>"></script>
    <?php endif; ?>
</body>
</html>
