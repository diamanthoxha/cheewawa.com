<?php

namespace ChiLove\Core;

/**
 * Front-controller router: maps a request to a context + theme template,
 * the way WordPress maps a query to the template hierarchy.
 */
final class Router
{
    /** Static pages backed by a page-<name>.php template. */
    private const PAGES = ['about', 'contact', 'privacy', 'cookies', 'unsubscribe'];

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $uri = '/' . trim($uri, '/');

        // Newsletter signup → newsletter plugin (hook system).
        if ($method === 'POST' && $uri === '/subscribe') {
            $email = trim((string) ($_POST['email'] ?? ''));
            $valid = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            if ($valid) {
                do_action('chi_subscribe', $email);
            }
            header('Location: /?subscribed=' . ($valid ? '1' : '0') . '#subscribe');
            return;
        }

        // Newsletter opt-out (compliance-20260722): removes the address, then
        // confirms without revealing whether it was subscribed (no enumeration).
        if ($method === 'POST' && $uri === '/unsubscribe') {
            $email = trim((string) ($_POST['email'] ?? ''));
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                db()->query("DELETE FROM chi_subscribers WHERE email = ?", [$email]);
            }
            header('Location: /unsubscribe?done=1');
            return;
        }

        // Contact form → contact plugin (hook system).
        if ($method === 'POST' && $uri === '/contact') {
            $name    = trim((string) ($_POST['name'] ?? ''));
            $email   = trim((string) ($_POST['email'] ?? ''));
            $message = trim((string) ($_POST['message'] ?? ''));
            $valid   = $name !== '' && $message !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
            if ($valid) {
                do_action('chi_contact', ['name' => $name, 'email' => $email, 'message' => $message]);
            }
            header('Location: /contact?sent=' . ($valid ? '1' : '0'));
            return;
        }

        // XML sitemap for crawlers (robots.txt already points here).
        if ($uri === '/sitemap.xml') {
            chi_sitemap_xml();
            return;
        }

        // RSS feed (legacy /feed URLs 301 here).
        if ($uri === '/rss.xml') {
            chi_rss_xml();
            return;
        }

        // REST API layer.
        if (str_starts_with($uri, '/api/')) {
            Rest::handle($uri);
            return;
        }

        $ctx = self::resolve($uri);
        $GLOBALS['chi'] = $ctx;

        if ($ctx->type === '404') {
            http_response_code(404);
        }

        $template = match ($ctx->type) {
            'single'  => 'single.php',
            'archive' => 'archive.php',
            'author'  => 'author.php',
            'search'  => 'search.php',
            'blog'    => 'blog.php',
            'page'    => 'page-' . $ctx->page . '.php',
            '404'     => '404.php',
            default   => 'front-page.php',
        };

        include CHILOVE_THEME_DIR . '/' . $template;
    }

    private static function resolve(string $uri): object
    {
        if ($uri === '/') {
            return (object) ['type' => 'home'];
        }
        if ($uri === '/blog') {
            return (object) ['type' => 'blog'];
        }
        if (in_array(ltrim($uri, '/'), self::PAGES, true)) {
            return (object) ['type' => 'page', 'page' => ltrim($uri, '/')];
        }
        if (preg_match('#^/post/([a-z0-9-]+)$#', $uri, $m)) {
            $post = get_post_by_slug($m[1]);
            return $post ? (object) ['type' => 'single', 'post' => $post] : (object) ['type' => '404'];
        }
        if (preg_match('#^/category/([a-z0-9-]+)$#', $uri, $m)) {
            $term = get_category_by_slug($m[1]);
            return $term ? (object) ['type' => 'archive', 'term' => $term] : (object) ['type' => '404'];
        }
        if (preg_match('#^/author/([a-z0-9-]+)$#', $uri, $m)) {
            $user = get_author_by_slug($m[1]);
            return $user ? (object) ['type' => 'author', 'user' => $user] : (object) ['type' => '404'];
        }
        if ($uri === '/search') {
            return (object) ['type' => 'search', 'q' => trim((string) ($_GET['q'] ?? ''))];
        }
        return (object) ['type' => '404'];
    }
}
