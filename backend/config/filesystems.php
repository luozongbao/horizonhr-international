<?php

return [
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
            'throw'  => false,
        ],
        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],
        'oss' => [
            'driver'                  => 's3',
            'key'                     => env('OSS_ACCESS_KEY_ID'),
            'secret'                  => env('OSS_ACCESS_KEY_SECRET'),
            'region'                  => env('OSS_REGION', 'oss-cn-wuhan-lr'),
            'bucket'                  => env('OSS_BUCKET'),
            'url'                     => env('OSS_URL'),
            'endpoint'                => env('OSS_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'throw'                   => false,
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

    'default' => env('FILESYSTEM_DISK', 'local'),
];
