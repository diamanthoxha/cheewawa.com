<?php

namespace ChiLove\Core;

/**
 * Tiny REST API layer (≈ wp-json). JSON read endpoints for posts/categories.
 */
final class Rest
{
    public static function handle(string $uri): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $payload = match ($uri) {
            '/api/posts'      => ['posts' => get_recent_posts(20)],
            '/api/categories' => ['categories' => get_categories()],
            default           => null,
        };

        if ($payload === null) {
            http_response_code(404);
            echo json_encode(['error' => 'not_found']);
            return;
        }

        echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
