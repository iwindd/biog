<?php

namespace frontend\components;

use common\components\_;

class GoogleMapHelper
{
    private const GOOGLE_MAP_KEY = 'AIzaSyBDzKtsv5PEXijgyFZtaHx3mz42vcEoqDQ'; // AIzaSyD2TidBDEwBra-l6LJDgBDcKmAK-ctwrjQ
    private const CALLBACK_FUNCTION_NAME = 'initialMap';

    public static function getGoogleMapApiUrl()
    {
        $googleMapKey = self::GOOGLE_MAP_KEY;
        $callbackFucntionName = self::CALLBACK_FUNCTION_NAME;
        $currentLanguage = _::currentLanguage();

        $url = "https://maps.googleapis.com/maps/api/js?key=${googleMapKey}&callback=${callbackFucntionName}&language=${currentLanguage}&libraries=places";

        return $url;
    }
}
