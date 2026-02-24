<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use common\components\_;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use backend\models\settings\Settings;
use backend\models\settings\SettingSearch;

/**
 * SettingsController implements the CRUD actions for Settings model.
 */
class SettingsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Settings models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SettingSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Settings model.
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
     * Creates a new Settings model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Settings();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Settings model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Settings model.
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
     * Finds the Settings model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Settings the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Settings::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }



    protected function findFacebookAutoPostSetting()
    {
        $settingsModel = new Settings();
        $settingsModel = $settingsModel->find()->where(['setting_key' => 'facebook_auto_post'])->one();

        if (!_::isNull($settingsModel)) {
            return $settingsModel;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    public function actionFacebookAutoPost()
    {
        $settingsModel = $this->findFacebookAutoPostSetting();

        $settingsModel->facebook_application_id = '111';
        $settingsModel->facebook_application_secrete = '222';
        $settingsModel->facebook_access_token = '333';

        $settings = [
            'auto_post' => true,
            'application_name' => 'facebook app',
            'application_id' => '111',
            'application_secrete' => '222',
            'access_token' => '333',
        ];

        // _::setupModel($settingsModel,[
        //     'setting_key' => _::toJsonString($settings)
        // ]);

        // _::saveModel($settingsModel);

        // _::debug(_::toJsonString($settings));

        // $POST = _::post();

        // if()

        return $this->render('/settings/facebook-auto-post/_form', [
            'settingValue' => $settingsModel
        ]);
    }
}
