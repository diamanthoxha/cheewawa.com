<?php

/**
 * ChiLove procedural core — the global, WordPress-style template API.
 * Thin wrappers over the OOP engine so themes can stay procedural.
 */

use ChiLove\Core\Hooks;
use ChiLove\Core\Database;

/* ---- Actions & Filters ---- */

function add_action(string $hook, callable $cb, int $priority = 10): void
{
    Hooks::addAction($hook, $cb, $priority);
}

function do_action(string $hook, mixed ...$args): void
{
    Hooks::doAction($hook, ...$args);
}

function add_filter(string $hook, callable $cb, int $priority = 10): void
{
    Hooks::addFilter($hook, $cb, $priority);
}

function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
{
    return Hooks::applyFilters($hook, $value, ...$args);
}

/* ---- Database accessor ---- */

function db(): Database
{
    return Database::instance();
}

/* ---- Escaping helpers ---- */

function esc_html(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function esc_attr(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

function esc_url(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
