<?php

namespace backend\controllers;

use Yii;
use backend\models\Wallboard;
use backend\models\WallboardSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\BackendHelper;

/**
 * WallboardController implements the CRUD actions for Wallboard model.
 */
class WallboardController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'teacher-student', 'teacher'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) 
                        {
                            switch($action->id){
                                case 'index':
                                    return PermissionAccess::BackendAccess('wallboard_list', 'controller');
                                break;

                                case 'create':
                                    return PermissionAccess::BackendAccess('wallboard_create', 'controller');
                                break;

                                case 'update':
                                case 'teacher-student':
                                    return PermissionAccess::BackendAccess('wallboard_update', 'controller');
                                break;

                                case 'view':
                                case 'teacher':
                                    return PermissionAccess::BackendAccess('wallboard_view', 'controller');
                                break;

                                case 'delete':
                                    return PermissionAccess::BackendAccess('wallboard_delete', 'controller');
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
     * Lists all Wallboard models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new WallboardSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Wallboard model.
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
     * Creates a new Wallboard model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Wallboard();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                
                $model->created_by_user_id = Yii::$app->user->identity->id;
                $model->updated_by_user_id = Yii::$app->user->identity->id;
                $model->created_at = date("Y-m-d H:i:s");
                $model->updated_at = date("Y-m-d H:i:s");
                $model->active = 1;

                if ($model->save()) {
                    $transaction->commit();

                    BackendHelper::saveUserLog('wallboard', Yii::$app->user->identity->id, $model->id, 'create wallboard', 'เพิ่มข้อมูล Wallboard' );

                    return $this->redirect(['index']);
                }

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Wallboard model.
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
                
                $model->updated_by_user_id = Yii::$app->user->identity->id;
                $model->updated_at = date("Y-m-d H:i:s");
                $model->active = 1;

                if ($model->save()) {
                    $transaction->commit();


                    BackendHelper::saveUserLog('wallboard', Yii::$app->user->identity->id, $model->id, 'update wallboard', 'แก้ไขข้อมูล Wallboard' );

                    return $this->redirect(['index']);
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
     * Deletes an existing Wallboard model.
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

            BackendHelper::saveUserLog('wallboard', Yii::$app->user->identity->id, $model->id, 'delete wallboard', 'ลบข้อมูล Wallboard' );
            
            return $this->redirect(['index']);
        }
    }

    /**
     * Finds the Wallboard model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Wallboard the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Wallboard::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
