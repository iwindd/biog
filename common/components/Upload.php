<?php

namespace common\components;

use yii;
use yii\web\UploadedFile;
use yii\imagine\Image;
use yii\helpers\Url;
use Imagine\Image\Box;

class Upload
{

    public static function upload($model, $folderName = 'picture', $prefixFile = '', $tempFileKey = null, $filenameOld)
    {
        $file = [];
        $result = array();
        $localPath = realpath(dirname(__FILE__) . '/../../') . '/document/' . $folderName . "/";

        if (!file_exists($localPath)) {
            mkdir($localPath, 0777, true);
        }

        try {
            $UploadedFile = UploadedFile::getInstance($model, 'file_display_name');
            if ($UploadedFile !== null) {
                if (!empty($tempFileKey)) {
                    Upload::removeFile($folderName, $tempFileKey);
                    Upload::removeFile($folderName, "thumbnail_" . $tempFileKey);
                }
                if (!empty($prefixFile)) {
                    $filename = $prefixFile . '_' . md5(date("Y-m-d H:i:s")) . '.' . $UploadedFile->extension;;
                } else {
                    $filename =  md5(date("Y-m-d H:i:s")) . '.' . $UploadedFile->extension;;
                }

                $UploadedFile->saveAs($localPath . $filename);

                if (file_exists($localPath . $filename)) {

                    $thumbnail = Image::thumbnail($localPath . $filename, 800, 480)
                        ->save(Yii::getAlias($localPath . "thumbnail_" . $filename), ['quality' => 100]);

                    if (file_exists($localPath . "thumbnail_" . $filename)) {
                        $result['thumbnail_file_key'] = "thumbnail_" . $filename;
                        $result['thumbnail_file_code'] = md5("thumbnail_" . $filename);
                        $result['thumbnail_file_display_name'] = $UploadedFile->basename . '.' . $UploadedFile->extension;
                    }

                    $result['file_key'] = $filename;
                    $result['file_code'] = md5($filename);
                    $result['file_display_name'] = $UploadedFile->basename . '.' . $UploadedFile->extension;
                } else {
                    $result['file_key'] = "error";
                }
            } else {
                $result['file_key'] = $tempFileKey;
                if (!empty($model->file_code)) {
                    $result['file_code'] = md5($model->file_code);
                }
                if (!empty($filenameOld)) {
                    $result['file_display_name'] = $filenameOld;
                }
            }
        } catch (\yii\db\Exception $e) {
            $result['file_key'] = $tempFileKey;
            if (!empty($model->file_code)) {
                $result['file_code'] = md5($model->file_code);
            }
            if (!empty($filenameOld)) {
                $result['file_display_name'] = $filenameOld;
            }
        }
        return $result;
    }

    public static function uploadFile($model, $folderName = 'picture', $fieldName, $filenameOld)
    {
        $file = [];
        $result = array();
        $localPath = realpath(dirname(__FILE__) . '/../../') . '/document/' . $folderName . "/";

        if (!file_exists($localPath)) {
            mkdir($localPath, 0777, true);
        }

        try {
            $UploadedFile = UploadedFile::getInstance($model, $fieldName);
            if ($UploadedFile !== null) {
                if (!empty($filenameOld)) {
                    Upload::removeFile($folderName, $filenameOld);
                }
                if (!empty($prefixFile)) {
                    $filename = $prefixFile . '_' . md5(date("Y-m-d H:i:s")) . '.' . $UploadedFile->extension;;
                } else {
                    $filename =  md5(date("Y-m-d H:i:s")) . '.' . $UploadedFile->extension;;
                }

                $UploadedFile->saveAs($localPath . $filename);

                if (file_exists($localPath . $filename)) {

                    $thumbnail = Image::thumbnail($localPath . $filename, 800, 480)
                        ->save(Yii::getAlias($localPath . "thumbnail_" . $filename), ['quality' => 100]);

                    if (file_exists($localPath . "thumbnail_" . $filename)) {
                        $result['thumbnail_file_key'] = "thumbnail_" . $filename;
                        $result['thumbnail_file_code'] = md5("thumbnail_" . $filename);
                        $result['thumbnail_file_display_name'] = $UploadedFile->basename . '.' . $UploadedFile->extension;
                    }

                    $result['file_key'] = $filename;
                    $result['file_code'] = md5($filename);
                    $result['file_display_name'] = $UploadedFile->basename . '.' . $UploadedFile->extension;
                } else {
                    $result['file_key'] = "error";
                }
            } else {
                $result['file_key'] = $tempFileKey;
                if (!empty($model->file_code)) {
                    $result['file_code'] = md5($model->file_code);
                }
                if (!empty($filenameOld)) {
                    $result['file_display_name'] = $filenameOld;
                }
            }
        } catch (\yii\db\Exception $e) {
            $result['file_key'] = $tempFileKey;
            if (!empty($model->file_code)) {
                $result['file_code'] = md5($model->file_code);
            }
            if (!empty($filenameOld)) {
                $result['file_display_name'] = $filenameOld;
            }
        }
        return $result;
    }

    public static function uploadPictureNoPermission($model, $folderName = 'picture', $filenameOld = null, $del = 0, $atrribute = 'picture')
    {
        $domain =  'http://localhost:8080/files/';
        $file = [];
        $result = array();
        $localPath = realpath(dirname(__FILE__) . '/../../') . '/frontend/web/files/' . $folderName . "/";

        $localPathThumbnail = realpath(dirname(__FILE__) . '/../../') . '/frontend/web/files/' . $folderName . "/thumbnail/";

        if (!file_exists($localPath)) {
            mkdir($localPath, 0777, true);
        }

        if (!file_exists($localPathThumbnail)) {
            mkdir($localPathThumbnail, 0777, true);
        }

        try {
            $UploadedFile = UploadedFile::getInstance($model, $atrribute);
            if ($UploadedFile !== null) {
                if (!empty($filenameOld)) {
                    Upload::removeFileNoPermission($folderName, $filenameOld);
                }

                $filename = $UploadedFile->basename . '.' . $UploadedFile->extension;

                $UploadedFile->saveAs($localPath . $filename);

                if (file_exists($localPath . $filename)) {

                    Image::getImagine()
                            ->open($localPath . $filename)
                            ->thumbnail(new Box(512, 512))
                            ->save(Yii::getAlias($localPathThumbnail. $filename), ['quality' => 100]);

                    $result= $filename;
                } else {
                    $result = "error";
                }
            } else if($del == 1){

                if (!empty($filenameOld)) {
                    Upload::removeFileNoPermission($folderName, $filenameOld);
                }

                $result = "remove";
            }else {
                $result = $filenameOld;
                
            }
        } catch (\yii\db\Exception $e) {
            $result = $filenameOld;
        }
        return $result;
    }


    public static function uploads($model, $folderName = 'picture', $atrribute = "files", $prefixFile = '', $tempFileKey = null)
    {
        $file = [];
        $results = array('data' => [], 'success_upload' => 1);
        $localPath = realpath(dirname(__FILE__) . '/../../') . '/document/' . $folderName . "/";

        if (!file_exists($localPath)) {
            mkdir($localPath, 0777, true);
        }

        try {
            $UploadedFiles = UploadedFile::getInstances($model, $atrribute);

            if (!empty($UploadedFiles)) {
                $cnt = 1;
                foreach ($UploadedFiles as $UploadedFile) {
                    $result = array();
                    if ($UploadedFile !== null) {
                        if (!empty($tempFileKey)) {
                            Upload::removeFile($folderName, $tempFileKey);
                        }
                        if (!empty($prefixFile)) {
                            $filename = $prefixFile . '_' . md5(date("Y-m-d H:i:s")) . $cnt . '.' . $UploadedFile->extension;;
                        } else {
                            $filename =  md5(date("Y-m-d H:i:s")) . $cnt . '.' . $UploadedFile->extension;;
                        }

                        // print '<pre>';
                        // print_r($localPath);
                        // print '</pre>';
                        // exit();

                        $UploadedFile->saveAs($localPath . $filename);

                        if (file_exists($localPath . $filename)) {
                            $result['file_key'] = $filename;
                            $result['file_code'] = md5($filename);
                            $result['file_display_name'] = $UploadedFile->basename . '.' . $UploadedFile->extension;
                        } else {
                            $results['success_upload'] = "0";
                            $results['data'][] = $result;
                            return $result;
                        }
                    }
                    $cnt++;
                    $results['data'][] = $result;
                }
            }

            return $results;

            // print "<pre>";
            // print_r($results);
            // print "</pre>";
            // exit();

        } catch (\yii\db\Exception $e) {
            $results['success_upload'] = "0";
            return $results;
        }
    }

    public static function uploadsNoPermimission($model, $folderName = 'picture', $atrribute = "files", $prefixFile = '', $tempFileKey = null)
    {
        $file = [];
        $results = array('data' => [], 'success_upload' => 1);
        //$localPath = realpath(dirname(__FILE__). '../../frontend/web/') . $folderName . "/";

        $localPath = realpath(dirname(__FILE__) . '/../../') . '/frontend/web/files/' . $folderName . "/";


        // print '<pre>';
        // print_r(dirname(__FILE__). '../../frontend/web/');
        // print '</pre>';
        // exit();

        if (!file_exists($localPath)) {
            mkdir($localPath, 0777, true);
        }

        try {
            $UploadedFiles = UploadedFile::getInstances($model, $atrribute);

            if (!empty($UploadedFiles)) {
                $cnt = 1;
                foreach ($UploadedFiles as $UploadedFile) {
                    $result = array();
                    if ($UploadedFile !== null) {
                        if (!empty($tempFileKey)) {
                            Upload::removeFile($folderName, $tempFileKey);
                        }
                        
                        $filename =  $UploadedFile->basename ."_".time().$cnt. '.' . $UploadedFile->extension;
                        

                        // print '<pre>';
                        // print_r($localPath);
                        // print '</pre>';
                        // exit();

                        $UploadedFile->saveAs($localPath . $filename);

                        if (file_exists($localPath . $filename)) {
                            $result['file_key'] = $filename;
                            $result['file_code'] = md5($filename);
                            $result['file_display_name'] = $UploadedFile->basename . '.' . $UploadedFile->extension;
                        } else {
                            $results['success_upload'] = "0";
                            $results['data'][] = $result;
                            return $result;
                        }
                    }
                    $cnt++;
                    $results['data'][] = $result;
                }
            }

            return $results;

            // print "<pre>";
            // print_r($results);
            // print "</pre>";
            // exit();

        } catch (\yii\db\Exception $e) {
            $results['success_upload'] = "0";
            return $results;
        }
    }

    public static function removeFile($folderName, $fileKey)
    {
        $localPath = realpath(dirname(__FILE__) . '/../../') . '/document/' . $folderName . "/" . $fileKey;
        if (file_exists($localPath)) {
            unlink($localPath);
            return 1;
        }
        return 1;
    }

    public static function removeFileNoPermission($folderName, $filename)
    {
        $localPath = realpath(dirname(__FILE__) . '/../../') . '/frontend/web/files/' . $folderName . "/" . $filename;
        if (file_exists($localPath)) {
            unlink($localPath);
            return 1;
        }
        return 0;
    }

    public static function readfilePicture($folderName, $fileKey)
    {
        $localPath = realpath(dirname(__FILE__) . '/../../') . '/document/' . $folderName . "/" . $fileKey;
        $docsFile = "";
        if (!empty($localPath)) {
            if (file_exists($localPath) && !empty($fileKey)) {
                $docsFile = '<img width="100%" src="data:image/png;base64, ' . base64_encode(file_get_contents($localPath)) . '" />';
            }
        }
        return $docsFile;
    }

    public static function readfilePictureNoPermission($folderName, $fileKey)
    {
        //$localPath = realpath(dirname(__FILE__) . '/../../') . '/document/' . $folderName . "/" . $fileKey;

        $localPath = realpath(dirname(__FILE__) . '/../../') . '/frontend/web/files/' . $folderName . "/" . $fileKey;

        $docsFile = "";
        if (!empty($localPath)) {
            if (file_exists($localPath) && !empty($fileKey)) {
                $docsFile = '<img width="100%" src="data:image/png;base64, ' . base64_encode(file_get_contents($localPath)) . '" />';
            }else{
                if ($folderName == 'blog') {
                    $docsFile = '<img width="100%" src="/images/BIOG_default_blog.png" />';
                } elseif ($folderName == 'knowledge') {
                    $docsFile = '<img width="100%" src="/images/BIOG_default_knowledge.png" />';
                } elseif ($folderName == 'news') {
                    $docsFile = '<img width="100%" src="/images/BIOG_default_news.png" />';
                }
            }
        }
        return $docsFile;
    }

    public static function readFileDocumentNoPermission($folderName, $fileKey)
    {
        //$localPath = realpath(dirname(__FILE__) . '/../../') . '/document/' . $folderName . "/" . $fileKey;

        $localPath = realpath(dirname(__FILE__) . '/../../') . '/frontend/web/files/' . $folderName . "/" . $fileKey;

        return $localPath;
    }

    public static function readfilePictureAddClass($folderName, $fileKey, $class)
    {
        $localPath = realpath(dirname(__FILE__) . '/../../') . '/document/' . $folderName . "/" . $fileKey;
        $docsFile = "";
        if (!empty($localPath)) {
            if (file_exists($localPath) && !empty($fileKey)) {
                $docsFile = '<img class="' . $class . '" src="data:image/png;base64, ' . base64_encode(file_get_contents($localPath)) . '" />';
            }else{
                $docsFile = '<img class="' . $class . '" src="/images/TK_LOGO.png" />';
            }
        }
        return $docsFile;
    }

    public static function readFileDocument($folderName, $fileKey)
    {
        $localPath = realpath(dirname(__FILE__) . '/../../') . '/document/' . $folderName . "/" . $fileKey;

        return $localPath;
    }

    public static function downloadFile($folderName, $fileKey, $fileName)
    {

        $localPath = realpath(dirname(__FILE__) . '/../../') . '/document/' . $folderName . "/" . $fileKey;
        $docsFile = "";
        if (!empty($localPath)) {
            if (file_exists($localPath)) {
                $docsFile = '<img width="100%" src="data:image/png;base64, ' . base64_encode(file_get_contents($localPath)) . '" />';
            }
        }

        $typefile = explode(".", $localPath);
        if ($typefile[1] == "pdf" || $typefile[1] == "PDF") {
            header('Content-Type: application/pdf');
            $fileName = $fileName . "." . $typefile[1];
        } else if ($typefile[1] == "doc") {
            header('Content-Type: application/msword');
            $fileName = $fileName . "." . $typefile[1];
        } else if ($typefile[1] == "docx") {
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            $fileName = $fileName . "." . $typefile[1];
        } else {
            header('Content-Type: image/jpeg');
        }

        header("Content-Transfer-Encoding: Binary");
        header('Content-Disposition: inline; filename=' . $fileName);
        header('Accept-Ranges: bytes');

        readfile($localPath);
    }
}
