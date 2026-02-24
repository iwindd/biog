<?php

namespace frontend\controllers;
use yii;
use yii\web\Controller;
use frontend\models\Map;

class MapController extends Controller
{
    public function actionIndex()
    {
        $mapModel = new Map();
        return $this->render('index',['mapModel' => $mapModel]);
    }


}
