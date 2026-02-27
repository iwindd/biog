<?php

namespace backend\components;

use Yii;
use backend\models\Region;
use backend\models\Province;
use backend\models\District;
use backend\models\Subdistrict;
use backend\models\Zipcode;

class ImportHelper
{
    /**
     * Parse coordinate string which can be UTM or LatLong
     * UTM: 47N,541659.42,1004476.58
     * LatLong: 9.0868565,99.3791066
     */
    public static function parseCoordinates($coordString)
    {
        if (empty($coordString)) {
            return ['lat' => null, 'lng' => null];
        }

        $parts = explode(',', $coordString);
        if (count($parts) === 3 && stripos($parts[0], 'N') !== false) {
            // UTM
            return self::utmToLatLon($parts[0], $parts[1], $parts[2]);
        } elseif (count($parts) === 2) {
            // LatLong
            return [
                'lat' => trim($parts[0]),
                'lng' => trim($parts[1])
            ];
        }

        return ['lat' => null, 'lng' => null];
    }

    /**
     * Simple UTM to LatLon (WGS84) conversion for Zone 47N/48N (Thailand)
     * This is a simplified version. For precision, a library should be used.
     * But for Thailand UTM 47N/48N, we can use a basic approximation or a known formula.
     */
    public static function utmToLatLon($zone, $easting, $northing)
    {
        // For simplicity, we might need a library or a complex formula.
        // If we don't have a library, we'll use a standard formula for UTM conversion.
        // Let's assume Zone 47N or 48N.
        
        $zoneNum = intval(preg_replace('/[^0-9]/', '', $zone));
        $northernHemisphere = true; // Thailand is in northern hemisphere

        $a = 6378137;
        $f = 1 / 298.257223563;
        $k0 = 0.9996;

        $e = sqrt(1 - pow( (1-$f), 2));
        $e2 = $e*$e / (1-$e*$e);

        $x = $easting - 500000;
        $y = $northing;

        $longOrigin = ($zoneNum - 1) * 6 - 180 + 3;
        $longOriginRad = deg2rad($longOrigin);

        $m = $y / $k0;
        $mu = $m / ($a * (1 - $e*$e/4 - 3*pow($e,4)/64 - 5*pow($e,6)/256));

        $phi1Rad = $mu + (3*$e2/8 - 3*pow($e2,2)/32) * sin(2*$mu) 
                  + (21*pow($e2,2)/256 - 45*pow($e2,3)/1024) * sin(4*$mu)
                  + (151*pow($e2,3)/4096) * sin(6*$mu);
        
        $n1 = $a / sqrt(1 - pow($e*sin($phi1Rad), 2));
        $t1 = pow(tan($phi1Rad), 2);
        $c1 = $e2 * pow(cos($phi1Rad), 2);
        $r1 = $a * (1 - $e*$e) / pow(1 - pow($e*sin($phi1Rad), 2), 1.5);
        $d = $x / ($n1 * $k0);

        $lat = $phi1Rad - ($n1 * tan($phi1Rad) / $r1) * (
                $d*$d/2 - (5 + 3*$t1 + 10*$c1 - 4*$c1*$c1 - 9*$e2) * pow($d,4)/24
                + (61 + 90*$t1 + 298*$c1 + 45*$t1*$t1 - 252*$e2 - 3*$c1*$c1) * pow($d,6)/720
            );
        $lat = rad2deg($lat);

        $lon = ($d - (1 + 2*$t1 + $c1) * pow($d,3)/6) + (5 - 2*$c1 + 28*$t1 - 3*$c1*$c1 + 8*$e2 + 24*$t1*$t1) * pow($d,5)/120;
        $lon = $longOrigin + rad2deg($lon / cos($phi1Rad));

        return ['lat' => $lat, 'lng' => $lon];
    }

    /**
     * Find address IDs from names
     */
    public static function findAddressIds($regionName, $provinceName, $districtName, $subdistrictName, $zipcode)
    {
        $res = [
            'region_id' => null,
            'province_id' => null,
            'district_id' => null,
            'subdistrict_id' => null,
            'zipcode_id' => null,
        ];

        if (!empty($regionName)) {
            $reg = Region::find()->where(['like', 'name_th', $regionName])->one();
            if ($reg) $res['region_id'] = $reg->id;
        }

        if (!empty($provinceName)) {
            $prov = Province::find()->where(['like', 'name_th', $provinceName])->one();
            if ($prov) $res['province_id'] = $prov->id;
        }

        if (!empty($districtName)) {
            $dist = District::find()->where(['like', 'name_th', $districtName])->one();
            if ($dist) $res['district_id'] = $dist->id;
        }

        if (!empty($subdistrictName)) {
            $sub = Subdistrict::find()->where(['like', 'name_th', $subdistrictName])->one();
            if ($sub) $res['subdistrict_id'] = $sub->id;
        }

        if (!empty($zipcode)) {
            $zp = Zipcode::find()->where(['zipcode' => $zipcode])->one();
            if ($zp) $res['zipcode_id'] = $zp->id;
        }

        return $res;
    }

    public static function autoFillAddress($lat, $lng)
    {
        if (empty($lat) || empty($lng)) {
            return [];
        }

        $apiKey = 'AIzaSyBDzKtsv5PEXijgyFZtaHx3mz42vcEoqDQ'; 
        $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng={$lat},{$lng}&language=th&key={$apiKey}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return [];
        }

        $data = json_decode($response, true);
        if ($data['status'] !== 'OK' || empty($data['results'])) {
            return [];
        }

        $components = $data['results'][0]['address_components'];
        $province = '';
        $district = '';
        $subdistrict = '';
        $zipcode = '';
        $country = '';

        foreach ($components as $component) {
            $types = $component['types'];
            $longName = $component['long_name'];

            if (in_array('administrative_area_level_1', $types)) {
                $province = $longName;
            } elseif (in_array('administrative_area_level_2', $types)) {
                $district = $longName;
            } elseif (in_array('sublocality_level_1', $types) || in_array('sublocality', $types)) {
                $subdistrict = $longName;
            } elseif (in_array('locality', $types) && empty($subdistrict)) {
                $subdistrict = $longName;
            } elseif (in_array('postal_code', $types)) {
                $zipcode = $longName;
            } elseif (in_array('country', $types)) {
                $country = $component['short_name'];
            }
        }

        if (empty($subdistrict)) {
            foreach ($components as $component) {
                if (in_array('neighborhood', $component['types'])) {
                    $subdistrict = $component['long_name'];
                    break;
                }
            }
        }

        if ($country !== 'TH') {
            return [];
        }

        return [
            'province' => str_replace('จ.', '', str_replace('จังหวัด', '', $province)),
            'district' => str_replace('อ.', '', str_replace('อำเภอ', '', str_replace('เขต', '', $district))),
            'subdistrict' => str_replace('ต.', '', str_replace('ตำบล', '', str_replace('แขวง', '', $subdistrict))),
            'zipcode' => $zipcode
        ];
    }
}
