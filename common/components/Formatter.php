<?php

namespace common\components;

use Yii;

class Formatter
{
    public static function getDisplaySourceLabelStatic($source_name, $author, $published_date, $reference_url)
    {
        $formattedItems = [];
        if (!empty($source_name)) {
            $formattedItems[] = $source_name;
        }elseif (!empty($reference_url)) {
            $host = parse_url($reference_url, PHP_URL_HOST);
            if ($host) {
                $host = str_replace('www.', '', $host);
                $hostParts = explode('.', $host);
                if (count($hostParts) > 0) {
                    $formattedItems[] = ucfirst($hostParts[0]);
                } else {
                    $formattedItems[] = ucfirst($host);
                }
            }
        }
        
        if (!empty($published_date)) {
            $formattedItems[] = '(' . date('d/m/Y', strtotime($published_date)) . ')';
        }

        if (!empty($author)) {
            $formattedItems[] = 'ผู้จัดทำโดย ' . $author;
        }

        if (!empty($reference_url)) {
            $formattedItems[] = '<a href="' . $reference_url . '" target="_blank">' . $reference_url . '</a>';
        }

        return !empty($formattedItems) ? implode('. ', $formattedItems) : '';
    }
}