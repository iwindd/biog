<?php

namespace frontend\controllers\content;

use Yii;
use yii\web\Controller;
use common\components\_;
use yii\helpers\ArrayHelper;
use frontend\models\content\Type;
use frontend\models\content\Content;
use frontend\models\content\Picture;
use frontend\models\content\Taxonomy;
use frontend\components\ContentHelper;
use frontend\components\KeywordHelper;
use frontend\components\LocationHelper;
use frontend\components\TaxonomyHelper;
use frontend\models\content\Constants;
//use frontend\models\content\ContentPlant;
use frontend\models\content\ContentTaxonomy;

use frontend\models\ContentTeacher;
use frontend\models\ContentAnimal;
use frontend\models\ContentEcotourism;
use frontend\models\ContentFungi;
use frontend\models\ContentExpert;
use frontend\models\ContentPlant;
use frontend\models\ContentProduct;
use frontend\models\StudentTeacher;
use common\components\Helper;

use frontend\components\PermissionAccess;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\filters\VerbFilter;

use yii\web\ForbiddenHttpException;

use frontend\components\FrontendHelper;

class ApproveController extends Controller
{

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
                        'actions' => ['plant', 'animal', 'fungi', 'expert', 'ecotourism', 'product', 'delete'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) 
                        {
                            switch($action->id){
                                case 'plant':
                                case 'animal':
                                case 'fungi':
                                case 'expert':
                                case 'ecotourism':
                                case 'product':
                                    return PermissionAccess::FrontendAccess('approved_content', 'controller');
                                
                                case 'delete':
                                    return PermissionAccess::FrontendAccess('student_delete_content', 'controller');

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

    private $stackErrorMessage = [];
    private function setStackErrorMessage($message)
    {
        array_push($this->stackErrorMessage, $message);
    }

    private function setFlashSuccess()
    {
        _::setFlash('CREATE_CONTENT_SUCCESS', [
            'message' => 'บันทึกข้อมูลเรียบร้อย',
            'options' => ['class' => 'alert-success mt-4']
        ]);
    }

    private function setFlashError($error)
    {
        _::setFlash('CREATE_CONTENT_ERROR', [
            'title' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล',
            'message' => $error,
            'options' => ['class' => 'alert-danger mt-4'],
        ]);
    }

    private function commit($transaction, $newModel, $modelOld, $newContentId)
    {
        if (_::issetNotEmpty($this->stackErrorMessage)) {
            _::throwErrorModel($this->stackErrorMessage);
        } else {


            if ($newModel->status == 'rejected') {
                FrontendHelper::saveUserLog('content', Yii::$app->user->identity->id, $newContentId, 'update content '.FrontendHelper::getContentTypeById($newModel->type_id), 'ไม่อนุมัติเนื้อหาหมายเหตุ: '.$newModel->note);
            }else if ($newModel->status == 'approved') {
                if (!empty($note)) {
                    FrontendHelper::saveUserLog('content', Yii::$app->user->identity->id, $newContentId, 'update content '.FrontendHelper::getContentTypeById($newModel->type_id), 'อนุมัติเนื้อหาหมายเหตุ: '.$newModel->note);
                }else{
                    FrontendHelper::saveUserLog('content', Yii::$app->user->identity->id, $newContentId, 'update content '.FrontendHelper::getContentTypeById($newModel->type_id), 'อนุมัติเนื้อหา');
                }
            }else{
                FrontendHelper::saveUserLog('content', Yii::$app->user->identity->id, $newContentId, 'update content '.FrontendHelper::getContentTypeById($newModel->type_id), 'แก้ไขข้อมูลเนื้อหา');
            }

            if($newModel->active == 1 && $modelOld->getOldAttribute('status') != $newModel->status){
                Helper::sendMailUpdateStatusContent($modelOld, $newModel);
            }

            $transaction->commit();

            //FrontendHelper::saveUserLog('content', Yii::$app->user->identity->id, $newContentId, 'update content '.FrontendHelper::getContentTypeById($newModel->type_id), 'แก้ไขข้อมูลเนื้อหา');

            //$this->setFlashSuccess();
            return true;
        }
    }

    private function throwError($th)
    {
        if ($th->getCode() == _::STATUS_CODE_MODEL_ERROR) {
            $this->setFlashError(_::getErrorListMessageModel($th));
        } else {
            throw $th;
        }
    }

    private static function canApproved($id){
        $contentModel = ContentTeacher::find()->where(['id' => $id, 'active' => 1])->one();

        if(!empty($contentModel->created_by_user_id)){

            
            $checkcanUpdate = StudentTeacher::find()->where(['student_id' => $contentModel->created_by_user_id, 'teacher_id' => Yii::$app->user->identity->id])->one();

            if(empty($checkcanUpdate)){
                throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
            }
        
        }else{
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }

    }

    private function generateContentTitle($contentName, $contentTypeName)
    {
        return (object) [
            'mainTitle' => "อัพเดตสถานะเนื้อหา : $contentName",
            'secondaryTitle' => "อัพเดตสถานะเนื้อหา$contentTypeName"
        ];
    }

    /*
    public function actionPlant($id)
    {
        $contentModel = ContentTeacher::find()->where(['id' => $id, 'active' => 1])->one();
        $contentPlant = ContentPlant::find()->where(['content_id' => $id])->one();

        if(!empty($contentModel->created_by_user_id)){
            $checkcanUpdate = StudentTeacher::find()->where(['student_id' => $contentModel->created_by_user_id, 'teacher_id' => Yii::$app->user->identity->id])->one();

            if(empty($checkcanUpdate)){
                throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
            }
        
        }else{
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }

        if(empty($contentPlant)){
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }

        $pictureModel = new Picture();
        $typeModel = new Type();

        $POST = _::post();

        if ($POST) {
         
            try {

                if(!empty($POST['ContentTeacher']['status'])){

                    $note = "";

                    if(!empty($POST['ContentTeacher']['note'])){
                        $note = $POST['ContentTeacher']['note'];
                    }

                    $updateStatus = Helper::updateStatusRevisionContent($id, $POST['ContentTeacher']['status'], 0, $note);

                    if($updateStatus == true){

                        return $this->redirect('/content/views/teacher');
                    }
                }
                        
            } catch (\Exception $th) {
              
                throw $th;
            }
        } else {
            $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId(_::getValue($contentModel->id));
        }

        return $this->render('/user/content/approve-content', [
            'pageType' => 'plant',
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,

            'model' => (object) [
                'content' => $contentModel,
                'contentPlant' => $contentPlant,
                'picture' => $pictureModel,
                'type' => $typeModel,
            ]
        ]);
    } */


    public function actionPlant($id)
    {

        $this->canApproved($id);

        $contentConfig = ContentHelper::getContentConfig('plant');

        $contentModel = ContentHelper::getContentById($id);
        $contentModel->scenario = Constants::SCENARIO_PLANT;

        $contentPlantModel = ContentHelper::getContentPlantByContentId($contentModel->id);
        $pictureList = ContentHelper::getAllPictureByContentId($contentModel->id);

        $POST = _::post();

        if ($POST) {

            $revisionContentModel = clone $contentModel;
            $revisionContentPlantModel = clone $contentPlantModel;

            $revisionContentModel->load($POST);
            $revisionContentPlantModel->load($POST);
            $currentDateTime = _::getDateTimeYmdHis();


            $transaction = _::beginTransaction();
            try {

                $revisionContentModel->isNewRecord = true;
                $revisionContentModel->id = null;
                $revisionContentModel->attributes = $revisionContentModel->getAttributes();

                $imageInContent = ContentHelper::saveImageFromUpload($revisionContentModel, 'picture_path', $contentConfig->upload_folder_name);
                if (!$imageInContent && !_::isNull($imageInContent)) {
                    $this->setStackErrorMessage('อัปโหลดรูปภาพไม่สำเร็จ');
                }

                _::setupModel($revisionContentModel, [
                    'type_id' => $contentConfig->type_id,
                    'picture_path' => $imageInContent ? $imageInContent : $contentModel->getOldAttribute('picture_path'),
                    'content_source_id' => $contentModel->id,
                    'updated_by_user_id' => _::currentUserId(),
                    'updated_at' => $currentDateTime,
                    'status' => $revisionContentModel->status,
                    'active' => 1,
                ]);

                if (_::saveModel($revisionContentModel)) {

                    _::setupModel($contentModel, [
                        'type_id' => $contentConfig->type_id,
                        'active' => 0,
                        'updated_by_user_id' => _::currentUserId(),
                    ]);

                    if (_::saveModel($contentModel)) {

                        $revisionContentPlantModel->isNewRecord = true;
                        $revisionContentPlantModel->id = null;
                        $revisionContentPlantModel->attributes = $contentPlantModel->getAttributes();

                        _::setupModel($revisionContentPlantModel, [
                            'content_id' => $revisionContentModel->id,
                            'created_at' => $currentDateTime,
                            'updated_at' => $currentDateTime,
                        ]);

                        if (_::saveModel($revisionContentPlantModel)) {

                            $taxonomyList = ArrayHelper::getValue($POST, 'Content.taxonomy_list');
                            TaxonomyHelper::updateTaxonomyInContent($revisionContentModel->id, $taxonomyList, $currentDateTime);
                            $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId($revisionContentModel->id);

                            $errorMessageSaveMultipleImage = ContentHelper::saveMultipleImageFromUpload($revisionContentModel->id, $revisionContentModel, $contentConfig->upload_folder_name);

                            if (_::issetNotEmpty($errorMessageSaveMultipleImage)) {
                                $this->setStackErrorMessage($errorMessageSaveMultipleImage);
                            } else {

                                $updatePictureInRevisionContent = ContentHelper::updatePictureInRevisionContent($revisionContentModel->id, $contentModel->id);

                                if (!$updatePictureInRevisionContent) {
                                    $this->setStackErrorMessage('อัปเดตรูปภาพประกอบไม่สำเร็จ');
                                }
                            }

                            if ($this->commit($transaction, $revisionContentModel, $contentModel, $revisionContentModel->id )) {
                                return $this->redirect(["/content/views/teacher"]);
                                // return $this->redirect("/content/views/student");
                            }
                        }
                    }
                }
            } catch (\Exception $th) {
                $transaction->rollBack();
                $this->throwError($th);
            }
        } else {
            $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId(_::getValue($contentModel->id));
        }


        return $this->render('/user/content/create-update/wrapper_form_content', [
            'model' => (object) [
                'content' => $contentModel,
                'contentPlant' => $contentPlantModel,
            ],

            'data' => (object) [
                'pictureList' => $pictureList,
            ],

            'actionType' => 'update',
            'pageType' => $contentConfig->type,
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,

            'text' => $this->generateContentTitle($contentModel->name, $contentConfig->name)
        ]);
    }


    public function actionAnimal($id)
    {
        $this->canApproved($id);

        $contentConfig = ContentHelper::getContentConfig('animal');

        $contentModel = ContentHelper::getContentById($id);
        $contentModel->scenario = Constants::SCENARIO_PLANT;

        $contentAnimalModel = ContentHelper::getContentAnimalByContentId($contentModel->id);
        $pictureList = ContentHelper::getAllPictureByContentId($contentModel->id);

        $POST = _::post();

        if ($POST) {

            $revisionContentModel = clone $contentModel;
            $revisionContentAnimalModel = clone $contentAnimalModel;

            $revisionContentModel->load($POST);
            $revisionContentAnimalModel->load($POST);
            $currentDateTime = _::getDateTimeYmdHis();

            $transaction = _::beginTransaction();
            try {

                $revisionContentModel->isNewRecord = true;
                $revisionContentModel->id = null;
                $revisionContentModel->attributes = $revisionContentModel->getAttributes();

                $imageInContent = ContentHelper::saveImageFromUpload($revisionContentModel, 'picture_path', $contentConfig->upload_folder_name);
                if (!$imageInContent && !_::isNull($imageInContent)) {
                    $this->setStackErrorMessage('อัปโหลดรูปภาพไม่สำเร็จ');
                }

                _::setupModel($revisionContentModel, [
                    'type_id' => $contentConfig->type_id,
                    'picture_path' => $imageInContent ? $imageInContent : $contentModel->getOldAttribute('picture_path'),
                    'content_source_id' => $contentModel->id,
                    'updated_by_user_id' => _::currentUserId(),
                    'updated_at' => $currentDateTime,
                    'status' => $revisionContentModel->status,
                    'active' => 1,
                ]);

                if (_::saveModel($revisionContentModel)) {

                    _::setupModel($contentModel, [
                        'type_id' => $contentConfig->type_id,
                        'active' => 0,
                        'updated_by_user_id' => _::currentUserId(),
                    ]);

                    if (_::saveModel($contentModel)) {

                        $revisionContentAnimalModel->isNewRecord = true;
                        $revisionContentAnimalModel->id = null;
                        $revisionContentAnimalModel->attributes = $contentAnimalModel->getAttributes();
                        _::setupModel($revisionContentAnimalModel, [
                            'content_id' => $revisionContentModel->id,
                            'created_at' => $currentDateTime,
                            'updated_at' => $currentDateTime,
                        ]);
                    }

                    if (_::saveModel($revisionContentAnimalModel)) {

                        $taxonomyList = ArrayHelper::getValue($POST, 'Content.taxonomy_list');
                        TaxonomyHelper::updateTaxonomyInContent($revisionContentModel->id, $taxonomyList, $currentDateTime);
                        $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId(_::getValue($revisionContentModel->id));

                        $errorMessageSaveMultipleImage = ContentHelper::saveMultipleImageFromUpload($revisionContentModel->id, $revisionContentModel, $contentConfig->upload_folder_name);

                        if (_::issetNotEmpty($errorMessageSaveMultipleImage)) {
                            $this->setStackErrorMessage($errorMessageSaveMultipleImage);
                        } else {

                            $updatePictureInRevisionContent = ContentHelper::updatePictureInRevisionContent($revisionContentModel->id, $contentModel->id);

                            if (!$updatePictureInRevisionContent) {
                                $this->setStackErrorMessage('อัปเดตรูปภาพประกอบไม่สำเร็จ');
                            }
                        }

                        if ($this->commit($transaction, $revisionContentModel, $contentModel, $revisionContentModel->id )) {
                            return $this->redirect(["/content/views/teacher"]);
                        }
                    }
                }
            } catch (\Exception $th) {
                $transaction->rollBack();
                $this->throwError($th);
            }
        } else {
            $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId(_::getValue($contentModel->id));
        }


        return $this->render('/user/content/create-update/wrapper_form_content', [
            'model' => (object) [
                'content' => $contentModel,
                'contentAnimal' => $contentAnimalModel,
            ],

            'data' => (object) [
                'pictureList' => $pictureList,
            ],

            'actionType' => 'update',
            'pageType' => $contentConfig->type,
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,

            'text' => $this->generateContentTitle($contentModel->name, $contentConfig->name)
        ]);
    }

    public function actionFungi($id)
    {
        $this->canApproved($id);

        $contentConfig = ContentHelper::getContentConfig('fungi');

        $contentModel = ContentHelper::getContentById($id);
        $contentModel->scenario = Constants::SCENARIO_FUNGAI;

        $contentFungiModel = ContentHelper::getContentFungiByContentId($contentModel->id);
        $pictureList = ContentHelper::getAllPictureByContentId($contentModel->id);

        $POST = _::post();

        if ($POST) {
            $revisionContentModel = clone $contentModel;
            $revisionContentFungiModel = clone $contentFungiModel;

            $revisionContentModel->load($POST);
            $revisionContentFungiModel->load($POST);
            $currentDateTime = _::getDateTimeYmdHis();

            $transaction = _::beginTransaction();
            try {

                $revisionContentModel->isNewRecord = true;
                $revisionContentModel->id = null;
                $revisionContentModel->attributes = $revisionContentModel->getAttributes();

                $imageInContent = ContentHelper::saveImageFromUpload($revisionContentModel, 'picture_path', $contentConfig->upload_folder_name);
                if (!$imageInContent && !_::isNull($imageInContent)) {
                    $this->setStackErrorMessage('อัปโหลดรูปภาพไม่สำเร็จ');
                }

                _::setupModel($revisionContentModel, [
                    'type_id' => $contentConfig->type_id,
                    'picture_path' => $imageInContent ? $imageInContent : $contentModel->getOldAttribute('picture_path'),
                    'content_source_id' => $contentModel->id,
                    'updated_by_user_id' => _::currentUserId(),
                    'updated_at' => $currentDateTime,
                    'status' => $revisionContentModel->status,
                    'active' => 1,
                ]);

                if (_::saveModel($revisionContentModel)) {

                    _::setupModel($contentModel, [
                        'type_id' => $contentConfig->type_id,
                        'active' => 0,
                        'updated_by_user_id' => _::currentUserId(),
                    ]);

                    if (_::saveModel($contentModel)) {

                        $revisionContentFungiModel->isNewRecord = true;
                        $revisionContentFungiModel->id = null;
                        $revisionContentFungiModel->attributes = $contentFungiModel->getAttributes();

                        _::setupModel($revisionContentFungiModel, [
                            'content_id' => $revisionContentModel->id,
                            'created_at' => $currentDateTime,
                            'updated_at' => $currentDateTime,
                        ]);

                        if (_::saveModel($revisionContentFungiModel)) {

                            $taxonomyList = ArrayHelper::getValue($POST, 'Content.taxonomy_list');
                            TaxonomyHelper::updateTaxonomyInContent($revisionContentModel->id, $taxonomyList, $currentDateTime);
                            $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId($revisionContentModel->id);

                            $errorMessageSaveMultipleImage = ContentHelper::saveMultipleImageFromUpload($revisionContentModel->id, $revisionContentModel, $contentConfig->upload_folder_name);

                            if (_::issetNotEmpty($errorMessageSaveMultipleImage)) {
                                $this->setStackErrorMessage($errorMessageSaveMultipleImage);
                            } else {

                                $updatePictureInRevisionContent = ContentHelper::updatePictureInRevisionContent($revisionContentModel->id, $contentModel->id);

                                if (!$updatePictureInRevisionContent) {
                                    $this->setStackErrorMessage('อัปเดตรูปภาพประกอบไม่สำเร็จ');
                                }
                            }

                            if ($this->commit($transaction, $revisionContentModel, $contentModel, $revisionContentModel->id )) {
                                return $this->redirect(["/content/views/teacher"]);
                                // return $this->redirect("/content/views/student");
                            }
                        }
                    }
                }
            } catch (\Exception $th) {
                $transaction->rollBack();
                $this->throwError($th);
            }
        } else {
            $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId(_::getValue($contentModel->id));
        }


        return $this->render('/user/content/create-update/wrapper_form_content', [
            'model' => (object) [
                'content' => $contentModel,
                'contentFungi' => $contentFungiModel,
            ],

            'data' => (object) [
                'pictureList' => $pictureList,
            ],

            'actionType' => 'update',
            'pageType' => $contentConfig->type,
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,

            'text' => $this->generateContentTitle($contentModel->name, $contentConfig->name)
        ]);
    }

    public function actionExpert($id)
    {

        $this->canApproved($id);

        $contentConfig = ContentHelper::getContentConfig('expert');

        $contentModel = ContentHelper::getContentById($id);

        $contentExpertModel = ContentHelper::getContentExpertByContentId($contentModel->id);

        $pictureList = ContentHelper::getAllPictureByContentId($contentModel->id);

        $POST = _::post();

        if ($POST) {
            $revisionContentModel = clone $contentModel;
            $revisionContentExpertModel = clone $contentExpertModel;

            $revisionContentModel->load($POST);
            $revisionContentExpertModel->load($POST);
            $currentDateTime = _::getDateTimeYmdHis();

            $transaction = _::beginTransaction();
            try {

                $revisionContentModel->isNewRecord = true;
                $revisionContentModel->id = null;
                $revisionContentModel->attributes = $revisionContentModel->getAttributes();

                $imageInContent = ContentHelper::saveImageFromUpload($revisionContentModel, 'picture_path', $contentConfig->upload_folder_name);
                if (!$imageInContent && !_::isNull($imageInContent)) {
                    $this->setStackErrorMessage('อัปโหลดรูปภาพไม่สำเร็จ');
                }

                _::setupModel($revisionContentModel, [
                    'type_id' => $contentConfig->type_id,
                    'picture_path' => $imageInContent ? $imageInContent : $contentModel->getOldAttribute('picture_path'),
                    'content_source_id' => $contentModel->id,
                    'updated_by_user_id' => _::currentUserId(),
                    'updated_at' => $currentDateTime,
                    'status' => $revisionContentModel->status,
                    'active' => 1,
                ]);

                if (_::saveModel($revisionContentModel)) {

                    _::setupModel($contentModel, [
                        'type_id' => $contentConfig->type_id,
                        'active' => 0,
                        'updated_by_user_id' => _::currentUserId(),
                    ]);

                    if (_::saveModel($contentModel)) {

                        $revisionContentExpertModel->isNewRecord = true;
                        $revisionContentExpertModel->id = null;
                        $revisionContentExpertModel->attributes = $contentExpertModel->getAttributes();

                        _::setupModel($revisionContentExpertModel, [
                            'content_id' => $revisionContentModel->id,
                            'expert_birthdate' => _::formatDateYmdtodmY($revisionContentExpertModel->expert_birthdate),
                            'created_at' => $currentDateTime,
                            'updated_at' => $currentDateTime,
                        ]);

                        if (_::saveModel($revisionContentExpertModel)) {

                            $taxonomyList = ArrayHelper::getValue($POST, 'Content.taxonomy_list');
                            TaxonomyHelper::updateTaxonomyInContent($revisionContentModel->id, $taxonomyList, $currentDateTime);
                            $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId($revisionContentModel->id);

                            $errorMessageSaveMultipleImage = ContentHelper::saveMultipleImageFromUpload($revisionContentModel->id, $revisionContentModel, $contentConfig->upload_folder_name);

                            if (_::issetNotEmpty($errorMessageSaveMultipleImage)) {
                                $this->setStackErrorMessage($errorMessageSaveMultipleImage);
                            } else {

                                $updatePictureInRevisionContent = ContentHelper::updatePictureInRevisionContent($revisionContentModel->id, $contentModel->id);

                                if (!$updatePictureInRevisionContent) {
                                    $this->setStackErrorMessage('อัปเดตรูปภาพประกอบไม่สำเร็จ');
                                }
                            }

                            if ($this->commit($transaction, $revisionContentModel, $contentModel, $revisionContentModel->id )) {
                                return $this->redirect(["/content/views/teacher"]);
                                // return $this->redirect("/content/views/student");
                            }
                        }
                    }
                }
            } catch (\Exception $th) {
                $transaction->rollBack();
                $this->throwError($th);
            }
        } else {
            $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId(_::getValue($contentModel->id));
        }


        return $this->render('/user/content/create-update/wrapper_form_content', [
            'model' => (object) [
                'content' => $contentModel,
                'contentExpert' => $contentExpertModel,
            ],

            'data' => (object) [
                'pictureList' => $pictureList,
            ],

            'actionType' => 'update',
            'pageType' => $contentConfig->type,
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,

            'text' => $this->generateContentTitle($contentModel->name, $contentConfig->name)
        ]);
    }

    public function actionEcotourism($id)
    {

        $this->canApproved($id);

        $contentConfig = (object) ContentHelper::CONTENT_CONFIG['ecotourism'];

        $contentModel = ContentHelper::getContentById($id);

        $contentEcotourismModel = ContentHelper::getContentEcotourismByContentId($contentModel->id);

        $pictureList = ContentHelper::getAllPictureByContentId($contentModel->id);


        $POST = _::post();

        if ($POST) {
            $revisionContentModel = clone $contentModel;
            $revisionContentEcotourismModel = clone $contentEcotourismModel;

            $revisionContentModel->load($POST);
            $revisionContentEcotourismModel->load($POST);
            $currentDateTime = _::getDateTimeYmdHis();



            $transaction = _::beginTransaction();
            try {

                $revisionContentModel->isNewRecord = true;
                $revisionContentModel->id = null;
                $revisionContentModel->attributes = $revisionContentModel->getAttributes();

                $imageInContent = ContentHelper::saveImageFromUpload($revisionContentModel, 'picture_path', $contentConfig->upload_folder_name);
                if (!$imageInContent && !_::isNull($imageInContent)) {
                    $this->setStackErrorMessage('อัปโหลดรูปภาพไม่สำเร็จ');
                }

                _::setupModel($revisionContentModel, [
                    'type_id' => $contentConfig->type_id,
                    'picture_path' => $imageInContent ? $imageInContent : $contentModel->getOldAttribute('picture_path'),
                    'content_source_id' => $contentModel->id,
                    'updated_by_user_id' => _::currentUserId(),
                    'updated_at' => $currentDateTime,
                    'status' => $revisionContentModel->status,
                    'active' => 1,
                ]);

                if (_::saveModel($revisionContentModel)) {

                    _::setupModel($contentModel, [
                        'type_id' => $contentConfig->type_id,
                        'active' => 0,
                        'updated_by_user_id' => _::currentUserId(),
                    ]);

                    if (_::saveModel($contentModel)) {

                        $revisionContentEcotourismModel->isNewRecord = true;
                        $revisionContentEcotourismModel->id = null;
                        $revisionContentEcotourismModel->attributes = $contentEcotourismModel->getAttributes();

                        _::setupModel($revisionContentEcotourismModel, [
                            'content_id' => $revisionContentModel->id,
                            'created_at' => $currentDateTime,
                            'updated_at' => $currentDateTime,
                        ]);
                    }

                    if (_::saveModel($revisionContentEcotourismModel)) {

                        $taxonomyList = ArrayHelper::getValue($POST, 'Content.taxonomy_list');
                        TaxonomyHelper::updateTaxonomyInContent($revisionContentModel->id, $taxonomyList, $currentDateTime);
                        $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId($revisionContentModel->id);

                        $errorMessageSaveMultipleImage = ContentHelper::saveMultipleImageFromUpload($revisionContentModel->id, $revisionContentModel, $contentConfig->upload_folder_name);

                        if (_::issetNotEmpty($errorMessageSaveMultipleImage)) {
                            $this->setStackErrorMessage($errorMessageSaveMultipleImage);
                        } else {

                            $updatePictureInRevisionContent = ContentHelper::updatePictureInRevisionContent($revisionContentModel->id, $contentModel->id);

                            if (!$updatePictureInRevisionContent) {
                                $this->setStackErrorMessage('อัปเดตรูปภาพประกอบไม่สำเร็จ');
                            }
                        }

                        if ($this->commit($transaction, $revisionContentModel, $contentModel, $revisionContentModel->id )) {
                                return $this->redirect(["/content/views/teacher"]);
                                // return $this->redirect("/content/views/student");
                            }
                    }
                }
            } catch (\Exception $th) {
                $transaction->rollBack();
                $this->throwError($th);
            }
        } else {
            $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId(_::getValue($contentModel->id));
        }


        return $this->render('/user/content/create-update/wrapper_form_content', [
            'model' => (object) [
                'content' => $contentModel,
                'contentEcotourism' => $contentEcotourismModel,
            ],

            'data' => (object) [
                'pictureList' => $pictureList,
            ],

            'actionType' => 'update',
            'pageType' => $contentConfig->type,
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,

            'text' => $this->generateContentTitle($contentModel->name, $contentConfig->name)
        ]);
    }

    public function actionProduct($id)
    {
        $this->canApproved($id);

        $contentConfig = ContentHelper::getContentConfig('product');

        $contentModel = ContentHelper::getContentById($id);
        $contentModel->scenario = Constants::SCENARIO_PRODUCT;

        $contentProductModel = ContentHelper::getContentProductByContentId($contentModel->id);

        $pictureList = ContentHelper::getAllPictureByContentId($contentModel->id);

        $POST = _::post();

        if ($POST) {
            $revisionContentModel = clone $contentModel;
            $revisionContentProductModel = clone $contentProductModel;

            $revisionContentModel->load($POST);
            $revisionContentProductModel->load($POST);
            $currentDateTime = _::getDateTimeYmdHis();


            $transaction = _::beginTransaction();
            try {

                $revisionContentModel->isNewRecord = true;
                $revisionContentModel->id = null;
                $revisionContentModel->attributes = $revisionContentModel->getAttributes();

                $imageInContent = ContentHelper::saveImageFromUpload($revisionContentModel, 'picture_path', $contentConfig->upload_folder_name);
                if (!$imageInContent && !_::isNull($imageInContent)) {
                    $this->setStackErrorMessage('อัปโหลดรูปภาพไม่สำเร็จ');
                }

                _::setupModel($revisionContentModel, [
                    'type_id' => $contentConfig->type_id,
                    'picture_path' => $imageInContent ? $imageInContent : $contentModel->getOldAttribute('picture_path'),
                    'content_source_id' => $contentModel->id,
                    'updated_by_user_id' => _::currentUserId(),
                    'updated_at' => $currentDateTime,
                    'status' => $revisionContentModel->status,
                    'active' => 1,
                ]);

                if (_::saveModel($revisionContentModel)) {

                    _::setupModel($contentModel, [
                        'type_id' => $contentConfig->type_id,
                        'active' => 0,
                        'updated_by_user_id' => _::currentUserId(),
                    ]);

                    if (_::saveModel($contentModel)) {

                        $revisionContentProductModel->isNewRecord = true;
                        $revisionContentProductModel->id = null;
                        $revisionContentProductModel->attributes = $contentProductModel->getAttributes();

                        _::setupModel($revisionContentProductModel, [
                            'content_id' => $revisionContentModel->id,
                            'created_at' => $currentDateTime,
                            'updated_at' => $currentDateTime,
                        ]);

                        if (_::saveModel($revisionContentProductModel)) {

                            $taxonomyList = ArrayHelper::getValue($POST, 'Content.taxonomy_list');
                            TaxonomyHelper::updateTaxonomyInContent($revisionContentModel->id, $taxonomyList, $currentDateTime);
                            $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId($revisionContentModel->id);

                            $errorMessageSaveMultipleImage = ContentHelper::saveMultipleImageFromUpload($revisionContentModel->id, $revisionContentModel, $contentConfig->upload_folder_name);

                            if (_::issetNotEmpty($errorMessageSaveMultipleImage)) {
                                $this->setStackErrorMessage($errorMessageSaveMultipleImage);
                            } else {

                                $updatePictureInRevisionContent = ContentHelper::updatePictureInRevisionContent($revisionContentModel->id, $contentModel->id);

                                if (!$updatePictureInRevisionContent) {
                                    $this->setStackErrorMessage('อัปเดตรูปภาพประกอบไม่สำเร็จ');
                                }
                            }

                            if ($this->commit($transaction, $revisionContentModel, $contentModel, $revisionContentModel->id )) {
                                return $this->redirect(["/content/views/teacher"]);
                                // return $this->redirect("/content/views/student");
                            }
                        }
                    }
                }
            } catch (\Exception $th) {
                $transaction->rollBack();
                $this->throwError($th);
            }
        } else {
            $contentModel->taxonomy_list = TaxonomyHelper::getTaxonomyListByContentId(_::getValue($contentModel->id));
        }


        return $this->render('/user/content/create-update/wrapper_form_content', [
            'model' => (object) [
                'content' => $contentModel,
                'contentProduct' => $contentProductModel,
            ],

            'data' => (object) [
                'pictureList' => $pictureList,
            ],

            'actionType' => 'update',
            'pageType' => $contentConfig->type,
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,

            'text' => $this->generateContentTitle($contentModel->name, $contentConfig->name)
        ]);
    }

    public function actionDelete($id)
    {
        $content = Helper::updateStatusRevisionContent($id, '', 1);
        if ($content['status'] == 'success') {
            return $this->redirect(['/content/views/student']);
        }
        if (!empty($content['error'])) {
            throw new \yii\web\BadRequestHttpException('ไม่สามารถลบข้อมูลเนื้อหานี้ได้เนื่องจาก: '.$content['error'][0]);
        }else{
            throw new \yii\web\BadRequestHttpException('ลบข้อมูลเนื้อหาไม่สำเร็จ');
        }
    }
}
