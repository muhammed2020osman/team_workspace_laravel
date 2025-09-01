<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],
     'allowed_origins' => [
        'https://tafaahum.vercel.app',
        'http://localhost:8002',
        'https://school.gumra-ai.com',

           'https://marketplace.zoom.us'
    ],
    // 'allowed_origins' => ['http://localhost:8080','https://marketplace.zoom.us','https://school.gumra-ai.com'],
 'allowed_origins_patterns' => [

    '#^http://localhost:\d+$#',

    '#^https:\/\/.*\.cloudworkstations\.dev$#',
    '#^https:\/\/.*\.vercel\.app$#',
    '#^https:\/\/.*\.hosted\.app$#'

],
    'allowed_headers' => ['*'],

    'exposed_headers' => [],
    'max_age' => 0,

    'supports_credentials' => true,

];
