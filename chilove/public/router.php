<?php

/**
 * Dev router for PHP's built-in server (`php -S ... public/router.php`).
 * Serves real files (assets) directly; routes everything else to the app.
 */

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($path !== '/' && is_file(__DIR__ . $path)) {
    return false; // let the built-in server serve the static asset as-is
}

require __DIR__ . '/index.php';
