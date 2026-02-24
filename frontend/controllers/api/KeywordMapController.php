<?php

namespace frontend\controllers\api;

use yii\helpers\Json;
use yii\web\Controller;
use common\components\_;
use frontend\models\content\Taxonomy;
use frontend\models\content\ContentTaxonomy;

class KeywordMapController extends Controller
{

    private function getUniqueTaxonomy()
    {
        // return Taxonomy::find()->select(['taxonomyid', 'taxonomy.name', 'content.type_id as content_type_id', 'content.id as content_id', 'content.active'])
        //     ->leftJoin('content_taxonomy', 'content_taxonomy.taxonomy_id = taxonomy.id')
        //     ->leftJoin('content', 'content.id = content_taxonomy.content_id')
        //     ->where(['content.active' => 1, 'content.status' => 'approved'])
        //     ->asArray()->all();

        return (new \yii\db\Query())
            ->select(['taxonomy_id as id', 'keyword as name'])
            ->from('keyword_map_statistics')
            ->groupBy(['taxonomy_id'])
            ->all(); 

    
    }

    private function getMapRelation()
    {
        /*
        return ContentTaxonomy::find()
            ->select([
                'content_taxonomy.content_id as content_taxonomy-content_id',
                'content_taxonomy.taxonomy_id as content_taxonomy-taxonomy_id',
                'content.id as content-id',
                'content.type_id as content_type-id',
                'type.id as type-id',
                'type.name as type-name',
                'taxonomy.id as taxonomy-id',
                'taxonomy.name as taxonomy-name'
            ])
            ->leftJoin('content', 'content.id = content_taxonomy.content_id')
            ->leftJoin('type', 'type.id = content.type_id')
            ->leftJoin('taxonomy', 'taxonomy.id = content_taxonomy.taxonomy_id')
            ->where([
                'content.active' => 1,
                'content.status' => 'approved'
            ])
            ->limit(100)
            ->asArray()
            ->all(); */

        return (new \yii\db\Query())
            ->select(['taxonomy_id', 'type as type_id'])
            ->from('keyword_map_statistics')
            ->all(); 
    }

    private function getUniqueType()
    {
        return $types = [
            [
                'id' => 1, 'name' => 'พืช', 'image' => '/images/icon/S_Plant.svg',
            ],
            [
                'id' => 2, 'name' => 'สัตว์', 'image' => '/images/icon/S_Animals.svg',
            ],
            [
                'id' => 3, 'name' => 'จุลินทรีย์', 'image' => '/images/icon/S_Funji.svg',
            ],
            [
                'id' => 4, 'name' => 'ภูมิปัญญา', 'image' => '/images/icon/S_Expert.svg',
            ],
            [
                'id' => 5, 'name' => 'สถานที่ท่องเที่ยวเชิงนิเวศ', 'image' => '/images/icon/S_Ecotourism.svg',
            ],
            [
                'id' => 6, 'name' => 'ผลิตภัณฑ์ชุมชน', 'image' => '/images/icon/S_Product.svg',
            ],
        ];
    }

    public function computeKeywordMap()
    {

        $keywordMap = [
            'nodes' => [],
            'edges' => []
        ];

        $types = $this->getUniqueType();
        $taxonomys = $this->getUniqueTaxonomy();
        $mapRelation = $this->getMapRelation();


        foreach ($types as $type) {
            $keywordMap['nodes'][] = [
                'id' => 'type_' . $type['id'],
                'label' => $type['name'],
                'shape' => 'circularImage',
                'image' => $type['image'],
                'group' => 'type_' . $type['id'],
            ];
        }


        foreach ($taxonomys as $key => $taxonomy) {
            $keywordMap['nodes'][] = [
                'id' => 'taxonomy_' . $taxonomy['id'],
                'label' => $taxonomy['name'],
                'shape' => 'box',
                'group' => 'taxonomy_' . $taxonomy['id'],
            ];
        }


        foreach ($mapRelation as $map) {

            $keywordMap['edges'][] = [
                'from' => 'taxonomy_' . $map['taxonomy_id'],
                'to' => 'type_' . $map['type_id'],
            ];
        }


        return $keywordMap;
    }

    public function actionSummary()
    {
        _::setResponseAsJson();
        //return $this->computeKeywordMap();
        return _::toJsonString($this->computeKeywordMap());
    }
}
