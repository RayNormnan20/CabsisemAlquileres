<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "pusher", "ably", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_DRIVER', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'host' => env('PUSHER_HOST', env('APP_ENV') === 'production' ? 'cabsisem.net.pe' : '127.0.0.1'),
                'port' => env('PUSHER_PORT', env('APP_ENV') === 'production' ? 6001 : 6001),
                'scheme' => env('PUSHER_SCHEME', env('APP_ENV') === 'production' ? 'https' : 'http'),
                'encrypted' => env('APP_ENV') === 'production',
                'useTLS' => env('PUSHER_SCHEME', env('APP_ENV') === 'production' ? 'https' : 'http') === 'https',
                'curl_options' => [
                    CURLOPT_SSL_VERIFYHOST => env('APP_ENV') === 'production' ? 2 : 0,
                    CURLOPT_SSL_VERIFYPEER => env('APP_ENV') === 'production' ? true : false,
                ],
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
                'verify' => env('APP_ENV') === 'production',
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];