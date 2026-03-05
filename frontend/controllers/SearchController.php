<?php

namespace frontend\controllers;
use frontend\models\Content;
use frontend\models\Knowledge;
use yii\data\Pagination;

class SearchController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $limit = 9;
        $page = 1;

        $limit2 = 6;
        $page_knowledge = 1;

        if(!empty($_GET['page'])) {
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            if(false === $page) {
                $page = 1;
            }
        }
        $offset = ($page - 1) * $limit;


        if(!empty($_GET['page-knowledge'])) {
            $page_knowledge = filter_input(INPUT_GET, 'page-knowledge', FILTER_VALIDATE_INT);
            if(false === $page_knowledge) {
                $page_knowledge = 1;
            }
        }
        $offset2 = ($page_knowledge - 1) * $limit2;

        $query = Content::find()
            ->leftJoin('content_type', 'content_type.id = content.type_id')
            ->where([
                'content.active' => 1,
                'content.status' => 'approved',
                'content.is_hidden' => false,
                'content_type.is_visible' => 1,
            ]);

        $query2 = Knowledge::find()->where(['active' =>1]);

        if(!empty($_GET['keyword'])){
            $keyword = $_GET['keyword'];
            $query->andWhere('MATCH(content.name) AGAINST(:keyword IN BOOLEAN MODE)', [':keyword' => '*' . $keyword . '*']);
            $query2->andWhere(['like', 'title', $keyword]);
        }

        if(!empty($_GET['region_id'])){
            $query->andWhere(['=', 'content.region_id', $_GET['region_id']]);
        }

        if(!empty($_GET['province_id'])){
            $query->andWhere(['=', 'content.province_id', $_GET['province_id']]);
        }

        if(!empty($_GET['district_id'])){
            $query->andWhere(['=', 'content.district_id', $_GET['district_id']]);
        }

        if(!empty($_GET['subdistrict_id'])){
            $query->andWhere(['=', 'content.subdistrict_id', $_GET['subdistrict_id']]);
        }

        if(!empty($_GET['taxonomy'])){
            $query->leftJoin('content_taxonomy', 'content_taxonomy.content_id = content.id');
            $query->leftJoin('taxonomy', 'taxonomy.id = content_taxonomy.taxonomy_id');
            

            $query->andWhere(['like', 'taxonomy.name', $_GET['taxonomy']]);
        }

        $queryType = array();

        if(!empty($_GET['content_plant'])){
            $queryType[] = 1;
        }

        if(!empty($_GET['content_animal'])){
            $queryType[] = 2;
        }

        if(!empty($_GET['content_fungi'])){
            $queryType[] = 3;
        }

        if(!empty($_GET['content_expert'])){
            $queryType[] = 4;
        }

        if(!empty($_GET['content_ecotourism'])){
            $queryType[] = 5;
        }

        if(!empty($_GET['content_product'])){
            $queryType[] = 6;
        }


        if(empty($_GET['content_knowledge']) && empty($_GET['keyword'])){
            $query2 = Knowledge::find()->where(['id' => 0]);
        }

        if(!empty($queryType)){
            $query->andWhere(['in', 'content.type_id', $queryType]);  
        }
        //content
        $countQuery = clone $query;
        $contentCount = $countQuery->count();
        $pagination = new Pagination(['totalCount' => $contentCount, 'pageSize'=>$limit]);
        $search = $query->limit($limit)->offset($offset)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();

        //knowledge
        $countQuery2 = clone $query2;
        $knowledgeCount = $countQuery2->count();
        $paginationKnowledge = new Pagination(['totalCount' => $knowledgeCount, 'pageSize'=>$limit2, 'pageParam' => 'page-knowledge']);
        $knowledge = $query2->limit($limit2)->offset($offset2)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();

        $totalCount = $contentCount + $knowledgeCount;

        return $this->render('index', [
            'search' => $search,
            'pagination' => $pagination,
            'knowledge' => $knowledge,
            'paginationKnowledge' => $paginationKnowledge,
            'totalCount' => $totalCount
        ]);
    }

}
