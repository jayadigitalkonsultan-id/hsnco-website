<?php

return [
    // APP_URL is the canonical public origin in production; local uses its APP_URL.
    'canonical_url' => env('APP_URL', 'https://hsncoatlaw.com'),
    'route_pages' => [
        'home' => 'home',
        'about' => 'about',
        'practice-areas' => 'practice-areas',
        'team' => 'team',
        'insights' => 'insights',
        'contact' => 'contact',
        'sitemap' => 'sitemap',
    ],
    'public_routes' => ['home', 'about', 'practice-areas', 'team', 'insights', 'contact', 'sitemap'],
];
