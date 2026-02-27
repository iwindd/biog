<?php

namespace common\components;

use Yii;

class UrlValidatorHelper
{
    /**
     * Dev-friendly URL rule
     * - DEV  : allow localhost + port
     * - PROD : strict http/https only
     */
    public static function devFriendly(string $attribute): array
    {
        if (YII_ENV_DEV) {

            return [
                [$attribute],
                'match',
                'pattern' => '/^https?:\/\/(localhost(:\d+)?|([\w-]+\.)+[\w-]+)(:\d+)?(\/.*)?$/i',
                'message' => 'URL ไม่ถูกต้อง (dev mode)'
            ];

        }

        return [
            [$attribute],
            'url',
            'validSchemes' => ['http', 'https'],
            'message' => 'URL ไม่ถูกต้อง'
        ];
    }
}