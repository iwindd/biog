<?php

namespace backend\controllers;

use Yii;
use backend\models\ExpertCategory;
use backend\models\ExpertCategorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\BackendHelper;

/**
 * ExpertCategoryController implements the CRUD actions for ExpertCategory model.
 */
class ExpertCategoryController extends Controller
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
                                    return PermissionAccess::BackendAccess('setting_view', 'controller');
                                break;

                                case 'create':
                                    return PermissionAccess::BackendAccess('setting_view', 'controller');
                                break;

                                case 'update':
                                    return PermissionAccess::BackendAccess('setting_view', 'controller');
                                break;

                                case 'view':
                                    return PermissionAccess::BackendAccess('setting_view', 'controller');
                                break;

                                case 'delete':
                                    return PermissionAccess::BackendAccess('setting_view', 'controller');
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
     * Lists all ExpertCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ExpertCategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ExpertCategory model.
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
     * Creates a new ExpertCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ExpertCategory();

        if ($model->load(Yii::$app->request->post())) {
            $model->active = 1;
            $model->created_at = date('Y-m-d H:i:s');
            $model->updated_at = date('Y-m-d H:i:s');
            if ($model->save()) {

                BackendHelper::saveUserLog('expert_category', Yii::$app->user->identity->id, $model->id, 'create expert category', 'เพิ่มหมวดหมู่ภูปัญญา');

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ExpertCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            $model->active = 1;
            $model->updated_at = date('Y-m-d H:i:s');
            if ($model->save()) {

                BackendHelper::saveUserLog('expert_category', Yii::$app->user->identity->id, $model->id, 'update expert category', 'แก้ไขหมวดหมู่ภูปัญญา');
                
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ExpertCategory model.
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

            BackendHelper::saveUserLog('expert_category', Yii::$app->user->identity->id, $model->id, 'delete expert category', 'ลบหมวดหมู่ภูปัญญา');
            
            return $this->redirect(['index']);
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Finds the ExpertCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ExpertCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ExpertCategory::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
