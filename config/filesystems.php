<?php

return [
    'default' => env('FILESYSTEM_DISK', 's3'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'id-jkt-1'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_ENDPOINT') . '/' . env('AWS_BUCKET'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'throw' => true,
            'visibility' => 'public',
            'bucket_endpoint' => true,
            'directory_env' => env('AWS_DIRECTORY'),
            'options' => [
                'version' => 'latest',
                'http' => [
                    'verify' => false
                ],
                'bucket_endpoint' => true,
                'use_path_style_endpoint' => true
            ],
        ],
    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],
];
