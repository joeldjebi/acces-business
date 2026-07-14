<?php

return [
    'platform_admin' => [
        'email' => env('PLATFORM_ADMIN_EMAIL', 'jo.djebi@gmail.com'),
        'name' => env('PLATFORM_ADMIN_NAME', 'Jo Djebi'),
    ],

    'security' => [
        'trusted_domains' => array_filter(array_map('trim', explode(',', env('SAAS_TRUSTED_DOMAINS', '')))),
        'force_https' => env('SAAS_FORCE_HTTPS', false),
    ],

    'billing' => [
        'provider' => env('BILLING_PROVIDER', 'manual'),
        'currency' => env('BILLING_CURRENCY', 'XOF'),
    ],
];
