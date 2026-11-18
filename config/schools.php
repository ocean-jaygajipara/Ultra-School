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
            'code'             => 'ues',
            'short_name'       => 'UES',
            'name'             => 'Ultra English School',
            'location'         => env('SCHOOL_LOCATION_UES', 'Behind Maruti Service Center, Veraval Road, Keshod - 362220'),
            'email'            => env('SCHOOL_EMAIL_UES', 'ultraenglishschool@gmail.com'),
            'mobile'           => env('SCHOOL_MOBILE_UES', '1234567890'),
            'standards'        => ['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th'],
            'passed_standards' => ['Std 1', 'Std 2', 'Std 3', 'Std 4', 'Std 5', 'Std 6', 'Std 7', 'Std 8'],
            'database'         => env('DB_DATABASE_UES', 'ultra_school_ues'),
            'username'         => env('DB_USERNAME_UES', env('DB_USERNAME')),
            'password'         => env('DB_PASSWORD_UES', env('DB_PASSWORD')),
            'drive_folder_id'  => env('GOOGLE_DRIVE_FOLDER_ID_UES'),
        ],
        'ups' => [
            'code'             => 'ups',
            'short_name'       => 'UPS',
            'name'             => 'Ultra Primary School',
            'location'         => env('SCHOOL_LOCATION_UPS', 'Behind Maruti Service Center, Veraval Road, Keshod - 362220'),
            'email'            => env('SCHOOL_EMAIL_UPS', 'ultraprimaryschool@gmail.com'),
            'mobile'           => env('SCHOOL_MOBILE_UPS', '1234657890'),
            'standards'        => ['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th'],
            'passed_standards' => ['Std 1', 'Std 2', 'Std 3', 'Std 4', 'Std 5', 'Std 6', 'Std 7', 'Std 8'],
            'database'         => env('DB_DATABASE_UPS', 'ultra_school_ups'),
            'username'         => env('DB_USERNAME_UPS', env('DB_USERNAME')),
            'password'         => env('DB_PASSWORD_UPS', env('DB_PASSWORD')),
            'drive_folder_id'  => env('GOOGLE_DRIVE_FOLDER_ID_UPS'),
        ],
        'uv' => [
            'code'             => 'uv',
            'short_name'       => 'UV',
            'name'             => 'Ultra Vidhyalay',
            'location'         => env('SCHOOL_LOCATION_UV', 'Behind Maruti Service Center, Veraval Road, Keshod - 362220'),
            'email'            => env('SCHOOL_EMAIL_UV', 'ultravidhyalay@gmail.com'),
            'mobile'           => env('SCHOOL_MOBILE_UV', '1234567980'),
            'standards'        => ['1st', '2nd', '3rd', '4th', '5th', '6th', '7th', '8th'],
            'passed_standards' => ['Std 1', 'Std 2', 'Std 3', 'Std 4', 'Std 5', 'Std 6', 'Std 7', 'Std 8'],
            'database'         => env('DB_DATABASE_UV', 'ultra_school_uv'),
            'username'         => env('DB_USERNAME_UV', env('DB_USERNAME')),
            'password'         => env('DB_PASSWORD_UV', env('DB_PASSWORD')),
            'drive_folder_id'  => env('GOOGLE_DRIVE_FOLDER_ID_UV'),
        ],
        'us' => [
            'code'             => 'us',
            'short_name'       => 'US',
            'name'             => 'Ultra Secondary School',
            'location'         => env('SCHOOL_LOCATION_US', 'Behind Maruti Service Center, Veraval Road, Keshod - 362220'),
            'email'            => env('SCHOOL_EMAIL_US', 'ultrasecondaryschool@gmail.com'),
            'mobile'           => env('SCHOOL_MOBILE_US', '1234567890'),
            'standards'        => [
                '9th - Gujarati Medium',
                '9th - English Medium',
                '10th - Gujarati Medium',
                '10th - English Medium',
            ],
            'passed_standards' => ['Std 8', 'Std 9', 'Std 10'],
            'database'         => env('DB_DATABASE_US', 'ultra_school_us'),
            'username'         => env('DB_USERNAME_US', env('DB_USERNAME')),
            'password'         => env('DB_PASSWORD_US', env('DB_PASSWORD')),
            'drive_folder_id'  => env('GOOGLE_DRIVE_FOLDER_ID_US'),
        ],
    ],
];
