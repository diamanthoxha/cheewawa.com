<?php
get_header();
$totalPosts = count_posts();
$cats       = get_categories();
?>

<section class="page-head">
    <span class="eyebrow"><?= chi_paw(16) ?> About</span>
    <h1>Hi, we're <span class="accent">Cheewawa</span></h1>
    <p>The place our friends already know: whenever a chihuahua question comes up,
       they ask us. This site is where we write the answers down.</p>
</section>

<div class="about-grid">
    <div class="about-prose">
        <p>Cheewawa started as a notebook full of scribbled care tips and a camera roll
           overflowing with Chihuahua photos. Today it's a friendly blog where new and
           seasoned owners come for honest, practical advice — and a daily dose of tiny-dog joy.</p>

        <p>We write about everything that matters to a Chihuahua's happy life: gentle training,
           cold-weather warmth, healthy feeding, common health questions, and the little
           moments that make these dogs so unforgettable.</p>

        <h2>What we cover</h2>
        <div class="cover-list">
            <?php foreach ($cats as $c): ?>
                <a class="category-pill" href="<?= category_permalink($c) ?>"><?= esc_html($c->name) ?></a>
            <?php endforeach; ?>
        </div>

        <h2>Our promise</h2>
        <p>Every tip is something we'd actually do with our own dogs. No fluff and no fear-mongering,
           just warm, useful guidance for raising a confident, well-loved Chihuahua.</p>

        <div class="hero-cta">
            <a class="button-primary" href="/blog">Read the blog <?= chi_icon('arrow', 18) ?></a>
            <a class="button-secondary" href="/contact">Say hello</a>
        </div>
    </div>

    <aside class="card sidebar-card author-card">
        <?= chi_portrait('blush') ?>
        <h3>Mia Carter</h3>
        <p>Founder &amp; resident Chihuahua wrangler. Lives with two very opinionated tiny dogs.</p>
        <div class="stat-row">
            <div class="stat"><b><?= (int) $totalPosts ?></b><span>POSTS</span></div>
            <div class="stat"><b><?= count($cats) ?></b><span>TOPICS</span></div>
            <div class="stat"><b>∞</b><span>PAW LOVE</span></div>
        </div>
    </aside>
</div>

<?php get_footer(); ?>
