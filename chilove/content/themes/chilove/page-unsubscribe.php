<?php get_header(); ?>

<section class="page-head">
    <span class="eyebrow"><?= chi_paw(16) ?> Newsletter</span>
    <h1>Unsubscribe</h1>
    <p>Sorry to see you go. Enter the email address you subscribed with and we'll remove it.</p>
</section>

<div style="max-width:30rem;margin:0 auto 3rem">
    <?php if (($_GET['done'] ?? null) === '1'): ?>
        <div class="card" style="padding:22px;text-align:center">
            <h3>All set 🐾</h3>
            <p>If that address was on our list, it has been removed. You won't hear from us again —
               though you're always welcome back at the <a href="/#subscribe">blog</a>.</p>
        </div>
    <?php else: ?>
        <div class="card" style="padding:22px">
            <form action="/unsubscribe" method="post" class="subscribe-form">
                <input type="email" name="email" placeholder="you@email.com" required>
                <button class="button-primary" type="submit">Unsubscribe</button>
            </form>
            <p class="small" style="margin:10px 0 0;font-size:.8rem;color:var(--muted-cocoa)">
                Removal is immediate. Questions? <a href="/contact">Contact us</a> — see also our
                <a href="/privacy">privacy policy</a>.</p>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
