<?php

namespace backend\controllers;

use Yii;
use backend\models\Blog;
use backend\models\BlogSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\BackendHelper;
use backend\models\BlogFile;

use common\components\Upload;
use common\components\Helper;
/**
 * BlogController implements the CRUD actions for Blog model.
 */
class BlogController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig'=>[
                    'class'=>AccessRule::className()
                ],
                'rules' => [
                    //dashboard_view
                    [
                        'actions' => ['index', 'view', 'create', 'update', 'delete'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) 
                        {
                            switch($action->id){
                                case 'index':
                                    return PermissionAccess::BackendAccess('blog_list', 'controller');
                                break;

                                case 'create':
                                    return PermissionAccess::BackendAccess('blog_create', 'controller');
                                break;

                                case 'update':
                                    return PermissionAccess::BackendAccess('blog_update', 'controller');
                                break;

                                case 'view':
                                    return PermissionAccess::BackendAccess('blog_view', 'controller');
                                break;

                                case 'delete':
                                    return PermissionAccess::BackendAccess('blog_delete', 'controller');
                                break;

                                default:
                                    return false;
                                break;
                            }
                            
                        }
                    ],
                   
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Blog models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BlogSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Blog model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Blog model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
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
                    }else{
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

                        BackendHelper::saveUserLog('blog', Yii::$app->user->identity->id, $model->id, 'create blog', 'เพิ่มข้อมูล blog' );

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
        ]);
    }

    /**
     * Updates an existing Blog model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $modelNew = new Blog();

        $case_error = array();

        $model->type_id = 7;

        $mediaModel = BlogFile::find()->where(['blog_id' => $id])->all();

        if ($modelNew->load(Yii::$app->request->post())) {

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


                // // delete image
                // $deleteImage = explode('/', $post['removeImage']);
                // if (!empty($deleteImage)) {
                //     foreach ($deleteImage as $imageId) {
                //         if (!empty($imageId)) {
                //             $imageItem = BlogFile::findOne($imageId);
                //             if (!empty($imageItem)) {
                //                 $checkRemovefile = Upload::removeFileNoPermission('blog', $imageItem->path);
                //                 if ($checkRemovefile) {
                //                     $imageItem->delete();
                //                 }
                //             }
                //         }
                //     }
                // }

                // // delete file
                // $deleteDocument = explode('/', $post['removeDocument']);
                // if (!empty($deleteDocument)) {
                //     foreach ($deleteDocument as $documentId) {
                //         if (!empty($documentId)) {
                //             $documentItem = BlogFile::findOne($documentId);
                //             if (!empty($documentItem)) {
                //                 $checkRemovefile = Upload::removeFileNoPermission('blog', $documentItem->path);
                //                 if ($checkRemovefile) {
                //                     $documentItem->delete();
                //                 }
                //             }
                //         }
                //     }
                // }


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
                    $deleteImage = explode('/', $post['removeImage']);
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
                    $deleteImage = explode('/', $post['removeDocument']);
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
        ]);
    }

    /**
     * Deletes an existing Blog model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->active = 0;
        if ($model->save()) {

            BackendHelper::saveUserLog('blog', Yii::$app->user->identity->id, $id, 'delete blog', 'ลบข้อมูล blog' );

            return $this->redirect(['index']);
        }
   
        throw new \yii\web\BadRequestHttpException('ลบข้อมูลบล็อกไม่สำเร็จ');
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

    /**
     * Finds the Blog model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Blog the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Blog::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
