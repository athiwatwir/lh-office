<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Site Profile
    |--------------------------------------------------------------------------
    |
    | Used when an agent has no system_type or an unknown value.
    |
    */

    'default_profile' => env('SITE_DEFAULT_PROFILE', 'A'),

    /*
    |--------------------------------------------------------------------------
    | Site Profiles
    |--------------------------------------------------------------------------
    |
    | Feature flags per profile. Linked to agents.system_type (A, B, ...).
    |
    */

    'profiles' => [
        'A' => [
            'zone' => true,
            'special_price' => false,
        ],
        'B' => [
            'zone' => false,
            'special_price' => true,
        ],
    ],

];
