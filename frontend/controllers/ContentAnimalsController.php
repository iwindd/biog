<?php

namespace frontend\controllers;
use Yii;
use yii\web\Controller;
use yii\data\Pagination;
use frontend\models\Content;
use frontend\models\content\ContentAnimal;
use frontend\models\Comment;
use frontend\models\ContentStatistics;
use frontend\models\content\Picture;
use common\components\Helper;
use frontend\components\FrontendHelper;


class ContentAnimalsController extends Controller
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

        $query = Content::find()->select(['content.id', 'name', 'description', 'content.picture_path', 'content.created_by_user_id', 'content.created_at', 'content.updated_at'])->where(['active' => 1, 'type_id' => 2, 'status' => 'approved']);
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize'=>$limit]);
        $animals = $query->limit($limit)->offset($offset)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();

        return $this->render('index', [
            'animals' => $animals,
            'pagination' => $pagination
        ]);
    }

    public function actionView($id) {

        $latestContentId = Helper::getEventIDActive($id);
        if($id != $latestContentId){
            return $this->redirect(['/content-animals/'.$latestContentId]);
        }

        $animals = Content::find()
        ->select(['id', 'name', 'description', 'type_id', 'status', 'picture_path', 'content_root_id', 'province_id', 'district_id', 'subdistrict_id', 'zipcode_id', 'created_by_user_id', 'created_at', 'latitude', 'longitude'])
        ->where(['id' => $id])->asArray()->one();
        if($animals["type_id"] != 2) {
            throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
        }
        //check can view
        if($animals["status"] == 'pending' || $animals["status"] == 'rejected'){
            if(empty(Yii::$app->user->identity->id)){
                throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
            }else if(Yii::$app->user->identity->id == $animals['created_by_user_id']){
                //ok
            }else{
                $teacher = FrontendHelper::checkCanViewContentForTeacher($animals['created_by_user_id']);
                if($teacher == false){
                    throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
                }
            }
        }

        $content_animal = ContentAnimal::find()
        ->where(['content_id' => $id])->one();
        if($animals["content_root_id"] != 0){
            $contentComment = Comment::find()->select(['id', 'user_id', 'created_at', 'message'])->where(['content_root_id' => $animals["content_root_id"]])->orderBy(['created_at' => SORT_DESC])->asArray()->all();
        }else{
            $contentComment = Comment::find()->select(['id', 'user_id', 'created_at', 'message'])->where(['content_root_id' => $id])->orderBy(['created_at' => SORT_DESC])->asArray()->all();
        }
        $other_content_animals = Content::find()->select(['id', 'name', 'description', 'picture_path', 'created_by_user_id', 'created_at'])->where(['type_id' => 2, 'active' => 1, 'status' => 'approved'])->andWhere(['not in', 'id', $id])->orderBy(['created_at' => SORT_DESC])->limit(3)->asArray()->all();

        $picture = Picture::find()->select(['path'])->where(['content_id' => $id])->asArray()->all();
        if (!empty($id)) {
            $dataContentStatistics = ContentStatistics::find()->select(['pageview'])->where(['content_root_id' => $id])->asArray()->one();

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
            'animals' => $animals,
            'content_animal' => $content_animal,
            'contentComment' => $contentComment,
            'other_content_animals' => $other_content_animals,
            'picture' => $picture
        ]);
    }

}
