<?php

/**
 * Web-root front controller. Boot the engine, then let the router render.
 */

require dirname(__DIR__) . '/core/bootstrap.php';

ChiLove\Core\Router::dispatch();
