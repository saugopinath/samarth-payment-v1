<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/private'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
            'report' => false,
        ],
        'ifms_sftp_026' => [
            'driver' => 'sftp',
            'host' => env('IFMS_SFTP_026_HOST', 'local'),
            'port' => (int) env('IFMS_SFTP_026_PORT', 22),
            'username' => env('IFMS_SFTP_026_USERNAME', ''),
            'password' => env('IFMS_SFTP_026_PASSWORD', ''),
            'root' => env('IFMS_SFTP_026_ROOT', '/gen026/'),
            'timeout' => (int) env('IFMS_SFTP_026_TIMEOUT', 20),
        ],

        'ifms_sftp_027' => [
            'driver' => 'sftp',
            'host' => env('IFMS_SFTP_027_HOST', 'local'),
            'port' => (int) env('IFMS_SFTP_027_PORT', 22),
            'username' => env('IFMS_SFTP_027_USERNAME', ''),
            'password' => env('IFMS_SFTP_027_PASSWORD', ''),
            'root' => env('IFMS_SFTP_027_ROOT', '/gen027/'),
            'timeout' => (int) env('IFMS_SFTP_027_TIMEOUT', 20),
        ],

        'ifms_sftp_028' => [
            'driver' => 'sftp',
            'host' => env('IFMS_SFTP_028_HOST', 'local'),
            'port' => (int) env('IFMS_SFTP_028_PORT', 22),
            'username' => env('IFMS_SFTP_028_USERNAME', ''),
            'password' => env('IFMS_SFTP_028_PASSWORD', ''),
            'root' => env('IFMS_SFTP_028_ROOT', '/gen028/'),
            'timeout' => (int) env('IFMS_SFTP_028_TIMEOUT', 20),
        ],

        'ifms_sftp_030' => [
            'driver' => 'sftp',
            'host' => env('IFMS_SFTP_030_HOST', 'local'),
            'port' => (int) env('IFMS_SFTP_030_PORT', 22),
            'username' => env('IFMS_SFTP_030_USERNAME', ''),
            'password' => env('IFMS_SFTP_030_PASSWORD', ''),
            'root' => env('IFMS_SFTP_030_ROOT', '/gen030/'),
            'timeout' => (int) env('IFMS_SFTP_030_TIMEOUT', 20),
        ],

        'ifms_sftp_031' => [
            'driver' => 'sftp',
            'host' => env('IFMS_SFTP_031_HOST', 'local'),
            'port' => (int) env('IFMS_SFTP_031_PORT', 22),
            'username' => env('IFMS_SFTP_031_USERNAME', ''),
            'password' => env('IFMS_SFTP_031_PASSWORD', ''),
            'root' => env('IFMS_SFTP_031_ROOT', '/gen031/'),
            'timeout' => (int) env('IFMS_SFTP_031_TIMEOUT', 20),
        ],

        'ifms_sftp_033' => [
            'driver' => 'sftp',
            'host' => env('IFMS_SFTP_033_HOST', 'local'),
            'port' => (int) env('IFMS_SFTP_033_PORT', 22),
            'username' => env('IFMS_SFTP_033_USERNAME', ''),
            'password' => env('IFMS_SFTP_033_PASSWORD', ''),
            'root' => env('IFMS_SFTP_033_ROOT', '/gen033/'),
            'timeout' => (int) env('IFMS_SFTP_033_TIMEOUT', 20),
        ],
        'sftp_sbi' => [
            'driver' =>  env('SBI_SFTP_DRIVER', 'sftp'),
            'host' => env('SBI_SFTP_HOST', '127.0.0.1'),
            'port' => (int) env('SBI_SFTP_PORT', 2201),
            'username' => env('SBI_SFTP_USERNAME', ''),
            'password' => env('SBI_SFTP_PASSWORD', ''),
            'root' => env('SBI_SFTP_ROOT', '/'),
            'timeout' => (int) env('SBI_SFTP_TIMEOUT', 30),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
