<?php
$ctx = chi_context();
$cur = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
?>
<!doctype html>
<html lang="en">
<head>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-1139542473885002"
         crossorigin="anonymous"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="Fd2-yxw5AvtpiMOe-Zm2breBbywGfzOsharXyNnMQZE" />
    <?php $seo = chi_seo_meta($ctx); ?>
    <title><?= esc_html($seo['title']) ?></title>
    <?php if (!empty($seo['description'])): ?>
    <meta name="description" content="<?= esc_attr($seo['description']) ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?= esc_attr($seo['canonical']) ?>">

    <?php // Social link previews (Open Graph + Twitter Card): Facebook, WhatsApp, and X read the featured image from these. ?>
    <?php
    $chiOgPost = (($ctx->type ?? '') === 'single' && isset($ctx->post)) ? $ctx->post : get_featured_post();
    $chiOgImg = $chiOgPost ? chi_featured_image($chiOgPost) : null;
    $chiOgUrl = $chiOgW = $chiOgH = $chiOgMime = null;
    if (is_string($chiOgImg) && $chiOgImg !== '') {
        $chiOgPath = '/' . ltrim(preg_replace('/[?#].*$/', '', $chiOgImg), '/');
        $chiOgJpg  = preg_replace('/\.webp$/i', '-og.jpg', $chiOgPath);
        $chiOgPick = is_file(CHILOVE_ROOT . '/public' . $chiOgJpg) ? $chiOgJpg : $chiOgPath;
        if (is_file(CHILOVE_ROOT . '/public' . $chiOgPick) && ($chiOgSize = @getimagesize(CHILOVE_ROOT . '/public' . $chiOgPick))) {
            // mtime version so scrapers (FB caches image fetches per URL, failures included) refetch replaced images.
            $chiOgUrl  = site_url() . $chiOgPick . '?v=' . (int) @filemtime(CHILOVE_ROOT . '/public' . $chiOgPick);
            $chiOgW    = $chiOgSize[0];
            $chiOgH    = $chiOgSize[1];
            $chiOgMime = $chiOgSize['mime'] ?? null;
        }
    }
    ?>
    <meta property="og:site_name" content="<?= esc_attr(site_name()) ?>">
    <meta property="og:type" content="<?= (($ctx->type ?? '') === 'single') ? 'article' : 'website' ?>">
    <meta property="og:title" content="<?= esc_attr((($ctx->type ?? '') === 'single' && isset($ctx->post)) ? $ctx->post->title : $seo['title']) ?>">
    <?php if (!empty($seo['description'])): ?>
    <meta property="og:description" content="<?= esc_attr($seo['description']) ?>">
    <?php endif; ?>
    <meta property="og:url" content="<?= esc_attr($seo['canonical']) ?>">
    <?php if ($chiFbApp = chi_fb_app_id()): ?>
    <meta property="fb:app_id" content="<?= esc_attr($chiFbApp) ?>">
    <?php endif; ?>
    <?php if ($chiOgUrl): ?>
    <meta property="og:image" content="<?= esc_attr($chiOgUrl) ?>">
    <meta property="og:image:width" content="<?= (int) $chiOgW ?>">
    <meta property="og:image:height" content="<?= (int) $chiOgH ?>">
    <?php if ($chiOgMime): ?>
    <meta property="og:image:type" content="<?= esc_attr($chiOgMime) ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:image" content="<?= esc_attr($chiOgUrl) ?>">
    <?php else: ?>
    <meta name="twitter:card" content="summary">
    <?php endif; ?>
    <?php // Internal search and paginated archives: crawlable but not indexable. ?>
    <?php if (($ctx->type ?? '') === 'search' || (in_array($ctx->type ?? '', ['blog', 'archive'], true) && (int) ($_GET['page'] ?? 1) > 1)): ?>
    <meta name="robots" content="noindex, follow">
    <?php endif; ?>
    <link rel="alternate" type="application/rss+xml" title="<?= esc_attr(site_name()) ?>" href="/rss.xml">

    <link rel="icon" href="/favicon.svg?v=20260715" type="image/svg+xml">
    <link rel="icon" href="/favicon.ico?v=20260715" sizes="32x32">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=20260715">

    <?php // Preload the LCP image with high priority so it beats the ad/analytics scripts to the bandwidth. ?>
    <?php if ($chiLcp = chi_lcp_image()): ?>
    <link rel="preload" as="image" href="<?= esc_attr($chiLcp['src']) ?>"<?= $chiLcp['srcset'] !== '' ? ' imagesrcset="' . esc_attr($chiLcp['srcset']) . '" imagesizes="' . esc_attr($chiLcp['sizes']) . '"' : '' ?> fetchpriority="high">
    <?php endif; ?>

    <?php // Google Analytics 4 — renders only when a valid Measurement ID is set in config. ?>
    <?php if ($ga = chi_analytics_id()): ?>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        // Consent Mode v2: storage defaults to denied for EEA/UK/CH visitors until
        // a consent choice arrives (the AdSense Privacy & messaging banner updates
        // this once enabled). Rest of world is unaffected.
        gtag('consent', 'default', {
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            analytics_storage: 'denied',
            wait_for_update: 500,
            region: ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU',
                     'IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES',
                     'SE','IS','LI','NO','GB','CH']
        });
        gtag('js', new Date());
        gtag('config', '<?= $ga /* whitelist-validated G-XXXX in chi_analytics_id() */ ?>');
        // gtag.js loads after window.load (+1.5 s) so analytics never competes
        // with content and the LCP image for slow-mobile bandwidth.
        window.addEventListener('load', function () {
            setTimeout(function () {
                var s = document.createElement('script');
                s.async = true;
                s.src = 'https://www.googletagmanager.com/gtag/js?id=<?= esc_attr($ga) ?>';
                document.head.appendChild(s);
            }, 1500);
        });
    </script>
    <?php endif; ?>

    <?php // Fonts are self-hosted: @font-face in theme.css, files in /assets/fonts (no third-party connections). ?>

    <?php // Site CSS inlined: zero render-blocking stylesheet requests (the 3 files gzip to ~6 KB inside the HTML). ?>
    <style><?php foreach (['theme', 'site', 'pages'] as $chiCssFile) {
        echo str_replace('</style', '', (string) @file_get_contents(CHILOVE_ROOT . '/public/assets/css/' . $chiCssFile . '.css')), "\n";
    } ?></style>
</head>
<body>
    <div class="announce">
        <?= chi_paw(15) ?> Because tiny dogs leave the biggest paw prints on our hearts. <?= chi_paw(15) ?>
    </div>

    <header class="site-header">
        <div class="container header-inner">
            <a class="brand" href="/"><?= chi_logo() ?></a>

            <nav class="nav" id="nav">
                <?php foreach (chi_nav_items() as $item): ?>
                    <a href="<?= $item['url'] ?>" class="<?= $cur === $item['url'] ? 'active' : '' ?>"><?= esc_html($item['label']) ?></a>
                <?php endforeach; ?>
                <a class="join-mobile" href="/#subscribe">Join the Pack</a><?php /* the .join button is hidden on small screens */ ?>
            </nav>

            <div class="header-actions">
                <form class="search" action="/search" method="get" role="search">
                    <button type="submit" aria-label="Search"><?= chi_icon('search', 18) ?></button>
                    <input type="search" name="q" placeholder="Search…" value="<?= esc_attr($ctx->q ?? '') ?>">
                </form>
                <a class="button-primary join" href="/#subscribe">Join the Pack <?= chi_paw(16) ?></a>
                <button class="nav-toggle" aria-label="Toggle menu" onclick="document.getElementById('nav').classList.toggle('open')">&#9776;</button>
            </div>
        </div>
    </header>

    <main class="container">
