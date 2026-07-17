<?php

/**
 * ChiLove — custom bootstrap process.
 *
 *   1. autoloader   2. config   3. procedural core + template tags + pages
 *   4. database     5. plugins  6. active theme
 *
 * Fires WordPress-style lifecycle actions along the way.
 */

use ChiLove\Core\Autoloader;
use ChiLove\Core\Database;

define('CHILOVE_START', microtime(true));
define('CHILOVE_ROOT', dirname(__DIR__));
define('CHILOVE_CORE', __DIR__);
define('CHILOVE_CONTENT', CHILOVE_ROOT . '/content');
define('CHILOVE_THEME', 'chilove');
define('CHILOVE_THEME_DIR', CHILOVE_CONTENT . '/themes/' . CHILOVE_THEME);

date_default_timezone_set('UTC');

require CHILOVE_CORE . '/Autoloader.php';
Autoloader::register(CHILOVE_CORE);

$config = require CHILOVE_ROOT . '/config.php';
require CHILOVE_CORE . '/functions.php';   // procedural engine API (hooks, esc, db)
require CHILOVE_CORE . '/template.php';     // template tags + queries + svg helpers
require CHILOVE_CORE . '/pages.php';        // nav, blog index, pagination, gallery

Database::boot($config['db']);

// Plugins — each plugin is a PHP file under content/plugins/<name>/
foreach (glob(CHILOVE_CONTENT . '/plugins/*/*.php') ?: [] as $plugin) {
    require $plugin;
}

// Active theme bootstrap
$themeFunctions = CHILOVE_THEME_DIR . '/functions.php';
if (is_file($themeFunctions)) {
    require $themeFunctions;
}

do_action('init');
