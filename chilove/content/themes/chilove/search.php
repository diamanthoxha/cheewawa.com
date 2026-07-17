<?php
get_header();
$q     = chi_context()->q;
$posts = $q !== '' ? search_posts($q) : [];
?>

<div class="blog-layout">
    <div class="content-col">
        <div class="section-head">
            <h2>Search<?= $q !== '' ? ': “' . esc_html($q) . '”' : '' ?></h2>
        </div>

        <?php if ($q === ''): ?>
            <p class="empty">Type something in the search box to find posts.</p>
        <?php elseif (!$posts): ?>
            <p class="empty">No posts matched “<?= esc_html($q) ?>”. Try another search?</p>
        <?php else: ?>
            <div class="post-grid">
                <?php foreach ($posts as $p): ?>
                    <?= chi_post_card($p) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
