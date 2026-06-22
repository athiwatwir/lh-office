<?php

return [

    'cache_path' => storage_path('app/image-cache'),

    'default_quality' => (int) env('IMAGE_PROXY_QUALITY', 80),

    'remote_timeout' => (int) env('IMAGE_PROXY_REMOTE_TIMEOUT', 15),

    'legacy_base_url' => env('LEGACY_IMAGE_BASE_URL', 'https://lovethaihome.com/img/upload'),

    'sizes' => [
        'thumb' => ['w' => 400, 'h' => 300],
        'gallery' => ['w' => 1200, 'h' => null],
        'admin' => ['w' => 800, 'h' => null],
    ],

];
