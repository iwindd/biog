<?php

namespace frontend\controllers;
use yii;
use yii\web\Controller;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;
use frontend\models\News;
use frontend\models\NewsStatistics;

use backend\models\NewsFile;

use common\components\Helper;

class NewsController extends Controller
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

        $query = News::find()->select(['id', 'title', 'description', 'public_date', 'picture_path'])->where(['active'=>1]);

        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize'=>$limit]);
        $news = $query->limit($limit)->offset($offset)->asArray()->orderBy(['public_date' => SORT_DESC])->all();
       

        return $this->render('index', [
            'news' => $news,
            'pagination' => $pagination
        ]);
    }

    public function actionView($id) {

        $latestContentId = Helper::getNewsIDActive($id);

        if($id != $latestContentId){
            return $this->redirect(['/news/'.$latestContentId]);
        }

        $news = News::find()->select(['id', 'title', 'description', 'picture_path'])->where(['id' => $latestContentId, 'active' => 1])->asArray()->one();
        $other_news = News::find()->select(['id', 'title', 'description', 'picture_path', 'created_at'])->where(['active'=>1])->andWhere(['not in', 'id', $latestContentId ])->asArray()->orderBy(['updated_at' => SORT_DESC])->limit(3)->all();
        
        $picture = NewsFile::find()->select(['path'])->where(['news_id' => $latestContentId, 'application_type' => 'image'])->asArray()->all();
        $files = NewsFile::find()->select(['name', 'path'])->where(['news_id' => $latestContentId, 'application_type' => 'file'])->asArray()->all();
        if (!empty($news)) {
            if (!empty($id)) {
                $dataNewsStatistics = NewsStatistics::find()->select(['pageview'])->where(['news_root_id' => $id])->asArray()->one();

                $session = Yii::$app->session;
                $canUpViewPage = false;
                if (empty($session['views_news'])) {
                    $session['views_news'] = [
                    'news_id' => $id,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ];
                    $canUpViewPage = true;
                } elseif ($_SERVER['REMOTE_ADDR'] != $session['views_news']['ip_address'] || $id != $session['views_news']['news_id']) {
                    $session['views_news'] = [
                    'news_id' => $id,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ];

                    $canUpViewPage = true;
                }


                //Count View
                if (!empty($dataNewsStatistics)) {
                    if ($canUpViewPage == true) {
                        $pageview = $dataNewsStatistics['pageview'] + 1;
                        Yii::$app->db->createCommand()
                        ->update('news_statistics', ['pageview' => $pageview], 'news_root_id = ' . $id)
                        ->execute();
                    }
                } else {
                    $count = new NewsStatistics;
                    $count->news_root_id = $id;
                    $count->pageview = 1;
                    $count->updated_at = date("Y-m-d H:i:s");
                    $count->save();
                }
            }

            return $this->render('view', [
            'news' => $news,
            'other_news' => $other_news,
            'picture' => $picture,
            'files' => $files
        ]);
        }else{
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }

}
