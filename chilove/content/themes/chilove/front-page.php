<?php
get_header();

$featured = get_featured_post();                              // newest published → hero slide
$grid     = get_recent_posts(9);     // newest 9 posts, hero article included so the grid also leads with the latest
?>

<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow"><?= chi_paw(16) ?> A Chihuahua Blog</span>
        <h1>All things <span class="accent">Chihuahua</span></h1>
        <?php if ($featured): ?>
            <p class="lead"><strong>Latest:</strong> <a href="<?= post_permalink($featured) ?>"><?= esc_html($featured->title) ?></a></p>
            <div class="hero-cta">
                <a class="button-primary" href="<?= post_permalink($featured) ?>">Read the latest <?= chi_icon('arrow', 18) ?></a>
                <a class="button-secondary" href="#subscribe">Join the Pack</a>
            </div>
        <?php else: ?>
            <p class="lead">Care tips, training guides, heartwarming stories, and everything
               in between for Chihuahua lovers.</p>
            <div class="hero-cta">
                <a class="button-primary" href="#latest">Latest Posts <?= chi_icon('arrow', 18) ?></a>
                <a class="button-secondary" href="#subscribe">Join the Pack</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="hero-art">
        <?php if ($featured): ?>
            <a class="hero-feature" href="<?= post_permalink($featured) ?>" aria-label="<?= esc_attr($featured->title) ?>"><?= chi_thumb($featured, 'blush', ['eager' => true, 'sizes' => 'hero']) ?><span class="hero-pill"><?= chi_heart(14) ?> Featured</span></a>
        <?php else: ?>
            <?= chi_portrait('blush') ?>
        <?php endif; ?>
        <span class="float float-heart"><?= chi_heart(28) ?></span>
        <span class="float float-paw"><?= chi_paw(30, '#d79a55') ?></span>
    </div>
</section>

<div class="blog-layout" id="latest">
    <div class="content-col">
        <div class="section-head">
            <h2><?= chi_paw(22) ?> Latest from the Blog</h2>
            <a class="see-all" href="/blog">View all <?= chi_icon('arrow', 14) ?></a>
        </div>

        <?php if ($grid): ?>
            <div class="post-grid">
                <?php foreach ($grid as $p): ?>
                    <?= chi_post_card($p) ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="muted">More chihuahua stories are on the way.</p>
        <?php endif; ?>

        <div class="center">
            <a class="button-secondary" href="/blog">View all posts <?= chi_icon('arrow', 16) ?></a>
        </div>
    </div>

    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
