<?php

namespace backend\controllers;

use Yii;
use backend\models\Content;
use backend\models\Picture;
use backend\models\ContentPlant;
use backend\models\ContentPlantSearch;
use backend\models\ContentTaxonomy;
use backend\models\Taxonomy;
use backend\models\Comment;
use backend\models\ContentStatistics;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\BackendHelper;

use common\components\Upload;
use common\components\Helper;

/**
 * ContentPlantController implements the CRUD actions for Content model.
 */
class ContentPlantController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'export'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) 
                        {
                            switch($action->id){
                                case 'index':
                                case 'export':
                                    return PermissionAccess::BackendAccess('content_list', 'controller');
                                break;

                                case 'create':
                                    return PermissionAccess::BackendAccess('content_create', 'controller');
                                break;

                                case 'update':
                                    return PermissionAccess::BackendAccess('content_update', 'controller');
                                break;

                                case 'view':
                                    return PermissionAccess::BackendAccess('content_view', 'controller');
                                break;

                                case 'delete':
                                    return PermissionAccess::BackendAccess('content_delete', 'controller');
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
     * Lists all Content models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ContentPlantSearch();
        $searchModel->type_id = 1;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Content model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $modelContentPlant  = ContentPlant::find()->where(['content_id'=>$id])->one();
        return $this->render('view', [
            'model' => $this->findModel($id),
            'modelContentPlant'=>$modelContentPlant,
        ]);
    }

    /**
     * Creates a new Content model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Content();
        $model->type_id = 1;
        $modelPlant = new ContentPlant();
        $mediaModel = array();
        $modelImageSource = [new \backend\models\ContentImageSource()];
        $modelDataSource = [new \backend\models\ContentDataSource()];

        $case_error = array();

        if ($model->load(Yii::$app->request->post()) && $modelPlant->load(Yii::$app->request->post())) {
        
            $modelImageSource = \backend\base\Model::createMultiple(\backend\models\ContentImageSource::classname());
            \backend\base\Model::loadMultiple($modelImageSource, Yii::$app->request->post());

            $modelDataSource = \backend\base\Model::createMultiple(\backend\models\ContentDataSource::classname());
            \backend\base\Model::loadMultiple($modelDataSource, Yii::$app->request->post());

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $model->active = 1;
                $checkUpdate = true;

                $model->created_by_user_id = Yii::$app->user->identity->id;
                $model->updated_by_user_id = Yii::$app->user->identity->id;
                $model->created_at = date("Y-m-d H:i:s");
                $model->updated_at = date("Y-m-d H:i:s");

                $model->description = $modelPlant->features;

                $post = Yii::$app->request->post();
                if (!empty($post['filecenter_picture_path'])) {
                    $model->picture_path = $post['filecenter_picture_path'];
                } else {
                    $mainPicture = Upload::uploadPictureNoPermission($model, 'content-plant', '', 0, 'picture_path');
                    if (!empty($mainPicture)) {
        
                        if ($mainPicture != 'error') {
                            $model->picture_path = $mainPicture;
                        }else{
                            $case_error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                        }
                    }
                }

                

                if($model->status == 'approved'){
                    $model->approved_by_user_id = Yii::$app->user->identity->id;
                }

                if ($model->save()) {
                    // upload multiple image
                    $files = Upload::uploadsNoPermimission($model, 'content-plant');
                    if (!empty($files)) {
                        if ($files['success_upload'] == 1) {
                            if (!empty($files['data'])) {
                                foreach ($files['data'] as $value) {
                                    $mediaModel = new Picture();
                                    $mediaModel->content_id = $model->id;
                                    $mediaModel->name = $value['file_display_name'];
                                    $mediaModel->path = $value['file_key'];
                                    $mediaModel->created_by_user_id = Yii::$app->user->identity->id;
                                    $mediaModel->updated_by_user_id = Yii::$app->user->identity->id;
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
                    if(!empty($post['Content']['taxonomy'])){
                        foreach ($post['Content']['taxonomy'] as $value) {
                            $modelTax = new ContentTaxonomy();
                            $modelTax->content_id = $model->id;
                            if (is_numeric($value)) {
                                $modelTax->taxonomy_id = $value;
                            }else{
                                $taxId = $this->getTaxonomyInputData($value);
                                if(!empty($taxId)){
                                    $modelTax->taxonomy_id = $taxId;
                                }
                            }
                            $duplicate = (new \yii\db\Query())
                                ->select(['content_id','taxonomy_id'])
                                ->from('content_taxonomy')
                                ->where(['content_id' => $modelTax->content_id])
                                ->andWhere(['taxonomy_id' => $modelTax->taxonomy_id])
                                ->all();
                            if (empty($duplicate)) {
                                $modelTax->created_at = date('Y-m-d H:i:s');
                                $modelTax->save();
                            }
                        }
                    }

                    $modelPlant->content_id = $model->id;
                    $modelPlant->created_at = date("Y-m-d H:i:s");
                    $modelPlant->updated_at = date("Y-m-d H:i:s");
                    if ($modelPlant->save()) {

                        foreach ($modelImageSource as $imgSrc) {
                            $imgSrc->content_id = $model->id;
                            if (!empty($imgSrc->source_name) || !empty($imgSrc->author) || !empty($imgSrc->published_date) || !empty($imgSrc->reference_url)) {
                                if (!$imgSrc->save(false)) {
                                    $checkUpdate = false;
                                }
                            }
                        }

                        foreach ($modelDataSource as $dataSource) {
                            $dataSource->content_id = $model->id;
                            if (!empty($dataSource->source_name) || !empty($dataSource->author) || !empty($dataSource->published_date) || !empty($dataSource->reference_url)) {
                                if (!$dataSource->save(false)) {
                                    $checkUpdate = false;
                                }
                            }
                        }

                        if ($checkUpdate) {
                            $transaction->commit();

                            BackendHelper::saveUserLog('content', Yii::$app->user->identity->id, $model->id, 'create content plant', 'เพิ่มเนื้อหาพืช');

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
            'modelPlant' => $modelPlant,
            'mediaModel' => $mediaModel,
            'modelImageSource' => $modelImageSource,
            'modelDataSource' => $modelDataSource,
            'case_error' => $case_error
        ]);
    }

    /**
     * Updates an existing Content model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $modelOld = $this->findModel($id);

        $modelOld->taxonomy = $this->getTaxonomy($modelOld->id);
       
        $model = new Content();

        $modelPlantOld = ContentPlant::find()->where(['content_id' => $id])->one();
        $modelPlant = new ContentPlant();

        $mediaModelOld = Picture::find()->where(['content_id' => $id])->all();
        $mediaModel = new Picture();

        $modelImageSourceOld = \backend\models\ContentImageSource::find()->where(['content_id' => $id])->all();
        $modelImageSource = (empty($modelImageSourceOld)) ? [new \backend\models\ContentImageSource()] : $modelImageSourceOld;

        $modelDataSourceOld = \backend\models\ContentDataSource::find()->where(['content_id' => $id])->all();
        $modelDataSource = (empty($modelDataSourceOld)) ? [new \backend\models\ContentDataSource()] : $modelDataSourceOld;

        $case_error = array();

        if ($model->load(Yii::$app->request->post()) && $modelPlant->load(Yii::$app->request->post())) {
            
            $modelImageSourceTemp = \backend\base\Model::createMultiple(\backend\models\ContentImageSource::classname(), $modelImageSourceOld);
            \backend\base\Model::loadMultiple($modelImageSourceTemp, Yii::$app->request->post());
            $modelImageSource = $modelImageSourceTemp;

            $modelDataSourceTemp = \backend\base\Model::createMultiple(\backend\models\ContentDataSource::classname(), $modelDataSourceOld);
            \backend\base\Model::loadMultiple($modelDataSourceTemp, Yii::$app->request->post());
            $modelDataSource = $modelDataSourceTemp;

            // print '<pre>';
            // print_r($model);
            // print '</pre>';
            // exit();
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $model->type_id = 1;
                $model->active = 1;

                $modelOld->active = 0;
                $modelOld->save();

                $checkUpdate = true;
                $post = Yii::$app->request->post();

                //find old latest content
                $latestContentId = Helper::getEventIDActive($modelOld->id);

                $modelOldLatest = $this->findModel($latestContentId);

                if( $modelOld->content_root_id == 0){
                    $model->content_root_id = $latestContentId;   
                }else{
                    $model->content_root_id = $modelOld->content_root_id;   
                }

                Content::updateAll(['active' => '0'], ['content_root_id' => $model->content_root_id]);
                Content::updateAll(['active' => '0'], ['id' => $model->content_root_id]);

                $model->content_source_id = $latestContentId;

                $model->created_by_user_id = $modelOldLatest->created_by_user_id;
                $model->updated_by_user_id = Yii::$app->user->identity->id;
                $model->created_at = $modelOldLatest->created_at;
                $model->updated_at = date("Y-m-d H:i:s");

                $model->description = $modelPlant->features;


                $del = 0;
                if(!empty($_POST['deletePic'])){
                    $del = $_POST['deletePic'];
                }

                if (!empty($post['filecenter_picture_path'])) {
                    $model->picture_path = $post['filecenter_picture_path'];
                } else {
                    $mainPicture = Upload::uploadPictureNoPermission($model, 'content-plant', $modelOld->getOldAttribute('picture_path'), $del, 'picture_path');

            
                    if (!empty($mainPicture)) {
        
                        if (!empty($mainPicture)) {
                            if ($mainPicture == 'error') {
                                $error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                                $checkUpdate = false;
                            }else if($mainPicture == 'remove'){
                                $model->picture_path = '';
                            }else{
                                $model->picture_path = $mainPicture;
                            }
                        }
                    }
                }

                if($model->status == 'approved'){
                    $model->approved_by_user_id = Yii::$app->user->identity->id;
                }

                if ($model->save()) {

                    $newContentId = $model->id;

                    // delete image
                    /*
                    $deleteImage = explode('/', $post['removeImage']);
                    if (!empty($deleteImage)) {
                        foreach ($deleteImage as $imageId) {
                            if (!empty($imageId)) {
                                $imageItem = Picture::findOne($imageId);
                                if (!empty($imageItem)) {
                                    $checkRemovefile = Upload::removeFileNoPermission('content-plant', $imageItem->path);
                                    if ($checkRemovefile) {
                                        $imageItem->delete();
                                    }
                                }
                            }
                        }
                    } */

                    // upload multiple image
                    $files = Upload::uploadsNoPermimission($model, 'content-plant');
                    if (!empty($files)) {
                        if ($files['success_upload'] == 1) {
                            if (!empty($files['data'])) {
                                foreach ($files['data'] as $value) {
                                    $newRecordPicture = $this->savePicture($newContentId, $value);
                                    if($newRecordPicture == false){
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
                        $medias = Picture::find()->where(['not in', 'id', $imagesId])->andWhere(['content_id' => $latestContentId])->asArray()->all();
                        
                        if(!empty($medias)){
                            foreach ($medias as $value) {
                                $value['file_display_name'] = $value['name'];
                                $value['file_key'] = $value['path'];
                                $newRecordPicture = $this->savePicture($newContentId, $value);
                                if($newRecordPicture == false){
                                    $checkUpdate = false;
                                    $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                }
                            }
                        }
                    }else{
                        $medias = Picture::find()->where(['content_id' => $latestContentId])->asArray()->all();
                        if(!empty($medias)){
                            foreach ($medias as $value) {
                                $value['file_display_name'] = $value['name'];
                                $value['file_key'] = $value['path'];
                                $newRecordPicture = $this->savePicture($newContentId, $value);
                                if($newRecordPicture == false){
                                    $checkUpdate = false;
                                    $case_error[] = array("message" => "ไฟล์ " . $value['file_display_name'] . " อัพโหลดไม่สำเร็จ");
                                }
                            }
                        }
                    }


                    /*
                    if(!empty($post['removeTaxonomy'])){
                        $rmTaxArray = explode('/', $post['removeTaxonomy']);
                        foreach($rmTaxArray as $valueTaxName){
                            if(!empty($valueTaxName)){
                                $modelTax = Taxonomy::find()->where(['name' => $valueTaxName])->one();
                                if (!empty($modelTax->id)) {
                                    \Yii::$app
                                        ->db
                                        ->createCommand()
                                        ->delete('content_taxonomy', ['taxonomy_id' => $modelTax->id])
                                        ->execute();
                                }
                            }
                        }
                    } */
                    if(!empty($post['Content']['taxonomy'])){
                        foreach ($post['Content']['taxonomy'] as $value) {
                            $modelTax = new ContentTaxonomy();
                            $modelTax->content_id = $newContentId;
                            if (is_numeric($value)) {
                                $modelTax->taxonomy_id = $value;
                            }else{
                                $taxId = $this->getTaxonomyInputData($value);
                                if(!empty($taxId)){
                                    $modelTax->taxonomy_id = $taxId;
                                }
                            }
                            $duplicate = (new \yii\db\Query())
                                ->select(['content_id','taxonomy_id'])
                                ->from('content_taxonomy')
                                ->where(['content_id' => $modelTax->content_id])
                                ->andWhere(['taxonomy_id' => $modelTax->taxonomy_id])
                                ->all();
                            if (empty($duplicate)) {
                                $modelTax->created_at = date('Y-m-d H:i:s');
                                $modelTax->save();
                            }
                        }
                    }

                    //clone comment
                    // $modelOldComment = Comment::find()->where(['content_id' => $latestContentId])->asArray()->all();
                    // if (!empty($modelOldComment)) {
                    //     foreach ($modelOldComment as $value) {
                    //         $newRecordComment = Helper::saveContentComment($newContentId, $value);
                    //         if($newRecordComment == false){
                    //             $case_error[] = "เพิ่มการแสดงความคิดเห็นไม่สำเร็จ";
                    //         }
                    //     }
                    // }

                    // $modelStatisticOld = ContentStatistics::find()->where(['content_id' => $latestContentId])->one();
                    // if (!empty($modelStatisticOld)) {
                    //     $dataStatisticOld = $modelStatisticOld->attributes;

                    //     $newStatisticModel = new ContentStatistics();
                    //     $newStatisticModel->setAttributes($dataStatisticOld);
                    //     $newStatisticModel->content_id = $newContentId;
                    //     $newStatisticModel->save();
                    // }

                    $modelPlant->content_id = $model->id;
                    $modelPlant->created_at = date("Y-m-d H:i:s");
                    $modelPlant->updated_at = date("Y-m-d H:i:s");
                    if ($modelPlant->save()) {

                        foreach ($modelImageSource as $imgSrc) {
                            // Note: We create new records because the Update flow creates a new revision 
                            // of Content with a new ID
                            $newImgSrc = new \backend\models\ContentImageSource();
                            $newImgSrc->content_id = $model->id;
                            $newImgSrc->source_name = $imgSrc->source_name;
                            $newImgSrc->author = $imgSrc->author;
                            $newImgSrc->published_date = $imgSrc->published_date;
                            $newImgSrc->reference_url = $imgSrc->reference_url;

                            if (!empty($newImgSrc->source_name) || !empty($newImgSrc->author) || !empty($newImgSrc->published_date) || !empty($newImgSrc->reference_url)) {
                                if (!$newImgSrc->save(false)) {
                                    $checkUpdate = false;
                                }
                            }
                        }

                        foreach ($modelDataSource as $dataSource) {
                            $newDataSource = new \backend\models\ContentDataSource();
                            $newDataSource->content_id = $model->id;
                            $newDataSource->source_name = $dataSource->source_name;
                            $newDataSource->author = $dataSource->author;
                            $newDataSource->published_date = $dataSource->published_date;
                            $newDataSource->reference_url = $dataSource->reference_url;

                            if (!empty($newDataSource->source_name) || !empty($newDataSource->author) || !empty($newDataSource->published_date) || !empty($newDataSource->reference_url)) {
                                if (!$newDataSource->save(false)) {
                                    $checkUpdate = false;
                                }
                            }
                        }

                        if ($checkUpdate) {
                            $transaction->commit();

                            if ($model->status == 'rejected') {
                                BackendHelper::saveUserLog('content', Yii::$app->user->identity->id, $newContentId, 'update content plant', 'แก้ไขเนื้อหาพืช สถานะไม่อนุมัติหมายเหตุ: '.$model->note);
                            }else{
                                BackendHelper::saveUserLog('content', Yii::$app->user->identity->id, $newContentId, 'update content plant', 'แก้ไขเนื้อหาพืช');
                            }

                            return $this->redirect(['view', 'id' => $model->id]);
                        }
                    }
                }


            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        
        }

        return $this->render('update', [
            'model' => $modelOld,
            'modelPlant' => $modelPlantOld,
            'mediaModel' => $mediaModelOld,
            'modelImageSource' => $modelImageSource,
            'modelDataSource' => $modelDataSource,
            'case_error' => $case_error
        ]);


    }

    private function getTaxonomy($id){

        $result = array();
        if(!empty($id)){
            $model = ContentTaxonomy::find()->where(['content_id' => $id])->orderBy(['taxonomy_id' => SORT_ASC])->all();
            
            foreach ($model as $key => $value) {
                $data = $this->getTaxonomyData($value->taxonomy_id);
                if(!empty($data)){
                    $result[] = $data->name;
                }
            } 
        }
        return $result;
    }

    private function getTaxonomyData($id){
        $model = Taxonomy::findOne($id);
        return $model;
    }

    private function getTaxonomyInputData($name)
    {
        $model = Taxonomy::find()->where(['name' => $name])->one();
        if(empty($model)){
            $model = new Taxonomy();
            $model->name = $name;
            $model->created_at = date('Y-m-d H:i:s');
            $model->updated_at = date('Y-m-d H:i:s');
            $model->save();
        }
        return $model->id;
    }

    private function savePicture($newContentId, $value)
    {
  
        $mediaModel = new Picture();
        $mediaModel->content_id = $newContentId;
        $mediaModel->name = $value['file_display_name'];
        $mediaModel->path = $value['file_key'];
        if(empty($value['created_by_user_id'])){
            $mediaModel->created_by_user_id = Yii::$app->user->identity->id;
        }else{
            $mediaModel->created_by_user_id = $value['created_by_user_id'];
        }
        $mediaModel->updated_by_user_id = Yii::$app->user->identity->id;
        $mediaModel->created_at = date("Y-m-d H:i:s");
        $mediaModel->updated_at = date("Y-m-d H:i:s");
        if (!$mediaModel->save()) {
            return false;
        }

        return true;
    }


    /**
     * Deletes an existing Content model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $content = Helper::updateStatusRevisionContent($id, '', 1);
        if ($content['status'] == 'success') {

            BackendHelper::saveUserLog('content', Yii::$app->user->identity->id, $id, 'delete content plant', 'ลบเนื้อหาพืช');
            
            return $this->redirect(['index']);
        }
        if (!empty($content['error'])) {
            throw new \yii\web\BadRequestHttpException('ไม่สามารถลบข้อมูลเนื้อหานี้ได้เนื่องจาก: '.$content['error'][0]);
        }else{
            throw new \yii\web\BadRequestHttpException('ลบข้อมูลเนื้อหาไม่สำเร็จ');
        }
    
    }

    public function actionExport(){

        $query = Content::find()->select([
            'content.id as content_id',
            'content.name',
            'content.type_id',
            'content_plant.other_name',
            'content_plant.features',
            'content_plant.benefit',
            'content_plant.found_source',
            'content_plant.common_name',
            'content_plant.scientific_name',
            'content_plant.family_name',
            'content.region_id',
            'content.province_id',
            'content.district_id',
            'content.subdistrict_id',
            'content.zipcode_id',
            'content.created_by_user_id',
            'content.approved_by_user_id',
            'content.status',
            'content.note',
            'content.created_at',
        ]);
        $query->leftJoin('content_plant', 'content_plant.content_id = content.id');
        $query->leftJoin('profile', 'profile.user_id = content.created_by_user_id');

        $query->andFilterWhere(['=', 'content.type_id', 1 ]);
        $query->andFilterWhere(['=', 'content.active', 1 ]);

        if(!empty($_GET['name'])){
            $query->andFilterWhere(['like', 'content.name', $_GET['name'] ]);
        }

        if(!empty($_GET['created_by_user_id'])){
            $query->andFilterWhere(['=', 'created_by_user_id', $_GET['created_by_user_id'] ]);
        }

        if(!empty($_GET['updated_by_user_id'])){
            $query->andFilterWhere(['=', 'updated_by_user_id', $_GET['updated_by_user_id'] ]);
        }

        if(!empty($_GET['approved_by_user_id'])){
            $query->andFilterWhere(['=', 'approved_by_user_id', $_GET['approved_by_user_id'] ]);
        }

        if(!empty($_GET['note'])){
            $query->andFilterWhere(['like', 'note', $_GET['note'] ]);
        }

        if(!empty($_GET['status'])){
            $query->andFilterWhere(['like', 'status', $_GET['status'] ]);
        }

        if(!empty($_GET['updated_at'])){
            $query->andFilterWhere(['like', 'updated_at', $_GET['updated_at'] ]);
        }
        $offset = 0;
        $size = 25000;
        if (!empty($_GET['file'])) {
            $offset = intval($_GET['file'] -1) * $size ;
        }

        $model = $query->asArray()->limit($size) ->offset($offset)->all();


        return $this->render('export',['model' => $model]);
    }

    /**
     * Finds the Content model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Content the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Content::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
