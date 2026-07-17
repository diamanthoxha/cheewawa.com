<?php

/**
 * ChiLove configuration — example.
 * Copy to config.php and fill in your environment's values.
 */

return [
    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'chilove',
        'user'    => 'chilove',
        'pass'    => 'change-me',
        'charset' => 'utf8mb4',
    ],
    'site' => [
        'name'    => 'Cheewawa',
        'tagline' => 'The chihuahua site that treats them like actual dogs.',
        'url'     => 'https://cheewawa.com',
    ],
    'analytics' => [
        // GA4 Web stream Measurement ID (G-XXXXXXXXXX); leave '' to disable.
        'ga4' => '',
        // Facebook App ID for the fb:app_id OG tag; '' omits the tag.
        'fb_app_id' => '',
    ],
];
