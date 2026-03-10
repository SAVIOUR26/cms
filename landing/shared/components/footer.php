    </main>

    <footer class="kn-footer" role="contentinfo">
        <div class="kn-footer__grid">
            <div class="kn-footer__brand">
                <div class="kn-footer__brand-top">
                    <img src="/shared/assets/img/kanda-icon.png" alt="KandaNews" width="40" height="40" style="border-radius:10px;">
                    <span class="kn-footer__brand-name">KandaNews Africa</span>
                </div>
                <p class="kn-footer__brand-desc">Africa's first digital flipping newspaper. Tap to Know. Tap to Grow.</p>
                <div class="kn-footer__social">
                    <a href="https://twitter.com/kandanews" aria-label="Twitter / X"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="https://facebook.com/kandanews" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://instagram.com/kandanews" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://linkedin.com/company/kandanews" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
            </div>
            <div>
                <h4 class="kn-footer__col-title">Quick Links</h4>
                <div class="kn-footer__links">
                    <a href="https://kandanews.africa">Africa Hub</a>
                    <a href="https://ug.kandanews.africa">Uganda Edition</a>
                    <a href="https://kandanews.africa/about.php">About Us</a>
                    <a href="https://kandanews.africa/blog/">Blog</a>
                    <a href="mailto:<?php echo h($_email); ?>">Contact Us</a>
                </div>
            </div>
            <div>
                <h4 class="kn-footer__col-title">Editions</h4>
                <div class="kn-footer__links">
                    <a href="https://ug.kandanews.africa">🇺🇬 Uganda</a>
                    <a href="https://ke.kandanews.africa">🇰🇪 Kenya</a>
                    <a href="https://ng.kandanews.africa">🇳🇬 Nigeria</a>
                    <a href="https://za.kandanews.africa">🇿🇦 South Africa</a>
                </div>
            </div>
            <div>
                <h4 class="kn-footer__col-title">Legal</h4>
                <div class="kn-footer__links">
                    <a href="https://kandanews.africa/privacy.php">Privacy Policy</a>
                    <a href="https://kandanews.africa/terms.php">Terms of Service</a>
                </div>
            </div>
        </div>
        <div class="kn-footer__bottom">
            <span>&copy; <?php echo date('Y'); ?> KandaNews Africa &mdash; Thirdsan Enterprises Ltd. All rights reserved.</span>
            <span>
                <a href="https://kandanews.africa/about.php">About</a> &middot;
                <a href="https://kandanews.africa/privacy.php">Privacy</a> &middot;
                <a href="https://kandanews.africa/terms.php">Terms</a>
            </span>
        </div>
    </footer>

    <script src="/shared/assets/js/main.js"></script>
    <?php if (isset($extra_js)): ?>
        <script src="<?php echo h($extra_js); ?>"></script>
    <?php endif; ?>
</body>
</html>
