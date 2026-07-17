<?php

/**
 * ChiLove theme bootstrap. Hooks into the core lifecycle.
 */

// Example of the theme using the filter system: trim card excerpts to 18 words.
add_filter('chi_excerpt_words', static fn (int $words): int => 18);

/**
 * Shared post-card partial — the one place the card block markup lives.
 * Media with a category-pill overlay, clamped title/excerpt, and a
 * date · read-time + "Read more" footer. Options:
 *   excerpt (bool, default true) — show the excerpt paragraph
 *   words   (int,  default 16)   — excerpt length
 *   lead    (bool, default false) — wide horizontal spotlight variant
 */
function chi_post_card(object $p, array $o = []): string
{
    $o += ['excerpt' => true, 'words' => 16, 'lead' => false];
    $url = post_permalink($p);
    $cat = trim((string) ($p->category ?? ''));

    // "6 min read" wraps in the narrow card foot; "6 min" says enough next to "Read more"
    $meta = array_filter([chi_date($p->published_at ?? null), str_replace(' min read', ' min', chi_read_time($p))]);

    $html  = '<article class="post-card card' . ($o['lead'] ? ' lead' : '') . '">';
    $html .= '<a href="' . esc_attr($url) . '" class="post-media" aria-label="' . esc_attr($p->title) . '">' . chi_thumb($p);
    if ($cat !== '') {
        $html .= '<span class="category-pill">' . esc_html($cat) . '</span>';
    }
    $html .= '</a>';
    $html .= '<div class="post-card-body">';
    $html .= '<h3><a href="' . esc_attr($url) . '">' . esc_html($p->title) . '</a></h3>';
    if ($o['excerpt']) {
        $html .= '<p>' . esc_html(chi_excerpt($p->excerpt ?? $p->content, (int) $o['words'])) . '</p>';
    }
    $html .= '<div class="card-foot">';
    $html .= '<div class="post-meta small">' . implode(' · ', $meta) . '</div>';
    $html .= '<span class="read-more">Read more ' . chi_icon('arrow', 15) . '</span>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '</article>';
    return $html;
}
