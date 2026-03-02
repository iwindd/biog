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

    /**
     * Ensure all address fields (region_id, province_id, district_id, subdistrict_id, zipcode_id)
     * are populated. If any are missing, auto-fill from coordinates via Google API.
     * Final fallback: lookup zipcode from subdistrict if still missing.
     *
     * @param array $item Import item with address fields and lat/lng
     * @return array Updated item with address fields filled in
     */
    public static function ensureAddressFields($item)
    {
        $addressFields = ['region_id', 'province_id', 'district_id', 'subdistrict_id', 'zipcode_id'];

        // Check if any address field is missing
        $hasMissing = false;
        foreach ($addressFields as $field) {
            if (empty($item[$field])) {
                $hasMissing = true;
                break;
            }
        }

        // Auto-fill missing fields from coordinates
        if ($hasMissing && !empty($item['latitude']) && !empty($item['longitude'])) {
            $autoAddress = self::autoFillAddress($item['latitude'], $item['longitude']);
            if (!empty($autoAddress)) {
                foreach ($addressFields as $field) {
                    if (empty($item[$field]) && !empty($autoAddress[$field])) {
                        $item[$field] = $autoAddress[$field];
                    }
                }
            }
        }

        // Final fallback: lookup zipcode from subdistrict
        if (empty($item['zipcode_id']) && !empty($item['subdistrict_id'])) {
            $zipModel = Zipcode::find()->where(['subdistrict_id' => $item['subdistrict_id']])->one();
            if ($zipModel) {
                $item['zipcode_id'] = $zipModel->id;
            }
        }

        return $item;
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

        $result = [
            'region_id' => null,
            'province_id' => null,
            'district_id' => null,
            'subdistrict_id' => null,
            'zipcode_id' => null,
            'province' => $province,
            'district' => $district,
            'subdistrict' => $subdistrict,
            'zipcode' => $zipcode
        ];

        // Strip common Thai prefixes for fuzzy matching (Same as ApiController)
        $stripPrefixes = function ($name) {
            $prefixes = ['จังหวัด', 'อำเภอ', 'เขต', 'แขวง', 'ตำบล', 'จ.', 'อ.', 'ต.'];
            foreach ($prefixes as $prefix) {
                if (mb_strpos($name, $prefix) === 0) {
                    $name = mb_substr($name, mb_strlen($prefix));
                }
            }
            return trim($name);
        };

        // Match province
        if (!empty($province)) {
            $cleanName = $stripPrefixes($province);
            $provModel = Province::find()
                ->where(['or',
                    ['like', 'name_th', $cleanName],
                    ['like', 'name_en', $province],
                ])
                ->one();
            if ($provModel) {
                $result['province_id'] = (int) $provModel->id;
                $result['region_id'] = (int) $provModel->region_id;
            }
        }

        // Match district (scoped to province if found)
        if (!empty($district)) {
            $cleanName = $stripPrefixes($district);
            $districtQuery = District::find()
                ->where(['or',
                    ['like', 'name_th', $cleanName],
                    ['like', 'name_en', $district],
                ]);
            if ($result['province_id']) {
                $districtQuery->andWhere(['province_id' => $result['province_id']]);
            }
            $distModel = $districtQuery->one();
            if ($distModel) {
                $result['district_id'] = (int) $distModel->id;
                // Also set province from district if not yet found
                if (!$result['province_id']) {
                    $result['province_id'] = (int) $distModel->province_id;
                    $provModel2 = Province::findOne($distModel->province_id);
                    if ($provModel2) {
                        $result['region_id'] = (int) $provModel2->region_id;
                    }
                }
            }
        }

        // Match subdistrict (scoped to district if found)
        if (!empty($subdistrict)) {
            $cleanName = $stripPrefixes($subdistrict);
            $subdistrictQuery = Subdistrict::find()
                ->where(['or',
                    ['like', 'name_th', $cleanName],
                    ['like', 'name_en', $subdistrict],
                ]);
            if ($result['district_id']) {
                $subdistrictQuery->andWhere(['district_id' => $result['district_id']]);
            }
            $subModel = $subdistrictQuery->one();
            if ($subModel) {
                $result['subdistrict_id'] = (int) $subModel->id;
            }
        }

        // Match zipcode (scoped to subdistrict if found)
        if (!empty($zipcode)) {
            $zipcodeQuery = Zipcode::find()->where(['zipcode' => $zipcode]);
            if ($result['subdistrict_id']) {
                $zipcodeQuery->andWhere(['subdistrict_id' => $result['subdistrict_id']]);
            }
            $zipModel = $zipcodeQuery->one();
            if ($zipModel) {
                $result['zipcode_id'] = (int) $zipModel->id;
            }
        }

        return $result;
    }

    // =========================================================================
    // Shared Import Methods (used by all content controllers)
    // =========================================================================

    /**
     * Default column mapping for Excel import (plant/animal/fungi)
     */
    public static function getDefaultColumnMapping()
    {
        return [
            0 => 'name',
            1 => 'other_name',
            2 => 'characteristics',
            3 => 'benefits',
            4 => 'found_source',
            5 => 'coord',
            6 => 'region',
            7 => 'province',
            8 => 'district',
            9 => 'subdistrict',
            10 => 'zipcode',
            11 => 'picture_path_label',
            12 => 'other_information',
            13 => 'season',
            14 => 'ability',
            15 => 'common_name',
            16 => 'scientific_name',
            17 => 'family_name',
            18 => 'illustration_labels',
            19 => 'taxonomy_names',
            20 => 'image_sources',
            21 => 'data_sources',
            22 => 'note',
            23 => 'status',
            24 => 'license_code',
            25 => 'is_hidden',
        ];
    }

    /**
     * Column mapping for Expert (ภูมิปัญญา/ปราชญ์) Excel import
     */
    public static function getExpertColumnMapping()
    {
        return [
            0 => 'expert_category_name',
            1 => 'name',
            2 => 'expert_firstname',
            3 => 'expert_lastname',
            4 => 'expert_birthdate_raw',
            5 => 'expert_expertise',
            6 => 'description',
            7 => 'image_sources',
            8 => 'data_sources',
            9 => 'expert_occupation',
            10 => 'expert_card_id',
            11 => 'phone',
            12 => 'address',
            13 => 'coord',
            14 => 'region',
            15 => 'province',
            16 => 'district',
            17 => 'subdistrict',
            18 => 'zipcode',
            19 => 'picture_path_label',
            20 => 'illustration_labels',
            21 => 'taxonomy_names',
            22 => 'note',
            23 => 'status',
            24 => 'license_code',
            25 => 'is_hidden',
        ];
    }

    /**
     * Lookup ExpertCategory by name
     * @param string $name
     * @return array ['expert_category_id' => ..., 'expert_category_error' => ...]
     */
    public static function lookupExpertCategory($name)
    {
        $result = ['expert_category_id' => null, 'expert_category_error' => null];
        $trimName = trim($name ?? '');
        if (empty($trimName)) {
            $result['expert_category_error'] = 'ไม่ได้ระบุหมวดหมู่ภูมิปัญญา/ปราชญ์';
            return $result;
        }

        $cat = \backend\models\ExpertCategory::find()->where(['name' => $trimName])->one();
        if ($cat) {
            $result['expert_category_id'] = $cat->id;
        } else {
            $result['expert_category_error'] = "ไม่พบหมวดหมู่ภูมิปัญญา/ปราชญ์ '{$trimName}'";
        }

        return $result;
    }

    /**
     * Process a single Excel row for Expert content type
     * @param array $row Raw Excel row data
     * @param array $columnMapping
     * @return array Processed item
     */
    public static function processExpertRow($row, $columnMapping)
    {
        $item = [];
        foreach ($columnMapping as $colIndex => $fieldName) {
            $item[$fieldName] = $row[$colIndex] ?? null;
        }

        // Custom mapping
        $item['status'] = $item['status'] ?? 'pending';
        $item['is_hidden'] = (trim($item['is_hidden'] ?? '') === 'ซ่อน') ? 1 : 0;

        // Expert category lookup
        $catResult = self::lookupExpertCategory($item['expert_category_name']);
        $item = array_merge($item, $catResult);

        // Parse birthdate
        $item['expert_birthdate'] = self::parseThaiDate($item['expert_birthdate_raw'] ?? '');

        // Process Coordinates and Address
        $coords = self::parseCoordinates($item['coord']);
        $item['latitude'] = $coords['lat'];
        $item['longitude'] = $coords['lng'];

        if (empty($item['province']) && empty($item['district']) && $item['latitude'] && $item['longitude']) {
            $autoAddress = self::autoFillAddress($item['latitude'], $item['longitude']);
            if (!empty($autoAddress)) {
                $item = array_merge($item, $autoAddress);
            }
        }

        // Map Address to IDs
        if (empty($item['province_id']) || empty($item['district_id'])) {
            $addressIds = self::findAddressIds(
                $item['region'], $item['province'], $item['district'], $item['subdistrict'], $item['zipcode']
            );
            $item = array_merge($item, $addressIds);
        }

        // Cover image lookup
        $coverResult = self::lookupCoverImage($item['picture_path_label']);
        $item = array_merge($item, $coverResult);

        // Illustration images lookup
        $illustResult = self::lookupIllustrations($item['illustration_labels']);
        $item = array_merge($item, $illustResult);

        // Image sources
        $imgSources = self::parseSourceEntries($item['image_sources'], 'แหล่งที่มารูปภาพ');
        $item['image_sources_data'] = $imgSources['data'];
        $item['image_sources_errors'] = $imgSources['errors'];

        // Data sources
        $dataSources = self::parseSourceEntries($item['data_sources'], 'แหล่งอ้างอิงข้อมูล');
        $item['data_sources_data'] = $dataSources['data'];
        $item['data_sources_errors'] = $dataSources['errors'];

        // License lookup
        $licenseResult = self::lookupLicense($item['license_code']);
        $item = array_merge($item, $licenseResult);

        // Fallback status
        $validStatuses = ['pending', 'approved', 'rejected'];
        $status = strtolower($item['status'] ?? '');
        $item['status'] = in_array($status, $validStatuses) ? $status : 'pending';

        return $item;
    }

    /**
     * Column mapping for Ecotourism (แหล่งท่องเที่ยวเชิงนิเวศ) Excel import
     */
    public static function getEcotourismColumnMapping()
    {
        return [
            0 => 'name',
            1 => 'description',
            2 => 'image_sources',
            3 => 'data_sources',
            4 => 'travel_information',
            5 => 'address',
            6 => 'coord',
            7 => 'region',
            8 => 'province',
            9 => 'district',
            10 => 'subdistrict',
            11 => 'zipcode',
            12 => 'picture_path_label',
            13 => 'phone',
            14 => 'contact_name',
            15 => 'contact',
            16 => 'illustration_labels',
            17 => 'taxonomy_names',
            18 => 'note',
            19 => 'status',
            20 => 'license_code',
            21 => 'is_hidden',
        ];
    }

    /**
     * Process a single Excel row for Ecotourism content type
     * @param array $row Raw Excel row data
     * @param array $columnMapping
     * @return array Processed item
     */
    public static function processEcotourismRow($row, $columnMapping)
    {
        $item = [];
        foreach ($columnMapping as $colIndex => $fieldName) {
            $item[$fieldName] = $row[$colIndex] ?? null;
        }

        // Custom mapping
        $item['status'] = $item['status'] ?? 'pending';
        $item['is_hidden'] = (trim($item['is_hidden'] ?? '') === 'ซ่อน') ? 1 : 0;

        // Process Coordinates and Address
        $coords = self::parseCoordinates($item['coord']);
        $item['latitude'] = $coords['lat'];
        $item['longitude'] = $coords['lng'];

        if (empty($item['province']) && empty($item['district']) && $item['latitude'] && $item['longitude']) {
            $autoAddress = self::autoFillAddress($item['latitude'], $item['longitude']);
            if (!empty($autoAddress)) {
                $item = array_merge($item, $autoAddress);
            }
        }

        // Map Address to IDs
        if (empty($item['province_id']) || empty($item['district_id'])) {
            $addressIds = self::findAddressIds(
                $item['region'], $item['province'], $item['district'], $item['subdistrict'], $item['zipcode']
            );
            $item = array_merge($item, $addressIds);
        }

        // Cover image lookup
        $coverResult = self::lookupCoverImage($item['picture_path_label']);
        $item = array_merge($item, $coverResult);

        // Illustration images lookup
        $illustResult = self::lookupIllustrations($item['illustration_labels']);
        $item = array_merge($item, $illustResult);

        // Image sources
        $imgSources = self::parseSourceEntries($item['image_sources'], 'แหล่งที่มารูปภาพ');
        $item['image_sources_data'] = $imgSources['data'];
        $item['image_sources_errors'] = $imgSources['errors'];

        // Data sources
        $dataSources = self::parseSourceEntries($item['data_sources'], 'แหล่งอ้างอิงข้อมูล');
        $item['data_sources_data'] = $dataSources['data'];
        $item['data_sources_errors'] = $dataSources['errors'];

        // License lookup
        $licenseResult = self::lookupLicense($item['license_code']);
        $item = array_merge($item, $licenseResult);

        // Fallback status
        $validStatuses = ['pending', 'approved', 'rejected'];
        $status = strtolower($item['status'] ?? '');
        $item['status'] = in_array($status, $validStatuses) ? $status : 'pending';

        return $item;
    }

    /**
     * Validate an ecotourism import item for the summary view
     * @param array $item Processed ecotourism import item
     * @return array List of error messages (empty = valid)
     */
    public static function validateEcotourismImportItem($item)
    {
        $errors = [];

        if (empty($item['name'])) {
            $errors[] = "ไม่มีชื่อเรื่อง (Required)";
        }
        if (empty($item['description'])) {
            $errors[] = "ไม่มีรายละเอียด";
        }
        if (empty($item['picture_path'])) {
            $errors[] = "ไม่มีรูปภาพปก (Required)";
        }
        if (empty($item['license_id'])) {
            $errors[] = "ไม่พบสัญญาอนุญาต (Required)";
        }

        // Geographical validation
        if (empty($item['province_id']) || empty($item['district_id']) || empty($item['subdistrict_id'])) {
            $errors[] = "ข้อมูลที่ตั้งไม่ครบถ้วน (รหัสจังหวัด/อำเภอ/ตำบล)";
        }

        if (!empty($item['picture_error'])) {
            $errors[] = $item['picture_error'];
        }
        if (!empty($item['license_error'])) {
            $errors[] = $item['license_error'];
        }

        foreach (['illustration_errors', 'image_sources_errors', 'data_sources_errors'] as $errKey) {
            if (!empty($item[$errKey])) {
                foreach ($item[$errKey] as $err) {
                    $errors[] = $err;
                }
            }
        }

        return $errors;
    }

    /**
     * Column mapping for Community Product (ผลิตภัณฑ์ชุมชน) Excel import
     */
    public static function getProductColumnMapping()
    {
        return [
            0 => 'name',
            1 => 'product_category_name',
            2 => 'product_features',
            3 => 'product_price',
            4 => 'product_distribution_location',
            5 => 'product_address',
            6 => 'region',
            7 => 'province',
            8 => 'district',
            9 => 'subdistrict',
            10 => 'zipcode',
            11 => 'product_phone',
            12 => 'coord',
            13 => 'product_main_material',
            14 => 'product_sources_material',
            15 => 'picture_path_label',
            16 => 'image_sources',
            17 => 'data_sources',
            18 => 'found_source',
            19 => 'contact',
            20 => 'illustration_labels',
            21 => 'taxonomy_names',
            22 => 'note',
            23 => 'status',
            24 => 'license_code',
            25 => 'is_hidden',
        ];
    }

    /**
     * Lookup product category by name
     * @param string $name
     * @return array ['product_category_id' => int|null, 'product_category_name' => string, 'product_category_error' => string|null]
     */
    public static function lookupProductCategory($name)
    {
        $result = [
            'product_category_id' => null,
            'product_category_name' => $name,
            'product_category_error' => null,
        ];

        if (empty($name)) {
            $result['product_category_error'] = 'ไม่มีชื่อหมวดหมู่ผลิตภัณฑ์';
            return $result;
        }

        $category = \backend\models\ProductCategory::find()
            ->where(['name' => trim($name)])
            ->andWhere(['active' => 1])
            ->one();

        if ($category) {
            $result['product_category_id'] = $category->id;
        } else {
            $result['product_category_error'] = "ไม่พบหมวดหมู่ผลิตภัณฑ์: " . $name;
        }
        return $result;
    }

    /**
     * Process a single Excel row for Community Product content type
     */
    public static function processProductRow($row, $columnMapping)
    {
        $item = [];
        foreach ($columnMapping as $colIndex => $fieldName) {
            $item[$fieldName] = $row[$colIndex] ?? null;
        }

        // Custom mapping
        $item['status'] = $item['status'] ?? 'pending';
        $item['is_hidden'] = (trim($item['is_hidden'] ?? '') === 'ซ่อน') ? 1 : 0;

        // Product category lookup
        $categoryResult = self::lookupProductCategory($item['product_category_name']);
        $item = array_merge($item, $categoryResult);

        // Parse price
        $price = $item['product_price'] ?? null;
        if ($price !== null && $price !== '') {
            $item['product_price'] = floatval(str_replace(',', '', $price));
        }

        // Process Coordinates and Address
        $coords = self::parseCoordinates($item['coord']);
        $item['latitude'] = $coords['lat'];
        $item['longitude'] = $coords['lng'];

        if (empty($item['province']) && empty($item['district']) && $item['latitude'] && $item['longitude']) {
            $autoAddress = self::autoFillAddress($item['latitude'], $item['longitude']);
            if (!empty($autoAddress)) {
                $item = array_merge($item, $autoAddress);
            }
        }

        // Map Address to IDs
        if (empty($item['province_id']) || empty($item['district_id'])) {
            $addressIds = self::findAddressIds(
                $item['region'], $item['province'], $item['district'], $item['subdistrict'], $item['zipcode']
            );
            $item = array_merge($item, $addressIds);
        }

        // Cover image lookup
        $coverResult = self::lookupCoverImage($item['picture_path_label']);
        $item = array_merge($item, $coverResult);

        // Illustration images lookup
        $illustResult = self::lookupIllustrations($item['illustration_labels']);
        $item = array_merge($item, $illustResult);

        // Image sources
        $imgSources = self::parseSourceEntries($item['image_sources'], 'แหล่งที่มารูปภาพ');
        $item['image_sources_data'] = $imgSources['data'];
        $item['image_sources_errors'] = $imgSources['errors'];

        // Data sources
        $dataSources = self::parseSourceEntries($item['data_sources'], 'แหล่งอ้างอิงข้อมูล');
        $item['data_sources_data'] = $dataSources['data'];
        $item['data_sources_errors'] = $dataSources['errors'];

        // License lookup
        $licenseResult = self::lookupLicense($item['license_code']);
        $item = array_merge($item, $licenseResult);

        // Fallback status
        $validStatuses = ['pending', 'approved', 'rejected'];
        $status = strtolower($item['status'] ?? '');
        $item['status'] = in_array($status, $validStatuses) ? $status : 'pending';

        return $item;
    }

    /**
     * Validate a community product import item
     */
    public static function validateProductImportItem($item)
    {
        $errors = [];

        if (empty($item['name'])) {
            $errors[] = "ไม่มีชื่อเรื่อง (Required)";
        }
        if (empty($item['product_category_id'])) {
            $errors[] = "ไม่มีหมวดหมู่ผลิตภัณฑ์ (Required)";
        }
        if (empty($item['picture_path'])) {
            $errors[] = "ไม่มีรูปภาพปก (Required)";
        }
        if (empty($item['license_id'])) {
            $errors[] = "ไม่พบสัญญาอนุญาต (Required)";
        }

        if (!empty($item['product_category_error'])) {
            $errors[] = $item['product_category_error'];
        }
        if (!empty($item['picture_error'])) {
            $errors[] = $item['picture_error'];
        }
        if (!empty($item['license_error'])) {
            $errors[] = $item['license_error'];
        }

        foreach (['illustration_errors', 'image_sources_errors', 'data_sources_errors'] as $errKey) {
            if (!empty($item[$errKey])) {
                foreach ($item[$errKey] as $err) {
                    $errors[] = $err;
                }
            }
        }

        return $errors;
    }

    /**
     * Parse Thai/Buddhist Era date string to SQL date format
     * Supports formats: dd-mm-yyyy, dd/mm/yyyy (where yyyy can be Buddhist Era)
     * @param string $dateString
     * @return string|null SQL date (Y-m-d) or null
     */
    public static function parseThaiDate($dateString)
    {
        if (empty($dateString)) {
            return null;
        }

        // Try dash separator first
        $dateParts = explode('-', $dateString);
        if (count($dateParts) !== 3) {
            // Try slash separator
            $dateParts = explode('/', $dateString);
        }

        if (count($dateParts) === 3) {
            $d = str_pad($dateParts[0], 2, '0', STR_PAD_LEFT);
            $m = str_pad($dateParts[1], 2, '0', STR_PAD_LEFT);
            $y = (int)$dateParts[2];
            // Convert Buddhist Era to CE
            if ($y > 2400) {
                $y -= 543;
            }
            return "{$y}-{$m}-{$d}";
        }

        return null;
    }

    /**
     * Parse source entries string (image sources or data sources)
     * Format: "sourceName,author,date,url; sourceName2,author2,date2,url2"
     * @param string $sourcesString
     * @param string $errorLabel Label for error messages (e.g. "แหล่งที่มารูปภาพ" or "แหล่งอ้างอิงข้อมูล")
     * @return array ['data' => [...], 'errors' => [...]]
     */
    public static function parseSourceEntries($sourcesString, $errorLabel)
    {
        $result = ['data' => [], 'errors' => []];
        if (empty($sourcesString)) {
            return $result;
        }

        $sources = explode(';', $sourcesString);
        foreach ($sources as $sourceString) {
            $sourceString = trim($sourceString);
            if (empty($sourceString)) {
                continue;
            }

            $parts = array_map('trim', explode(',', $sourceString));
            $sourceName = $parts[0] ?? '';
            $author = $parts[1] ?? '';
            $publishedDate = $parts[2] ?? '';
            $url = $parts[3] ?? '';

            $sqlDate = self::parseThaiDate($publishedDate);

            if (empty($url)) {
                $result['errors'][] = "{$errorLabel}ต้องมี URL อ้างอิง (พบ: '{$sourceString}')";
            } else {
                $result['data'][] = [
                    'source_name' => $sourceName,
                    'author' => $author,
                    'published_date' => $sqlDate,
                    'reference_url' => $url,
                ];
            }
        }

        return $result;
    }

    /**
     * Lookup FileCenter for cover image by label
     * @param string $label
     * @return array ['picture_path' => ..., 'picture_error' => ...]
     */
    public static function lookupCoverImage($label)
    {
        if (empty($label)) {
            return ['picture_path' => null];
        }

        $fileCenter = \common\models\FileCenter::find()->where(['label' => trim($label)])->one();
        if ($fileCenter) {
            return ['picture_path' => $fileCenter->file_path];
        }

        return [
            'picture_path' => null,
            'picture_error' => "ไม่พบรูปภาพปกจากป้ายกำกับ '{$label}'",
        ];
    }

    /**
     * Lookup FileCenter for illustration images by labels string
     * @param string $labelsString comma or semicolon separated
     * @return array ['files' => [...], 'illustration_errors' => [...]]
     */
    public static function lookupIllustrations($labelsString)
    {
        $result = ['files' => [], 'illustration_errors' => []];
        if (empty($labelsString)) {
            return $result;
        }

        $labels = preg_split('/[,;]+/', $labelsString);
        foreach ($labels as $label) {
            $trimLabel = trim($label);
            if (empty($trimLabel)) {
                continue;
            }

            $fcPic = \common\models\FileCenter::find()->where(['label' => $trimLabel])->one();
            if ($fcPic) {
                $result['files'][] = $fcPic->file_path;
            } else {
                $result['illustration_errors'][] = "ไม่พบรูปภาพประกอบ '{$trimLabel}'";
            }
        }

        return $result;
    }

    /**
     * Lookup License by code
     * @param string $code
     * @return array ['license_id' => ..., 'license_name' => ..., 'license_description' => ..., 'license_error' => ...]
     */
    public static function lookupLicense($code)
    {
        $result = [
            'license_id' => null,
            'license_name' => null,
            'license_description' => null,
            'license_error' => null,
        ];

        $trimCode = trim($code ?? '');
        if (empty($trimCode) || $trimCode === '-') {
            return $result;
        }

        $license = \backend\models\License::find()->where(['code' => $trimCode])->one();
        if ($license) {
            $result['license_id'] = $license->id;
            $result['license_name'] = $license->name;
            $result['license_description'] = $license->description;
        } else {
            $result['license_error'] = "ไม่พบสัญญาอนุญาตจากรหัส '{$trimCode}'";
        }

        return $result;
    }

    /**
     * Process a single Excel row into a structured import item
     * @param array $row Raw Excel row data
     * @param array $columnMapping
     * @return array Processed item with all lookups done
     */
    public static function processExcelRow($row, $columnMapping)
    {
        $item = [];
        foreach ($columnMapping as $colIndex => $fieldName) {
            $item[$fieldName] = $row[$colIndex] ?? null;
        }

        // Custom mapping formats
        $item['status'] = $item['status'] ?? 'pending';
        $item['is_hidden'] = (trim($item['is_hidden'] ?? '') === 'ซ่อน') ? 1 : 0;

        // Process Coordinates and Address
        $coords = self::parseCoordinates($item['coord']);
        $item['latitude'] = $coords['lat'];
        $item['longitude'] = $coords['lng'];

        if (empty($item['province']) && empty($item['district']) && $item['latitude'] && $item['longitude']) {
            $autoAddress = self::autoFillAddress($item['latitude'], $item['longitude']);
            if (!empty($autoAddress)) {
                $item = array_merge($item, $autoAddress);
            }
        }

        // Map Address to IDs
        if (empty($item['province_id']) || empty($item['district_id'])) {
            $addressIds = self::findAddressIds(
                $item['region'], $item['province'], $item['district'], $item['subdistrict'], $item['zipcode']
            );
            $item = array_merge($item, $addressIds);
        }

        // Cover image lookup
        $coverResult = self::lookupCoverImage($item['picture_path_label']);
        $item = array_merge($item, $coverResult);

        // Illustration images lookup
        $illustResult = self::lookupIllustrations($item['illustration_labels']);
        $item = array_merge($item, $illustResult);

        // Image sources
        $imgSources = self::parseSourceEntries($item['image_sources'], 'แหล่งที่มารูปภาพ');
        $item['image_sources_data'] = $imgSources['data'];
        $item['image_sources_errors'] = $imgSources['errors'];

        // Data sources
        $dataSources = self::parseSourceEntries($item['data_sources'], 'แหล่งอ้างอิงข้อมูล');
        $item['data_sources_data'] = $dataSources['data'];
        $item['data_sources_errors'] = $dataSources['errors'];

        // License lookup
        $licenseResult = self::lookupLicense($item['license_code']);
        $item = array_merge($item, $licenseResult);

        // Fallback status
        $validStatuses = ['pending', 'approved', 'rejected'];
        $status = strtolower($item['status'] ?? '');
        $item['status'] = in_array($status, $validStatuses) ? $status : 'pending';

        return $item;
    }

    /**
     * Parse an Excel file and return structured import data
     * @param \backend\models\ContentImportForm $model
     * @param array|null $columnMapping Custom column mapping (null = default)
     * @param callable|null $rowProcessor Custom row processor (null = processExcelRow)
     * @return array|null
     */
    public static function parseExcelFile($model, $columnMapping = null, $rowProcessor = null)
    {
        $filePath = \Yii::getAlias('@runtime/uploads/') . 'import_' . time() . '.' . $model->importFile->extension;
        \yii\helpers\FileHelper::createDirectory(dirname($filePath));
        $model->importFile->saveAs($filePath);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            $mapping = $columnMapping ?? self::getDefaultColumnMapping();
            $processor = $rowProcessor ?? [self::class, 'processExcelRow'];
            $importData = [];

            // Start from Row 3 (Index 2) up to 22 (Max 20 rows from row 3)
            for ($i = 2; $i < min(count($rows), 22); $i++) {
                $row = $rows[$i];
                if (empty($row[0])) {
                    continue; // Skip empty first column
                }

                $importData[] = call_user_func($processor, $row, $mapping);
            }

            unlink($filePath); // Delete temp file
            return $importData;
        } catch (\Exception $e) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            \Yii::$app->session->setFlash('error', 'Error parsing file: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Copy a file from FileCenter uploads to destination folder
     * @param string $srcPath Source path (relative, e.g. /uploads/filecenter/xxx.jpg)
     * @param string $destFolder Destination folder name (e.g. 'content-plant')
     * @return string|null New filename or null if not a filecenter path
     * @throws \Exception if copy fails
     */
    public static function copyFileCenterImage($srcPath, $destFolder)
    {
        if (strpos($srcPath, '/uploads/filecenter/') === false && strpos($srcPath, '/uploads/') === false) {
            return null; // Not a filecenter path, return null to use as-is
        }

        $sourcePath = \Yii::getAlias('@frontend/web') . $srcPath;
        if (!file_exists($sourcePath)) {
            return null;
        }

        $ext = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $newName = 'pic_' . uniqid() . time() . '.' . $ext;
        $destDir = \Yii::getAlias('@frontend/web/files/' . $destFolder);
        \yii\helpers\FileHelper::createDirectory($destDir);

        if (!copy($sourcePath, $destDir . '/' . $newName)) {
            throw new \Exception("ไฟล์คัดลอกไม่สำเร็จ: " . basename($srcPath));
        }

        return $newName;
    }

    /**
     * Save image sources for a content
     * @param int $contentId
     * @param array $sourcesData Array of source data arrays
     * @throws \Exception
     */
    public static function saveImageSources($contentId, $sourcesData)
    {
        if (empty($sourcesData)) {
            return;
        }

        foreach ($sourcesData as $sourceData) {
            $imgSrc = new \backend\models\ContentImageSource();
            $imgSrc->content_id = $contentId;
            $imgSrc->source_name = $sourceData['source_name'];
            $imgSrc->author = $sourceData['author'];
            $imgSrc->published_date = $sourceData['published_date'];
            $imgSrc->reference_url = $sourceData['reference_url'];

            if (!$imgSrc->save(false)) {
                throw new \Exception("ไม่สามารถบันทึกข้อมูลแหล่งที่มารูปภาพได้");
            }
        }
    }

    /**
     * Save data sources for a content
     * @param int $contentId
     * @param array $sourcesData Array of source data arrays
     * @throws \Exception
     */
    public static function saveDataSources($contentId, $sourcesData)
    {
        if (empty($sourcesData)) {
            return;
        }

        foreach ($sourcesData as $sourceData) {
            $dataSrc = new \backend\models\ContentDataSource();
            $dataSrc->content_id = $contentId;
            $dataSrc->source_name = $sourceData['source_name'];
            $dataSrc->author = $sourceData['author'];
            $dataSrc->published_date = $sourceData['published_date'];
            $dataSrc->reference_url = $sourceData['reference_url'];

            if (!$dataSrc->save(false)) {
                throw new \Exception("ไม่สามารถบันทึกข้อมูลแหล่งอ้างอิงข้อมูลได้");
            }
        }
    }

    /**
     * Save gallery images from FileCenter for a content
     * @param int $contentId
     * @param array $files Array of file paths
     * @param string $destFolder Destination folder name
     * @param callable $savePictureCallback Callback to save picture record: function($contentId, $value)
     * @throws \Exception
     */
    public static function saveGalleryImages($contentId, $files, $destFolder, $savePictureCallback)
    {
        if (empty($files)) {
            return;
        }

        foreach ($files as $fcPath) {
            $newName = self::copyFileCenterImage($fcPath, $destFolder);
            if ($newName !== null) {
                $value = [
                    'file_display_name' => basename($fcPath),
                    'file_key' => $newName,
                    'created_by_user_id' => \Yii::$app->user->identity->id,
                ];
                $result = call_user_func($savePictureCallback, $contentId, $value);
                if ($result === false) {
                    throw new \Exception("ไฟล์รูปประกอบ " . basename($fcPath) . " บันทึกไม่สำเร็จ");
                }
            }
        }
    }

    /**
     * Save taxonomy tags for a content
     * @param int $contentId
     * @param string $taxonomyNames Comma-separated tag names
     * @param callable $getTaxIdCallback Callback to get/create taxonomy ID: function($tagName) => int
     */
    public static function saveTaxonomyTags($contentId, $taxonomyNames, $getTaxIdCallback)
    {
        if (empty($taxonomyNames)) {
            return;
        }

        $tags = explode(',', $taxonomyNames);
        foreach ($tags as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) {
                continue;
            }

            $taxId = call_user_func($getTaxIdCallback, $tagName);
            if (!empty($taxId)) {
                $duplicate = (new \yii\db\Query())
                    ->select(['content_id', 'taxonomy_id'])
                    ->from('content_taxonomy')
                    ->where(['content_id' => $contentId])
                    ->andWhere(['taxonomy_id' => $taxId])
                    ->all();

                if (empty($duplicate)) {
                    $modelTax = new \backend\models\ContentTaxonomy();
                    $modelTax->content_id = $contentId;
                    $modelTax->taxonomy_id = $taxId;
                    $modelTax->created_at = date('Y-m-d H:i:s');
                    $modelTax->save(false);
                }
            }
        }
    }

    /**
     * Save a full content record from import data (Content + related data)
     * The type-specific model (plant/animal) is saved via callback.
     *
     * @param array $item Processed import item
     * @param array $config Configuration:
     *   - 'type_id' => int (1=plant, 2=animal)
     *   - 'folder' => string (e.g. 'content-plant')
     *   - 'saveTypeSpecific' => callable($contentId, $item) => bool
     *   - 'savePicture' => callable($contentId, $value) => bool
     *   - 'getTaxonomyInputData' => callable($tagName) => int
     * @return \backend\models\Content The saved content model
     * @throws \Exception on save failure
     */
    public static function saveContentFromImport($item, $config)
    {
        $content = new \backend\models\Content();
        $content->type_id = $config['type_id'];
        $content->name = $item['name'];
        $content->latitude = (string)$item['latitude'];
        $content->longitude = (string)$item['longitude'];
        // Ensure all address fields are populated (auto-fill from coordinates if needed)
        $item = self::ensureAddressFields($item);
        $content->region_id = $item['region_id'];
        $content->province_id = $item['province_id'];
        $content->district_id = $item['district_id'];
        $content->subdistrict_id = $item['subdistrict_id'];
        $content->zipcode_id = $item['zipcode_id'];
        $content->status = $item['status'];
        $content->note = $item['note'];
        $content->is_hidden = (string)($item['is_hidden'] ?? 0);
        $content->license_id = $item['license_id'] ?? null;

        // Copy Cover Image
        $content->picture_path = null;
        if (!empty($item['picture_path'])) {
            $newName = self::copyFileCenterImage($item['picture_path'], $config['folder']);
            $content->picture_path = ($newName !== null) ? $newName : $item['picture_path'];
        }

        $content->created_by_user_id = \Yii::$app->user->identity->id;
        $content->updated_by_user_id = \Yii::$app->user->identity->id;
        $content->created_at = date('Y-m-d H:i:s');
        $content->updated_at = date('Y-m-d H:i:s');
        $content->active = 1;

        if (!$content->save()) {
            throw new \Exception('Failed to save content: ' . json_encode($content->errors));
        }

        // Save type-specific model via callback
        $typeResult = call_user_func($config['saveTypeSpecific'], $content->id, $item);
        if ($typeResult === false) {
            throw new \Exception('Failed to save type-specific info');
        }

        // Save gallery images
        self::saveGalleryImages($content->id, $item['files'] ?? [], $config['folder'], $config['savePicture']);

        // Save image sources
        self::saveImageSources($content->id, $item['image_sources_data'] ?? []);

        // Save data sources
        self::saveDataSources($content->id, $item['data_sources_data'] ?? []);

        // Save taxonomy tags
        self::saveTaxonomyTags($content->id, $item['taxonomy_names'] ?? '', $config['getTaxonomyInputData']);

        return $content;
    }

    /**
     * Run the full import confirm flow: transaction, save all items, cleanup session
     *
     * @param array $data Import data from session
     * @param array $config Same config as saveContentFromImport + 'sessionKey'
     * @return array ['success' => bool, 'count' => int]
     */
    public static function confirmImport($data, $config)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            foreach ($data as $item) {
                self::saveContentFromImport($item, $config);
            }
            $transaction->commit();
            \Yii::$app->session->remove($config['sessionKey']);
            \Yii::$app->session->setFlash('success', 'นำเข้าข้อมูลสำเร็จ ' . count($data) . ' รายการ');
            return ['success' => true, 'count' => count($data)];
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::$app->session->setFlash('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
            return ['success' => false, 'count' => 0];
        }
    }

    /**
     * Validate an import item for the summary view
     * @param array $item Processed import item
     * @return array List of error messages (empty = valid)
     */
    public static function validateImportItem($item)
    {
        $errors = [];

        if (empty($item['name'])) {
            $errors[] = "ไม่มีชื่อเรื่อง (Required)";
        }
        if (empty($item['characteristics'])) {
            $errors[] = "ไม่มีลักษณะ/คุณสมบัติ (Required)";
        }
        if (empty($item['scientific_name'])) {
            $errors[] = "ไม่มีชื่อวิทยาศาสตร์ (Required)";
        }
        if (empty($item['picture_path'])) {
            $errors[] = "ไม่มีรูปภาพปก (Required)";
        }
        if (empty($item['license_id'])) {
            $errors[] = "ไม่พบสัญญาอนุญาต (Required)";
        }

        // Geographical validation
        if (empty($item['province_id']) || empty($item['district_id']) || empty($item['subdistrict_id'])) {
            $errors[] = "ข้อมูลที่ตั้งไม่ครบถ้วน (รหัสจังหวัด/อำเภอ/ตำบล)";
        }
        if (empty($item['latitude']) || empty($item['longitude'])) {
            $errors[] = "พิกัดไม่ถูกต้อง";
        }

        if (!empty($item['picture_error'])) {
            $errors[] = $item['picture_error'];
        }
        if (!empty($item['license_error'])) {
            $errors[] = $item['license_error'];
        }

        foreach (['illustration_errors', 'image_sources_errors', 'data_sources_errors'] as $errKey) {
            if (!empty($item[$errKey])) {
                foreach ($item[$errKey] as $err) {
                    $errors[] = $err;
                }
            }
        }

        return $errors;
    }

    /**
     * Validate an expert import item for the summary view
     * @param array $item Processed expert import item
     * @return array List of error messages (empty = valid)
     */
    public static function validateExpertImportItem($item)
    {
        $errors = [];

        if (empty($item['name'])) {
            $errors[] = "ไม่มีชื่อเรื่อง (Required)";
        }
        if (empty($item['expert_category_id'])) {
            $errors[] = "ไม่มีหมวดหมู่ภูมิปัญญา/ปราชญ์ (Required)";
        }
        if (empty($item['expert_firstname'])) {
            $errors[] = "ไม่มีชื่อ ผู้รู้/ปราชญ์";
        }
        if (empty($item['picture_path'])) {
            $errors[] = "ไม่มีรูปภาพปก (Required)";
        }
        if (empty($item['license_id'])) {
            $errors[] = "ไม่พบสัญญาอนุญาต (Required)";
        }

        // Geographical validation
        if (empty($item['province_id']) || empty($item['district_id']) || empty($item['subdistrict_id'])) {
            $errors[] = "ข้อมูลที่ตั้งไม่ครบถ้วน (รหัสจังหวัด/อำเภอ/ตำบล)";
        }

        if (!empty($item['picture_error'])) {
            $errors[] = $item['picture_error'];
        }
        if (!empty($item['license_error'])) {
            $errors[] = $item['license_error'];
        }
        if (!empty($item['expert_category_error'])) {
            $errors[] = $item['expert_category_error'];
        }

        foreach (['illustration_errors', 'image_sources_errors', 'data_sources_errors'] as $errKey) {
            if (!empty($item[$errKey])) {
                foreach ($item[$errKey] as $err) {
                    $errors[] = $err;
                }
            }
        }

        return $errors;
    }
}
