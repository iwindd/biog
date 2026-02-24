<?php

namespace frontend\controllers;

use common\components\Helper;
use frontend\components\FrontendHelper;
use frontend\models\content\ContentExpert;
use frontend\models\content\Picture;
use frontend\models\Comment;
use frontend\models\Content;
use frontend\models\ContentStatistics;
use yii\data\Pagination;
use yii\web\Controller;
use Yii;

class ContentExpertController extends Controller
{
    public function actionIndex()
    {
        FrontendHelper::checkContentTypeVisible(4);

        $limit = 6;
        $page = 1;
        if (!empty($_GET['page'])) {
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            if (false === $page) {
                $page = 1;
            }
        }
        $offset = ($page - 1) * $limit;

        $query = Content::find()->select(['content.id', 'name', 'description', 'content.picture_path', 'content.created_by_user_id', 'content.created_at', 'content.updated_at'])->where(['type_id' => 4, 'active' => 1, 'status' => 'approved', 'is_hidden' => false]);
        $countQuery = clone $query;

        $totalCount = Yii::$app->cache->getOrSet('content_expert_count', function () use ($countQuery) {
            return $countQuery->count();
        }, 60 * 5);

        $pagination = new Pagination(['totalCount' => $totalCount, 'pageSize' => $limit]);
        $expert = $query->limit($limit)->offset($offset)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();

        return $this->render('index', [
            'expert' => $expert,
            'pagination' => $pagination
        ]);
    }

    public function actionView($id)
    {
        $latestContentId = Helper::getEventIDActive($id);
        if ($id != $latestContentId) {
            return $this->redirect(['/content-expert/' . $latestContentId]);
        }

        $expert = Content::find()->where(['id' => $id, 'is_hidden' => false])->one();
        if ($expert->type_id != 4) {
            throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
        }
        // check can view
        FrontendHelper::checkCanViewContent($expert->status, $expert['created_by_user_id'], 4);

        $content_expert = ContentExpert::find()->where(['content_id' => $id])->one();
        $rootId = $expert->content_root_id != 0 ? $expert->content_root_id : $id;
        $contentComment = Comment::find()
            ->select(['id', 'user_id', 'created_at', 'message'])
            ->where(['content_root_id' => $rootId])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->all();
        $other_content_expert = Content::find()->where(['type_id' => 4, 'active' => 1, 'status' => 'approved'])->andWhere(['not in', 'id', $id])->orderBy(['created_at' => SORT_DESC])->limit(3)->asArray()->all();
        $picture = Picture::find()->where(['content_id' => $id])->asArray()->all();
        FrontendHelper::incrementContentPageview($id);

        return $this->render('view', [
            'expert' => $expert,
            'content_expert' => $content_expert,
            'contentComment' => $contentComment,
            'other_content_expert' => $other_content_expert,
            'picture' => $picture
        ]);
    }
}
