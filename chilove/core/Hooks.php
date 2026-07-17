<?php

namespace ChiLove\Core;

/**
 * WordPress-style Actions & Filters.
 *
 * One registry keyed by hook name, then by priority. Actions are just
 * filters whose return value is ignored — same as WP core.
 */
final class Hooks
{
    /** @var array<string, array<int, list<callable>>> */
    private static array $filters = [];

    public static function addFilter(string $hook, callable $cb, int $priority = 10): void
    {
        self::$filters[$hook][$priority][] = $cb;
    }

    public static function applyFilters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if (empty(self::$filters[$hook])) {
            return $value;
        }
        ksort(self::$filters[$hook]);
        foreach (self::$filters[$hook] as $callbacks) {
            foreach ($callbacks as $cb) {
                $value = $cb($value, ...$args);
            }
        }
        return $value;
    }

    public static function addAction(string $hook, callable $cb, int $priority = 10): void
    {
        self::addFilter($hook, $cb, $priority);
    }

    public static function doAction(string $hook, mixed ...$args): void
    {
        if (empty(self::$filters[$hook])) {
            return;
        }
        ksort(self::$filters[$hook]);
        foreach (self::$filters[$hook] as $callbacks) {
            foreach ($callbacks as $cb) {
                $cb(...$args);
            }
        }
    }
}
