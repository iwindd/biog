<?php
return [
    'bsVersion' => '4.x',
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
    'shortUrlDomain' => 'http://localhost:20080/s/',
    'urlFrontend' => rtrim(getenv('FRONTEND_URL') ?: 'http://localhost:20080', '/'),
    'fileCenterUploadLimits' => [
        'image' => [
            'extensions' => ['png', 'jpg', 'jpeg'],
            'maxSize' => 8 * 1024 * 1024, // 8MB
        ],
        'document' => [
            'extensions' => ['pdf'],
            'maxSize' => 25 * 1024 * 1024, // 25MB
        ]
    ],
    'thaid_env' => strtolower(trim(getenv('THAID_ENV') ?: 'sandbox')),
    'thaid_client_id' => trim(getenv('THAID_CLIENT_ID') ?: ''),
    'thaid_basic_token' => trim(getenv('THAID_BASIC_TOKEN') ?: ''),
    'thaid_secret' => trim(getenv('THAID_SECRET') ?: ''),
    'thaid_scope' => trim(getenv('THAID_SCOPE') ?: 'pid name birthdate'),
    'thaid_frontend_redirect_uri' => trim(getenv('THAID_FRONTEND_REDIRECT_URI') ?: ''),
    'thaid_backend_redirect_uri' => trim(getenv('THAID_BACKEND_REDIRECT_URI') ?: ''),
];
