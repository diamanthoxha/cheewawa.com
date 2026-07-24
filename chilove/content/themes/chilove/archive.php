<?php
get_header();
$term    = chi_context()->term;
// Windowed query + pagination so every post stays reachable from its hub
// (pagination-20260724); previously only the newest 12 were linked here.
$page    = max(1, (int) ($_GET['paged'] ?? 1));
$perPage = 12;
$total   = count_posts($term->slug);
$posts   = get_posts_window($perPage, ($page - 1) * $perPage, $term->slug);
$base    = '/category/' . $term->slug;
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
                    <?= chi_post_card($p, ['lead' => $i === 0 && $page === 1]) ?>
                <?php endforeach; ?>
            </div>

            <?= chi_pagination($page, $total, $perPage, $base) ?>
        <?php endif; ?>
    </div>
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
