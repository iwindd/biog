<?php

namespace frontend\controllers\api;

use Yii;
use yii\web\Response;
use yii\web\Controller;
use common\components\_;
use yii\helpers\ArrayHelper;
use frontend\components\LocationHelper;

class LocationController extends Controller
{
    private function sendDataOutput($type)
    {
        _::setResponseAsJson();
        $POST = _::post();
        $depdropId = ArrayHelper::getValue($POST, 'depdrop_parents.0');

        $output = [];

        if (_::issetNotEmpty($depdropId)) {
            switch ($type) {
                case 'province':
                    $output = LocationHelper::getProvinceByRegionId($depdropId);
                    break;
                case 'district':
                    $output = LocationHelper::getDistrictByProvinceId($depdropId);
                    break;
                case 'subdistrict':
                    $output = LocationHelper::getSubdistrictByDistrictId($depdropId);
                    break;
                case 'zipcode':
                    $output = LocationHelper::getZipcodeBySubdistrictId($depdropId);
                    break;

                default:
                    $output = [];
                    break;
            }

            return [
                'output' => $output,
                'selected' => ''
            ];
        }

        return [
            'output' => '',
            'selected' => ''
        ];
    }

    public function actionGetProvinceList()
    {
        return $this->sendDataOutput('province');
    }


    public function actionGetDistrictList()
    {
        return $this->sendDataOutput('district');
    }

    public function actionGetSubdistrictList()
    {
        return $this->sendDataOutput('subdistrict');
    }

    public function actionGetZipcodeList()
    {
        return $this->sendDataOutput('zipcode');
    }
}
