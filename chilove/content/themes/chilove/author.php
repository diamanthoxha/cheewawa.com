<?php
get_header();
$u       = chi_context()->user;
// Paginated so the full archive is reachable from the author hub
// (pagination-20260724); previously capped at the newest 60 with no links on.
$page    = max(1, (int) ($_GET['paged'] ?? 1));
$perPage = 12;
$total   = count_posts_by_author((int) $u->id);
$posts   = get_posts_by_author((int) $u->id, $perPage, ($page - 1) * $perPage);
$base    = site_url();
?>

<style>
.author-hero{display:flex;gap:22px;align-items:center;padding:26px;margin-bottom:1.6rem}
.author-hero img{border-radius:50%;flex-shrink:0}
.author-hero h1{margin:0 0 8px;font-size:1.7rem}
.author-hero .author-meta{margin:0 0 10px;font-weight:700;color:var(--muted-cocoa)}
.author-hero p{margin:0}
@media (max-width:600px){.author-hero{flex-direction:column;text-align:center}}
</style>

<div class="blog-layout">
    <div class="content-col">
        <a class="back" href="/blog">&larr; Back to all posts</a>

        <section class="card author-hero">
            <?php if (!empty($u->avatar)): ?>
                <img src="<?= esc_attr($u->avatar) ?>" alt="<?= esc_attr($u->display_name) ?>" width="110" height="110">
            <?php endif; ?>
            <div>
                <h1><?= esc_html($u->display_name) ?></h1>
                <?php $chiIsEditor = (int) $u->post_count === 0; ?>
                <p class="author-meta"><?= $chiIsEditor ? 'Founder &amp; Editor of ' . esc_html(site_name()) : (int) $u->post_count . ' article' . ((int) $u->post_count === 1 ? '' : 's') . ' on ' . esc_html(site_name()) ?></p>
                <?php if (!empty($u->bio)): ?><p><?= esc_html($u->bio) ?></p><?php endif; ?>
                <?php if ($chiSameas = chi_author_sameas($u)): ?>
                    <p style="margin-top:10px">
                    <?php foreach ($chiSameas as $chiUrl): ?>
                        <a href="<?= esc_attr($chiUrl) ?>" rel="me noopener" target="_blank"><?= str_contains($chiUrl, 'instagram') ? chi_icon('instagram', 20) : (str_contains($chiUrl, 'facebook') ? chi_icon('facebook', 20) : esc_html(parse_url($chiUrl, PHP_URL_HOST))) ?></a>
                    <?php endforeach; ?>
                    </p>
                <?php endif; ?>
            </div>
        </section>

        <?php if ($posts): ?>
            <div class="section-head">
                <h2><?= chi_paw(22) ?> Articles by <?= esc_html($u->display_name) ?></h2>
            </div>
            <div class="post-grid">
                <?php foreach (array_values($posts) as $i => $p): ?>
                    <?= chi_post_card($p, ['lead' => $i === 0 && $page === 1]) ?>
                <?php endforeach; ?>
            </div>

            <?= chi_pagination($page, $total, $perPage, author_permalink($u)) ?>
        <?php else: ?>
            <div class="section-head">
                <h2><?= chi_paw(22) ?> The writers <?= esc_html($u->display_name) ?> edits</h2>
            </div>
            <div class="cover-list">
                <?php foreach (get_authors() as $chiA): ?>
                    <?php if ((int) $chiA->post_count > 0): ?>
                        <a class="category-pill" href="<?= esc_attr(author_permalink($chiA)) ?>"><?= esc_html($chiA->display_name) ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php get_sidebar(); ?>
</div>

<?php
// ProfilePage + Person schema: ties bylines and Article author URLs to a real page.
$person = array_filter([
    '@type'       => 'Person',
    'name'        => $u->display_name,
    'url'         => $base . author_permalink($u),
    'image'       => !empty($u->avatar) ? $base . $u->avatar : null,
    'description' => $u->bio ?: null,
    'sameAs'      => chi_author_sameas($u) ?: null,
    'worksFor'    => chi_org_schema(),
], static fn ($v) => $v !== null);
$profile = [
    '@context'   => 'https://schema.org',
    '@type'      => 'ProfilePage',
    'mainEntity' => $person,
];
?>
<script type="application/ld+json"><?= json_encode($profile, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>

<?php get_footer(); ?>
