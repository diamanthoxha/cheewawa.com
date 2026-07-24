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
.comments{margin-top:2.4rem}
.comments .comment{padding:14px 18px;margin:10px 0}
.comments .comment p{margin:.35rem 0 0}
.comment-form-card{padding:18px;margin-top:14px}
.comment-form input[type=text],.comment-form input[type=email],.comment-form textarea{width:100%;margin:6px 0;padding:10px 12px;border:1px solid #d9c6b4;border-radius:10px;font:inherit;background:#fffdf9}
.comment-form .hp-field{position:absolute;left:-9999px;opacity:0;height:0;overflow:hidden}
.comment-form .fineprint{margin:8px 0 0;font-size:.8rem;color:var(--muted-cocoa)}
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

            <?php // Reader comments (comments-20260723): approved shown; new ones held for review. ?>
            <?php $chiComments = get_post_comments((int) $p->id); ?>
            <section class="comments" id="comments">
                <h2><?= count($chiComments) ?: 'No' ?> comment<?= count($chiComments) === 1 ? '' : 's' ?> yet</h2>
                <?php if (($_GET['commented'] ?? null) === '1'): ?>
                    <div class="notice">Thanks! Your comment is awaiting review and will appear once approved. 🐾</div>
                <?php endif; ?>
                <?php foreach ($chiComments as $chiCm): ?>
                    <div class="card comment">
                        <p class="post-meta small"><strong><?= esc_html($chiCm->author_name) ?></strong> · <?= chi_date($chiCm->created_at) ?></p>
                        <p><?= nl2br(esc_html($chiCm->body)) ?></p>
                    </div>
                <?php endforeach; ?>
                <div class="card comment-form-card">
                    <h3>Leave a comment</h3>
                    <form action="/comment" method="post" class="comment-form">
                        <input type="hidden" name="post_id" value="<?= (int) $p->id ?>">
                        <input type="hidden" name="ts" value="<?= time() ?>">
                        <p class="hp-field" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>
                        <input type="text" name="author_name" placeholder="Your name" required minlength="2" maxlength="80">
                        <input type="email" name="author_email" placeholder="Email (optional, never shown)">
                        <textarea name="body" placeholder="Your comment…" required minlength="10" maxlength="3000" rows="4"></textarea>
                        <button class="button-primary" type="submit">Post comment</button>
                        <p class="fineprint">Comments are reviewed before they appear. By commenting you agree to our <a href="/privacy">privacy policy</a>.</p>
                    </form>
                </div>
            </section>

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
