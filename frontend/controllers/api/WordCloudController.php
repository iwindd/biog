<?php

namespace frontend\controllers\api;

use yii\helpers\Json;
use Yii;
use yii\web\Controller;
use common\components\_;
use frontend\models\content\Taxonomy;
use frontend\models\content\ContentTaxonomy;

class WordCloudController extends Controller
{

    private function getUniqueTaxonomy()
    {
        return Taxonomy::find()->select(['taxonomy.id', 'taxonomy.name', 'content.type_id as content_type_id', 'content.id as content_id', 'content.active'])
            ->leftJoin('content_taxonomy', 'content_taxonomy.taxonomy_id = taxonomy.id')
            ->leftJoin('content', 'content.id = content_taxonomy.content_id')
            ->where(['content.active' => 1, 'content.status' => 'approved'])
            ->asArray()->all();
    }

    private function getWordCloudRelation()
    {

        //$dataCcountmax  = Yii::$app->db->createCommand('SELECT  COUNT(`content_taxonomy`.`id`) AS counted, `content_taxonomy`.`taxonomy_id` as `content_taxonomy-taxonomy_id`, `content_taxonomy`.`content_id`, `taxonomy`.`name` as `taxonomy-name`  FROM `content_taxonomy` LEFT JOIN `taxonomy` ON `taxonomy`.`id` = `content_taxonomy`.`taxonomy_id`  GROUP BY taxonomy_id, content_id ORDER BY `counted`  DESC LIMIT 100')->queryAll();

        //$dataCcountmax = Yii::$app->db->createCommand('SELECT `content_taxonomy`.`taxonomy_id` AS `content_taxonomy-taxonomy_id`, `content_taxonomy`.`content_id`, `taxonomy`.`name` AS `taxonomy-name` FROM `content_taxonomy` LEFT JOIN `content` ON `content_taxonomy`.`content_id` = `content`.`id` LEFT JOIN `taxonomy` ON taxonomy.id = content_taxonomy.taxonomy_id WHERE `active`=1 LIMIT 100 ')->queryAll();

        // print '<pre>';
        // print_r($dataCcountmax);
        // print "</pre>";
        // exit(); 

        return  ContentTaxonomy::find()
            ->select([
                'content_taxonomy.taxonomy_id as content_taxonomy-taxonomy_id',
                'content_taxonomy.content_id',
                'taxonomy.name as taxonomy-name'
            ])
            ->leftJoin('taxonomy', 'taxonomy.id = content_taxonomy.taxonomy_id')
            ->joinWith('contents')
            ->limit(100)
            ->asArray()
            ->all(); 

            // print '<pre>';
            // print_r($data);
            // print "</pre>";
            // exit();    

        //return  Yii::$app->db->createCommand('SELECT `content_taxonomy`.`taxonomy_id` AS `content_taxonomy-taxonomy_id`, `content_taxonomy`.`content_id`, `taxonomy`.`name` AS `taxonomy-name` FROM `content_taxonomy` LEFT JOIN `content` ON `content_taxonomy`.`content_id` = `content`.`id` LEFT JOIN `taxonomy` ON taxonomy.id = content_taxonomy.taxonomy_id WHERE `active`=1')->queryAll();
    
    }


    public function computeWordCloud()
    {

        $wordCloud = [];

        $query = (new \yii\db\Query())
                ->select(['*'])
                ->from('word_cloud_statistics')
                ->limit(100)
                ->all(); 

        if (_::issetNotEmpty($query)) {
            foreach ($query as $key => $word) {
                $wordCloud[] = array( 'text' => $word['keyword'], 'weight' => $word['total'], 'link' => '/search?taxonomy='.$word['keyword']);
            }
        }

        /*

        $wordCloudRelation = $this->getWordCloudRelation();

        if (_::issetNotEmpty($wordCloudRelation)) {
            foreach ($wordCloudRelation as $key => $word) {

                // $word['contents'] = 0;
                // if(!empty($word['content_id'])){
                //     $word['contents'] = Yii::$app->db->createCommand('SELECT COUNT(`content_taxonomy`.`id`) FROM `content_taxonomy` WHERE `content_taxonomy`.`content_id` = :contentID' , ['contentID' => $word['content_id']])->queryScalar();
                // } 

                // print '<pre>';
                // print_r($word);
                // print '</pre>';
                // exit();
                
                $wordCloud[$key]['text'] = $word['taxonomy-name'];
                $wordCloud[$key]['weight'] = count(_::issetNotEmpty($word['contents']) ? $word['contents'] : 0);
                //$wordCloud[$key]['weight'] = $word['contents'];
                $wordCloud[$key]['link'] = '/search?taxonomy='. $word['taxonomy-name'];
            }
        } */

        return $wordCloud;
    }

    public function actionSummary()
    {
        _::setResponseAsJson();

        return $this->computeWordCloud();
    }
}
