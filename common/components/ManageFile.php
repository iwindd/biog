<?php

namespace common\components;

use yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\UploadedFile;

use common\components\Console;


/**
 * ===== Muhammad Imoeb =====
 * http://dixonsatit.github.io/2014/11/30/upload-json.html
 * 
 * 
 * class อัปโหลดไฟล์ยืดหยุ่น
 * กำลังทำ ยังไม่เสร็จ
 * 
 */

class ManageFile
{

    const IMAGE_FOLDER = 'images';
    const THUMBNAIL_FOLDER = 'thumbs';
    const DOCUMENT_FOLDER = 'documents';
    const VIDEO_FOLDER = 'documents';
    const FILES_FOLDER = 'files';


    public $basePath;
    public $baseUrl;

    // ตั้งชื่อ folder
    public $folder;
    // โยน model เข้ามาจาก Controllers
    public $model;
    // field ที่รับมาจาก $_FILES
    public $fields;
    // กำหนดชื่อ prefix
    public $prefixName;

    public $saveAsJson = false;
    public $saveWithFolder = false;

    // กำหนดไฟล์เพื่อไว้ตั้งชื่อ folder แยก
    private $imageType = ['jpg', 'png', 'gif'];
    private $documentType = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt'];
    private $videoType = ['webm', 'mp4', 'm4p', 'm4v', 'avi', 'wmv', 'mov'];
    private $fileType = ['rar', 'zip', '7zip'];


    public function __construct()
    {
        // rootdirectory/folderENV/
        $this->basePath = Yii::getAlias('@webroot');
        // folderENV/web
        $this->baseUrl = Yii::getAlias('@web');
    }

    public function oneUpload()
    {
        if ($this->model !== null) {
            // อัปโหลดไฟล์ field ที่เซ็ตไว้
            $file = UploadedFile::getInstance($this->model, $this->fields);

            // เช็คถ้ามีไฟล์อัปโหลด
            if (isset($file) && !empty($file)) {

                // ตั้งชื่อไฟล์
                $fileName = $this->setFileName($file);
                // ตั้ง folder ที่เก็บไฟล์
                $folder = $this->getFolderLocation();
                // ตั้ง path ที่ upload
                //$folderUploadPath = $this->getFolderUploadPath();

                $folderUploadPath = realpath(dirname(__FILE__) . '/../../') . '/frontend/web/files/' . $folder . "/";

                // save file
                $file->saveAs($folderUploadPath . $fileName);
                // TODO ยังไม่ได้ทำเช็คสร้าง folder อัตโนมัติ

                $output = '';
                if ($this->saveAsJson == true) {
                    $output = [
                        'key' => $this->getFileKey($fileName),
                        'name' => $fileName,
                        'originalName' => $file->baseName . '.' . $file->extension,
                        'folder' => $folder
                    ];
                    return json_encode($output);
                } else {
                    $output = $fileName;
                    return $output;
                }
            } else {
                // ถ้าไม่มีไฟล์อัปโหลด
                return false;
            }
        } else {
            // not have model
        }
    }


    public function uploads()
    {
        if ($this->model !== null) {

            $arrayFiles = [];
            foreach ($this->fields as $field) {
                // อัปโหลดไฟล์ทั้งหมดใน field นั้น ๆ
                $files = UploadedFile::getInstances($this->model, $field);

                // ลูปไฟล์ที่อัปโหลดทั้งหมด
                foreach ($files as $file) {
                    $fileName = $this->setFileName($file);
                    $folder = $this->getFolderLocation();
                    $folderUploadPath = $this->getFolderUploadPath();

                    $file->saveAs($folderUploadPath . $fileName);

                    if ($this->saveAsJson == true) {
                        $arrayFiles[] = [
                            'key' => $this->getFileKey($fileName),
                            'name' => $fileName,
                            'originalName' => $file->baseName . '.' . $file->extension,
                            'folder' => $folder
                        ];
                    }
                }
            }

            return json_encode($arrayFiles);
            Console::Log($arrayFiles);
        } else {
            // not have model
        }
    }

    public function removeFile($fileObject)
    {
        // โยน object แบบนี้เข้ามา
        // {
        //     key: "be25c236bb60f79f18a4f52f2c2af570"
        //     name: "be25c236bb60f79f18a4f52f2c2af570.jpg"
        //     folder: "/web/uploads/ชื่อfolder/" 
        // }
    }

    private function createDirectory($folderName)
    {
        // if ($folderName != NULL) {
        //     $basePath = Freelance::getUploadPath();
        //     if (BaseFileHelper::createDirectory($basePath . $folderName, 0777)) {
        //         BaseFileHelper::createDirectory($basePath . $folderName . '/thumbnail', 0777);
        //     }
        // }
        // return;
    }

    public static function getImageUrl($jsonImageData)
    {
        if (is_array($jsonImageData)) {
            $images = json_decode($jsonImageData, true);
            $image = [];
            foreach ($images as $key => $value) {
                $image[] = [
                    'url' => $images[$key]['folder'] . $images[$key]['name']
                ];
            }

            return $image;
        } else {
            return $jsonImageData;
        }
    }

    private function getFileKey($fileName)
    {
        $fileName = explode('.', $fileName);
        $fileName = $fileName[0];

        return $fileName;
    }

    private function setFileName($file)
    {
        $defaultFileName = md5($file->baseName . time() . uniqid()) . '.' . $file->extension;

        if ($this->prefixName == true) {
            return $this->prefixName . $defaultFileName;
        } else {
            return $defaultFileName;
        }
    }

    private function getFolderLocation()
    {
        $folder = '/' . $this->folder . '/';
        $folder = str_replace('\\', '/', $folder);

        return $folder;

        // path folder จะได้ /web/uploads/modelfolder/
    }

    private function getFolderUploadPath()
    {
        $folder = $this->basePath . '/' . $this->folder . '/';
        $folder = str_replace('\\', '/', $folder);

        return $folder;

        // path folder จะได้ localhost/web/uploads/modelfolder/foldertype/
    }

    private function getFolderExtension($fileExtension)
    {
        $folder = '';

        if (in_array($fileExtension, $this->imageType)) {
            $folder = self::IMAGE_FOLDER . '/';
        } elseif (in_array($fileExtension, $this->documentType)) {
            $folder = self::DOCUMENT_FOLDER . '/';
        } elseif (in_array($fileExtension, $this->videoType)) {
            $folder = self::VIDEO_FOLDER . '/';
        } elseif (in_array($fileExtension, $this->fileType)) {
            $folder = self::FILES_FOLDER . '/';
        } else {
            $folder = self::FILES_FOLDER . '/';
        }

        return $folder;
    }

    // public static function createThumbnail() {

    // }

    // public static function delete() {

    // }

    // protected function createDirectory(){

    // }

    // protected function uploadWithModel(){

    // }

    // protected function uploadWithOutModel(){

    // }
}
