<aside class="sidebar">
    <div class="card sidebar-card" id="about">
        <h3><?= chi_paw(20) ?> About us</h3>
        <p>We live with chihuahuas and write down what actually works. Health, training,
           and true stories about the world's tiniest dogs.</p>
    </div>

    <div class="card sidebar-card">
        <h3>Popular Posts</h3>
        <ul class="popular">
            <?php foreach (get_popular_posts(4) as $p): ?>
                <li>
                    <a class="pop-thumb" href="<?= post_permalink($p) ?>"><?= chi_thumb($p, 'mini') ?></a>
                    <div>
                        <a href="<?= post_permalink($p) ?>"><?= esc_html($p->title) ?></a>
                        <span class="post-meta small"><?= chi_date($p->published_at) ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="card subscribe" id="subscribe">
        <h3>Join Our Pack <?= chi_heart(18, '#ffffff') ?></h3>
        <p>Get new posts, care tips, and cute Chihuahua moments in your inbox.</p>
        <?php if (($_GET['subscribed'] ?? null) === '1'): ?>
            <div class="notice">You're in the pack! 🐾</div>
        <?php elseif (($_GET['subscribed'] ?? null) === '0'): ?>
            <div class="notice">Hmm, that email didn't look right — try again?</div>
        <?php endif; ?>
        <form action="/subscribe" method="post" class="subscribe-form">
            <input type="email" name="email" placeholder="you@email.com" required>
            <button class="button-primary" type="submit">Subscribe</button>
        </form>
        <p class="small" style="margin:8px 0 0;font-size:.78rem;color:var(--muted-cocoa)">By subscribing you agree to our <a href="/privacy">privacy policy</a>. Unsubscribe anytime at <a href="/unsubscribe">cheewawa.com/unsubscribe</a>.</p>
    </div>
</aside>
