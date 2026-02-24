<?php

namespace frontend\controllers;
use yii\web\Controller;
use Yii;
use yii\data\Pagination;
use frontend\models\Knowledge;
use frontend\models\KnowledgeStatistics;
use common\components\Helper;

use backend\models\KnowledgeFile;

class KnowledgeController extends Controller
{
    public function actionIndex()
    {
        $limit = 6;
        $page = 1;
        if(!empty($_GET['page'])) {
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            if(false === $page) {
                $page = 1;
            }
        }
        
        $offset = ($page - 1) * $limit;

        $limitVideo = 6;
        $pageVideo = 1;
        if(!empty($_GET['page-video'])) {
            $pageVideo = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            if(false === $pageVideo) {
                $pageVideo = 1;
            }
        }
        $offsetVideo = ($pageVideo - 1) * $limitVideo;

        $query_infographic = Knowledge::find()->select(['id', 'title', 'description', 'picture_path', 'created_at'])->where(['type' => 'Infographic', 'active'=>1]);
        $count_query_infographic = clone $query_infographic;
        $pagination_infographic = new Pagination(['totalCount' => $count_query_infographic->count(), 'pageSize'=>$limit]);
        $knowledge_infographic = $query_infographic->limit($limit)->offset($offset)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();
        
        $query_videos = Knowledge::find()->select(['id', 'title', 'description', 'picture_path', 'created_at'])->where(['type' => 'Video', 'active'=>1]);
        $count_query_videos = clone $query_videos;
        $pagination_videos = new Pagination(['totalCount' => $count_query_videos->count(), 'pageSize'=>$limitVideo, 'pageParam' => 'page-video']);
        $knowledge_videos = $query_videos->limit($limitVideo)->offset($offsetVideo)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();
        
        return $this->render('index', [
            'knowledge_infographic' => $knowledge_infographic,
            'pagination_infographic' => $pagination_infographic,
            'knowledge_videos' => $knowledge_videos,
            'pagination_videos' => $pagination_videos,
            
        ]);
    }

    public function actionView($id) {

        $latestContentId = Helper::getKnowledgeIDActive($id);

        if($id != $latestContentId){
            return $this->redirect(['/knowledge/'.$latestContentId]);
        }

        $knowledge = Knowledge::find()->select(['title', 'description', 'picture_path'])->where(['active' => 1, 'id' => $latestContentId])->one();

        if (!empty($knowledge)) {
            if ($knowledge->type == 'Infographic') {
                $other_knowledge = Knowledge::find()->select(['id', 'title', 'description', 'path', 'picture_path', 'created_at'])->where(['type' => 'Infographic', 'active' => 1])->andWhere(['not in', 'id', $latestContentId])->asArray()->orderBy(['updated_at' => SORT_DESC])->limit(3)->all();
            } else {
                $other_knowledge = Knowledge::find()->where(['type' => 'Video', 'active' => 1])->andWhere(['not in', 'id', $latestContentId])->asArray()->orderBy(['updated_at' => SORT_DESC])->limit(3)->all();
            }

            $picture = KnowledgeFile::find()->select(['name', 'path'])->where(['knowledge_id' => $latestContentId, 'application_type' => 'image'])->asArray()->all();
            $files = KnowledgeFile::find()->select(['name', 'path'])->where(['knowledge_id' => $latestContentId, 'application_type' => 'file'])->asArray()->all();
            if (!empty($id)) {
                $dataKnowledgeStatistics = KnowledgeStatistics::find()->where(['knowledge_root_id' => $id])->asArray()->one();

                $session = Yii::$app->session;
                $canUpViewPage = false;
                if (empty($session['views_knowledge'])) {
                    $session['views_knowledge'] = [
                        'knowledge_id' => $id,
                        'ip_address' => $_SERVER['REMOTE_ADDR']
                    ];
                    $canUpViewPage = true;
                } elseif ($_SERVER['REMOTE_ADDR'] != $session['views_knowledge']['ip_address'] || $id != $session['views_knowledge']['knowledge_id']) {
                    $session['views_knowledge'] = [
                        'knowledge_id' => $id,
                        'ip_address' => $_SERVER['REMOTE_ADDR']
                    ];

                    $canUpViewPage = true;
                }
                //Count View
                if (!empty($dataKnowledgeStatistics)) {
                    if ($canUpViewPage == true) {
                        $pageview = $dataKnowledgeStatistics['pageview'] + 1;
                        Yii::$app->db->createCommand()
                            ->update('knowledge_statistics', ['pageview' => $pageview], 'knowledge_root_id = ' . $id)
                            ->execute();
                    }
                } else {
                    $count = new KnowledgeStatistics;
                    $count->knowledge_root_id = $id;
                    $count->pageview = 1;
                    $count->updated_at = date("Y-m-d H:i:s");
                    $count->save();
                }
            }

            
            return $this->render('view', [
                'knowledge' => $knowledge,
                'other_knowledge' => $other_knowledge,
                'picture' => $picture,
                'files' => $files
            ]);
        }else{
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }

}
