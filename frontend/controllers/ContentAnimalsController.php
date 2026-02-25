<?php

namespace frontend\controllers;

use common\components\Helper;
use frontend\components\FrontendHelper;
use frontend\models\content\ContentAnimal;
use frontend\models\content\Picture;
use frontend\models\Comment;
use frontend\models\Content;
use frontend\models\ContentStatistics;
use yii\data\Pagination;
use yii\web\Controller;
use Yii;

class ContentAnimalsController extends Controller
{
    public function actionIndex()
    {
        FrontendHelper::checkContentTypeVisible(2);

        $limit = 6;
        $page = 1;
        if (!empty($_GET['page'])) {
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            if (false === $page) {
                $page = 1;
            }
        }
        $offset = ($page - 1) * $limit;

        $query = Content::find()->select(['content.id', 'name', 'description', 'content.picture_path', 'content.created_by_user_id', 'content.created_at', 'content.updated_at'])->where(['active' => 1, 'type_id' => 2, 'status' => 'approved', 'is_hidden' => false]);
        $countQuery = clone $query;

        $totalCount = Yii::$app->cache->getOrSet('content_animal_count', function () use ($countQuery) {
            return $countQuery->count();
        }, 60 * 5);

        $pagination = new Pagination(['totalCount' => $totalCount, 'pageSize' => $limit]);
        $animals = $query->limit($limit)->offset($offset)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();

        return $this->render('index', [
            'animals' => $animals,
            'pagination' => $pagination
        ]);
    }

    public function actionView($id)
    {
        $latestContentId = Helper::getEventIDActive($id);
        if ($id != $latestContentId) {
            return $this->redirect(['/content-animals/' . $latestContentId]);
        }

        $animals = Content::find()
            ->select(['id', 'name', 'description', 'type_id', 'status', 'picture_path', 'content_root_id', 'province_id', 'district_id', 'subdistrict_id', 'zipcode_id', 'created_by_user_id', 'created_at', 'latitude', 'longitude', 'license_id'])
            ->where(['id' => $id, 'is_hidden' => false])
            ->one();
        if ($animals['type_id'] != 2) {
            throw new \yii\web\HttpException(404, 'The requested Item could not be found.');
        }
        // check can view
        FrontendHelper::checkCanViewContent($animals['status'], $animals['created_by_user_id'], 2);

        $content_animal = ContentAnimal::find()
            ->where(['content_id' => $id])
            ->one();
        $rootId = $animals['content_root_id'] != 0 ? $animals['content_root_id'] : $id;
        $contentComment = Comment::find()
            ->select(['id', 'user_id', 'created_at', 'message'])
            ->where(['content_root_id' => $rootId])
            ->orderBy(['created_at' => SORT_DESC])
            ->asArray()
            ->all();
        $other_content_animals = Content::find()->select(['id', 'name', 'description', 'picture_path', 'created_by_user_id', 'created_at'])->where(['type_id' => 2, 'active' => 1, 'status' => 'approved'])->andWhere(['not in', 'id', $id])->orderBy(['created_at' => SORT_DESC])->limit(3)->asArray()->all();

        $picture = Picture::find()->select(['path'])->where(['content_id' => $id])->asArray()->all();
        FrontendHelper::incrementContentPageview($id);

        return $this->render('view', [
            'animals' => $animals,
            'content_animal' => $content_animal,
            'contentComment' => $contentComment,
            'other_content_animals' => $other_content_animals,
            'picture' => $picture
        ]);
    }
}
