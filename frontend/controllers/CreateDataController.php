<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use frontend\models\user\login\User;

class CreateDataController extends Controller
{
    private $pageList = [
        'plants' => 'พืช',
        'animal' => 'สัตว์',
        'fungi' => 'จุลินทรีย์',
        'expert' => 'ภูมิปัญญา / ปราชญ์',
        'ecotourism' => 'การท่องเที่ยวเชิงนิเวศ',
        'products' => 'ผลิตภัณฑ์ชุมชน',
    ];

    public function actionIndex()
    {
        $page = Yii::$app->request->get('page');

        $userModel = new User();
        return $this->render('/user/mydata/createData', [
            'userModel' => $userModel,
            'page' => $this->switchPage($page),
            'pageList' => $this->pageList
        ]);
    }

    private function switchPage($page)
    {
        // ?page=plants
        $pageList = [
            'plants',
            'animal',
            'fungi',
            'expert',
            'ecotourism',
            'products',
        ];

        if (in_array($page, $pageList)) {
            return $page;
        } else {
            return 'plants';
        }
    }
}
