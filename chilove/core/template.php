<?php

/**
 * ChiLove template tags, queries, and inline SVG helpers.
 * The procedural API a theme uses — the WordPress-flavoured part of the core.
 */

/* ---------------------------------------------------------------------------
 * Site + context
 * ------------------------------------------------------------------------- */

function chi_site(): array
{
    static $site;
    return $site ??= (require CHILOVE_ROOT . '/config.php')['site'];
}

function site_name(): string { return chi_site()['name']; }
function site_tagline(): string { return chi_site()['tagline']; }
function site_url(): string { return rtrim(chi_site()['url'], '/'); }

/** GA4 Measurement ID (G-XXXXXXXXXX) from config, or '' when analytics is off. */
function chi_analytics_id(): string
{
    static $id = null;
    if ($id === null) {
        $cfg = require CHILOVE_ROOT . '/config.php';
        $raw = (string) ($cfg['analytics']['ga4'] ?? '');
        // Only accept a real Measurement ID; ignore blanks/placeholders/property IDs.
        $id = preg_match('/^G-[A-Z0-9]{6,15}$/', $raw) ? $raw : '';
    }
    return $id;
}

/** Facebook App ID for the fb:app_id meta tag, or '' (tag omitted) unless a numeric ID is configured. */
function chi_fb_app_id(): string
{
    static $id = null;
    if ($id === null) {
        $cfg = require CHILOVE_ROOT . '/config.php';
        $raw = (string) ($cfg['analytics']['fb_app_id'] ?? '');
        $id = preg_match('/^\d{5,20}$/', $raw) ? $raw : '';
    }
    return $id;
}

/** The request context set by the Router (home / single / archive / search / 404). */
function chi_context(): object
{
    return $GLOBALS['chi'] ?? (object) ['type' => 'home'];
}

/* ---------------------------------------------------------------------------
 * Template loading (WordPress-style)
 * ------------------------------------------------------------------------- */

function get_header(): void  { include CHILOVE_THEME_DIR . '/header.php'; }
function get_footer(): void  { include CHILOVE_THEME_DIR . '/footer.php'; }
function get_sidebar(): void { include CHILOVE_THEME_DIR . '/sidebar.php'; }

function asset(string $path): string
{
    // filemtime cache-buster: browsers pick up new CSS/JS the moment a file is redeployed
    $rel  = '/assets/' . ltrim($path, '/');
    $v    = @filemtime(CHILOVE_ROOT . '/public' . $rel);
    return $v ? $rel . '?v=' . $v : $rel;
}

function post_permalink(object $p): string     { return '/post/' . $p->slug; }
function category_permalink(object $t): string  { return '/category/' . $t->slug; }

/* ---------------------------------------------------------------------------
 * Authors (author-20260722): /author/{slug} pages driven by chi_users.
 * Users have no slug column — the URL slug is derived from display_name.
 * ------------------------------------------------------------------------- */

function chi_author_slug(string $displayName): string
{
    return trim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9]+/', '-', strtolower($displayName))), '-');
}

function author_permalink(object $u): string { return '/author/' . chi_author_slug((string) $u->display_name); }

/** Authors that get a page: solo-author model (soloauthor-20260722) — only users with
 *  published posts. All content is authored by the site editor; retired persona
 *  accounts hold zero posts and stay invisible (their old URLs 301 in .htaccess). */
function get_authors(): array
{
    static $authors = null;
    return $authors ??= db()->getResults(
        "SELECT u.*, COUNT(p.id) AS post_count FROM chi_users u
         JOIN chi_posts p ON p.author_id = u.id AND p.status = 'publish'
         GROUP BY u.id ORDER BY post_count DESC"
    );
}

/** The site's named editor (editor-20260722): the real person accountable for the content. */
function chi_site_editor(): ?object
{
    foreach (get_authors() as $u) {
        if ($u->display_name === 'Arjeta Mehmeti') {
            return $u;
        }
    }
    return null;
}

/** Verifiable social profiles for an author (sameas column = JSON array of URLs). */
function chi_author_sameas(object $u): array
{
    if (empty($u->sameas)) {
        return [];
    }
    $urls = json_decode((string) $u->sameas, true);
    return is_array($urls) ? array_values(array_filter($urls, 'is_string')) : [];
}

function get_author_by_slug(string $slug): ?object
{
    foreach (get_authors() as $u) {
        if (chi_author_slug((string) $u->display_name) === $slug) {
            return $u;
        }
    }
    return null;
}

function get_posts_by_author(int $authorId, int $limit = 60): array
{
    $sql = "SELECT " . chi_post_columns() . " FROM chi_posts p
            LEFT JOIN chi_users u ON u.id = p.author_id
            WHERE p.author_id = ? AND p.status = 'publish'
            ORDER BY p.published_at DESC LIMIT " . (int) $limit;
    return db()->getResults($sql, [(int) $authorId]);
}

/* ---------------------------------------------------------------------------
 * Queries  (lean on the $wpdb-style Database layer)
 * ------------------------------------------------------------------------- */

function chi_post_columns(): string
{
    return "p.*, u.display_name AS author, u.bio AS author_bio, u.avatar AS author_avatar,
        (SELECT t.name FROM chi_terms t JOIN chi_post_terms pt ON pt.term_id = t.id WHERE pt.post_id = p.id LIMIT 1) AS category,
        (SELECT t.slug FROM chi_terms t JOIN chi_post_terms pt ON pt.term_id = t.id WHERE pt.post_id = p.id LIMIT 1) AS category_slug";
}

function get_categories(): array
{
    return db()->getResults(
        "SELECT t.*, (SELECT COUNT(*) FROM chi_post_terms pt WHERE pt.term_id = t.id) AS post_count
         FROM chi_terms t WHERE t.taxonomy = 'category' ORDER BY t.id"
    );
}

function get_category_by_slug(string $slug): ?object
{
    return db()->getRow("SELECT * FROM chi_terms WHERE slug = ? AND taxonomy = 'category' LIMIT 1", [$slug]);
}

function get_recent_posts(int $limit = 6, ?string $excludeSlug = null): array
{
    $sql = "SELECT " . chi_post_columns() . " FROM chi_posts p
            LEFT JOIN chi_users u ON u.id = p.author_id
            WHERE p.status = 'publish'";
    $params = [];
    if ($excludeSlug !== null) { $sql .= " AND p.slug <> ?"; $params[] = $excludeSlug; }
    $sql .= " ORDER BY p.published_at DESC LIMIT " . (int) $limit;
    return db()->getResults($sql, $params);
}

function get_post_by_slug(string $slug): ?object
{
    $sql = "SELECT " . chi_post_columns() . " FROM chi_posts p
            LEFT JOIN chi_users u ON u.id = p.author_id
            WHERE p.slug = ? AND p.status = 'publish' LIMIT 1";
    return db()->getRow($sql, [$slug]);
}

function get_featured_post(): ?object
{
    // Newest published article is the homepage hero, so every new article features on the homepage.
    return get_recent_posts(1)[0] ?? null;
}

function get_popular_posts(int $limit = 4): array
{
    $sql = "SELECT " . chi_post_columns() . " FROM chi_posts p
            LEFT JOIN chi_users u ON u.id = p.author_id
            WHERE p.status = 'publish' ORDER BY p.read_time DESC, p.id ASC LIMIT " . (int) $limit;
    return db()->getResults($sql);
}

function get_posts_by_category(string $slug, int $limit = 12): array
{
    $sql = "SELECT " . chi_post_columns() . " FROM chi_posts p
            LEFT JOIN chi_users u ON u.id = p.author_id
            JOIN chi_post_terms pt ON pt.post_id = p.id
            JOIN chi_terms t ON t.id = pt.term_id
            WHERE t.slug = ? AND p.status = 'publish'
            ORDER BY p.published_at DESC LIMIT " . (int) $limit;
    return db()->getResults($sql, [$slug]);
}

function search_posts(string $q, int $limit = 12): array
{
    $like = '%' . $q . '%';
    $sql = "SELECT " . chi_post_columns() . " FROM chi_posts p
            LEFT JOIN chi_users u ON u.id = p.author_id
            WHERE p.status = 'publish' AND (p.title LIKE ? OR p.excerpt LIKE ? OR p.content LIKE ?)
            ORDER BY p.published_at DESC LIMIT " . (int) $limit;
    return db()->getResults($sql, [$like, $like, $like]);
}

/* ---------------------------------------------------------------------------
 * Formatting helpers
 * ------------------------------------------------------------------------- */

function chi_excerpt(?string $text, int $words = 20): string
{
    $text = trim(strip_tags((string) $text));
    $parts = preg_split('/\s+/', $text) ?: [];
    if (count($parts) <= $words) { return $text; }
    return implode(' ', array_slice($parts, 0, $words)) . '…';
}

function chi_date(?string $dt): string { return $dt ? date('M j, Y', strtotime($dt)) : ''; }

function chi_read_time(object $p): string { return (int) ($p->read_time ?? 5) . ' min read'; }

/* ---------------------------------------------------------------------------
 * Inline SVG: brand logo, icons, decorations, and Chihuahua illustrations
 * ------------------------------------------------------------------------- */

function chi_logo(): string
{
    return <<<SVG
<svg viewBox="0 0 340 120" fill="none" xmlns="http://www.w3.org/2000/svg" aria-label="Cheewawa — A Chihuahua Blog">
  <g transform="translate(8 18)">
    <path d="M43 48c13 0 25 12 25 24 0 8-6 12-14 12-4 0-7-2-11-2s-7 2-11 2c-8 0-14-4-14-12 0-12 12-24 25-24Z" fill="#D79A55"/>
    <ellipse cx="22" cy="33" rx="10" ry="14" fill="#D79A55" transform="rotate(-18 22 33)"/>
    <ellipse cx="43" cy="22" rx="10" ry="14" fill="#D79A55"/>
    <ellipse cx="64" cy="33" rx="10" ry="14" fill="#D79A55" transform="rotate(18 64 33)"/>
  </g>
  <text x="100" y="61" font-family="Baloo 2, Arial, sans-serif" font-size="40" font-weight="800"><tspan fill="#2B180D">Chee</tspan><tspan fill="#F86F86">wawa</tspan></text>
  <path d="M198 25c0-6 9-6 9 0 0-6 9-6 9 0 0 8-9 14-9 14s-9-6-9-14Z" fill="#F86F86"/>
  <text x="102" y="88" font-family="Nunito, Arial, sans-serif" font-size="16" font-weight="700" fill="#7C5B46">A Chihuahua Blog</text>
</svg>
SVG;
}

function chi_paw(int $size = 20, ?string $color = null): string
{
    $c = $color ?? 'currentColor';
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 64 64" fill="' . $c . '" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
        . '<path d="M32 34c9 0 17 8 17 16 0 5-4 8-9 8-3 0-5-1-8-1s-5 1-8 1c-5 0-9-3-9-8 0-8 8-16 17-16Z"/>'
        . '<ellipse cx="18" cy="25" rx="7" ry="10" transform="rotate(-18 18 25)"/>'
        . '<ellipse cx="30" cy="17" rx="7" ry="10"/>'
        . '<ellipse cx="46" cy="25" rx="7" ry="10" transform="rotate(18 46 25)"/>'
        . '<ellipse cx="55" cy="37" rx="6" ry="8" transform="rotate(30 55 37)"/></svg>';
}

function chi_heart(int $size = 18, string $color = '#f86f86'): string
{
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 64 64" fill="' . $color . '" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">'
        . '<path d="M32 52S10 39 10 23c0-8 6-13 13-13 4 0 8 2 9 6 1-4 5-6 9-6 7 0 13 5 13 13 0 16-22 29-22 29Z"/></svg>';
}

function chi_icon(string $name, int $size = 20): string
{
    if ($name === 'paw') { return chi_paw($size); }

    $paths = [
        'search'    => '<circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>',
        'arrow'     => '<path d="M5 12h14"/><path d="M13 6l6 6-6 6"/>',
        'clock'     => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'mail'      => '<rect x="3" y="5" width="18" height="14" rx="3"/><path d="M4 7l8 6 8-6"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.2" fill="currentColor" stroke="none"/>',
        'youtube'   => '<rect x="3" y="6" width="18" height="12" rx="4"/><path d="M11 9.5l4 2.5-4 2.5z" fill="currentColor" stroke="none"/>',
        'facebook'  => '<path d="M14 8h2V5h-2c-2 0-3 1-3 3v2H9v3h2v6h3v-6h2l1-3h-3V8z" fill="currentColor" stroke="none"/>',
    ];
    $body = $paths[$name] ?? '';
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">' . $body . '</svg>';
}

/** Palette pairs for the illustrated photo placeholders. */
function chi_palette(string $variant): array
{
    return match ($variant) {
        'blush' => ['#ffd9e0', '#fff1df'],
        'sky'   => ['#cdeef4', '#fff7ec'],
        'cream' => ['#fff1df', '#fff7ec'],
        'mini'  => ['#ffe9cf', '#fff3e3'],
        default => ['#ffe3c4', '#fff7ec'], // tan
    };
}

/** A cute, stylised Chihuahua illustration used in place of real photos. */
function chi_portrait(string $variant = 'tan'): string
{
    static $n = 0; $n++;
    $id = 'cg' . $n;
    [$c1, $c2] = chi_palette($variant);
    $fur = '#e0a866'; $fur2 = '#d79a55'; $inner = '#ffd0d9'; $dark = '#2b180d';

    return <<<SVG
<svg class="portrait" viewBox="0 0 480 360" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice" role="img" aria-label="Chihuahua illustration">
  <defs><linearGradient id="$id" x1="0" y1="0" x2="0" y2="1">
    <stop offset="0" stop-color="$c1"/><stop offset="1" stop-color="$c2"/></linearGradient></defs>
  <rect width="480" height="360" fill="url(#$id)"/>
  <circle cx="64" cy="66" r="6" fill="#ffffff" opacity=".55"/>
  <circle cx="420" cy="300" r="9" fill="#ffffff" opacity=".5"/>
  <circle cx="402" cy="80" r="5" fill="#ffffff" opacity=".5"/>
  <path d="M150 50 L118 200 L235 165 Z" fill="$fur2"/>
  <path d="M330 50 L362 200 L245 165 Z" fill="$fur2"/>
  <path d="M158 80 L142 178 L222 158 Z" fill="$inner"/>
  <path d="M322 80 L338 178 L258 158 Z" fill="$inner"/>
  <ellipse cx="240" cy="226" rx="118" ry="100" fill="$fur"/>
  <ellipse cx="240" cy="264" rx="62" ry="48" fill="#fff3e3"/>
  <circle cx="168" cy="258" r="14" fill="$inner" opacity=".85"/>
  <circle cx="312" cy="258" r="14" fill="$inner" opacity=".85"/>
  <ellipse cx="198" cy="212" rx="16" ry="18" fill="$dark"/>
  <ellipse cx="282" cy="212" rx="16" ry="18" fill="$dark"/>
  <circle cx="203" cy="206" r="5" fill="#fff"/>
  <circle cx="287" cy="206" r="5" fill="#fff"/>
  <ellipse cx="240" cy="246" rx="14" ry="10" fill="$dark"/>
  <path d="M240 256 Q240 270 226 272 M240 256 Q240 270 254 272" stroke="$dark" stroke-width="3" fill="none" stroke-linecap="round"/>
</svg>
SVG;
}

/** Pick an illustration tint from the post's category (deterministic). */
function chi_thumb(?object $post = null, ?string $variant = null, array $opts = []): string
{
    // Prefer a real featured photo when the post has one; fall back to the SVG illustration.
    $photo = chi_featured_image($post);
    if ($photo !== null) {
        $alt = $post && !empty($post->title) ? $post->title : 'Chihuahua';
        static $dims = [];
        if (!array_key_exists($photo, $dims)) {
            $dims[$photo] = @getimagesize(CHILOVE_ROOT . '/public/' . ltrim($photo, '/')) ?: null;
        }
        $d = $dims[$photo];
        $srcset = chi_srcset($photo);
        $sizes  = $opts['sizes'] ?? ($variant === 'mini' ? 'mini' : 'card');
        $prio   = !empty($opts['eager']) ? ' loading="eager" fetchpriority="high"' : ' loading="lazy"';
        return '<img class="portrait" src="' . esc_attr($photo) . '" alt="' . esc_attr($alt) . '"'
            . ($d ? ' width="' . $d[0] . '" height="' . $d[1] . '"' : '')
            . ($srcset !== '' ? ' srcset="' . esc_attr($srcset) . '" sizes="' . esc_attr(chi_sizes($sizes)) . '"' : '')
            . $prio . '>';
    }

    if ($variant === null) {
        $map = ['care-tips' => 'tan', 'training' => 'sky', 'lifestyle' => 'blush', 'health' => 'cream', 'gallery' => 'blush'];
        $key = $post->category_slug ?? $post->slug ?? '';
        $variant = $map[$key] ?? ['tan', 'blush', 'sky', 'cream'][abs(crc32((string) $key)) % 4];
    }
    return chi_portrait($variant);
}

/* ---------------------------------------------------------------------------
 * Real post photos (uploaded images), with SVG illustration as the fallback
 * ------------------------------------------------------------------------- */

/** Uploaded photos for a post: files under public/assets/img/posts/<slug>/. */
function chi_post_photos(string $slug): array
{
    $slug = preg_replace('/[^a-z0-9-]/', '', strtolower($slug));
    if ($slug === '') {
        return [];
    }
    $dir   = CHILOVE_ROOT . '/public/assets/img/posts/' . $slug;
    $files = array_merge(
        glob("$dir/*.webp") ?: [],
        glob("$dir/*.jpg")  ?: [],
        glob("$dir/*.jpeg") ?: [],
        glob("$dir/*.png")  ?: []
    );
    sort($files);

    $out = [];
    foreach ($files as $file) {
        $name = basename($file);
        if (preg_match('/-\d+w$/', pathinfo($name, PATHINFO_FILENAME))) {
            continue; // generated responsive variants (gen_img_variants.sh) are not standalone photos
        }
        if (stripos($name, 'memorial') !== false) {
            continue; // memorial images render in their own dedicated block, not the grid
        }
        $base = preg_replace('/^\d+[-_]?/', '', pathinfo($name, PATHINFO_FILENAME)); // drop ordering prefix for alt
        $out[] = [
            'url' => '/assets/img/posts/' . $slug . '/' . rawurlencode($name),
            'alt' => ucfirst(str_replace('-', ' ', (string) $base)),
        ];
    }
    return $out;
}

/** Featured photo: the featured_image column if it points to a real file, else the first uploaded photo, else null (caller falls back to the SVG). */
function chi_featured_image(?object $post): ?string
{
    if ($post && !empty($post->featured_image)) {
        $rel = ltrim((string) $post->featured_image, '/');
        if (is_file(CHILOVE_ROOT . '/public/' . $rel)) {
            return '/' . $rel;
        }
    }
    $photos = $post && !empty($post->slug) ? chi_post_photos($post->slug) : [];
    return $photos[0]['url'] ?? null;
}

/** A dedicated memorial image for a post (any file whose name contains "memorial"), or null. */
function chi_post_memorial(string $slug): ?array
{
    $slug = preg_replace('/[^a-z0-9-]/', '', strtolower($slug));
    if ($slug === '') {
        return null;
    }
    $dir   = CHILOVE_ROOT . '/public/assets/img/posts/' . $slug;
    $files = array_merge(
        glob("$dir/*memorial*.webp") ?: [],
        glob("$dir/*memorial*.jpg")  ?: [],
        glob("$dir/*memorial*.jpeg") ?: [],
        glob("$dir/*memorial*.png")  ?: []
    );
    $files = array_values(array_filter($files, static fn ($f) => !preg_match('/-\d+w\.[a-z]+$/i', $f)));
    if (!$files) {
        return null;
    }
    return [
        'url' => '/assets/img/posts/' . $slug . '/' . rawurlencode(basename($files[0])),
        'alt' => 'In loving memory',
    ];
}

/* ---------------------------------------------------------------------------
 * SEO: per-page <title>/description/canonical, HTML body, JSON-LD
 * ------------------------------------------------------------------------- */

/** Per-context SEO meta for <head>. */
function chi_seo_meta(object $ctx): array
{
    $base = site_url();
    $name = site_name();
    $type = $ctx->type ?? 'home';

    if ($type === 'single' && isset($ctx->post)) {
        $p = $ctx->post;
        $desc = !empty($p->meta_description) ? $p->meta_description : chi_excerpt($p->excerpt ?: $p->content, 28);
        return ['title' => $p->title . ' · ' . $name, 'description' => $desc, 'canonical' => $base . '/post/' . $p->slug];
    }
    if ($type === 'archive' && isset($ctx->term)) {
        $t = $ctx->term;
        return ['title' => $t->name . ' · ' . $name, 'description' => 'Chihuahua ' . $t->name . ' articles, guides, and stories from ' . $name . '.', 'canonical' => $base . '/category/' . $t->slug];
    }
    if ($type === 'blog') {
        return ['title' => 'Blog · ' . $name, 'description' => 'All chihuahua articles, guides, and stories from ' . $name . '.', 'canonical' => $base . '/blog'];
    }
    if ($type === 'page') {
        $page = ucfirst((string) ($ctx->page ?? ''));
        return ['title' => $page . ' · ' . $name, 'description' => null, 'canonical' => $base . '/' . ($ctx->page ?? '')];
    }
    if ($type === 'author' && isset($ctx->user)) {
        $u = $ctx->user;
        $desc = chi_excerpt((string) ($u->bio ?? ''), 28) ?: ('Articles by ' . $u->display_name . ' on ' . $name . '.');
        return ['title' => $u->display_name . ', Author · ' . $name, 'description' => $desc, 'canonical' => $base . author_permalink($u)];
    }
    if ($type === 'search') {
        return ['title' => 'Search · ' . $name, 'description' => null, 'canonical' => $base . '/search'];
    }
    return ['title' => $name . ': chihuahuas, minus the myths', 'description' => 'Honest chihuahua care, training, health, and real stories, minus the myths and the fluff, from people who actually live with the breed.', 'canonical' => $base . '/'];
}

/** Render post body: author-authored HTML as-is, plain text as paragraphs. */
function chi_content_html(?string $content): string
{
    $content = (string) $content;
    if (preg_match('/<(p|h2|h3|ul|ol|figure|blockquote)\b/i', $content)) {
        return chi_responsive_content($content);
    }
    return nl2br(esc_html($content));
}

/** Extract FAQ pairs (each <h3> question + following <p> answer) from body HTML. */
function chi_faq_from_content(string $html): array
{
    if (stripos($html, '<h3') === false) {
        return [];
    }
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><div>' . $html . '</div>');
    libxml_clear_errors();

    $faqs = [];
    foreach ($doc->getElementsByTagName('h3') as $h3) {
        $q = trim($h3->textContent);
        if (substr($q, -1) !== '?') {
            continue;   // FAQPage may only carry real, visible questions (seo-20260722)
        }
        $n = $h3->nextSibling;
        while ($n && $n->nodeType !== XML_ELEMENT_NODE) {
            $n = $n->nextSibling;
        }
        if ($n && strtolower($n->nodeName) === 'p') {
            $a = trim($n->textContent);
            if ($q !== '' && $a !== '') {
                $faqs[] = ['q' => $q, 'a' => $a];
            }
        }
    }
    return $faqs;
}

/** Organization node reused as Article publisher and in the sitewide block (author-20260722). */
function chi_org_schema(): array
{
    $base = site_url();
    $org = [
        '@type' => 'Organization',
        'name'  => site_name(),
        'url'   => $base . '/',
        'logo'  => ['@type' => 'ImageObject', 'url' => $base . '/apple-touch-icon.png'],
    ];
    if ($editor = chi_site_editor()) {
        $founder = ['@type' => 'Person', 'name' => $editor->display_name, 'url' => $base . author_permalink($editor)];
        if ($sameas = chi_author_sameas($editor)) {
            $founder['sameAs'] = $sameas;
        }
        $org['founder'] = $founder;
    }
    return $org;
}

/** Sitewide Organization + WebSite JSON-LD, emitted once from header.php (author-20260722). */
function chi_org_jsonld(): string
{
    $base = site_url();
    $org = ['@context' => 'https://schema.org'] + chi_org_schema();
    $site = [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => site_name(),
        'url'      => $base . '/',
        'potentialAction' => [
            '@type'       => 'SearchAction',
            'target'      => ['@type' => 'EntryPoint', 'urlTemplate' => $base . '/search?q={search_term_string}'],
            'query-input' => 'required name=search_term_string',
        ],
    ];
    $out = '';
    foreach ([$org, $site] as $b) {
        $out .= '<script type="application/ld+json">' . json_encode($b, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
    }
    return $out;
}

/** Article + BreadcrumbList + FAQPage JSON-LD for a single post. */
function chi_jsonld_blocks(object $post): string
{
    $base = site_url();
    $img  = chi_featured_image($post);

    $isHealth = (($post->category_slug ?? '') === 'health');
    $article = array_filter([
        '@context'         => 'https://schema.org',
        '@type'            => $isHealth ? 'MedicalWebPage' : 'Article',
        'headline'         => $post->title,
        'description'      => $post->meta_description ?? null,
        'image'            => $img ? $base . $img : null,
        'datePublished'    => !empty($post->published_at) ? date('c', strtotime($post->published_at)) : null,
        'dateModified'     => !empty($post->updated_at) ? date('c', strtotime($post->updated_at))
            : (!empty($post->published_at) ? date('c', strtotime($post->published_at)) : null),
        'author'           => !empty($post->author)
            ? ['@type' => 'Person', 'name' => $post->author, 'url' => $base . '/author/' . chi_author_slug((string) $post->author)]
            : ['@type' => 'Organization', 'name' => site_name(), 'url' => $base . '/'],
        'editor'           => ($chiEd = chi_site_editor())
            ? ['@type' => 'Person', 'name' => $chiEd->display_name, 'url' => $base . author_permalink($chiEd)]
            : null,
        'publisher'        => chi_org_schema(),
        'mainEntityOfPage' => $base . '/post/' . $post->slug,
    ], static fn ($v) => $v !== null);

    $crumbs = [['name' => 'Home', 'url' => $base . '/']];
    if (!empty($post->category) && !empty($post->category_slug)) {
        $crumbs[] = ['name' => $post->category, 'url' => $base . '/category/' . $post->category_slug];
    }
    $crumbs[] = ['name' => $post->title, 'url' => $base . '/post/' . $post->slug];
    $breadcrumb = ['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => []];
    foreach ($crumbs as $i => $c) {
        $breadcrumb['itemListElement'][] = ['@type' => 'ListItem', 'position' => $i + 1, 'name' => $c['name'], 'item' => $c['url']];
    }

    $blocks = [$article, $breadcrumb];

    $faqs = chi_faq_from_content((string) ($post->content ?? ''));
    if ($faqs) {
        $faqPage = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
        foreach ($faqs as $f) {
            $faqPage['mainEntity'][] = ['@type' => 'Question', 'name' => $f['q'], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']]];
        }
        $blocks[] = $faqPage;
    }

    $out = '';
    foreach ($blocks as $b) {
        $out .= '<script type="application/ld+json">' . json_encode($b, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
    }
    return $out;
}
/* ---------------------------------------------------------------------------
 * Responsive images (perf-20260715)
 * Pre-generated width variants (~/bin/gen_img_variants.sh) served via srcset.
 * ------------------------------------------------------------------------- */

/** srcset for an uploaded post image, listing only variants that exist on disk ('' if none). */
function chi_srcset(string $url): string
{
    static $cache = [];
    if (array_key_exists($url, $cache)) {
        return $cache[$url];
    }
    $path = rawurldecode($url);
    if (!preg_match('~^(/assets/img/posts/.+)\.(webp|jpe?g|png)$~i', $path, $m)) {
        return $cache[$url] = '';
    }
    $entries = [];
    foreach ([160, 480, 768, 1024] as $w) {
        $rel = $m[1] . '-' . $w . 'w.' . $m[2];
        if (is_file(CHILOVE_ROOT . '/public' . $rel)) {
            $entries[] = $rel . ' ' . $w . 'w';
        }
    }
    if (!$entries) {
        return $cache[$url] = '';
    }
    $dim = @getimagesize(CHILOVE_ROOT . '/public' . $path);
    if (!empty($dim[0])) {
        $entries[] = $path . ' ' . $dim[0] . 'w';
    }
    return $cache[$url] = implode(', ', $entries);
}

/** sizes= presets matching the theme's layout slots. */
function chi_sizes(string $preset): string
{
    return match ($preset) {
        'featured' => '(max-width: 900px) 92vw, (max-width: 1282px) calc(92vw - 352px), 828px',
        'hero'     => '(max-width: 900px) 92vw, (max-width: 1282px) 41vw, 490px',
        'mini'     => '64px',
        'body'     => '(max-width: 620px) 92vw, 560px',
        default    => '(max-width: 600px) 92vw, (max-width: 900px) 46vw, 300px', // card grids
    };
}

/** The LCP image for the current page (post featured photo / homepage hero), for a <head> preload. */
function chi_lcp_image(): ?array
{
    $ctx  = chi_context();
    $type = $ctx->type ?? '';
    $post = null;
    $preset = 'featured';
    if ($type === 'single' && !empty($ctx->post)) {
        $post = $ctx->post;
    } elseif ($type === 'home') {
        $post = get_featured_post();
        $preset = 'hero';
    }
    if (!$post) {
        return null;
    }
    $src = chi_featured_image($post);
    if (!$src) {
        return null;
    }
    $srcset = chi_srcset($src);
    return ['src' => $src, 'srcset' => $srcset, 'sizes' => $srcset !== '' ? chi_sizes($preset) : ''];
}

/** Add srcset/sizes to post-body <img> tags whose variants exist (leaves authored lazy-loading alone). */
function chi_responsive_content(string $html): string
{
    $out = preg_replace_callback('~<img\b[^>]*>~i', static function (array $m): string {
        $tag = $m[0];
        if (stripos($tag, 'srcset=') !== false || !preg_match('~src="([^"]+)"~i', $tag, $s)) {
            return $tag;
        }
        $srcset = chi_srcset($s[1]);
        if ($srcset === '') {
            return $tag;
        }
        $end = substr($tag, -2) === '/>' ? 2 : 1;
        return substr($tag, 0, -$end)
            . ' srcset="' . esc_attr($srcset) . '" sizes="' . esc_attr(chi_sizes('body')) . '"'
            . ($end === 2 ? ' />' : '>');
    }, $html);
    return $out ?? $html;
}
