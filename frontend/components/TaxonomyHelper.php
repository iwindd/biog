<?php

namespace frontend\components;

use common\components\_;
use frontend\models\content\ContentTaxonomy;
use frontend\models\content\Taxonomy;
use yii\helpers\ArrayHelper;

class TaxonomyHelper
{
    private static $currentDateTime;

    private static function setCurrentDateTime($currentDateTime)
    {
        self::$currentDateTime = $currentDateTime;
    }

    public static function getTaxonomyList()
    {
        $taxonomyModel = new Taxonomy();

        $taxonomyList = $taxonomyModel->find()
            ->select([
                'id',
                'name'
            ])->asArray()->all();

        return _::issetNotEmpty($taxonomyList) ? $taxonomyList : [];
    }

    public static function getTaxonomyListByContentId($contentId)
    {
        if (_::issetNotEmpty($contentId)) {
            $contentTaxonomyModel = new ContentTaxonomy();
            $contentTaxonomyTableName = ContentTaxonomy::tableName();

            $taxonomyTableName = Taxonomy::tableName();

            $allTaxonomyInContent = $contentTaxonomyModel->find()
                ->select([
                    "${taxonomyTableName}.name as name",
                    // "${taxonomyTableName}.name as name",
                ])
                ->leftJoin($taxonomyTableName, "${taxonomyTableName}.id = ${contentTaxonomyTableName}.taxonomy_id")
                ->where([
                    "${contentTaxonomyTableName}.content_id" => $contentId
                ])->asArray()->all();

            if (_::issetNotEmpty($allTaxonomyInContent)) {
                return ArrayHelper::getColumn($allTaxonomyInContent, 'name');
            }
        }
    }

    public static function getTaxonomyListByContentName($contentId)
    {
        if (_::issetNotEmpty($contentId)) {
            $contentTaxonomyModel = new ContentTaxonomy();
            $contentTaxonomyTableName = ContentTaxonomy::tableName();

            $taxonomyTableName = Taxonomy::tableName();

            $allTaxonomyInContent = $contentTaxonomyModel->find()
                ->select([
                    "${taxonomyTableName}.name as name",
                    // "${taxonomyTableName}.name as name",
                ])
                ->leftJoin($taxonomyTableName, "${taxonomyTableName}.id = ${contentTaxonomyTableName}.taxonomy_id")
                ->where([
                    "${contentTaxonomyTableName}.content_id" => $contentId
                ])->asArray()->all();

            if (_::issetNotEmpty($allTaxonomyInContent)) {
                return ArrayHelper::getColumn($allTaxonomyInContent, 'name');
            } else {
                return array();
            }
        } else {
            return array();
        }
    }

    private static function taxonomyExist($taxonomyId)
    {
        return Taxonomy::find()->where(['id' => $taxonomyId])->exists();
    }

    private static function taxonomyInContentExist($contentId, $taxonomyId)
    {
        return ContentTaxonomy::find()->where([
            'content_id' => $contentId,
            'taxonomy_id' => $taxonomyId
        ])->exists();
    }

    public static function insertTaxonomyInContent($contentId, $taxonomyList, $currentDateTime)
    {
        $resultProcess = (object) [
            'type' => null,
            'recoverTxonomyIdList' => [],
            'lastAddedtaxonomyIdList' => [],
        ];

        if (_::issetNotEmpty($taxonomyList)) {
            foreach ($taxonomyList as $taxonomyId) {
                self::setCurrentDateTime($currentDateTime);

                $taxonomyExist = self::taxonomyExist($taxonomyId);
                $taxonomyInContentExist = self::taxonomyInContentExist($contentId, $taxonomyId);

                // มี tax มี tax ใน concontent
                if ($taxonomyExist && $taxonomyInContentExist) {
                    // nothing
                }
                // มี tax ไม่มี tax ใน content
                elseif ($taxonomyExist && !$taxonomyInContentExist) {
                    $resultProcess->type = 'SAVE_CONTENT_TAXONOMY';
                    array_push($resultProcess->recoverTxonomyIdList, self::saveContentTaxonomy($contentId, $taxonomyId));
                }
                // ไม่มี tax
                else {
                    $resultProcess->type = 'SAVE_TAXONOMY_AND_CONTENT_TAXONOMY';
                    array_push($resultProcess->lastAddedtaxonomyIdList, self::saveTaxonomyAndContentTaxonomy($contentId, $taxonomyId));
                }
            }
        }

        // _::debug([
        //     $resultProcess->type,
        //     $resultProcess->recoverTxonomyIdList,
        //     $resultProcess->lastAddedtaxonomyIdList
        // ]);
        return $resultProcess;
    }

    public static function updateTaxonomyInContent($contentId, $taxonomyList, $currentDateTime)
    {
        if (_::issetNotEmpty($taxonomyList)) {

            $insertTaxonomyInContent = self::insertTaxonomyInContent($contentId, $taxonomyList, $currentDateTime);

            if (_::issetNotEmpty($insertTaxonomyInContent)) {
                $taxonomyList = array_merge($insertTaxonomyInContent->recoverTxonomyIdList, $taxonomyList);
                $taxonomyList = array_merge($insertTaxonomyInContent->lastAddedtaxonomyIdList, $taxonomyList);
            }

            $contentTaxonomyModel = new ContentTaxonomy();
            $contentTaxonomyModel->deleteAll(['AND', "content_id = ${contentId}", ['NOT IN', 'taxonomy_id', $taxonomyList]]);
        } else {
            $contentTaxonomyModel = new ContentTaxonomy();
            $contentTaxonomyModel->deleteAll(['content_id' => $contentId]);
        }
    }

    private static function saveContentTaxonomy($contentId, $taxonomyId)
    {
        $contentTaxonomyModel = new ContentTaxonomy();

        _::setupModel($contentTaxonomyModel, [
            'content_id' => $contentId,
            'taxonomy_id' => $taxonomyId,
            'created_at' => self::$currentDateTime,
        ]);

        if (_::saveModel($contentTaxonomyModel)) {
            return $contentTaxonomyModel->id;
        }
    }

    private static function saveTaxonomyAndContentTaxonomy($contentId, $taxonomyId)
    {
        $taxonomyModel = new Taxonomy();

        _::setupModel($taxonomyModel, [
            'name' => $taxonomyId,
            'created_at' => self::$currentDateTime,
            'updated_at' => self::$currentDateTime,
        ]);

        if (_::saveModel($taxonomyModel)) {
            self::saveContentTaxonomy($contentId, $taxonomyModel->id);
        }

        return $taxonomyModel->id;
    }
}
