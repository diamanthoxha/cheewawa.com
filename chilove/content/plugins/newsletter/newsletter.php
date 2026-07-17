<?php

/**
 * Plugin: ChiLove Newsletter
 * Captures "Join the Pack" signups by hooking the chi_subscribe action —
 * a small demonstration of the plugin + hook system.
 */

add_action('chi_subscribe', function (string $email): void {
    try {
        db()->query("INSERT IGNORE INTO chi_subscribers (email) VALUES (?)", [$email]);
    } catch (\Throwable $e) {
        // Swallow in dev; a real plugin would log this.
    }
});
