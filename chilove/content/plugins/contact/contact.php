<?php

/**
 * Plugin: ChiLove Contact
 * Stores contact-form submissions by hooking the chi_contact action.
 */

add_action('chi_contact', function (array $msg): void {
    try {
        db()->query(
            "INSERT INTO chi_messages (name, email, message) VALUES (?, ?, ?)",
            [$msg['name'], $msg['email'], $msg['message']]
        );
    } catch (\Throwable $e) {
        // Swallow in dev; a real plugin would log or email.
    }
});
