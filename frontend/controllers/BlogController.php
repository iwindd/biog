<?php

namespace frontend\controllers;
use Yii;
use yii\data\Pagination;
use frontend\models\Blog;
use frontend\models\BlogComment;
use frontend\models\BlogStatistics;
use frontend\models\BlogSearch;
use yii\web\NotFoundHttpException;

use yii\web\ForbiddenHttpException;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\BackendHelper;
use backend\models\BlogFile;

use common\components\Upload;
use common\components\Helper;
use frontend\components\BlogHelper;
use frontend\components\FrontendHelper;

class BlogController extends \yii\web\Controller
{
    public function actionIndex()
    {
        $limit = 6;
        $page = 1;
        if(!empty($_GET['page'])) {
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            if(false === $page) {
                $page = 1;
            }
        }
        $offset = ($page - 1) * $limit;

        $query = Blog::find()->select(['id', 'title', 'description', 'picture_path', 'created_by_user_id', 'created_at'])->where(['active'=>1]);
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize'=>$limit]);
        $blog = $query->limit($limit)->offset($offset)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();
       
        return $this->render('index', [
            'blog' => $blog,
            'pagination' => $pagination
        ]);

    }

    public function actionView($id) {


        $latestContentId = Helper::getBlogIDActive($id);


        if($id != $latestContentId){
            return $this->redirect(['/blog/'.$latestContentId]);
        }

        $blog = Blog::find()->select(['id', 'title', 'description', 'picture_path', 'created_by_user_id', 'created_at', 'video_url', 'source_information', 'blog_root_id'])->where(['id' => $latestContentId, 'active' => 1])->asArray()->one();

        if (!empty($blog)) {
            //show Comment
            if ($blog["blog_root_id"] != 0) {
                $blogComment = BlogComment::find()->select(['id', 'user_id', 'created_at', 'message'])->where(['blog_root_id' => $blog["blog_root_id"]])->orderBy(['created_at' => SORT_DESC])->asArray()->all();
            } else {
                $blogComment = BlogComment::find()->select(['id', 'user_id', 'created_at', 'message'])->where(['blog_root_id' => $id])->orderBy(['created_at' => SORT_DESC])->asArray()->all();
            }
            $otherBlog = Blog::find()->select(['id', 'title', 'description', 'created_by_user_id', 'created_at', 'picture_path'])->where(['active'=>1])->andWhere(['not in', 'id', $id])->asArray()->orderBy(['updated_at' => SORT_DESC])->limit(3)->all();
            
            $picture = BlogFile::find()->select(['path'])->where(['blog_id' => $id, 'application_type' => 'image'])->asArray()->all();
            $files = BlogFile::find()->select(['name', 'path'])->where(['blog_id' => $id, 'application_type' => 'file'])->asArray()->all();
            if (!empty($id)) {
                $dataKnowledgeStatistics = BlogStatistics::find()->where(['blog_root_id' => $id])->asArray()->one();

                $session = Yii::$app->session;
                $canUpViewPage = false;
                if (empty($session['views_blog'])) {
                    $session['views_blog'] = [
                        'blog_id' => $id,
                        'ip_address' => $_SERVER['REMOTE_ADDR']
                    ];
                    $canUpViewPage = true;
                }else if( $_SERVER['REMOTE_ADDR'] != $session['views_blog']['ip_address'] || $id != $session['views_blog']['blog_id']){
                    
                    $session['views_blog'] = [
                        'blog_id' => $id,
                        'ip_address' => $_SERVER['REMOTE_ADDR']
                    ];

                    $canUpViewPage = true;
                }

                if (!empty($dataKnowledgeStatistics)) {
                    if ($canUpViewPage == true) {
                        $pageview = $dataKnowledgeStatistics['pageview'] + 1;
                        Yii::$app->db->createCommand()
                            ->update('blog_statistics', ['pageview' => $pageview], 'blog_root_id = ' . $id)
                            ->execute();
                    }
                } else {
                    $count = new BlogStatistics;
                    $count->blog_root_id = $id;
                    $count->pageview = 1;
                    $count->updated_at = date("Y-m-d H:i:s");
                    $count->save();
                }
            }
        }else{
            throw new NotFoundHttpException();
        }

        return $this->render('view', [
            'blog' => $blog,
            'otherBlog' => $otherBlog,
            'picture' => $picture,
            'files' => $files,
            'blogComment' => $blogComment
        ]);
    }

    public function actionList() {
        if (!empty(Yii::$app->user->identity->id)) {

            $searchModel = new BlogSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

            return $this->render('list', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
            ]);
        }
        throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
    }

    public function actionCreate()
    {

        if (!empty(Yii::$app->user->identity->id)) {
            $model = new Blog();
            $model->type_id = 7;
            $case_error = array();

            $checkUpdate = true;

            //$mediaModel = BlogFile::find()->where(['blog_id' => $id])->all();

            if ($model->load(Yii::$app->request->post())) {
                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $mainPicture = Upload::uploadPictureNoPermission($model, 'blog', '', 0, 'picture_path');
                    if (!empty($mainPicture)) {
                        if ($mainPicture != 'error') {
                            $model->picture_path = $mainPicture;
                        } else {
                            $case_error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                        }
                    }

                    $post = Yii::$app->request->post();
                    $model->created_by_user_id = Yii::$app->user->identity->id;
                    $model->updated_by_user_id = Yii::$app->user->identity->id;
                    $model->created_at = date("Y-m-d H:i:s");
                    $model->updated_at = date("Y-m-d H:i:s");
                    $model->active = 1;

                    if ($model->save()) {

                        // upload multiple image
                        $files = Upload::uploadsNoPermimission($model, 'blog');
                        if (!empty($files)) {
                            if ($files['success_upload'] == 1) {
                                if (!empty($files['data'])) {
                                    foreach ($files['data'] as $value) {
                                        $mediaModel = new BlogFile();
                                        $mediaModel->blog_id = $model->id;
                                        $mediaModel->application_type = "image";
                                        $mediaModel->name = $value['file_display_name'];
                                        $mediaModel->path = $value['file_key'];
                                        $mediaModel->created_at = date("Y-m-d H:i:s");
                                        $mediaModel->updated_at = date("Y-m-d H:i:s");
                                        if (!$mediaModel->save()) {
                                            $checkUpdate = false;
                                            $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                        }
                                    }
                                }
                            } else {
                                $checkUpdate = false;
                                $case_error[] = array("message" => "อัพโหลดไฟล์ไม่สำเร็จ");
                            }
                        }

                        // upload multiple files
                        $documents = Upload::uploadsNoPermimission($model, 'blog', 'document');
                        // printr($documents);
                        if (!empty($documents)) {
                            if ($documents['success_upload'] == 1) {
                                if (!empty($documents['data'])) {
                                    foreach ($documents['data'] as $value) {
                                        $mediaModel = new BlogFile();
                                        $mediaModel->blog_id = $model->id;
                                        $mediaModel->application_type = "file";
                                        $mediaModel->name = $value['file_display_name'];
                                        $mediaModel->path = $value['file_key'];
                                        $mediaModel->created_at = date("Y-m-d H:i:s");
                                        $mediaModel->updated_at = date("Y-m-d H:i:s");
                                        if (!$mediaModel->save()) {
                                            $checkUpdate = false;
                                            $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                        }
                                    }
                                }
                            } else {
                                $checkUpdate = false;
                                $case_error[] = array("message" => "อัพโหลดไฟล์ไม่สำเร็จ");
                            }
                        }

                        if ($checkUpdate) {
                            $transaction->commit();

                            BackendHelper::saveUserLog('blog', Yii::$app->user->identity->id, $model->id, 'create blog', 'เพิ่มข้อมูล blog');

                            return $this->redirect(['view', 'id' => $model->id]);
                        }
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }

            return $this->render('create', [
                'model' => $model,
                'case_error' => $case_error,
                'pageType' => 'blog'
            ]);
        }else{
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    public function actionUpdate($id)
    {

        if (!empty(Yii::$app->user->identity->id)) {
            $model = Blog::findOne($id);

            if($model->created_by_user_id == Yii::$app->user->identity->id){

                $pictureList = BlogHelper::getAllPictureByContentId($id);
                $documentList = BlogHelper::getAllDocumentByContentId($id);

                $modelNew = new Blog();

                $case_error = array();

                $model->type_id = 7;

                $mediaModel = BlogFile::find()->where(['blog_id' => $id])->all();

                if ($modelNew->load(Yii::$app->request->post())) {

                    // print '<pre>';
                    // print_r(Yii::$app->request->post());
                    // print '</pre>';
                    // exit();

                    $checkUpdate = true;

                    $transaction = \Yii::$app->db->beginTransaction();
                    try {
                        $post = Yii::$app->request->post();

                        //set new model
                        $modelNew->type_id = 7;
                        $modelNew->active = 1;

                        
                        $modelNew->created_by_user_id = $model->created_by_user_id;
                        $modelNew->updated_by_user_id = Yii::$app->user->identity->id;
                        $modelNew->updated_at = date("Y-m-d H:i:s");
                        $modelNew->created_at = $model->getOldAttribute('created_at');

                        //find old latest content
                        $latestContentId = Helper::getBlogIDActive($model->id);

                        if( $model->blog_root_id == 0){
                            $modelNew->blog_root_id = $latestContentId;   
                        }else{
                            $modelNew->blog_root_id = $model->blog_root_id;   
                        }

                        $modelNew->blog_source_id = $latestContentId;
        
                        Blog::updateAll(['active' => '0'], ['blog_root_id' => $modelNew->blog_root_id]);
                        Blog::updateAll(['active' => '0'], ['id' => $modelNew->blog_root_id]);

                        $del = 0;
                        if(!empty($_POST['deletePic'])){
                            $del = $_POST['deletePic'];
                        }

                        $mainPicture = Upload::uploadPictureNoPermission($modelNew, 'blog', $model->getOldAttribute('picture_path'), $del, 'picture_path');
                        // print '<pre>';
                        // print_r($mainPicture);
                        // print '</pre>';
                        // exit();
                        if (!empty($mainPicture)) {
                            if ($mainPicture == 'error') {
                                $error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                            }else if($mainPicture == 'remove'){
                                $modelNew->picture_path = '';
                            }else{
                                $modelNew->picture_path = $mainPicture;
                            }
                        }

                        if ($modelNew->save()) {

                            $newContentId = $modelNew->id;

                            // upload multiple image
                            $files = Upload::uploadsNoPermimission($modelNew, 'blog');
                            if (!empty($files)) {
                                if ($files['success_upload'] == 1) {
                                    if (!empty($files['data'])) {
                                        foreach ($files['data'] as $value) {
                                            $mediaModel = new BlogFile();
                                            $mediaModel->blog_id = $newContentId;
                                            $mediaModel->application_type = "image";
                                            $mediaModel->name = $value['file_display_name'];
                                            $mediaModel->path = $value['file_key'];
                                            $mediaModel->created_at = date("Y-m-d H:i:s");
                                            $mediaModel->updated_at = date("Y-m-d H:i:s");
                                            if (!$mediaModel->save()) {
                                                $checkUpdate = false;
                                                $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                            }
                                        }
                                    }
                                } else {
                                    $checkUpdate = false;
                                    $case_error[] = array("message" => "อัพโหลดไฟล์ไม่สำเร็จ");
                                }
                            }

                            //check files remove 
                            $imagesId = array();
                            $deleteImage = explode(',', $post['removeImage']);
                            if (!empty($deleteImage)) {
                                foreach ($deleteImage as $imageId) {
                                    if (!empty($imageId)) {
                                        $imagesId[] = $imageId;
                                    }
                                }
                            }
                            $medias = array();
                            if (!empty($imagesId)) {
                                $medias = BlogFile::find()->where(['not in', 'id', $imagesId])->andWhere(['blog_id' => $latestContentId, 'application_type' => 'image'])->asArray()->all();
                                if(!empty($medias)){
                                    foreach ($medias as $value) {
                                        $value['file_display_name'] = $value['name'];
                                        $value['file_key'] = $value['path'];
                                        $newRecordPicture = $this->saveBlogFile('image', $newContentId, $value);
                                        if($newRecordPicture == false){
                                            $checkUpdate = false;
                                            $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                        }
                                    }
                                }
                            }else{
                                $medias = BlogFile::find()->where(['blog_id' => $latestContentId, 'application_type' => 'image'])->asArray()->all();
                                if(!empty($medias)){
                                    foreach ($medias as $value) {
                                        $value['file_display_name'] = $value['name'];
                                        $value['file_key'] = $value['path'];
                                        $newRecordPicture = $this->saveBlogFile('image', $newContentId, $value);
                                        if($newRecordPicture == false){
                                            $checkUpdate = false;
                                            $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                        }
                                    }
                                }
                            }



                            // upload multiple files
                            $documents = Upload::uploadsNoPermimission($modelNew, 'blog', 'document');
                            // printr($documents);
                            if (!empty($documents)) {
                                if ($documents['success_upload'] == 1) {
                                    if (!empty($documents['data'])) {
                                        foreach ($documents['data'] as $value) {
                                            $mediaModel = new BlogFile();
                                            $mediaModel->blog_id = $newContentId;
                                            $mediaModel->application_type = "file";
                                            $mediaModel->name = $value['file_display_name'];
                                            $mediaModel->path = $value['file_key'];
                                            $mediaModel->created_at = date("Y-m-d H:i:s");
                                            $mediaModel->updated_at = date("Y-m-d H:i:s");
                                            if (!$mediaModel->save()) {
                                                $checkUpdate = false;
                                                $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                            }
                                        }
                                    }
                                } else {
                                    $checkUpdate = false;
                                    $case_error[] = array("message" => "อัพโหลดไฟล์ไม่สำเร็จ");
                                }
                            }

                            //check files removeDocument remove 
                            $docId = array();
                            $deleteImage = explode(',', $post['removeDocument']);
                            if (!empty($deleteImage)) {
                                foreach ($deleteImage as $doc) {
                                    if (!empty($doc)) {
                                        $docId[] = $doc;
                                    }
                                }
                            }
                            $medias = array();
                            if (!empty($docId)) {
                                $medias = BlogFile::find()->where(['not in', 'id', $docId])->andWhere(['blog_id' => $latestContentId, 'application_type' => 'file'])->asArray()->all();
                                if(!empty($medias)){
                                    foreach ($medias as $value) {
                                        $value['file_display_name'] = $value['name'];
                                        $value['file_key'] = $value['path'];
                                        $newRecordPicture = $this->saveBlogFile('file',$newContentId, $value);
                                        if($newRecordPicture == false){
                                            $checkUpdate = false;
                                            $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                        }
                                    }
                                }
                            }else{
                                $medias = BlogFile::find()->where(['blog_id' => $latestContentId, 'application_type' => 'file'])->asArray()->all();
                                if(!empty($medias)){
                                    foreach ($medias as $value) {
                                        $value['file_display_name'] = $value['name'];
                                        $value['file_key'] = $value['path'];
                                        $newRecordPicture = $this->saveBlogFile('file', $newContentId, $value);
                                        if($newRecordPicture == false){
                                            $checkUpdate = false;
                                            $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                        }
                                    }
                                }
                            }

                            if ($checkUpdate) {
                                $transaction->commit();

                                BackendHelper::saveUserLog('blog', Yii::$app->user->identity->id, $newContentId, 'update blog', 'แก้ไขข้อมูล blog' );

                                return $this->redirect(['view', 'id' => $newContentId]);
                            }
                        }

                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        throw $e;
                    }
                    
                }

                return $this->render('update', [
                    'model' => $model,
                    'case_error' => $case_error,
                    'mediaModel' => $mediaModel,
                    'pageType' => 'blog',
                    'data' => (object) [
                        'pictureList' => $pictureList,
                        'documentList' => $documentList,
                    ],
                ]);
            }else{
                throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
            }
    
        }else{
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
    }

    public function actionDelete($id) {
  
        $model = Blog::findOne($id);

        if(Yii::$app->user->identity->id == $model->created_by_user_id) {
            $model->active = 0;

            if ($model->save()) {
    
                FrontendHelper::saveUserLog('blog', Yii::$app->user->identity->id, $id, 'delete blog', 'ลบข้อมูล blog' );
                return $this->redirect(['list']);
            }
        }
        return $this->redirect(['list']);
        
    }


    private function saveBlogFile($type, $newContentId, $value)
    {
        $mediaModel = new BlogFile();
        $mediaModel->blog_id = $newContentId;
        $mediaModel->application_type = $type;
        $mediaModel->name = $value['file_display_name'];
        $mediaModel->path = $value['file_key'];

        // if(empty($value['created_by_user_id'])){
        //     $mediaModel->created_by_user_id = Yii::$app->user->identity->id;
        // }else{
        //     $mediaModel->created_by_user_id = $value['created_by_user_id'];
        // }
        // $mediaModel->updated_by_user_id = Yii::$app->user->identity->id;
        $mediaModel->created_at = date("Y-m-d H:i:s");
        $mediaModel->updated_at = date("Y-m-d H:i:s");
        if (!$mediaModel->save()) {
            return false;
        }

        return true;
    }

}