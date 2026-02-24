<?php

namespace backend\controllers;
use yii\db\Query;
use backend\models\Taxonomy;

class TaxonomyController extends \yii\web\Controller
{
    public function actionTaxonomy($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = new Query;
            $query->select('id, name AS text')
                ->from('taxonomy')
                ->where(['like', 'name', $q])
                ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }
        elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Taxonomy::find($id)->name];
        }
        return $out;
    }

}
