<?php

namespace frontend\components;

use common\components\_;
use frontend\models\location\Region;
use frontend\models\location\Zipcode;
use frontend\models\location\District;
use frontend\models\location\Province;
use frontend\models\location\Subdistrict;

class LocationHelper
{
    // $regionModel = new Region();
    //     $proviceModel = new Province();
    //     $districtModel = new District();
    //     $subdistrctModel = new Subdistrict();
    //     $zipcodeModel = new Zipcode();

    public static function getRegionList()
    {
        return Region::find()->select(['id', 'name_th'])->all();
    }

    public static function getProvinceByRegionId($regionId)
    {
        $provinceModel = new Province();

        $provinceList = $provinceModel->find()
            ->select([
                'id',
                'name_th as name'
            ])
            ->where([
                'region_id' => $regionId
            ])->asArray()->all();

        return $provinceList;
    }

    public static function getDistrictByProvinceId($provinceId)
    {
        $districtModel = new District();

        $districtList = $districtModel->find()
            ->select([
                'id',
                'name_th as name'
            ])
            ->where([
                'province_id' => $provinceId
            ])->asArray()->all();

        return $districtList;
    }

    public static function getSubdistrictByDistrictId($districtId)
    {
        $subdistrictModel = new Subdistrict();

        $subdistrictList = $subdistrictModel->find()
            ->select([
                'id',
                'name_th as name'
            ])
            ->where([
                'district_id' => $districtId
            ])->asArray()->all();

        return $subdistrictList;
    }

    public static function getZipcodeBySubdistrictId($subdistrictId)
    {
        $zipcodeModel = new Zipcode();

        $zipcodeList = $zipcodeModel->find()
            ->select([
                'id',
                'zipcode as name'
            ])
            ->where([
                'subdistrict_id' => $subdistrictId
            ])->asArray()->all();

        return $zipcodeList;
    }
}
