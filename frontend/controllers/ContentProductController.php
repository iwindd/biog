<?php

namespace frontend\controllers;

use common\components\Helper;
use frontend\components\FrontendHelper;
use frontend\models\content\ContentProduct;
use frontend\models\content\Picture;
use frontend\models\Comment;
use frontend\models\Content;
use frontend\models\ContentStatistics;
use yii\data\Pagination;
use yii\web\Controller;
use Yii;

class ContentProductController extends Controller
{
    public function actionIndex()
    {
        $limit = 6;
        $page = 1;
        if (!empty($_GET['page'])) {
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            if (false === $page) {
                $page = 1;
            }
        }
        $offset = ($page - 1) * $limit;

        $query = Content::find()->select(['content.id', 'name', 'description', 'content.picture_path', 'content.created_by_user_id', 'content.created_at', 'content.updated_at'])->where(['type_id' => 6, 'active' => 1, 'status' => 'approved', 'is_hidden' => false]);
        $countQuery = clone $query;
        $totalCount = Yii::$app->cache->getOrSet('content_product_count', function () use ($countQuery) {
            return $countQuery->count();
        }, 60 * 5);

        $pagination = new Pagination(['totalCount' => $totalCount, 'pageSize' => $limit]);
        $product = $query->limit($limit)->offset($offset)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();

        return $this->render('index', [
            'product' => $product,
            'pagination' => $pagination
        ]);
    }

    public function actionView($id)
    {
        $latestContentId = Helper::getEventIDActive($id);
        if ($id != $latestContentId) {
            return $this->redirect(['/content-product/' . $latestContentId]);
        }

        $product = Content::find()->where(['id' => $id, 'is_hidden' => false])->asArray()->one();
        if ($product['type_id'] != 6) {
            throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
        }
        // check can view
        if ($product['status'] == 'pending' || $product['status'] == 'rejected') {
            if (empty(Yii::$app->user->identity->id)) {
                throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
            } else if (Yii::$app->user->identity->id == $product['created_by_user_id']) {
                // ok
            } else {
                $teacher = FrontendHelper::checkCanViewContentForTeacher($product['created_by_user_id']);
                if ($teacher == false) {
                    throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
                }
            }
        }

        $content_product = ContentProduct::find()->where(['content_id' => $id])->one();
        if ($product['content_root_id'] != 0) {
            $contentComment = Comment::find()
                ->select(['id', 'user_id', 'created_at', 'message'])
                ->where(['content_root_id' => $product['content_root_id']])
                ->orderBy(['created_at' => SORT_DESC])
                ->asArray()
                ->all();
        } else {
            $contentComment = Comment::find()
                ->select(['id', 'user_id', 'created_at', 'message'])
                ->where(['content_root_id' => $id])
                ->orderBy(['created_at' => SORT_DESC])
                ->asArray()
                ->all();
        }
        $other_content_product = Content::find()
            ->select(['id', 'name', 'description', 'picture_path', 'created_by_user_id', 'created_at'])
            ->where(['active' => 1, 'status' => 'approved', 'type_id' => 6])
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
            } else if ($_SERVER['REMOTE_ADDR'] != $session['views_content']['ip_address'] || $id != $session['views_content']['content_id']) {
                $session['views_content'] = [
                    'content_id' => $id,
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ];

                $canUpViewPage = true;
            }

            // Count View
            if (!empty($dataContentStatistics)) {
                if ($canUpViewPage == true) {
                    $pageview = $dataContentStatistics['pageview'] + 1;
                    Yii::$app
                        ->db
                        ->createCommand()
                        ->update('content_statistics', ['pageview' => $pageview], 'content_root_id = ' . $id)
                        ->execute();
                }
            } else {
                $count = new ContentStatistics;
                $count->content_root_id = $id;
                $count->pageview = 1;
                $count->updated_at = date('Y-m-d H:i:s');
                $count->save();
            }
        }

        return $this->render('view', [
            'product' => $product,
            'content_product' => $content_product,
            'contentComment' => $contentComment,
            'other_content_product' => $other_content_product,
            'picture' => $picture
        ]);
    }
}
