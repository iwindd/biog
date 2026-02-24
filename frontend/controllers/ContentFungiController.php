<?php

namespace frontend\controllers;

use common\components\Helper;
use frontend\components\FrontendHelper;
use frontend\models\content\ContentFungi;
use frontend\models\content\Picture;
use frontend\models\Comment;
use frontend\models\Content;
use frontend\models\ContentStatistics;
use yii\data\Pagination;
use yii\web\Controller;
use Yii;

class ContentFungiController extends Controller
{
    public function actionIndex()
    {
        FrontendHelper::checkContentTypeVisible(3);

        $limit = 6;
        $page = 1;
        if (!empty($_GET['page'])) {
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            if (false === $page) {
                $page = 1;
            }
        }
        $offset = ($page - 1) * $limit;

        $query = Content::find()->select(['content.id', 'name', 'description', 'content.picture_path', 'content.created_by_user_id', 'content.created_at', 'content.updated_at'])->where(['type_id' => 3, 'status' => 'approved', 'active' => 1, 'is_hidden' => false]);
        $countQuery = clone $query;

        $totalCount = Yii::$app->cache->getOrSet('content_fungi_count', function () use ($countQuery) {
            return $countQuery->count();
        }, 60 * 5);

        $pagination = new Pagination(['totalCount' => $totalCount, 'pageSize' => $limit]);
        $fungi = $query->limit($limit)->offset($offset)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();

        return $this->render('index', [
            'fungi' => $fungi,
            'pagination' => $pagination
        ]);
    }

    public function actionView($id)
    {
        $latestContentId = Helper::getEventIDActive($id);
        if ($id != $latestContentId) {
            return $this->redirect(['/content-fungi/' . $latestContentId]);
        }

        $fungi = Content::find()->where(['id' => $id, 'is_hidden' => false])->one();
        if ($fungi->type_id != 3) {
            throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
        }
        // check can view
        FrontendHelper::checkCanViewContent($fungi->status, $fungi['created_by_user_id'], 3);

        $content_fungi = ContentFungi::find()->where(['content_id' => $id])->one();
        $rootId = $fungi->content_root_id != 0 ? $fungi->content_root_id : $id;
        $contentComment = Comment::find()
            ->select(['id', 'user_id', 'created_at', 'message'])
            ->where(['content_root_id' => $rootId])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->all();
        $other_content_fungi = Content::find()->select(['id', 'name', 'description', 'picture_path', 'created_by_user_id', 'created_at'])->where(['type_id' => 3, 'active' => 1, 'status' => 'approved'])->andWhere(['not in', 'id', $id])->orderBy(['created_at' => SORT_DESC])->limit(3)->asArray()->all();
        $picture = Picture::find()->select(['path'])->where(['content_id' => $id])->asArray()->all();
        FrontendHelper::incrementContentPageview($id);

        return $this->render('view', [
            'fungi' => $fungi,
            'content_fungi' => $content_fungi,
            'contentComment' => $contentComment,
            'other_content_fungi' => $other_content_fungi,
            'picture' => $picture
        ]);
    }
}
