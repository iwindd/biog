<?php

namespace common\components;

use yii;
use yii\helpers\Html;
use yii\web\Response;
use yii\imagine\Image;
use yii\helpers\FileHelper;

use yii\imagine\Image\Box;
use yii\imagine\Image\Color;
use yii\imagine\Image\ImageInterface;
use yii\imagine\Image\ImagineInterface;

class FileLibrary
{

    const UPLOAD_FOLDER = 'files';

    // const localBaseUrl = 'http://tceb-front.com';
    // const localBaseUrl = 'http://localhost:8080';
    const localBaseUrl = null;




    public static function getImageDefault($folder, $cssClass)
    {
        if ($folder == "content-plant") {
            return Html::img('/images/BIOG_default_plant.png', ['class' => $cssClass]);
        } else if ($folder == "content-animal") {
            return Html::img('/images/BIOG_default_animal.png', ['class' => $cssClass]);
        } else if ($folder == "content-ecotourism") {
            return Html::img('/images/BIOG_default_ecotourism.png', ['class' => $cssClass]);
        } else if ($folder == "content-expert") {
            return Html::img('/images/BIOG_default_expert.png', ['class' => $cssClass]);
        } else if ($folder == "content-fungi") {
            return Html::img('/images/BIOG_default_fungi.png', ['class' => $cssClass]);
        } else if ($folder == "content-product") {
            return Html::img('/images/BIOG_default_product.png', ['class' => $cssClass]);
        } else if ($folder == "knowledge") {
            return Html::img('/images/BIOG_default_knowledge.png', ['class' => $cssClass]);
        } else if ($folder == "news") {
            return Html::img('/images/BIOG_default_news.png', ['class' => $cssClass]);
        } else if ($folder == "blog") {
            return Html::img('/images/BIOG_default_blog.png', ['class' => $cssClass]);
        } else if ($folder == "profile") {
            return Html::img('/images/default-user.png', ['class' => $cssClass]);
        }
        return Html::img('/images/default.png', ['class' => $cssClass]);
    }


    public static function getImageFrontend($folder, $file, $subfolder = 'thumbnail-image', $thumbnailImage = false, $width = '100%', $cssClass = 'w-100')
    {
        if (!empty($file)) {
            $frontendPath = \yii::getAlias('@frontend');
            $imagePath = str_replace('\\', '/', $frontendPath);
            $imageFilePath = $imagePath . '/web/' . FileLibrary::UPLOAD_FOLDER . '/' . $folder . '/' . $file;
            $imageThumbnailPath = FileLibrary::UPLOAD_FOLDER . '/' . $folder . '/' . $subfolder . '/' . $file;

            if ($thumbnailImage == true) {
                if (file_exists($imageThumbnailPath)) {
                    $file = 'data:image/png;base64, ' . base64_encode(file_get_contents($imageThumbnailPath));
                    return Html::img($file, ['class' => $cssClass, 'width' => $width]);
                } else {
                    return FileLibrary::getImageDefault($folder, $cssClass);
                }
            } else {
                if (file_exists($imageFilePath)) {
                    $file = 'data:image/png;base64, ' . base64_encode(file_get_contents($imageFilePath));
                    return Html::img($file, ['class' => $cssClass, 'width' => $width]);
                } else {
                    return FileLibrary::getImageDefault($folder, $cssClass);
                }
            }
        } else {
            return FileLibrary::getImageDefault($folder, $cssClass);
        }
    }

    public function isImage($filePath)
    {
        return @is_array(getimagesize($filePath)) ? true : false;
    }

    public static function getImageProfile($data, $folder, $subfolder = 'thumbnail-image', $thumbnailImage = false, $width = 100, $cssClass = '')
    {
        $frontendPath = Yii::getAlias('@frontend/web/');
        $baseUrl      = self::localBaseUrl;

        if (!empty($data)) {
            // $data = str_replace('path', 'name', $data);
            // $data = json_decode($data);
            // $data = (!empty($data->name)) ? $data : json_decode('{"name":"/images/Default_IMG/user-default-img-80x80.png"}');

            $filePath = $folder . '/' . $subfolder . '/' . $data;
            // print_r($filePath);
            //exit();
            $fileUrl  = (isset($baseUrl)) ? $baseUrl  . '/' . $filePath :   '/' . $filePath;

            $fileThumbnailPath = $folder . '/' . $subfolder . '/' . $data;
            $fileThumbnailUrl  = (isset($baseUrl)) ? $baseUrl  . '/' . $fileThumbnailPath :   '/' . $fileThumbnailPath;

            $fileExtension = explode('.', $data);
            $fileExtension = $fileExtension[1];



            if ($thumbnailImage == true) {

                if (file_exists($frontendPath . $fileThumbnailPath)) {
                    if ($fileExtension == 'jpg' || $fileExtension == 'png' || $fileExtension == 'jpeg') {
                        $imageUrl = Html::img($fileThumbnailUrl, ['class' => $cssClass, 'width' => $width]);
                    } else {
                        $imageUrl =  '<div class="file-preview-other">' .
                            '<a href="' . $fileUrl . '"><i class="glyphicon glyphicon-file"></i>' . $data . '</a>' .
                            '</div>';
                    }
                } elseif (file_exists($frontendPath . $filePath)) {

                    if ($fileExtension == 'jpg' || $fileExtension == 'png' || $fileExtension == 'jpeg') {
                        $imageUrl = Html::img($fileThumbnailUrl, ['class' => $cssClass, 'width' => $width]);
                    } else {
                        $imageUrl =  '<div class="file-preview-other">' .
                            '<a href="' . $fileUrl . '"><i class="glyphicon glyphicon-file"></i>' . $data . '</a>' .
                            '</div>';
                    }
                } else {
                    if ($fileExtension == 'jpg' || $fileExtension == 'png' || $fileExtension == 'jpeg') {
                        $imageUrl = Html::img($baseUrl . '/images/default.png', ['class' => $cssClass, 'width' => $width]);
                    } else {
                        $imageUrl =  'ไม่พบไฟล์';
                    }
                }
            } else {
                if (file_exists($frontendPath . $filePath)) {

                    $imageUrl = $fileUrl;
                } else {
                    if ($fileExtension == 'jpg' || $fileExtension == 'png' || $fileExtension == 'jpeg') {
                        $imageUrl =  '/images/default.png';
                    } else {
                        $imageUrl =  'ไม่พบไฟล์';
                    }
                }
            }
        } else {
            if ($thumbnailImage == true) {
                $imageUrl = Html::img('/images/default.png', ['class' => $cssClass, 'width' => $width]);
            } else {
                $imageUrl =  '/images/default.png';
            }
        }

        return $imageUrl;
    }

    public function GetExtension($file)
    {
        if (!empty($file)) {
            $info  = pathinfo($file);
            $extension = strtoupper($info['extension']);
            return $extension;
        } else {
            return " ";
        }
    }
    public function FileSizeConvert($bytes)
    {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach ($arBytes as $arItem) {
            if ($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(",", ".", strval(round($result, 2))) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

    public static function getDailyFile($file)
    {
        $result = '';
        $frontendPath = Yii::getAlias('@frontend/web/');
        $folder = "daily";

        if ($file != "") {
            $data    = json_decode($file);
            if (isset($data) && !empty($data)) {
                $filePath = '/' . FileLibrary::UPLOAD_FOLDER . '/' . $folder . '/file/' . $data->name;

                if (file_exists($frontendPath . $filePath)) {
                    $result = $filePath;
                } else {
                    $result = '';
                }
            } else {
                $result = '';
            }
        }

        return $result;
    }
    public function getPathMultimediaPhoto($data, $folder)
    {
        if ($data != "") {
            $filePath = FileLibrary::UPLOAD_FOLDER . "/" . $folder . $data;
            // $isImage  = FileLibrary::isImage($filePath);
            if (file_exists($filePath)) {
                //if($isImage){
                $file = $filePath;

                // }else{
                //     $file = 'images/default.png'; /*'<div class="file-preview-other">' .
                //             '<a href="'.$filePath.'"><i class="glyphicon glyphicon-file"></i>'.$data->name.'</a>' .
                //             '</div>'*/;
                // }

            } else {
                $file =  'images/default.png';
            }
        } else {
            $file =  'images/default.png';
        }
        //$file_size =FileLibrary::FileSizeConvert(filesize($file));
        $extension = FileLibrary::GetExtension($file);
        return array(
            "file" => "/" . $file,
            // => $file_size,
            "extension" => $extension,
        );
    }
}
