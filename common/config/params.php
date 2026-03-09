<?php
return [
    'bsVersion' => '4.x',
    'adminEmail' => 'admin@example.com',
    'supportEmail' => 'support@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',
    'user.passwordResetTokenExpire' => 3600,
    'shortUrlDomain' => 'http://localhost:20080/s/',
    'urlFrontend' => 'http://localhost:20080',
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
    'thaid_env' => getenv('THAID_ENV') ?: 'sandbox',
    'thaid_client_id' => getenv('THAID_CLIENT_ID'),
    'thaid_basic_token' => getenv('THAID_BASIC_TOKEN'),
    'thaid_secret' => getenv('THAID_SECRET'),
];
