<?php

namespace frontend\components;

use Yii;
use common\components\_;
use yii\helpers\ArrayHelper;
use common\components\Upload;
use yii\web\NotFoundHttpException;
use frontend\models\content\Content;
use frontend\models\content\Picture;
use frontend\models\content\ContentFungi;
use frontend\models\content\ContentPlant;
use frontend\models\content\ContentAnimal;
use frontend\models\content\ContentExpert;
use frontend\models\content\ContentProduct;
use frontend\models\content\ExpertCategory;
use frontend\models\content\ProductCategory;
use frontend\models\content\ContentEcotourism;

class ContentHelper
{
    const CONTENT_PAGE_LIST = [
        'plant' => 'พืช',
        'animal' => 'สัตว์',
        'fungi' => 'จุลินทรีย์',
        'expert' => 'ภูมิปัญญา / ปราชญ์',
        'ecotourism' => 'การท่องเที่ยวเชิงนิเวศ',
        'product' => 'ผลิตภัณฑ์ชุมชน',
    ];

    const CONTENT_CONFIG = [
        'plant' => [
            'type_id' => 1,
            'type' => 'plant',
            'name' => 'พืช',
            'upload_folder_name' => 'content-plant',
        ],
        'animal' => [
            'type_id' => 2,
            'type' => 'animal',
            'name' => 'สัตว์',
            'upload_folder_name' => 'content-animal',
        ],
        'fungi' => [
            'type_id' => 3,
            'type' => 'fungi',
            'name' => 'จุลินทรีย์',
            'upload_folder_name' => 'content-fungi',
        ],
        'expert' => [
            'type_id' => 4,
            'type' => 'expert',
            'name' => 'ภูมิปัญญา / ปราชญ์',
            'upload_folder_name' => 'content-expert',
        ],
        'ecotourism' => [
            'type_id' => 5,
            'type' => 'ecotourism',
            'name' => 'การท่องเที่ยวเชิงนิเวศ',
            'upload_folder_name' => 'content-ecotourism',
        ],
        'product' => [
            'type_id' => 6,
            'type' => 'product',
            'name' => 'ผลิตภัณฑ์ชุมชน',
            'upload_folder_name' => 'content-product',
        ],
    ];

    public static function getContentConfig($contentConfig)
    {
        return _::toObject(self::CONTENT_CONFIG[$contentConfig]);
    }


    public static function getContentStatus($status)
    {
        $listContentStatus = [
            'pending' => 'รออนุมัติ',
            'approved' => 'อนุมัติ',
            'rejected' => 'ไม่อนุมัติ'
        ];

        if (_::issetNotEmpty($status)) {
            return $listContentStatus[$status];
        }
    }

    public static function getContentById($id)
    {
        if (_::issetNotEmpty($id)) {
            $model = Content::find()->where(['id' => $id])->one();

            return _::throwNotFoundIfNotFoundModel($model);
        }
    }

    public static function getAllPictureByContentId($contentId)
    {
        if (_::issetNotEmpty($contentId)) {
            $model = Picture::find()->where([
                'content_id' => $contentId
            ])->asArray()->all();

            if (_::issetNotEmpty($model)) {
                return $model;
            }
        }
    }

    // <!-- coppy มาก่อน ค่อย refactor ทีหลัง -->
    public static function updatePictureInRevisionContent($revisionContentId, $contentId)
    {
        $POST = _::post();
        $stackIdRemoveFile = ArrayHelper::getValue($POST, 'stack_id_remove_file');
        $stackIdRemoveFile = (_::issetEmpty($stackIdRemoveFile) || _::isNotSetEmpty($stackIdRemoveFile)) ? null : explode(',', $stackIdRemoveFile);

        _::debug($stackIdRemoveFile);

        $isUpdated = true;

        // ถ้าไม่ได้ลบรูป ให้ copy รูป row เดิมทั้งหมด แล้วเปลี่ยน content_id เป็น content_id ที่อัปเดตล่าสุด
        if (_::isNull($stackIdRemoveFile)) {
            $pictureModel = new Picture();
            $revisionPictureModel = clone $pictureModel;

            $pictureListInContent = $pictureModel->find()->where(['content_id' => $contentId])->asArray()->all();

            if (_::issetNotEmpty($pictureListInContent)) {

                foreach ($pictureListInContent as $picture) {

                    $picture = (object) $picture;

                    $revisionPictureModel->isNewRecord = true;
                    $revisionPictureModel->id = null;

                    _::setupModel($revisionPictureModel, [
                        'content_id' => $revisionContentId,
                        'name' => $picture->name,
                        'path' => $picture->path,
                        'created_by_user_id' => $picture->created_by_user_id,
                        'updated_by_user_id' => _::currentUserId(),
                        'created_at' => $picture->created_at,
                        'updated_at' => _::getDateTimeYmdHis()
                    ]);

                    if (!_::saveModel($revisionPictureModel)) {
                        $isUpdated = false;
                    }
                }
            }
        }
        // ถ้ามีการลบรูป ให้ copy row ที่ไม่ได้ถูกลบ แล้วเปลี่ยน content_id
        else {
            $pictureModel = new Picture();
            $revisionPictureModel = clone $pictureModel;

            // ดึงรูปทั้งหมดที่ไม่ได้โดนลบ
            $pictureListInContent = $pictureModel->find()->where([
                'AND',
                "content_id = ${contentId}",
                ['NOT IN', 'id', $stackIdRemoveFile]
            ])->asArray()->all();

            if (_::issetNotEmpty($pictureListInContent)) {
                foreach ($pictureListInContent as $picture) {

                    $picture = (object) $picture;

                    $revisionPictureModel->isNewRecord = true;
                    $revisionPictureModel->id = null;

                    _::setupModel($revisionPictureModel, [
                        'content_id' => $revisionContentId,
                        'name' => $picture->name,
                        'path' => $picture->path,
                        'created_by_user_id' => $picture->created_by_user_id,
                        'updated_by_user_id' => _::currentUserId(),
                        'created_at' => $picture->created_at,
                        'updated_at' => _::getDateTimeYmdHis()
                    ]);

                    if (!_::saveModel($revisionPictureModel)) {
                        $isUpdated = false;
                    }
                }
            }
        }

        return $isUpdated;
    }

    public static function getContentPlantByContentId($contentId)
    {
        if (_::issetNotEmpty($contentId)) {
            $model = ContentPlant::find()->where([
                'content_id' => $contentId
            ])->one();

            return _::throwNotFoundIfNotFoundModel($model);
        }
    }

    public static function getContentAnimalByContentId($contentId)
    {
        if (_::issetNotEmpty($contentId)) {
            $model = ContentAnimal::find()->where([
                'content_id' => $contentId
            ])->one();

            return _::throwNotFoundIfNotFoundModel($model);
        }
    }

    public static function getContentFungiByContentId($contentId)
    {
        if (_::issetNotEmpty($contentId)) {
            $model = ContentFungi::find()->where([
                'content_id' => $contentId
            ])->one();

            return _::throwNotFoundIfNotFoundModel($model);
        }
    }

    public static function getExpertCategoryList()
    {
        $expertCategoryModel = new ExpertCategory();
        $expertCategoryList = $expertCategoryModel->find()->select([
            'id',
            'name'
        ])->where(['active' => 1])->all();

        if (_::issetNotEmpty($expertCategoryList)) {
            return $expertCategoryList;
        }

        return [];
    }

    public static function getProductCategoryList()
    {
        $productCategoryModel = new ProductCategory();
        $expertCategoryList = $productCategoryModel->find()->select([
            'id',
            'name'
        ])->where(['active' => 1])->all();

        if (_::issetNotEmpty($expertCategoryList)) {
            return $expertCategoryList;
        }

        return [];
    }

    public static function getContentExpertByContentId($contentId)
    {
        if (_::issetNotEmpty($contentId)) {
            $model = ContentExpert::find()->where([
                'content_id' => $contentId
            ])->one();

            return _::throwNotFoundIfNotFoundModel($model);
        }
    }

    public static function getContentEcotourismByContentId($contentId)
    {
        if (_::issetNotEmpty($contentId)) {
            $model = ContentEcotourism::find()->where([
                'content_id' => $contentId
            ])->one();

            return _::throwNotFoundIfNotFoundModel($model);
        }
    }

    public static function getContentProductByContentId($contentId)
    {
        if (_::issetNotEmpty($contentId)) {
            $model = ContentProduct::find()->where([
                'content_id' => $contentId
            ])->one();

            return _::throwNotFoundIfNotFoundModel($model);
        }
    }

    public static function saveImageFromUpload($model, $attributeName, $uploadFolderName, $oldFileName = null, $delete = 0)
    {
        $file = Upload::uploadPictureNoPermission($model, $uploadFolderName, $oldFileName, $delete, $attributeName);

        if (_::issetNotEmpty($file)) {
            if ($file != 'error') {
                return $file;
            } else {
                return false;
            }
        }

        if (_::isNotSetEmpty($file)) {
            return null;
        }

        return false;
    }

    public static function saveMultipleImageFromUpload($contentId, $model, $uploadFolderName)
    {
        $errorMessage = [];

        $fileFromUpload = Upload::uploadsNoPermimission($model, $uploadFolderName);
        // _::debug($_FILES);
        // _::debug($fileFromUpload);

        if (_::issetNotEmpty($fileFromUpload)) {
            if (($fileFromUpload['success_upload'] == 1) && (sizeof($fileFromUpload['data']) >= 1)) {
                foreach ($fileFromUpload['data'] as $file) {
                    $pictureModel = new Picture();

                    _::setupModel($pictureModel, [
                        'content_id' => $contentId,
                        'name' => $file['file_display_name'],
                        'path' => $file['file_key'],
                        'created_by_user_id' => _::currentUserId(),
                        'updated_by_user_id' => _::currentUserId(),
                        'created_at' => _::getDateTimeYmdHis(),
                        'updated_at' => _::getDateTimeYmdHis(),
                    ]);

                    if (!_::saveModel($pictureModel)) {
                        array_push($errorMessage, "ไฟล์ {$file['file_display_name']} อัปโหลดไม่สำเร็จ");
                    }
                }
            } else {
                // ไม่มีการอัปโหลด
                // array_push($errorMessage, "อัปโหลดไม่สำเร็จ");
            }
        }

        return $errorMessage;
    }

    public static function getInitialPreviewFiles($files, $type)
    {
        $folderName = self::CONTENT_CONFIG[$type]['upload_folder_name'];

        $folderPathName = "/files/{$folderName}/";

        // {caption: "Nature-1.jpg", width: "120px", url: "/site/file-delete", key: 1},
        $initialPreview = [];

        if (_::issetNotEmpty($files)) {

            if (is_array($files)) {
                foreach ($files as $file) {
                    $file = (object) $file;

                    if (_::issetNotEmpty($file)) {

                        array_push($initialPreview, '<img src="' . $folderPathName . $file->path . '" class="img-fluid img-thumbnail" style="max-height:220px;"/>');
                    }
                }

                return $initialPreview;
            } else {
                return '<img src="' . $folderPathName . $files . '" class="img-fluid img-thumbnail" style="max-height:220px;"/>';
            }
        } else {
            return '';
        }
    }

    public static function getInitialPreviewConfigFiles($files)
    {
        // {caption: "Nature-1.jpg", width: "120px", url: "/site/file-delete", key: 1},
        $initialPreviewConfig = [];

        if (_::issetNotEmpty($files)) {

            if (is_array($files)) {

                foreach ($files as $file) {
                    $file = (object) $file;

                    if (_::issetNotEmpty($file)) {

                        $initialPreviewConfig[] = (object) [
                            'caption' => $file->name,
                            'width' => '120px',
                            // 'url' => $folderPathName . $file->path,
                            'key' => $file->id
                        ];
                    }
                }
            } else {
                $initialPreviewConfig = [];
            }

            return $initialPreviewConfig;
        }
    }
}
