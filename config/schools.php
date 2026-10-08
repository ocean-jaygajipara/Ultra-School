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
        ],
        'ups' => [
            'code'       => 'ups',
            'short_name' => 'UPS',
            'name'       => 'Ultra Primary School',
            'database'   => env('DB_DATABASE_UPS', 'ultra_school_ups'),
        ],
        'uv' => [
            'code'       => 'uv',
            'short_name' => 'UV',
            'name'       => 'Ultra Vidhyalay',
            'database'   => env('DB_DATABASE_UV', 'ultra_school_uv'),
        ],
        'us' => [
            'code'       => 'us',
            'short_name' => 'US',
            'name'       => 'Ultra Secondary School',
            'database'   => env('DB_DATABASE_US', 'ultra_school_us'),
        ],
    ],
];
