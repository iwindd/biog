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
use frontend\components\ContentHelper;
use frontend\models\content\Constants;
use frontend\components\TaxonomyHelper;
use frontend\components\PermissionAccess;
use frontend\models\content\ContentFungi;
use frontend\models\content\ContentPlant;
use frontend\models\content\ContentAnimal;
use frontend\models\content\ContentExpert;
use frontend\models\content\ContentProduct;
use frontend\models\content\ContentEcotourism;
use frontend\models\content\ContentStatistics;
use frontend\components\FrontendHelper;

class CreateController extends Controller
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
            'body' => 'บันทึกข้อมูลเรียบร้อย',
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

            FrontendHelper::saveUserLog('content', Yii::$app->user->identity->id, $newContentId, 'create content '.FrontendHelper::getContentTypeById($newModel->type_id), 'เพิ่มข้อมูลเนื้อหา');

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

    private function generateContentTitle($contentName)
    {
        return (object) [
            'mainTitle' => 'การนำเข้าข้อมูลของฉัน',
            'secondaryTitle' => "การนำเข้าข้อมูล${contentName}ใหม่"
        ];
    }

    public function actionPlant()
    {
        $contentConfig = ContentHelper::getContentConfig('plant');

        $scenario = ['scenario' => Constants::SCENARIO_PLANT];

        $contentModel = new Content($scenario);
        $contentPlantModel = new ContentPlant();
        $pictureModel = new Picture();
        $contentStatisticsModel = new ContentStatistics();

        $POST = _::post();

        if ($POST) {
            $contentModel->load($POST);
            $contentPlantModel->load($POST);
            $currentDateTime = _::getDateTimeYmdHis();

            $transaction = _::beginTransaction();
            try {

                $imageInContent = ContentHelper::saveImageFromUpload($contentModel, 'picture_path', $contentConfig->upload_folder_name);
                if (!$imageInContent) {
                    $this->setStackErrorMessage('อัปโหลดรูปภาพไม่สำเร็จ');
                }

                _::setupModel($contentModel, [
                    'type_id' => $contentConfig->type_id,
                    'picture_path' => $imageInContent,
                    'description' => $contentPlantModel->features,
                    'created_by_user_id' => _::currentUserId(),
                    'updated_by_user_id' => _::currentUserId(),
                    'latitude' => $POST['Content']['latitude'],
                    'longitude' => $POST['Content']['longitude'],
                    'created_at' => $currentDateTime,
                    'updated_at' => $currentDateTime,
                    'status' => 'pending',
                    'active' => 1
                ]);

                if (_::saveModel($contentModel)) {

                    $errorMessageSaveMultipleImage = ContentHelper::saveMultipleImageFromUpload($contentModel->id, $contentModel, $contentConfig->upload_folder_name);
                    if (_::issetNotEmpty($errorMessageSaveMultipleImage)) {
                        $this->setStackErrorMessage('อัปโหลดรูปภาพประกอบไม่สำเร็จ');
                    }

                    _::setupModel($contentModel, [
                        'content_root_id' => $contentModel->id,
                    ]);
                    $contentModel->update();

                    $taxonomyList = ArrayHelper::getValue($POST, 'Content.taxonomy_list');
                    if (_::issetNotEmpty($taxonomyList)) {
                        TaxonomyHelper::insertTaxonomyInContent($contentModel->id, $taxonomyList, $currentDateTime);
                    }

                    _::setupModel($contentPlantModel, [
                        'content_id' => $contentModel->id,
                        'created_at' => $currentDateTime,
                        'updated_at' => $currentDateTime,
                    ]);

                    if (_::saveModel($contentPlantModel)) {

                        _::setupModel($contentStatisticsModel, [
                            'content_root_id' => $contentModel->id,
                            'pageview' => 0,
                            'like_count' => 0
                        ]);

                        if (_::saveModel($contentStatisticsModel)) {

                            if ($this->commit($transaction, $contentModel, $contentModel->id )) {
                                // return $this->redirect(["/content/update/{$contentConfig->type}/{$contentModel->id}"]);
                                return $this->redirect("/content/views/student");
                            }
                        }
                    }
                }
            } catch (\Exception $th) {
                $transaction->rollBack();
                $this->throwError($th);
            }
        }

        return $this->render('/user/content/create-update/wrapper_form_content', [
            'model' => (object) [
                'content' => $contentModel,
                'contentPlant' => $contentPlantModel,
                'picture' => $pictureModel,
            ],

            'data' => (object) [
                'pictureList' => [],
            ],

            'actionType' => 'create',
            'pageType' => $contentConfig->type,
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,

            'text' => $this->generateContentTitle($contentConfig->name)
        ]);
    }


    public function actionAnimal()
    {
        $contentConfig = ContentHelper::getContentConfig('animal');

        $scenario = ['scenario' => Constants::SCENARIO_PLANT];

        $contentModel = new Content($scenario);
        $contentAnimalModel = new ContentAnimal();
        $pictureModel = new Picture();
        $contentStatisticsModel = new ContentStatistics();

        $POST = _::post();

        if ($POST) {
            $contentModel->load($POST);
            $contentAnimalModel->load($POST);
            $currentDateTime = _::getDateTimeYmdHis();

            $transaction = _::beginTransaction();
            try {

                $imageInContent = ContentHelper::saveImageFromUpload($contentModel, 'picture_path', $contentConfig->upload_folder_name);
                if (!$imageInContent) {
                    $this->setStackErrorMessage('อัปโหลดรูปภาพไม่สำเร็จ');
                }

                _::setupModel($contentModel, [
                    'type_id' => $contentConfig->type_id,
                    'picture_path' => $imageInContent,
                    'description' => $contentAnimalModel->features,
                    'created_by_user_id' => _::currentUserId(),
                    'updated_by_user_id' => _::currentUserId(),
                    'latitude' => $POST['Content']['latitude'],
                    'longitude' => $POST['Content']['longitude'],
                    'created_at' => $currentDateTime,
                    'updated_at' => $currentDateTime,
                    'status' => 'pending',
                    'active' => 1
                ]);


                if (_::saveModel($contentModel)) {

                    $errorMessageSaveMultipleImage = ContentHelper::saveMultipleImageFromUpload($contentModel->id, $contentModel, $contentConfig->upload_folder_name);
                    if (_::issetNotEmpty($errorMessageSaveMultipleImage)) {
                        $this->setStackErrorMessage('อัปโหลดรูปภาพประกอบไม่สำเร็จ');
                    }

                    _::setupModel($contentModel, [
                        'content_root_id' => $contentModel->id,
                    ]);
                    $contentModel->update();

                    $taxonomyList = ArrayHelper::getValue($POST, 'Content.taxonomy_list');
                    if (_::issetNotEmpty($taxonomyList)) {
                        TaxonomyHelper::insertTaxonomyInContent($contentModel->id, $taxonomyList, $currentDateTime);
                    }

                    _::setupModel($contentAnimalModel, [
                        'content_id' => $contentModel->id,
                        'created_at' => $currentDateTime,
                        'updated_at' => $currentDateTime,
                    ]);

                    if (_::saveModel($contentAnimalModel)) {

                        _::setupModel($contentStatisticsModel, [
                            'content_root_id' => $contentModel->id,
                            'pageview' => 0,
                            'like_count' => 0
                        ]);

                        if (_::saveModel($contentStatisticsModel)) {

                            if ($this->commit($transaction, $contentModel, $contentModel->id )) {
                                // return $this->redirect(["/content/update/{$contentConfig->type}/{$contentModel->id}"]);
                                return $this->redirect("/content/views/student");
                            }
                        }
                    }
                }
            } catch (\Exception $th) {
                $transaction->rollBack();
                $this->throwError($th);
            }
        }


        return $this->render('/user/content/create-update/wrapper_form_content', [
            'model' => (object) [
                'content' => $contentModel,
                'contentAnimal' => $contentAnimalModel,
                'picture' => $pictureModel,
            ],

            'data' => (object) [
                'pictureList' => [],
            ],

            'actionType' => 'create',
            'pageType' => $contentConfig->type,
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,

            'text' => $this->generateContentTitle($contentConfig->name)
        ]);
    }

    public function actionFungi()
    {
        $contentConfig = ContentHelper::getContentConfig('fungi');

        $scenario = ['scenario' => Constants::SCENARIO_FUNGAI];

        $contentModel = new Content($scenario);
        $contentFungiModel = new ContentFungi();
        $pictureModel = new Picture();
        $contentStatisticsModel = new ContentStatistics();

        $POST = _::post();

        if ($POST) {
            $contentModel->load($POST);
            $contentFungiModel->load($POST);
            $currentDateTime = _::getDateTimeYmdHis();

            $transaction = _::beginTransaction();
            try {

                $imageInContent = ContentHelper::saveImageFromUpload($contentModel, 'picture_path', $contentConfig->upload_folder_name);
                if (!$imageInContent) {
                    $this->setStackErrorMessage('อัปโหลดรูปภาพไม่สำเร็จ');
                }

                _::setupModel($contentModel, [
                    'type_id' => $contentConfig->type_id,
                    'picture_path' => $imageInContent,
                    'description' => $contentFungiModel->features,
                    'created_by_user_id' => _::currentUserId(),
                    'updated_by_user_id' => _::currentUserId(),
                    'latitude' => $POST['Content']['latitude'],
                    'longitude' => $POST['Content']['longitude'],
                    'created_at' => $currentDateTime,
                    'updated_at' => $currentDateTime,
                    'status' => 'pending',
                    'active' => 1
                ]);

                if (_::saveModel($contentModel)) {

                    $errorMessageSaveMultipleImage = ContentHelper::saveMultipleImageFromUpload($contentModel->id, $contentModel, $contentConfig->upload_folder_name);
                    if (_::issetNotEmpty($errorMessageSaveMultipleImage)) {
                        $this->setStackErrorMessage('อัปโหลดรูปภาพประกอบไม่สำเร็จ');
                    }

                    _::setupModel($contentModel, [
                        'content_root_id' => $contentModel->id,
                    ]);
                    $contentModel->update();

                    $taxonomyList = ArrayHelper::getValue($POST, 'Content.taxonomy_list');
                    if (_::issetNotEmpty($taxonomyList)) {
                        TaxonomyHelper::insertTaxonomyInContent($contentModel->id, $taxonomyList, $currentDateTime);
                    }

                    _::setupModel($contentFungiModel, [
                        'content_id' => $contentModel->id,
                        'created_at' => $currentDateTime,
                        'updated_at' => $currentDateTime,
                    ]);

                    if (_::saveModel($contentFungiModel)) {

                        _::setupModel($contentStatisticsModel, [
                            'content_root_id' => $contentModel->id,
                            'pageview' => 0,
                            'like_count' => 0
                        ]);

                        if (_::saveModel($contentStatisticsModel)) {

                            if ($this->commit($transaction, $contentModel, $contentModel->id )) {
                                // return $this->redirect(["/content/update/{$contentConfig->type}/{$contentModel->id}"]);
                                return $this->redirect("/content/views/student");
                            }
                        }
                    }
                }
            } catch (\Exception $th) {
                $transaction->rollBack();
                $this->throwError($th);
            }
        }


        return $this->render('/user/content/create-update/wrapper_form_content', [
            'model' => (object) [
                'content' => $contentModel,
                'contentFungi' => $contentFungiModel,
                'picture' => $pictureModel,
            ],

            'data' => (object) [
                'pictureList' => [],
            ],

            'actionType' => 'create',
            'pageType' => $contentConfig->type,
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,

            'text' => $this->generateContentTitle($contentConfig->name)
        ]);
    }

    public function actionExpert()
    {
        $contentConfig = ContentHelper::getContentConfig('expert');

        $scenario = ['scenario' => Constants::SCENARIO_EXPERT];

        $contentModel = new Content($scenario);
        $contentExpoertModel = new ContentExpert();
        $pictureModel = new Picture();
        $contentStatisticsModel = new ContentStatistics();

        $POST = _::post();

        if ($POST) {
            $contentModel->load($POST);
            $contentExpoertModel->load($POST);
            $currentDateTime = _::getDateTimeYmdHis();

            $transaction = _::beginTransaction();
            try {

                $imageInContent = ContentHelper::saveImageFromUpload($contentModel, 'picture_path', $contentConfig->upload_folder_name);
                if (!$imageInContent) {
                    $this->setStackErrorMessage('อัปโหลดรูปภาพไม่สำเร็จ');
                }

                _::setupModel($contentModel, [
                    'type_id' => $contentConfig->type_id,
                    'picture_path' => $imageInContent,
                    'created_by_user_id' => _::currentUserId(),
                    'updated_by_user_id' => _::currentUserId(),
                    'latitude' => $POST['Content']['latitude'],
                    'longitude' => $POST['Content']['longitude'],
                    'created_at' => $currentDateTime,
                    'updated_at' => $currentDateTime,
                    'status' => 'pending',
                    'active' => 1
                ]);

                if (_::saveModel($contentModel)) {

                    $errorMessageSaveMultipleImage = ContentHelper::saveMultipleImageFromUpload($contentModel->id, $contentModel, $contentConfig->upload_folder_name);
                    if (_::issetNotEmpty($errorMessageSaveMultipleImage)) {
                        $this->setStackErrorMessage('อัปโหลดรูปภาพประกอบไม่สำเร็จ');
                    }

                    _::setupModel($contentModel, [
                        'content_root_id' => $contentModel->id,
                    ]);
                    $contentModel->update();

                    $taxonomyList = ArrayHelper::getValue($POST, 'Content.taxonomy_list');
                    if (_::issetNotEmpty($taxonomyList)) {
                        TaxonomyHelper::insertTaxonomyInContent($contentModel->id, $taxonomyList, $currentDateTime);
                    }

                    _::setupModel($contentExpoertModel, [
                        'content_id' => $contentModel->id,
                        'expert_birthdate' => _::formatDateYmdtodmY($contentExpoertModel->expert_birthdate),
                        'created_at' => $currentDateTime,
                        'updated_at' => $currentDateTime,
                    ]);

                    if (_::saveModel($contentExpoertModel)) {

                        _::setupModel($contentStatisticsModel, [
                            'content_root_id' => $contentModel->id,
                            'pageview' => 0,
                            'like_count' => 0
                        ]);

                        if (_::saveModel($contentStatisticsModel)) {

                            if ($this->commit($transaction, $contentModel, $contentModel->id )) {
                                // return $this->redirect(["/content/update/{$contentConfig->type}/{$contentModel->id}"]);
                                return $this->redirect("/content/views/student");
                            }
                        }
                    }
                }
            } catch (\Exception $th) {
                $transaction->rollBack();
                $this->throwError($th);
            }
        }


        return $this->render('/user/content/create-update/wrapper_form_content', [
            'model' => (object) [
                'content' => $contentModel,
                'contentExpert' => $contentExpoertModel,
                'picture' => $pictureModel,
            ],

            'data' => (object) [
                'pictureList' => [],
            ],

            'actionType' => 'create',
            'pageType' => $contentConfig->type,
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,


            'text' => $this->generateContentTitle($contentConfig->name)
        ]);
    }

    public function actionEcotourism()
    {
        $contentConfig = ContentHelper::getContentConfig('ecotourism');

        $scenario = ['scenario' => Constants::SCENARIO_ECOTOURISM];

        $contentModel = new Content($scenario);
        $contentEcotourismModel = new ContentEcotourism();
        $pictureModel = new Picture();
        $contentStatisticsModel = new ContentStatistics();

        $POST = _::post();

        if ($POST) {
            $contentModel->load($POST);
            $contentEcotourismModel->load($POST);
            $currentDateTime = _::getDateTimeYmdHis();

            $transaction = _::beginTransaction();
            try {

                $imageInContent = ContentHelper::saveImageFromUpload($contentModel, 'picture_path', $contentConfig->upload_folder_name);
                if (!$imageInContent) {
                    $this->setStackErrorMessage('อัปโหลดรูปภาพไม่สำเร็จ');
                }

                _::setupModel($contentModel, [
                    'type_id' => $contentConfig->type_id,
                    'picture_path' => $imageInContent,
                    'created_by_user_id' => _::currentUserId(),
                    'updated_by_user_id' => _::currentUserId(),
                    'latitude' => $POST['Content']['latitude'],
                    'longitude' => $POST['Content']['longitude'],
                    'created_at' => $currentDateTime,
                    'updated_at' => $currentDateTime,
                    'status' => 'pending',
                    'active' => 1
                ]);

                if (_::saveModel($contentModel)) {

                    $errorMessageSaveMultipleImage = ContentHelper::saveMultipleImageFromUpload($contentModel->id, $contentModel, $contentConfig->upload_folder_name);
                    if (_::issetNotEmpty($errorMessageSaveMultipleImage)) {
                        $this->setStackErrorMessage('อัปโหลดรูปภาพประกอบไม่สำเร็จ');
                    }

                    _::setupModel($contentModel, [
                        'content_root_id' => $contentModel->id,
                    ]);
                    $contentModel->update();

                    $taxonomyList = ArrayHelper::getValue($POST, 'Content.taxonomy_list');
                    if (_::issetNotEmpty($taxonomyList)) {
                        TaxonomyHelper::insertTaxonomyInContent($contentModel->id, $taxonomyList, $currentDateTime);
                    }

                    _::setupModel($contentEcotourismModel, [
                        'content_id' => $contentModel->id,
                        'created_at' => $currentDateTime,
                        'updated_at' => $currentDateTime,
                    ]);

                    if (_::saveModel($contentEcotourismModel)) {

                        _::setupModel($contentStatisticsModel, [
                            'content_root_id' => $contentModel->id,
                            'pageview' => 0,
                            'like_count' => 0
                        ]);

                        if (_::saveModel($contentStatisticsModel)) {

                            if ($this->commit($transaction, $contentModel, $contentModel->id )) {
                                // return $this->redirect(["/content/update/{$contentConfig->type}/{$contentModel->id}"]);
                                return $this->redirect("/content/views/student");
                            }
                        }
                    }
                }
            } catch (\Exception $th) {
                $transaction->rollBack();
                $this->throwError($th);
            }
        }


        return $this->render('/user/content/create-update/wrapper_form_content', [
            'model' => (object) [
                'content' => $contentModel,
                'contentEcotourism' => $contentEcotourismModel,
                'picture' => $pictureModel,
            ],

            'data' => (object) [
                'pictureList' => [],
            ],

            'actionType' => 'create',
            'pageType' => $contentConfig->type,
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,

            'text' => $this->generateContentTitle($contentConfig->name)
        ]);
    }

    public function actionProduct()
    {
        $contentConfig = (object) ContentHelper::CONTENT_CONFIG['product'];

        $scenario = ['scenario' => Constants::SCENARIO_PLANT];

        $contentModel = new Content($scenario);
        $contentProductModel = new ContentProduct();
        $pictureModel = new Picture();
        $contentStatisticsModel = new ContentStatistics();

        $POST = _::post();

        if ($POST) {
            $contentModel->load($POST);
            $contentProductModel->load($POST);
            $currentDateTime = _::getDateTimeYmdHis();

            $transaction = _::beginTransaction();
            try {

                $imageInContent = ContentHelper::saveImageFromUpload($contentModel, 'picture_path', $contentConfig->upload_folder_name);
                if (!$imageInContent) {
                    $this->setStackErrorMessage('อัปโหลดรูปภาพไม่สำเร็จ');
                }

                _::setupModel($contentModel, [
                    'type_id' => $contentConfig->type_id,
                    'picture_path' => $imageInContent,
                    'description' => $contentProductModel->product_features,
                    'created_by_user_id' => _::currentUserId(),
                    'updated_by_user_id' => _::currentUserId(),
                    'latitude' => $POST['Content']['latitude'],
                    'longitude' => $POST['Content']['longitude'],
                    'created_at' => $currentDateTime,
                    'updated_at' => $currentDateTime,
                    'status' => 'pending',
                    'active' => 1
                ]);

                if (_::saveModel($contentModel)) {

                    $errorMessageSaveMultipleImage = ContentHelper::saveMultipleImageFromUpload($contentModel->id, $contentModel, $contentConfig->upload_folder_name);
                    if (_::issetNotEmpty($errorMessageSaveMultipleImage)) {
                        $this->setStackErrorMessage('อัปโหลดรูปภาพประกอบไม่สำเร็จ');
                    }

                    _::setupModel($contentModel, [
                        'content_root_id' => $contentModel->id,
                    ]);
                    $contentModel->update();

                    $taxonomyList = ArrayHelper::getValue($POST, 'Content.taxonomy_list');
                    if (_::issetNotEmpty($taxonomyList)) {
                        TaxonomyHelper::insertTaxonomyInContent($contentModel->id, $taxonomyList, $currentDateTime);
                    }

                    _::setupModel($contentProductModel, [
                        'content_id' => $contentModel->id,
                        'created_at' => $currentDateTime,
                        'updated_at' => $currentDateTime,
                    ]);

                    if (_::saveModel($contentProductModel)) {

                        _::setupModel($contentStatisticsModel, [
                            'content_root_id' => $contentModel->id,
                            'pageview' => 0,
                            'like_count' => 0
                        ]);

                        if (_::saveModel($contentStatisticsModel)) {

                            if ($this->commit($transaction, $contentModel, $contentModel->id )) {
                                // return $this->redirect(["/content/update/{$contentConfig->type}/{$contentModel->id}"]);
                                return $this->redirect("/content/views/student");
                            }
                        }
                    }
                }
            } catch (\Exception $th) {
                $transaction->rollBack();
                $this->throwError($th);
            }
        }


        return $this->render('/user/content/create-update/wrapper_form_content', [
            'model' => (object) [
                'content' => $contentModel,
                'contentProduct' => $contentProductModel,
                'picture' => $pictureModel,
            ],

            'data' => (object) [
                'pictureList' => [],
            ],

            'actionType' => 'create',
            'pageType' => $contentConfig->type,
            'pageList' => ContentHelper::CONTENT_PAGE_LIST,

            'text' => $this->generateContentTitle($contentConfig->name)
        ]);
    }
}
