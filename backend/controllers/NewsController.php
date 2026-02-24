<?php

namespace backend\controllers;

use Yii;
use backend\models\News;
use backend\models\NewsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\BackendHelper;

use backend\models\NewsFile;
use common\components\Upload;
use common\components\Helper;

/**
 * NewsController implements the CRUD actions for News model.
 */
class NewsController extends Controller
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
                                    return PermissionAccess::BackendAccess('news_list', 'controller');
                                break;

                                case 'create':
                                    return PermissionAccess::BackendAccess('news_create', 'controller');
                                break;

                                case 'update':
                                case 'teacher-student':
                                    return PermissionAccess::BackendAccess('news_update', 'controller');
                                break;

                                case 'view':
                                case 'teacher':
                                    return PermissionAccess::BackendAccess('news_view', 'controller');
                                break;

                                case 'delete':
                                    return PermissionAccess::BackendAccess('news_delete', 'controller');
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
     * Lists all News models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new NewsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single News model.
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
     * Creates a new News model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new News();
        $case_error = array();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $checkUpdate = true;
                
                $post = Yii::$app->request->post();
                $model->created_by_user_id = Yii::$app->user->identity->id;
                $model->updated_by_user_id = Yii::$app->user->identity->id;
                $model->created_at = date("Y-m-d H:i:s");
                $model->updated_at = date("Y-m-d H:i:s");
                $model->active = 1;

                $mainPicture = Upload::uploadPictureNoPermission($model, 'news', '', 0, 'picture_path');
                if (!empty($mainPicture)) {
    
                    if ($mainPicture != 'error') {
                        $model->picture_path = $mainPicture;
                    }else{
                        $case_error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                    }
                }


                if ($model->save()) {

                    // upload multiple image
                    $files = Upload::uploadsNoPermimission($model, 'news');
                    if (!empty($files)) {
                        if ($files['success_upload'] == 1) {
                            if (!empty($files['data'])) {
                                foreach ($files['data'] as $value) {
                                    $mediaModel = new NewsFile();
                                    $mediaModel->news_id = $model->id;
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
                    $documents = Upload::uploadsNoPermimission($model, 'news', 'document');
                    // printr($documents);
                    if (!empty($documents)) {
                        if ($documents['success_upload'] == 1) {
                            if (!empty($documents['data'])) {
                                foreach ($documents['data'] as $value) {
                                    $mediaModel = new NewsFile();
                                    $mediaModel->news_id = $model->id;
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
    
                        if ($model->save()) {
                            $transaction->commit();

                            BackendHelper::saveUserLog('news', Yii::$app->user->identity->id, $model->id, 'create news', 'ลบข้อมูลข่าวสาร');

                            return $this->redirect(['view', 'id' => $model->id]);
                        }
                    }

                }
       

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->render('create', [
            'model' => $model,
            'case_error' => $case_error
        ]);
    }

    /**
     * Updates an existing News model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $case_error = array();

        $mediaModel = NewsFile::find()->where(['news_id' => $id])->all();

        $modelNew = new News();

        if ($modelNew->load(Yii::$app->request->post())) {

            $checkUpdate = true;

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $post = Yii::$app->request->post();

                //set new model
                $modelNew->active = 1;
                $modelNew->created_by_user_id = $model->created_by_user_id;
                $modelNew->updated_by_user_id = Yii::$app->user->identity->id;
                $modelNew->updated_at = date("Y-m-d H:i:s");
                $modelNew->created_at = $model->getOldAttribute('created_at');

                //find old latest content
                $latestContentId = Helper::getNewsIDActive($model->id);

                if( $model->news_root_id == 0){
                    $modelNew->news_root_id = $latestContentId;   
                }else{
                    $modelNew->news_root_id = $model->news_root_id;   
                }

                $modelNew->news_source_id = $latestContentId;
 
                News::updateAll(['active' => '0'], ['news_root_id' => $modelNew->news_root_id]);
                News::updateAll(['active' => '0'], ['id' => $modelNew->news_root_id]);

                $del = 0;
                if(!empty($_POST['deletePic'])){
                    $del = $_POST['deletePic'];
                }

                $mainPicture = Upload::uploadPictureNoPermission($modelNew, 'news', $model->getOldAttribute('picture_path'), $del, 'picture_path');

  
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
                //             $imageItem = NewsFile::findOne($imageId);
                //             if (!empty($imageItem)) {
                //                 $checkRemovefile = Upload::removeFileNoPermission('news', $imageItem->path);
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
                //             $documentItem = NewsFile::findOne($documentId);
                //             if (!empty($documentItem)) {
                //                 $checkRemovefile = Upload::removeFileNoPermission('news', $documentItem->path);
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
                    $files = Upload::uploadsNoPermimission($modelNew, 'news');
                    if (!empty($files)) {
                        if ($files['success_upload'] == 1) {
                            if (!empty($files['data'])) {
                                foreach ($files['data'] as $value) {
                                    $mediaModel = new NewsFile();
                                    $mediaModel->news_id = $newContentId;
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

                    //check image remove 
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
                        $medias = NewsFile::find()->where(['not in', 'id', $imagesId])->andWhere(['news_id' => $latestContentId, 'application_type' => 'image'])->asArray()->all();
                        if(!empty($medias)){
                            foreach ($medias as $value) {
                                $value['file_display_name'] = $value['name'];
                                $value['file_key'] = $value['path'];
                                $newRecordPicture = $this->saveNewsFile('image', $newContentId, $value);
                                if($newRecordPicture == false){
                                    $checkUpdate = false;
                                    $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                }
                            }
                        }
                    }else{
                        $medias = NewsFile::find()->where(['news_id' => $latestContentId, 'application_type' => 'image'])->asArray()->all();
                        if(!empty($medias)){
                            foreach ($medias as $value) {
                                $value['file_display_name'] = $value['name'];
                                $value['file_key'] = $value['path'];
                                $newRecordPicture = $this->saveNewsFile('image', $newContentId, $value);
                                if($newRecordPicture == false){
                                    $checkUpdate = false;
                                    $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                }
                            }
                        }
                    }



                    // upload multiple files
                    $documents = Upload::uploadsNoPermimission($modelNew, 'news', 'document');
                    // printr($documents);
                    if (!empty($documents)) {
                        if ($documents['success_upload'] == 1) {
                            if (!empty($documents['data'])) {
                                foreach ($documents['data'] as $value) {
                                    $mediaModel = new NewsFile();
                                    $mediaModel->news_id = $newContentId;
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
                        $medias = NewsFile::find()->where(['not in', 'id', $docId])->andWhere(['news_id' => $latestContentId, 'application_type' => 'file'])->asArray()->all();
                        if(!empty($medias)){
                            foreach ($medias as $value) {
                                $value['file_display_name'] = $value['name'];
                                $value['file_key'] = $value['path'];
                                $newRecordPicture = $this->saveNewsFile('file',$newContentId, $value);
                                if($newRecordPicture == false){
                                    $checkUpdate = false;
                                    $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                }
                            }
                        }
                    }else{
                        $medias = NewsFile::find()->where(['news_id' => $latestContentId, 'application_type' => 'file'])->asArray()->all();
                        if(!empty($medias)){
                            foreach ($medias as $value) {
                                $value['file_display_name'] = $value['name'];
                                $value['file_key'] = $value['path'];
                                $newRecordPicture = $this->saveNewsFile('file', $newContentId, $value);
                                if($newRecordPicture == false){
                                    $checkUpdate = false;
                                    $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                }
                            }
                        }
                    }

                    if ($checkUpdate) {
                        $transaction->commit();

                        BackendHelper::saveUserLog('news', Yii::$app->user->identity->id, $newContentId, 'update news', 'แก้ไขข้อมูลข่าวสาร');

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
     * Deletes an existing News model.
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

            BackendHelper::saveUserLog('news', Yii::$app->user->identity->id, $id, 'delete news', 'ลบข้อมูลข่าวสาร');

            return $this->redirect(['index']);
        }
   
        throw new \yii\web\BadRequestHttpException('ลบข้อมูลไม่สำเร็จ');
    }


    private function saveNewsFile($type, $newContentId, $value)
    {
        $mediaModel = new NewsFile();
        $mediaModel->news_id = $newContentId;
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
     * Finds the News model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return News the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = News::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
