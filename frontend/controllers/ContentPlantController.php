<?php

namespace frontend\controllers;

use frontend\models\Content;
use yii\data\Pagination;
use yii\web\Controller;
use Yii;
// use frontend\models\ContentPlant;
use common\components\Helper;
use frontend\components\FrontendHelper;
use frontend\models\content\ContentPlant;
use frontend\models\content\Picture;
use frontend\models\Comment;
use frontend\models\ContentStatistics;

class ContentPlantController extends Controller
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

        $query = Content::find()->select(['content.id', 'name', 'description', 'content.picture_path', 'content.created_by_user_id', 'content.created_at', 'content.updated_at']);
        $query->where([
            'content.active' => 1,
            'content.type_id' => 1,
            'content.status' => 'approved',
            'content.is_hidden' => false
        ]);
        $countQuery = clone $query;

        $totalCount = Yii::$app->cache->getOrSet('content_plant_count', function () use ($countQuery) {
            return $countQuery->count();
        }, 60 * 5);

        $pagination = new Pagination(['totalCount' => $totalCount, 'pageSize' => $limit]);
        $plant = $query->limit($limit)->offset($offset)->asArray()->orderBy(['content.updated_at' => SORT_DESC])->all();

        return $this->render('index', [
            'plant' => $plant,
            'pagination' => $pagination
        ]);
    }

    public function actionView($id)
    {
        $latestContentId = Helper::getEventIDActive($id);
        if ($id != $latestContentId) {
            return $this->redirect(['/content-plant/' . $latestContentId]);
        }

        $content = Content::find()
            ->select(['id', 'name', 'description', 'picture_path', 'type_id', 'photo_credit', 'source_information', 'province_id', 'district_id', 'subdistrict_id', 'zipcode_id', 'latitude', 'longitude', 'status', 'content_root_id', 'created_by_user_id', 'created_at'])
            ->where(['id' => $id, 'is_hidden' => false])
            ->asArray()
            ->one();

        if ($content['type_id'] != 1) {
            throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
        }

        // check can view
        FrontendHelper::checkCanViewContent($content['status'], $content['created_by_user_id']);

        $content_plant = ContentPlant::find()->where(['content_id' => $id])->one();
        $rootId = $content['content_root_id'] != 0 ? $content['content_root_id'] : $id;
        $contentComment = Comment::find()
            ->select(['id', 'user_id', 'created_at', 'message'])
            ->where(['content_root_id' => $rootId])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->all();

        $other_content_plant = Content::find()
            ->select(['id', 'name', 'description', 'picture_path', 'created_by_user_id', 'created_at'])
            ->where(['type_id' => 1, 'active' => 1, 'status' => 'approved'])
            ->andWhere(['not in', 'id', $id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(3)
            ->asArray()
            ->all();
        $picture = Picture::find()->where(['content_id' => $id])->asArray()->all();

        FrontendHelper::incrementContentPageview($id);

        return $this->render('view', [
            'content' => $content,
            'content_plant' => $content_plant,
            'contentComment' => $contentComment,
            'other_content_plant' => $other_content_plant,
            'picture' => $picture
        ]);
    }
}
