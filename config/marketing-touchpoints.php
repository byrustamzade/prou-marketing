<?php

return [
    'enabled' => true,

    'track' => [
        'methods' => ['GET'],
        'only_with_utm' => false,
        // Skip writing a new touchpoint when the same visitor hits the exact same URL
        // again within this many seconds. Set to 0 to disable.
        'dedupe_seconds' => 300,
        'utm_keys' => [
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'utm_content',
            'utm_id',
            'gclid',
            'fbclid',
            'msclkid',
        ],
    ],

    'cookie' => [
        'name' => 'marketing_touch_token',
        'minutes' => 60 * 24 * 365 * 2,
        'path' => '/',
        'domain' => null,
        'secure' => null,
        'http_only' => true,
        'same_site' => 'lax',
    ],

    'middleware' => [
        'alias' => 'track-touchpoints',
        'auto_track_web' => false,
        'except' => [
            'marketing*',
            'telescope*',
            'horizon*',
            '_debugbar*',
        ],
    ],

    'route' => [
        'enabled' => true,
        'prefix' => 'marketing',
        'name' => 'marketing-touchpoints.',
        'middleware' => ['web', 'auth'],
    ],

    'tables' => [
        'visitors' => 'marketing_visitors',
        'touchpoints' => 'marketing_touchpoints',
        'conversions' => 'marketing_conversions',
    ],

    'orders' => [
        'model' => null,
        'table' => 'orders',
        'primary_key' => 'id',
    ],

    'dashboard' => [
        'per_page' => 30,
    ],
];
