<?php

namespace backend\controllers;

use Yii;
use backend\models\Knowledge;
use backend\models\KnowledgeSearch;
use backend\models\KnowledgeFile;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\BackendHelper;

use common\components\Upload;
use common\components\Helper;


/**
 * KnowledgeController implements the CRUD actions for Knowledge model.
 */
class KnowledgeController extends Controller
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
                                    return PermissionAccess::BackendAccess('knowledge_list', 'controller');
                                break;

                                case 'create':
                                    return PermissionAccess::BackendAccess('knowledge_create', 'controller');
                                break;

                                case 'update':
                                case 'teacher-student':
                                    return PermissionAccess::BackendAccess('knowledge_update', 'controller');
                                break;

                                case 'view':
                                case 'teacher':
                                    return PermissionAccess::BackendAccess('knowledge_view', 'controller');
                                break;

                                case 'delete':
                                    return PermissionAccess::BackendAccess('knowledge_delete', 'controller');
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
     * Lists all Knowledge models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new KnowledgeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Knowledge model.
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
     * Creates a new Knowledge model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Knowledge();
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

                
                $mainPicture = Upload::uploadPictureNoPermission($model, 'knowledge', '', 0, 'picture_path');
                if (!empty($mainPicture)) {
    
                    if ($mainPicture != 'error') {
                        $model->picture_path = $mainPicture;
                    }else{
                        $case_error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                    }
                }
                
                if ($model->save()) {

                    // upload multiple image
                    $files = Upload::uploadsNoPermimission($model, 'knowledge');
                    if (!empty($files)) {
                        if ($files['success_upload'] == 1) {
                            if (!empty($files['data'])) {
                                foreach ($files['data'] as $value) {
                                    $mediaModel = new KnowledgeFile();
                                    $mediaModel->knowledge_id = $model->id;
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
                    $documents = Upload::uploadsNoPermimission($model, 'knowledge', 'document');
                    // printr($documents);
                    if (!empty($documents)) {
                        if ($documents['success_upload'] == 1) {
                            if (!empty($documents['data'])) {
                                foreach ($documents['data'] as $value) {
                                    $mediaModel = new KnowledgeFile();
                                    $mediaModel->knowledge_id = $model->id;
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

                            BackendHelper::saveUserLog('knowledge', Yii::$app->user->identity->id, $model->id, 'create knowledge', 'เพิ่มข้อมูลคลังความรู้');
                            
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
     * Updates an existing Knowledge model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $case_error = array();

        $mediaModel = KnowledgeFile::find()->where(['knowledge_id' => $id])->all();

        $modelNew = new Knowledge();

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
                $latestContentId = Helper::getKnowledgeIDActive($model->id);

                if( $model->knowledge_root_id == 0){
                    $modelNew->knowledge_root_id = $latestContentId;   
                }else{
                    $modelNew->knowledge_root_id = $model->knowledge_root_id;   
                }

                $modelNew->knowledge_source_id = $latestContentId;
 
                Knowledge::updateAll(['active' => '0'], ['knowledge_root_id' => $modelNew->knowledge_root_id]);
                Knowledge::updateAll(['active' => '0'], ['id' => $modelNew->knowledge_root_id]);

                $del = 0;
                if(!empty($_POST['deletePic'])){
                    $del = $_POST['deletePic'];
                }

                $mainPicture = Upload::uploadPictureNoPermission($modelNew, 'knowledge', $model->getOldAttribute('picture_path'), $del, 'picture_path');
                
                if (!empty($mainPicture)) {
                    if ($mainPicture == 'error') {
                        $error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                    }else if($mainPicture == 'remove'){
                        $modelNew->picture_path = '';
                    }else{
                        $modelNew->picture_path = $mainPicture;
                    }
                }

                // print '<pre>';
                // print_r($mainPicture);
                // print '</pre>';
                // exit();

                // // delete image
                // $deleteImage = explode('/', $post['removeImage']);
                // if (!empty($deleteImage)) {
                //     foreach ($deleteImage as $imageId) {
                //         if (!empty($imageId)) {
                //             $imageItem = KnowledgeFile::findOne($imageId);
                //             if (!empty($imageItem)) {
                //                 $checkRemovefile = Upload::removeFileNoPermission('knowledge', $imageItem->path);
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
                //             $documentItem = KnowledgeFile::findOne($documentId);
                //             if (!empty($documentItem)) {
                //                 $checkRemovefile = Upload::removeFileNoPermission('knowledge', $documentItem->path);
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
                    $files = Upload::uploadsNoPermimission($modelNew, 'knowledge');
                    if (!empty($files)) {
                        if ($files['success_upload'] == 1) {
                            if (!empty($files['data'])) {
                                foreach ($files['data'] as $value) {
                                    $mediaModel = new KnowledgeFile();
                                    $mediaModel->knowledge_id = $newContentId;
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
                        $medias = KnowledgeFile::find()->where(['not in', 'id', $imagesId])->andWhere(['knowledge_id' => $latestContentId, 'application_type' => 'image'])->asArray()->all();
                        if(!empty($medias)){
                            foreach ($medias as $value) {
                                $value['file_display_name'] = $value['name'];
                                $value['file_key'] = $value['path'];
                                $newRecordPicture = $this->saveKnowledgeFile('image', $newContentId, $value);
                                if($newRecordPicture == false){
                                    $checkUpdate = false;
                                    $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                }
                            }
                        }
                    }else{
                        $medias = KnowledgeFile::find()->where(['knowledge_id' => $latestContentId, 'application_type' => 'image'])->asArray()->all();
                        if(!empty($medias)){
                            foreach ($medias as $value) {
                                $value['file_display_name'] = $value['name'];
                                $value['file_key'] = $value['path'];
                                $newRecordPicture = $this->saveKnowledgeFile('image', $newContentId, $value);
                                if($newRecordPicture == false){
                                    $checkUpdate = false;
                                    $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                }
                            }
                        }
                    }



                    // upload multiple files
                    $documents = Upload::uploadsNoPermimission($modelNew, 'knowledge', 'document');
                    // printr($documents);
                    if (!empty($documents)) {
                        if ($documents['success_upload'] == 1) {
                            if (!empty($documents['data'])) {
                                foreach ($documents['data'] as $value) {
                                    $mediaModel = new KnowledgeFile();
                                    $mediaModel->knowledge_id = $newContentId;
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
                        $medias = KnowledgeFile::find()->where(['not in', 'id', $docId])->andWhere(['knowledge_id' => $latestContentId, 'application_type' => 'file'])->asArray()->all();
                        if(!empty($medias)){
                            foreach ($medias as $value) {
                                $value['file_display_name'] = $value['name'];
                                $value['file_key'] = $value['path'];
                                $newRecordPicture = $this->saveKnowledgeFile('file',$newContentId, $value);
                                if($newRecordPicture == false){
                                    $checkUpdate = false;
                                    $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                }
                            }
                        }
                    }else{
                        $medias = KnowledgeFile::find()->where(['knowledge_id' => $latestContentId, 'application_type' => 'file'])->asArray()->all();
                        if(!empty($medias)){
                            foreach ($medias as $value) {
                                $value['file_display_name'] = $value['name'];
                                $value['file_key'] = $value['path'];
                                $newRecordPicture = $this->saveKnowledgeFile('file', $newContentId, $value);
                                if($newRecordPicture == false){
                                    $checkUpdate = false;
                                    $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                }
                            }
                        }
                    }

                    if ($checkUpdate) {
                        $transaction->commit();

                        BackendHelper::saveUserLog('knowledge', Yii::$app->user->identity->id, $newContentId, 'update knowledge', 'แก้ไขข้อมูลคลังความรู้');

                        return $this->redirect(['view', 'id' => $newContentId]);
                    }
                }

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
            
        }

        // print "<pre>";
        // print_r($model);
        // print '</pre>';
        // exit();

        return $this->render('update', [
            'model' => $model,
            'case_error' => $case_error,
            'mediaModel' => $mediaModel,
        ]);
    }

    /**
     * Deletes an existing Knowledge model.
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

            BackendHelper::saveUserLog('knowledge', Yii::$app->user->identity->id, $id, 'delete knowledge', 'ลบข้อมูลคลังความรู้');

            return $this->redirect(['index']);
        }
   
        throw new \yii\web\BadRequestHttpException('ลบข้อมูลไม่สำเร็จ');
    }


    private function saveKnowledgeFile($type, $newContentId, $value)
    {
        $mediaModel = new KnowledgeFile();
        $mediaModel->knowledge_id = $newContentId;
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
     * Finds the Knowledge model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Knowledge the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Knowledge::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
