<?php
return [
    'adminEmail' => 'admin@example.com',
    //'urlWebBiog' => 'http://biogang.net/',
    'urlWebBiog' => getenv("FRONTEND_URL"),
    'corsAllowedOrigins' => [
        'http://localhost:20080',
        'http://localhost:21080',
    ],
    'corsAllowWildcard' => false,
    'bsVersion' => '3.x',
];
