<?php
get_header();
$p       = chi_context()->post;
$related = get_related_posts($p, 3);
?>

<style>
.single-media .featured-photo{width:100%;height:auto;display:block;border-radius:inherit;object-fit:cover}
.post-content figure{margin:1.8rem 0}
.post-content figure img{width:100%;max-width:560px;height:auto;border-radius:12px;display:block;margin:0 auto;box-shadow:var(--shadow-soft)}
.post-content figcaption{margin-top:.5rem;font-size:.9rem;color:var(--muted-cocoa);text-align:center;font-style:italic}
.post-memorial{margin:2.5rem 0;padding:.6rem;text-align:center}
.post-memorial img{width:100%;max-width:520px;height:auto;border-radius:12px;display:block;margin:0 auto}
.post-memorial figcaption{margin-top:.6rem;font-weight:700;color:var(--muted-cocoa)}
</style>

<article class="single">
    <div class="blog-layout">
        <div class="content-col">
            <a class="back" href="/blog">&larr; Back to all posts</a>
            <header class="single-head">
                <span class="category-pill"><?= esc_html($p->category ?? '') ?></span>
                <h1 class="post-title"><?= esc_html($p->title) ?></h1>
                <div class="post-meta">
                    <span><?php if (!empty($p->author)): ?><a href="<?= esc_attr('/author/' . chi_author_slug((string) $p->author)) ?>"><?= esc_html($p->author) ?></a><?php endif; ?></span> ·
                    <span><?= chi_date($p->published_at) ?></span> ·
                    <?php if (!empty($p->updated_at) && substr((string) $p->updated_at, 0, 10) !== substr((string) $p->published_at, 0, 10)): ?>
                    <span>Updated <?= chi_date($p->updated_at) ?></span> ·
                    <?php endif; ?>
                    <span><?= chi_read_time($p) ?></span>
                </div>
            </header>
            <?php
            $featured = chi_featured_image($p);
            $fdim = $featured ? @getimagesize(CHILOVE_ROOT . '/public/' . ltrim($featured, '/')) : null;
            ?>
            <div class="single-media card">
                <?php if ($featured): ?>
                    <?php $fset = chi_srcset($featured); ?>
                    <img class="featured-photo" src="<?= esc_attr($featured) ?>" alt="<?= esc_attr($p->title) ?>"<?= $fdim ? ' width="' . $fdim[0] . '" height="' . $fdim[1] . '"' : '' ?><?= $fset !== '' ? ' srcset="' . esc_attr($fset) . '" sizes="' . esc_attr(chi_sizes('featured')) . '"' : '' ?> loading="eager" fetchpriority="high">
                <?php else: ?>
                    <?= chi_thumb($p, 'tan') ?>
                <?php endif; ?>
            </div>
            <div class="post-content"><?= chi_content_html($p->content ?? '') ?></div>

            <?php if (!empty($p->author_bio)): ?>
                <div class="card author-box">
                    <?php if (!empty($p->author_avatar)): ?>
                        <img class="author-avatar" src="<?= esc_attr($p->author_avatar) ?>" alt="<?= esc_attr($p->author) ?>" width="72" height="72" loading="lazy">
                    <?php endif; ?>
                    <div>
                        <h3>Written by <a href="<?= esc_attr('/author/' . chi_author_slug((string) $p->author)) ?>"><?= esc_html($p->author) ?></a></h3>
                        <p><?= esc_html($p->author_bio) ?></p>
                        <p><a class="read-more" href="<?= esc_attr('/author/' . chi_author_slug((string) $p->author)) ?>">All posts by <?= esc_html($p->author) ?> <?= chi_icon('arrow', 14) ?></a></p>
                        <?php if (($chiEd = chi_site_editor()) && $chiEd->display_name !== $p->author): ?>
                        <p class="post-meta small">Edited by <a href="<?= esc_attr(author_permalink($chiEd)) ?>"><?= esc_html($chiEd->display_name) ?></a></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php
            $memorial = chi_post_memorial($p->slug);
            $mdim = $memorial ? @getimagesize(CHILOVE_ROOT . '/public/' . ltrim($memorial['url'], '/')) : null;
            ?>
            <?php if ($memorial): ?>
                <figure class="post-memorial card">
                    <img src="<?= esc_attr($memorial['url']) ?>" alt="<?= esc_attr($memorial['alt']) ?>"<?= $mdim ? ' width="' . $mdim[0] . '" height="' . $mdim[1] . '"' : '' ?> loading="lazy">
                    <figcaption>In loving memory</figcaption>
                </figure>
            <?php endif; ?>

            <?php if ($related): ?>
                <section class="related">
                    <h2>More from <?= esc_html($p->category ?? 'the blog') ?></h2>
                    <div class="post-grid">
                        <?php foreach ($related as $r): ?>
                            <?= chi_post_card($r, ['excerpt' => false]) ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
        <?php get_sidebar(); ?>
    </div>
</article>

<?= chi_jsonld_blocks($p) ?>

<?php get_footer(); ?>
