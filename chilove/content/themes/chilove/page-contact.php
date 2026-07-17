<?php
get_header();
$sent = $_GET['sent'] ?? null;
?>

<section class="page-head">
    <span class="eyebrow"><?= chi_paw(16) ?> Contact</span>
    <h1>Come say <span class="accent">hello</span></h1>
    <p>Questions, story ideas, or just want to share a photo of your pup? We'd love to hear from you.</p>
</section>

<div class="contact-grid">
    <div class="card sidebar-card">
        <?php if ($sent === '1'): ?>
            <div class="notice page">Thanks! Your message is on its way. 🐾</div>
        <?php elseif ($sent === '0'): ?>
            <div class="notice page">Please check your name, a valid email, and a message.</div>
        <?php endif; ?>

        <form class="contact-form" action="/contact" method="post">
            <div class="field">
                <label for="name">Your name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="you@email.com" required>
            </div>
            <div class="field">
                <label for="message">Message</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <button class="button-primary" type="submit">Send message <?= chi_icon('arrow', 16) ?></button>
        </form>
    </div>

    <aside class="card sidebar-card contact-aside">
        <h3><?= chi_paw(20) ?> Other ways to reach us</h3>
        <p>We answer most messages within a couple of days. For quick hellos, find us on social.</p>
        <div class="socials">
            <a href="https://www.instagram.com/the_pupslife/" target="_blank" rel="noopener" aria-label="Instagram"><?= chi_icon('instagram', 24) ?></a>
            <a href="https://www.facebook.com/chihuahuasareawesome" target="_blank" rel="noopener" aria-label="Facebook"><?= chi_icon('facebook', 24) ?></a>
            <a href="mailto:di@manth.co" aria-label="Email"><?= chi_icon('mail', 24) ?></a>
        </div>
    </aside>
</div>

<?php get_footer(); ?>
