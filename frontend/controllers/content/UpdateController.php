<?php

namespace frontend\controllers\content;

use Yii;
use yii\web\Controller;
use common\components\_;
use yii\filters\AccessRule;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use frontend\models\content\Type;
use frontend\models\content\Content;
use frontend\models\content\Picture;
use frontend\models\content\Taxonomy;
use frontend\components\ContentHelper;
use frontend\components\KeywordHelper;
use frontend\models\content\Constants;
use frontend\components\LocationHelper;
use frontend\components\TaxonomyHelper;
use frontend\components\PermissionAccess;
use frontend\models\content\ContentPlant;
use frontend\models\content\ContentTaxonomy;
use frontend\components\FrontendHelper;

class UpdateController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className()
                ],
                'rules' => [
                    [
                        'actions' => ['plant', 'animal', 'fungi', 'expert', 'ecotourism', 'product'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            switch ($action->id) {
                                case 'plant':
                                case 'animal':
                                case 'fungi':
                                case 'expert':
                                case 'ecotourism':
                                case 'product':
                                    if (
                                        PermissionAccess::FrontendAccess('student_create_content', 'controller') &&
                                        PermissionAccess::FrontendAccess('student_update_content', 'controller')
                                    ) {
                                        return true;
                                    }
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

    private function commit($transaction, $newModel, $newContentId)
    {
        if (_::issetNotEmpty($this->stackErrorMessage)) {
            _::throwErrorModel($this->stackErrorMessage);
        } else {
            $transaction->commit();

            FrontendHelper::saveUserLog('content', Yii::$app->user->identity->id, $newContentId, 'update content '.FrontendHelper::getContentTypeById($newModel->type_id), 'แก้ไขข้อมูลเนื้อหา');

            $this->setFlashSuccess();
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

    private function generateContentTitle($contentName, $contentTypeName)
    {
        return (object) [
            'mainTitle' => "แก้ไขข้อมูล : $contentName",
            'secondaryTitle' => "การแก้ไขข้อมูล$contentTypeName"
        ];
    }

    public function actionPlant($id)
    {
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
                    'latitude' => $POST['Content']['latitude'],
                    'longitude' => $POST['Content']['longitude'],
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
                        //$revisionContentPlantModel->attributes = $contentPlantModel->getAttributes();

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

                            if ($this->commit($transaction, $revisionContentModel, $revisionContentModel->id )) {
                                return $this->redirect(["/content/update/{$contentConfig->type}/{$revisionContentModel->id}"]);
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
                    'latitude' => $POST['Content']['latitude'],
                    'longitude' => $POST['Content']['longitude'],
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
                        //$revisionContentAnimalModel->attributes = $contentAnimalModel->getAttributes();

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

                        if ($this->commit($transaction, $revisionContentModel, $revisionContentModel->id )) {
                            return $this->redirect(["/content/update/{$contentConfig->type}/{$revisionContentModel->id}"]);
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
                    'latitude' => $POST['Content']['latitude'],
                    'longitude' => $POST['Content']['longitude'],
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
                        //$revisionContentFungiModel->attributes = $contentFungiModel->getAttributes();

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

                            if ($this->commit($transaction, $revisionContentModel, $revisionContentModel->id )) {
                                return $this->redirect(["/content/update/{$contentConfig->type}/{$revisionContentModel->id}"]);
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
                    'latitude' => $POST['Content']['latitude'],
                    'longitude' => $POST['Content']['longitude'],
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
                        //$revisionContentExpertModel->attributes = $contentExpertModel->getAttributes();

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

                            if ($this->commit($transaction, $revisionContentModel, $revisionContentModel->id )) {
                                return $this->redirect(["/content/update/{$contentConfig->type}/{$revisionContentModel->id}"]);
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
                    'latitude' => $POST['Content']['latitude'],
                    'longitude' => $POST['Content']['longitude'],
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
                        //$revisionContentEcotourismModel->attributes = $contentEcotourismModel->getAttributes();

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

                        if ($this->commit($transaction, $revisionContentModel, $revisionContentModel->id )) {
                            return $this->redirect(["/content/update/{$contentConfig->type}/{$revisionContentModel->id}"]);
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
                    'latitude' => $POST['Content']['latitude'],
                    'longitude' => $POST['Content']['longitude'],
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
                        //$revisionContentProductModel->attributes = $contentProductModel->getAttributes();

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

                            if ($this->commit($transaction, $revisionContentModel, $revisionContentModel->id )) {
                                return $this->redirect(["/content/update/{$contentConfig->type}/{$revisionContentModel->id}"]);
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
}
