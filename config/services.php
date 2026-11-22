<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'auth_service' => [
        'enabled' => env('AUTH_SERVICE_ENABLED', false),
        'url' => env('AUTH_SERVICE_URL', 'http://localhost:8001/api/verify'),
    ],

    'product_service' => [
        'url' => env('PRODUCT_SERVICE_URL', 'http://localhost:8000/api/products'),
        'timeout' => env('PRODUCT_SERVICE_TIMEOUT', 5),
        'reduce_stock' => env('PRODUCT_SERVICE_REDUCE_STOCK', false),
    ],

    'order_service' => [
        'url' => env('ORDER_SERVICE_URL', 'http://localhost:8002/api/orders'),
        'timeout' => env('ORDER_SERVICE_TIMEOUT', 5),
    ],

];
