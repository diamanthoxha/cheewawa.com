<?php

/**
 * ChiLove — page-level queries and helpers:
 * navigation, the blog index, pagination, related posts, and the gallery.
 */

/** Primary navigation (Home · categories · About). Gallery routes to its own page. */
function chi_nav_items(): array
{
    $items = [['label' => 'Home', 'url' => '/']];
    foreach (get_categories() as $c) {
        if ($c->slug === 'gallery') { continue; }   // Gallery hidden from nav
        $items[] = ['label' => $c->name, 'url' => category_permalink($c)];
    }
    // About intentionally omitted from nav (the /about page still exists for in-article links).
    return $items;
}

function count_posts(?string $categorySlug = null): int
{
    if ($categorySlug !== null) {
        return (int) db()->getVar(
            "SELECT COUNT(*) FROM chi_posts p
             JOIN chi_post_terms pt ON pt.post_id = p.id
             JOIN chi_terms t ON t.id = pt.term_id
             WHERE t.slug = ? AND p.status = 'publish'",
            [$categorySlug]
        );
    }
    return (int) db()->getVar("SELECT COUNT(*) FROM chi_posts WHERE status = 'publish'");
}

function get_posts_page(int $perPage, int $page, ?string $categorySlug = null): array
{
    $perPage = max(1, $perPage);
    return get_posts_window($perPage, max(0, $page - 1) * $perPage, $categorySlug);
}

function get_posts_window(int $limit, int $offset, ?string $categorySlug = null): array
{
    $perPage = max(1, $limit);
    $offset  = max(0, $offset);

    if ($categorySlug !== null) {
        $sql = "SELECT " . chi_post_columns() . " FROM chi_posts p
                LEFT JOIN chi_users u ON u.id = p.author_id
                JOIN chi_post_terms pt ON pt.post_id = p.id
                JOIN chi_terms t ON t.id = pt.term_id
                WHERE t.slug = ? AND p.status = 'publish'
                ORDER BY p.published_at DESC LIMIT $perPage OFFSET $offset";
        return db()->getResults($sql, [$categorySlug]);
    }

    $sql = "SELECT " . chi_post_columns() . " FROM chi_posts p
            LEFT JOIN chi_users u ON u.id = p.author_id
            WHERE p.status = 'publish'
            ORDER BY p.published_at DESC LIMIT $perPage OFFSET $offset";
    return db()->getResults($sql);
}

function get_related_posts(object $post, int $limit = 3): array
{
    $cat = $post->category_slug ?? null;
    if ($cat === null) {
        return [];
    }
    $sql = "SELECT " . chi_post_columns() . " FROM chi_posts p
            LEFT JOIN chi_users u ON u.id = p.author_id
            JOIN chi_post_terms pt ON pt.post_id = p.id
            JOIN chi_terms t ON t.id = pt.term_id
            WHERE t.slug = ? AND p.slug <> ? AND p.status = 'publish'
            ORDER BY p.published_at DESC LIMIT " . (int) $limit;
    return db()->getResults($sql, [$cat, $post->slug]);
}

/** Illustrated gallery tiles (variant, caption) — stands in for real photos. */
function chi_gallery_items(): array
{
    return [
        ['blush', 'Snug as a bug'],
        ['tan',   'Sunny afternoon nap'],
        ['sky',   'Ready for a walk'],
        ['cream', 'Cookie, please?'],
        ['tan',   'Sweater weather'],
        ['blush', 'Big ears, bigger heart'],
        ['sky',   'Tiny adventurer'],
        ['cream', 'Lap-warmer on duty'],
    ];
}

function chi_pagination(int $page, int $total, int $perPage, string $base): string
{
    $pages = (int) ceil($total / max(1, $perPage));
    if ($pages <= 1) {
        return '';
    }
    $sep = str_contains($base, '?') ? '&' : '?';
    $url = static fn (int $i): string =>
        htmlspecialchars($i === 1 ? $base : $base . $sep . 'paged=' . $i, ENT_QUOTES);

    // Windowed: first + last + current±1, with gaps — stays tappable on phones.
    $shown = array_unique(array_filter(
        [1, $page - 1, $page, $page + 1, $pages],
        static fn (int $i): bool => $i >= 1 && $i <= $pages
    ));
    sort($shown);

    $out = '<nav class="pagination" aria-label="Pagination">';
    if ($page > 1) {
        $out .= '<a href="' . $url($page - 1) . '" aria-label="Previous page">&larr;</a>';
    }
    $prev = 0;
    foreach ($shown as $i) {
        if ($i > $prev + 1) {
            $out .= '<span class="gap">…</span>';
        }
        $cls = $i === $page ? ' class="active"' : '';
        $out .= '<a' . $cls . ' href="' . $url($i) . '">' . $i . '</a>';
        $prev = $i;
    }
    if ($page < $pages) {
        $out .= '<a href="' . $url($page + 1) . '" aria-label="Next page">&rarr;</a>';
    }
    return $out . '</nav>';
}

/** XML sitemap: home, blog, static pages, categories, and published posts. */
function chi_sitemap_xml(): void
{
    $base = site_url();

    // 'title'/'type' ride along in the chee: namespace for the styled XSL view;
    // crawlers only read the standard sitemap elements.
    $urls = [
        ['loc' => $base . '/',        'changefreq' => 'daily',   'title' => site_name(),      'type' => 'Home'],
        ['loc' => $base . '/blog',    'changefreq' => 'daily',   'title' => 'Blog',           'type' => 'Blog'],
        ['loc' => $base . '/about',   'changefreq' => 'monthly', 'title' => 'About',          'type' => 'Page'],
        ['loc' => $base . '/contact', 'changefreq' => 'monthly', 'title' => 'Contact',        'type' => 'Page'],
        ['loc' => $base . '/privacy', 'changefreq' => 'monthly', 'title' => 'Privacy Policy', 'type' => 'Page'],
        ['loc' => $base . '/cookies', 'changefreq' => 'monthly', 'title' => 'Cookies Policy', 'type' => 'Page'],
    ];

    foreach (get_categories() as $c) {
        if ($c->slug === 'gallery') { continue; }   // hidden from nav, keep out of the sitemap too
        $urls[] = [
            'loc'        => $base . category_permalink($c),
            'changefreq' => 'weekly',
            'title'      => $c->name,
            'type'       => 'Category',
        ];
    }

    $posts = db()->getResults(
        "SELECT slug, title, published_at FROM chi_posts WHERE status = 'publish' ORDER BY published_at DESC"
    );
    foreach ($posts as $p) {
        $urls[] = [
            'loc'        => $base . '/post/' . $p->slug,
            'lastmod'    => substr((string) $p->published_at, 0, 10),
            'changefreq' => 'monthly',
            'title'      => $p->title,
            'type'       => 'Article',
        ];
    }

    header('Content-Type: application/xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    // Browsers render the XSL as a styled, clickable page; crawlers read the raw XML.
    echo '<?xml-stylesheet type="text/xsl" href="/sitemap.xsl?v=20260715b"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:chee="https://cheewawa.com/ns/sitemap">' . "\n";
    foreach ($urls as $u) {
        echo '  <url><loc>' . esc_html($u['loc']) . '</loc>';
        if (!empty($u['lastmod'])) {
            echo '<lastmod>' . $u['lastmod'] . '</lastmod>';
        }
        if (!empty($u['changefreq'])) {
            echo '<changefreq>' . $u['changefreq'] . '</changefreq>';
        }
        echo '<chee:title>' . esc_html($u['title']) . '</chee:title>';
        echo '<chee:type>' . esc_html($u['type']) . '</chee:type>';
        echo '</url>' . "\n";
    }
    echo '</urlset>' . "\n";
}

/** RSS 2.0 feed of the latest posts (linked from legacy /feed redirects). */
function chi_rss_xml(): void
{
    $base = site_url();
    header('Content-Type: application/rss+xml; charset=utf-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<rss version="2.0"><channel>' . "\n";
    echo '<title>' . esc_html(site_name()) . '</title>' . "\n";
    echo '<link>' . esc_html($base) . '</link>' . "\n";
    echo '<description>' . esc_html(site_tagline()) . '</description>' . "\n";
    foreach (get_recent_posts(20) as $p) {
        $url = $base . post_permalink($p);
        echo '<item>';
        echo '<title>' . esc_html($p->title) . '</title>';
        echo '<link>' . esc_html($url) . '</link>';
        echo '<guid isPermaLink="true">' . esc_html($url) . '</guid>';
        echo '<pubDate>' . date('r', strtotime((string) $p->published_at)) . '</pubDate>';
        echo '<description>' . esc_html((string) $p->excerpt) . '</description>';
        echo '</item>' . "\n";
    }
    echo '</channel></rss>' . "\n";
}
