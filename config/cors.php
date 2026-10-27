<?php

// Endpoint publik portal untuk landing Astro: /api/assessment/score dan /api/session (dengan cookie).
return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST'],

    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', (string) env('CORS_ALLOWED_ORIGINS', 'http://localhost:4321'))))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Accept', 'Content-Type', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 600,

    // /api/session membaca cookie session portal (fetch `credentials: 'include'` dari landing).
    'supports_credentials' => true,

];
