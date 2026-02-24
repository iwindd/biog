<?php
namespace backend\controllers;

use Yii;
use api\models\SignupForm;
use dektrium\user\models\LoginForm;
use dektrium\user\models\RegistrationForm;
use dektrium\user\helpers\Password;

use backend\models\Content;
use backend\models\KnowledgeFile;
use backend\models\NewsFile;
use backend\models\BlogFile;

use sizeg\jwt\Jwt;
use sizeg\jwt\JwtHttpBearerAuth;

use yii\rest\ActiveController;
use yii\web\Controller;

use yii\web\Response;
use yii\filters\VerbFilter;


class ReadfileController extends Controller
{   
    public function actionIndex()
    {
        print 1;
        exit;  
    }

    public function actionPreview($code)
    {
        
        $file = Content::find()->where(['thumbnail_file_code' => $code])->one();
        if(!empty($file)){
            $pathFileTrue =  realpath(dirname(__FILE__).'/../../').'/document/content/'.$file['thumbnail_file_key'];
            $filePath =  realpath($pathFileTrue);
            $type = 'image/jpeg';
            header('Content-Type:'.$type);
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit();
        }

    }

    public function actionDownloadKnowledge($code){
        $file = KnowledgeFile::find()->where(['id' => $code])->one();
        if(!empty($file)){

            $localPath = realpath(dirname(__FILE__) . '/../../') . '/frontend/web/files/knowledge/';

            if($file->application_type == "image"){
                $pathFileTrue = $localPath.$file['path'];
                $filePath =  realpath($pathFileTrue);
                $type = 'image/jpeg';
                header('Content-Type:'.$type);
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit();
            }else if($file->application_type == "file"){

                $pathFileTrue =  $localPath.$file['path'];
                $filePath =  realpath($pathFileTrue);

                
                // print $file['file_key'];
                // exit();
                // $type = 'image/jpeg';
                // header('Content-Type:'.$type);
                // header('Content-Length: ' . filesize($filePath));
                // readfile($filePath);


                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header("Content-Disposition:attachment;filename=".$file['name']);
            

                header("Content-Transfer-Encoding: Binary");

                //header('Content-Disposition: inline; filename='.$fileName2);
                //header("Content-Disposition:attachment;filename='downloaded.pdf'");

                header('Accept-Ranges: bytes');



                readfile($filePath);

                exit();
            }
        }
    }

    public function actionDownloadNews($code){
        $file = NewsFile::find()->where(['id' => $code])->one();
        if(!empty($file)){

            $localPath = realpath(dirname(__FILE__) . '/../../') . '/frontend/web/files/news/';

            if($file->application_type == "image"){
                $pathFileTrue = $localPath.$file['path'];
                $filePath =  realpath($pathFileTrue);
                $type = 'image/jpeg';
                header('Content-Type:'.$type);
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit();
            }else if($file->application_type == "file"){

                $pathFileTrue =  $localPath.$file['path'];
                $filePath =  realpath($pathFileTrue);

                
                // print $file['file_key'];
                // exit();
                // $type = 'image/jpeg';
                // header('Content-Type:'.$type);
                // header('Content-Length: ' . filesize($filePath));
                // readfile($filePath);


                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header("Content-Disposition:attachment;filename=".$file['name']);
            

                header("Content-Transfer-Encoding: Binary");

                //header('Content-Disposition: inline; filename='.$fileName2);
                //header("Content-Disposition:attachment;filename='downloaded.pdf'");

                header('Accept-Ranges: bytes');



                readfile($filePath);

                exit();
            }
        }
    }

    public function actionDownloadBlog($code){
        $file = BlogFile::find()->where(['id' => $code])->one();
        if(!empty($file)){

            $localPath = realpath(dirname(__FILE__) . '/../../') . '/frontend/web/files/blog/';

            if($file->application_type == "image"){
                $pathFileTrue = $localPath.$file['path'];
                $filePath =  realpath($pathFileTrue);
                $type = 'image/jpeg';
                header('Content-Type:'.$type);
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit();
            }else if($file->application_type == "file"){

                $pathFileTrue =  $localPath.$file['path'];
                $filePath =  realpath($pathFileTrue);

                
                // print $file['file_key'];
                // exit();
                // $type = 'image/jpeg';
                // header('Content-Type:'.$type);
                // header('Content-Length: ' . filesize($filePath));
                // readfile($filePath);


                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header("Content-Disposition:attachment;filename=".$file['name']);
            

                header("Content-Transfer-Encoding: Binary");

                //header('Content-Disposition: inline; filename='.$fileName2);
                //header("Content-Disposition:attachment;filename='downloaded.pdf'");

                header('Accept-Ranges: bytes');



                readfile($filePath);

                exit();
            }
        }
    }

}



