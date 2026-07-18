<?php
get_header();

$cat     = (string) ($_GET['cat'] ?? '');
$cat     = preg_match('/^[a-z0-9-]+$/', $cat) ? $cat : '';
$page    = max(1, (int) ($_GET['paged'] ?? 1));
$perPage = 6;

$total = count_posts($cat !== '' ? $cat : null);
// Page 1 renders its first post as a full-width lead card, so it takes one
// extra post (1 lead + 6 grid cards) to keep the 3-column grid even; later
// pages show 6 and start one post further in.
$posts = $page === 1
    ? get_posts_window($perPage + 1, 0, $cat !== '' ? $cat : null)
    : get_posts_window($perPage, ($page - 1) * $perPage + 1, $cat !== '' ? $cat : null);
$base  = '/blog' . ($cat !== '' ? '?cat=' . $cat : '');
?>

<section class="page-head">
    <span class="eyebrow"><?= chi_paw(16) ?> The Cheewawa Blog</span>
    <h1>Every post, in one place</h1>
    <p>Care, training, lifestyle, and health — everything we've written about life with tiny dogs.</p>
</section>

<div class="filter-chips">
    <a class="chip <?= $cat === '' ? 'active' : '' ?>" href="/blog">All</a>
    <?php foreach (get_categories() as $c): ?>
        <?php if ($c->slug === 'gallery') continue; ?>
        <a class="chip <?= $cat === $c->slug ? 'active' : '' ?>" href="/blog?cat=<?= $c->slug ?>"><?= esc_html($c->name) ?></a>
    <?php endforeach; ?>
</div>

<?php if (!$posts): ?>
    <p class="empty center">No posts here yet — check back soon!</p>
<?php else: ?>
    <div class="post-grid">
        <?php foreach (array_values($posts) as $i => $p): ?>
            <?= chi_post_card($p, ['lead' => $i === 0 && $page === 1, 'words' => 18]) ?>
        <?php endforeach; ?>
    </div>

    <?= chi_pagination($page, max(0, $total - 1), $perPage, $base) ?>
<?php endif; ?>

<?php get_footer(); ?>
