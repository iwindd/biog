<?php

namespace frontend\components;

use Yii;
use common\components\_;
use yii\helpers\ArrayHelper;
use common\components\Upload;
use yii\web\NotFoundHttpException;
use frontend\models\Blog;
use backend\models\BlogFile;

class BlogHelper
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
    
        'blog' => [
            'type_id' => 7,
            'type' => 'blog',
            'name' => 'บล็อก',
            'upload_folder_name' => 'blog',
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

    public static function getAllPictureByContentId($blogId)
    {
        if (_::issetNotEmpty($blogId)) {
            $model = BlogFile::find()->where([
                'blog_id' => $blogId,
                'application_type' => 'image'
            ])->asArray()->all();

            if (_::issetNotEmpty($model)) {
                return $model;
            }
        }
    }

    public static function getAllDocumentByContentId($blogId)
    {
        if (_::issetNotEmpty($blogId)) {
            $model = BlogFile::find()->where([
                'blog_id' => $blogId,
                'application_type' => 'file'
            ])->asArray()->all();

            if (_::issetNotEmpty($model)) {
                return $model;
            }
        }
    }

    public static function getInitialPreviewFiles($files, $type)
    {

        $folderName = self::CONTENT_CONFIG[$type]['upload_folder_name'];

        $folderPathName = "/files/{$folderName}/";

        $imageType = ['jpg', 'jpeg', 'png', 'gif'];

        // {caption: "Nature-1.jpg", width: "120px", url: "/site/file-delete", key: 1},
        $initialPreview = [];

        if (_::issetNotEmpty($files)) {

            if (is_array($files)) {
                foreach ($files as $file) {
                    $file = (object) $file;

                    if (_::issetNotEmpty($file)) {

                        $file_parts = pathinfo($file->path);

                        if (in_array($file_parts['extension'], $imageType)) {
                            array_push($initialPreview, '<img src="' . $folderPathName . $file->path . '" class="img-fluid img-thumbnail" style="max-height:220px;"/>');
                        }else{
                            array_push($initialPreview, '<span class="file-other-icon"><i class="fas fa-file"></i></span>');
                        }
                    }
                }

                return $initialPreview;
            } else {
                return '<img src="' . $folderPathName . $files . '" class="img-fluid img-thumbnail" style="max-height:220px;"/>;';
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
