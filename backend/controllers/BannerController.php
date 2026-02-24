<?php

namespace backend\controllers;

use Yii;
use backend\models\Banner;
use backend\models\BannerSearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;

use common\components\Upload;
use common\components\Helper;

use backend\components\BackendHelper;


/**
 * BannerController implements the CRUD actions for Banner model.
 */
class BannerController extends Controller
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
                                    return PermissionAccess::BackendAccess('banner_list', 'controller');
                                break;

                                case 'update':
                                    return PermissionAccess::BackendAccess('banner_update', 'controller');
                                break;

                                case 'view':
                                    return PermissionAccess::BackendAccess('banner_view', 'controller');
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
     * Lists all Banner models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new BannerSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Banner model.
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
     * Creates a new Banner model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Banner();
        $case_error = array();

        if ($model->load(Yii::$app->request->post())) {

            $transaction = \Yii::$app->db->beginTransaction();
            try {

                $mainPicture = Upload::uploadPictureNoPermission($model, 'banner', '', 0, 'picture_path');
                if (!empty($mainPicture)) {
    
                    if ($mainPicture != 'error') {
                        $model->picture_path = $mainPicture;
                    }else{
                        $case_error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                    }
                }

                $model->created_at = date("Y-m-d H:i:s");
                $model->updated_at = date("Y-m-d H:i:s");
                $model->active = 1;

                if ($model->save()) {
                    $transaction->commit();

                    BackendHelper::saveUserLog('banner', Yii::$app->user->identity->id, $model->id, 'create banner', 'เพิ่มข้อมูล banner' );

                    return $this->redirect(['view', 'id' => $model->id]);    
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
     * Updates an existing Banner model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {

            $transaction = \Yii::$app->db->beginTransaction();
            try {

                $mainPicture = Upload::uploadPictureNoPermission($model, 'banner', $model->getOldAttribute('picture_path'), 0, 'picture_path');
                if (!empty($mainPicture)) {
    
                    if ($mainPicture != 'error') {
                        $model->picture_path = $mainPicture;
                    }else{
                        $case_error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                    }
                }

                $model->updated_at = date("Y-m-d H:i:s");
                $model->active = 1;

                if ($model->save()) {
                    $transaction->commit();

                    BackendHelper::saveUserLog('banner', Yii::$app->user->identity->id, $model->id, 'update banner', 'แก้ไขข้อมูล banner' );

                    return $this->redirect(['view', 'id' => $model->id]);    
                }

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
            
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Banner model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Banner model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Banner the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Banner::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
