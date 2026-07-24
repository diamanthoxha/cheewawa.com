<?php
get_header();
$term  = chi_context()->term;
$posts = get_posts_by_category($term->slug);
?>

<div class="blog-layout">
    <div class="content-col">
        <div class="section-head">
            <h1><?= chi_paw(22) ?> <?= esc_html($term->name) ?></h1>
        </div>

        <?php if (!$posts): ?>
            <p class="empty">No posts in this category yet — check back soon!</p>
        <?php else: ?>
            <div class="post-grid">
                <?php foreach (array_values($posts) as $i => $p): ?>
                    <?= chi_post_card($p, ['lead' => $i === 0]) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
