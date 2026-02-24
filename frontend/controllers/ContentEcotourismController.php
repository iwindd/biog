<?php

namespace frontend\controllers;
use Yii;
use yii\web\Controller;
use yii\data\Pagination;
use frontend\models\Content;
use frontend\models\content\ContentEcotourism;
use frontend\models\Comment;
use frontend\models\ContentStatistics;
use frontend\models\content\Picture;
use common\components\Helper;
use frontend\components\FrontendHelper;

class ContentEcotourismController extends Controller
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

        $query = Content::find()->select(['content.id', 'name', 'description', 'content.picture_path', 'content.created_by_user_id', 'content.created_at', 'content.updated_at'])->where(['type_id' => 5, 'active' => 1, 'status' => 'approved']);
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize'=>$limit]);
        $ecotourism = $query->limit($limit)->offset($offset)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();

        return $this->render('index', [
            'ecotourism' => $ecotourism,
            'pagination' => $pagination
        ]);
    }

    public function actionView($id) {

        $latestContentId = Helper::getEventIDActive($id);
        if($id != $latestContentId){
            return $this->redirect(['/content-ecotourism/'.$latestContentId]);
        }

        $ecotourism = Content::find()->where(['id' => $id])->asArray()->one();
        if($ecotourism["type_id"] != 5) {
            throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
        }
        //check can view
        if($ecotourism["status"] == 'pending' || $ecotourism["status"] == 'rejected'){
            if(empty(Yii::$app->user->identity->id)){
                throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
            }else if(Yii::$app->user->identity->id == $ecotourism["created_by_user_id"]){
                //ok
            }else{
                $teacher = FrontendHelper::checkCanViewContentForTeacher($ecotourism->created_by_user_id);
                if($teacher == false){
                    throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
                }
            }
        }

        $content_ecotourism = ContentEcotourism::find()->where(['content_id' => $id])->one();
        if($ecotourism["content_root_id"] != 0){
            $contentComment = Comment::find()->select(['id', 'user_id', 'created_at', 'message'])->where(['content_root_id' => $ecotourism["content_root_id"]])->orderBy(['created_at' => SORT_DESC])->asArray()->all();
        }else{
            $contentComment = Comment::find()->seelct(['id', 'user_id', 'created_at', 'message'])->where(['content_root_id' => $id])->orderBy(['created_at' => SORT_DESC])->asArray()->all();
        }
        $other_content_ecotourism = Content::find()->select(['id', 'name', 'description', 'picture_path', 'created_by_user_id', 'created_at'])->where(['type_id' => 5,'active' => 1, 'status' => 'approved'])->andWhere(['not in', 'id', $id])->orderBy(['created_at' => SORT_DESC])->limit(3)->asArray()->all();
        $picture = Picture::find()->select(['path'])->where(['content_id' => $id])->asArray()->all();
        if (!empty($id)) {
            $dataContentStatistics = ContentStatistics::find()->where(['content_root_id' => $id])->asArray()->one();

            $session = Yii::$app->session;
            $canUpViewPage = false;
            if (empty($session['views_content'])) {
                $session['views_content'] = [
                    'content_id' => $id,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ];
                $canUpViewPage = true;
            }else if( $_SERVER['REMOTE_ADDR'] != $session['views_content']['ip_address'] || $id != $session['views_content']['content_id']){
                
                $session['views_content'] = [
                    'content_id' => $id,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ];

                $canUpViewPage = true;
            }

            //Count View
            if (!empty($dataContentStatistics)) {
                if ($canUpViewPage == true) {
                    $pageview = $dataContentStatistics['pageview'] + 1;
                    Yii::$app->db->createCommand()
                        ->update('content_statistics', ['pageview' => $pageview], 'content_root_id = ' . $id)
                        ->execute();
                }
            }
            else {
                $count = new ContentStatistics;
                $count->content_root_id = $id;
                $count->pageview = 1;
                $count->updated_at = date("Y-m-d H:i:s");
                $count->save();
            }
        }
        
        return $this->render('view', [
            'ecotourism' => $ecotourism,
            'content_ecotourism' => $content_ecotourism,
            'contentComment' => $contentComment,
            'other_content_ecotourism' => $other_content_ecotourism,
            'picture' => $picture
        ]);
    }

}
