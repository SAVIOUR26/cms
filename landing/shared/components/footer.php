    </main>

    <footer class="kn-footer" role="contentinfo">
        <div class="kn-footer__grid">
            <div class="kn-footer__brand">
                <div class="kn-footer__brand-top">
                    <img src="/shared/assets/img/kanda-square.png" alt="KandaNews" width="36" height="36">
                    <span class="kn-footer__brand-name">KandaNews Africa</span>
                </div>
                <p class="kn-footer__brand-desc">Africa's first digital flipping newspaper. Tap to Know. Tap to Grow.</p>
                <div class="kn-footer__social">
                    <a href="https://twitter.com/kandanews" aria-label="Twitter"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="https://facebook.com/kandanews" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://instagram.com/kandanews" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>
            <div>
                <h4 class="kn-footer__col-title">Quick Links</h4>
                <div class="kn-footer__links">
                    <a href="https://kandanews.africa">Africa Hub</a>
                    <a href="https://ug.kandanews.africa">Uganda</a>
                    <a href="https://kandanews.africa/blog/">Blog</a>
                    <a href="mailto:<?php echo h($_email); ?>">Contact Us</a>
                </div>
            </div>
            <div>
                <h4 class="kn-footer__col-title">Legal</h4>
                <div class="kn-footer__links">
                    <a href="/privacy.php">Privacy Policy</a>
                    <a href="/terms.php">Terms of Service</a>
                </div>
            </div>
        </div>
        <div class="kn-footer__bottom">
            <span>&copy; <?php echo date('Y'); ?> KandaNews Africa &mdash; Thirdsan Enterprises Ltd. All rights reserved.</span>
            <span><a href="/privacy.php">Privacy</a> &middot; <a href="/terms.php">Terms</a></span>
        </div>
    </footer>

    <script src="/shared/assets/js/main.js"></script>
    <?php if (isset($extra_js)): ?>
        <script src="<?php echo h($extra_js); ?>"></script>
    <?php endif; ?>
</body>
</html>
