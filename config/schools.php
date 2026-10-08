<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Active School
    |--------------------------------------------------------------------------
    */
    'default' => env('DEFAULT_SCHOOL', 'ues'),

    /*
    |--------------------------------------------------------------------------
    | Registered Schools and their Databases
    |--------------------------------------------------------------------------
    */
    'list' => [
        'ues' => [
            'code'       => 'ues',
            'short_name' => 'UES',
            'name'       => 'Ultra English School',
            'database'   => env('DB_DATABASE_UES', 'ultra_school_ues'),
            'username'   => env('DB_USERNAME_UES', env('DB_USERNAME')),
            'password'   => env('DB_PASSWORD_UES', env('DB_PASSWORD')),
        ],
        'ups' => [
            'code'       => 'ups',
            'short_name' => 'UPS',
            'name'       => 'Ultra Primary School',
            'database'   => env('DB_DATABASE_UPS', 'ultra_school_ups'),
            'username'   => env('DB_USERNAME_UPS', env('DB_USERNAME')),
            'password'   => env('DB_PASSWORD_UPS', env('DB_PASSWORD')),
        ],
        'uv' => [
            'code'       => 'uv',
            'short_name' => 'UV',
            'name'       => 'Ultra Vidhyalay',
            'database'   => env('DB_DATABASE_UV', 'ultra_school_uv'),
            'username'   => env('DB_USERNAME_UV', env('DB_USERNAME')),
            'password'   => env('DB_PASSWORD_UV', env('DB_PASSWORD')),
        ],
        'us' => [
            'code'       => 'us',
            'short_name' => 'US',
            'name'       => 'Ultra Secondary School',
            'database'   => env('DB_DATABASE_US', 'ultra_school_us'),
            'username'   => env('DB_USERNAME_US', env('DB_USERNAME')),
            'password'   => env('DB_PASSWORD_US', env('DB_PASSWORD')),
        ],
    ],
];
