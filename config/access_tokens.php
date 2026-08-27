<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Code generation
    |--------------------------------------------------------------------------
    |
    | The 4-digit keyspace (10,000 codes) collides within the birthday bound,
    | so App\Actions\GenerateAccessToken retries a few times on a unique
    | constraint hit instead of failing on the first collision.
    |
    */

    'max_generation_attempts' => 5,

    /*
    |--------------------------------------------------------------------------
    | Login throttling
    |--------------------------------------------------------------------------
    |
    | Per-IP rate limiting for the access-code login screen: how many failed
    | attempts are allowed before the lockout toast, and how long (seconds)
    | each attempt is held against the IP.
    |
    */

    'login' => [
        'max_attempts' => 8,
        'decay_seconds' => 300,
    ],

];
