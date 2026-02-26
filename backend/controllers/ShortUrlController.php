<?php

namespace backend\controllers;

use common\models\ShortUrl;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * ShortUrlController implements the CRUD actions for ShortUrl model.
 */
class ShortUrlController extends Controller
{
    /**
     * @inheritdoc
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
            // You might want to add AccessControl behavior if needed
        ];
    }

    /**
     * Lists all ShortUrl models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => ShortUrl::find()->orderBy(['id' => SORT_DESC]),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ShortUrl model.
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
     * Creates a new ShortUrl model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new ShortUrl();

        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->code)) {
                $model->generateCode();
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ShortUrl model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post())) {
            if (empty($model->code)) {
                $model->generateCode();
            }
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'บันทึกข้อมูลเรียบร้อยแล้ว');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing ShortUrl model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        Yii::$app->session->setFlash('success', 'ลบข้อมูลเรียบร้อยแล้ว');

        return $this->redirect(['index']);
    }

    /**
     * Resolves short code and redirects to target URL.
     *
     * @param string $code
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the short code is not found
     */
    public function actionRedirect($code)
    {
        $shortUrl = ShortUrl::findOne(['code' => $code]);

        if ($shortUrl) {
            return $this->redirect($shortUrl->target_url);
        }

        throw new NotFoundHttpException('The requested page does not exist or has expired.');
    }

    /**
     * AJAX action to toggle between original URL and short URL.
     * POST params: url (the current URL), mode ('shorten' or 'expand')
     * Returns JSON: { success: true, url: '...', mode: 'short'|'original' }
     */
    public function actionToggleShortUrl()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $request = Yii::$app->request;

        if (!$request->isAjax || !$request->isPost) {
            return ['success' => false, 'message' => 'Invalid request'];
        }

        $url = trim($request->post('url', ''));
        $mode = $request->post('mode', 'shorten'); // 'shorten' or 'expand'
        $shortUrlBase = Yii::$app->params['shortUrlDomain'] ?? '';

        if (empty($url)) {
            return ['success' => false, 'message' => 'URL is empty'];
        }

        if ($mode === 'expand') {
            // Current value is a short URL, try to find the original
            $code = str_replace($shortUrlBase, '', $url);
            $model = ShortUrl::findOne(['code' => $code]);
            if ($model) {
                return ['success' => true, 'url' => $model->target_url, 'mode' => 'original'];
            }
            return ['success' => false, 'message' => 'Short URL not found'];
        }

        // mode === 'shorten': find existing or create new
        $model = ShortUrl::findOne(['target_url' => $url]);
        if ($model) {
            return ['success' => true, 'url' => $shortUrlBase . $model->code, 'mode' => 'short'];
        }

        // Create new
        $model = new ShortUrl();
        $model->target_url = $url;
        $model->generateCode();
        if ($model->save()) {
            return ['success' => true, 'url' => $shortUrlBase . $model->code, 'mode' => 'short'];
        }

        return ['success' => false, 'message' => 'Failed to create short URL'];
    }

    /**
     * Finds the ShortUrl model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ShortUrl the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ShortUrl::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
