<?php

namespace frontend\controllers;
use Yii;
use yii\web\Controller;
use yii\data\Pagination;
use frontend\models\Content;
//use frontend\models\ContentPlant;
use frontend\models\content\ContentPlant;
use frontend\models\ContentStatistics;
use frontend\models\Comment;
use frontend\models\content\Picture;
use common\components\Helper;
use frontend\components\FrontendHelper;


class ContentPlantController extends Controller
{
    public function actionIndex() {
        $limit = 6;
        $page = 1;
        if(!empty($_GET['page'])) {
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            if(false === $page) {
                $page = 1;
            }
        }
        $offset = ($page - 1) * $limit;
        //$query = Content::find()->where(['type_id' => 1, 'active' => 1]);

        $query = Content::find()->select(['content.id', 'name', 'description', 'content.picture_path', 'content.created_by_user_id', 'content.created_at', 'content.updated_at']);
        //$query->leftJoin('content_plant','content_plant.content_id = content.id');
        $query->where(['content.active'=>1, 'content.type_id' => 1, 'content.status' => 'approved']);

        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize'=>$limit]);
        $plant = $query->limit($limit)->offset($offset)->asArray()->orderBy(['content.updated_at' => SORT_DESC])->all();

        return $this->render('index', [
            'plant' => $plant,
            'pagination' => $pagination
        ]);
        
    }

    public function actionView($id) {
        $latestContentId = Helper::getEventIDActive($id);
        if($id != $latestContentId){
            return $this->redirect(['/content-plant/'.$latestContentId]);
        }

        $content = Content::find()
        ->select(['id', 'name', 'description', 'picture_path', 'type_id', 'photo_credit', 'source_information','province_id', 'district_id', 'subdistrict_id', 'zipcode_id', 'latitude', 'longitude', 'status', 'content_root_id', 'created_by_user_id', 'created_at'])
        ->where(['id' => $id])->asArray()->one();
        
        if($content["type_id"] != 1) {
            throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
        }

        //check can view
        if($content["status"] == 'pending' || $content["status"] == 'rejected'){
            if(empty(Yii::$app->user->identity->id)){
                throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
            }else if(Yii::$app->user->identity->id == $content['created_by_user_id']){
                //ok
            }else{
                $teacher = FrontendHelper::checkCanViewContentForTeacher($content['created_by_user_id']);
                if($teacher == false){
                    throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
                }
            }
        }

        $content_plant = ContentPlant::find()->where(['content_id' => $id])->one();
        if($content["content_root_id"] != 0){
            $contentComment = Comment::find()
            ->select(['id', 'user_id', 'created_at', 'message'])
            ->where(['content_root_id' => $content["content_root_id"]])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->all();
        }else{
            $contentComment = Comment::find()
            ->select(['id', 'user_id', 'created_at', 'message'])
            ->where(['content_root_id' => $id])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->all();
        }
        
        $other_content_plant = Content::find()
            ->select(['id', 'name', 'description', 'picture_path', 'created_by_user_id', 'created_at'])
            ->where(['type_id' => 1, 'active' => 1, 'status' => 'approved'])
            ->andWhere(['not in', 'id', $id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(3)
            ->asArray()
            ->all();
        $picture = Picture::find()->where(['content_id' => $id])->asArray()->all();

        if (!empty($id)) {
            $dataContentStatistics = ContentStatistics::find()
            ->select(['pageview'])
            ->where(['content_root_id' => $id])
            ->asArray()
            ->one();

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
            'content' => $content,
            'content_plant' => $content_plant,
            'contentComment' => $contentComment,
            'other_content_plant' => $other_content_plant,
            'picture' => $picture
        ]);
    }

}
