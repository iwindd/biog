<?php

namespace frontend\controllers;
use Yii;
use yii\web\Controller;
use yii\data\Pagination;
use frontend\models\Content;
use frontend\models\content\ContentExpert;
use frontend\models\ContentStatistics;
use frontend\models\Comment;
use frontend\models\content\Picture;
use common\components\Helper;
use frontend\components\FrontendHelper;

class ContentExpertController extends Controller
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

        $query = Content::find()->select(['content.id', 'name', 'description', 'content.picture_path', 'content.created_by_user_id', 'content.created_at', 'content.updated_at'])->where(['type_id' => 4, 'active' => 1, 'status' => 'approved']);
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize'=>$limit]);
        $expert = $query->limit($limit)->offset($offset)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();

        return $this->render('index', [
            'expert' => $expert,
            'pagination' => $pagination
        ]);
    }

    public function actionView($id) {

        $latestContentId = Helper::getEventIDActive($id);
        if($id != $latestContentId){
            return $this->redirect(['/content-expert/'.$latestContentId]);
        }

        $expert = Content::find()->where(['id' => $id])->one();
        if($expert->type_id != 4) {
            throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
        }
        //check can view
        if($expert->status == 'pending' || $expert->status == 'rejected'){
            if(empty(Yii::$app->user->identity->id)){
                throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
            }else if(Yii::$app->user->identity->id == $expert['created_by_user_id']){
                //ok
            }else{
                $teacher = FrontendHelper::checkCanViewContentForTeacher($expert['created_by_user_id']);
                if($teacher == false){
                    throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
                }
            }
        }

        $content_expert = ContentExpert::find()->where(['content_id' => $id])->one();
        if($expert->content_root_id != 0){
            $contentComment = Comment::find()->select(['id', 'user_id', 'created_at', 'message'])->where(['content_root_id' => $expert->content_root_id])->orderBy(['created_at' => SORT_DESC])->asArray()->all();
        }else{
            $contentComment = Comment::find()->select(['id', 'user_id', 'created_at', 'message'])->where(['content_root_id' => $id])->orderBy(['created_at' => SORT_DESC])->asArray()->all();
        }
        $other_content_expert = Content::find()->where(['type_id' => 4, 'active' => 1, 'status' => 'approved'])->andWhere(['not in', 'id', $id])->orderBy(['created_at' => SORT_DESC])->limit(3)->asArray()->all();
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
            'expert' => $expert,
            'content_expert' => $content_expert,
            'contentComment' => $contentComment,
            'other_content_expert' => $other_content_expert,
            'picture' => $picture
        ]);
    }

}
