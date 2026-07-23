    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div class="footer-brand">
                <?= chi_logo() ?>
                <p>We live with chihuahuas and write down what actually works. Health, training, and true stories about very small dogs.</p>
            </div>
            <div>
                <h4>Explore</h4>
                <?php foreach (get_categories() as $c): ?>
                    <?php if ($c->slug === 'gallery') continue; ?>
                    <a href="<?= category_permalink($c) ?>"><?= esc_html($c->name) ?></a>
                <?php endforeach; ?>
            </div>
            <div>
                <h4>Information</h4>
                <a href="/about">About us</a>
                <a href="/contact">Contact</a>
                <a href="/privacy">Privacy Policy</a>
                <a href="/cookies">Cookie Policy</a>
                <a href="/sitemap.xml">Sitemap</a>
            </div>
            <div>
                <h4>Let's Connect</h4>
                <div class="socials">
                    <?php // Author's real profiles (matches Person sameAs schema); swap to brand accounts when they exist. ?>
                    <a href="https://www.instagram.com/jett.mehmeti/" target="_blank" rel="noopener" aria-label="Instagram"><?= chi_icon('instagram') ?></a>
                    <a href="https://www.facebook.com/arjetamehemeti" target="_blank" rel="noopener" aria-label="Facebook"><?= chi_icon('facebook') ?></a>
                    <a href="/contact" aria-label="Contact us"><?= chi_icon('mail') ?></a>
                </div>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>© <?= date('Y') ?> <?= esc_html(site_name()) ?>. <?= esc_html(site_tagline()) ?></span>
            <span>Made by <a href="https://hoxmedia.net/" target="_blank" rel="noopener">HOX Media</a> with <?= chi_heart(14) ?> for tiny dogs.</span>
        </div>
    </footer>

    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
