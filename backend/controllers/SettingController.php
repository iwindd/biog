<?php

namespace backend\controllers;

use Yii;
use backend\models\Variables;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use common\components\Helper;
use backend\models\Users;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\BackendHelper;


/**
 * SettingController implements the CRUD actions for Variables model.
 */
class SettingController extends Controller
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
                        'actions' => ['index', 'expert', 'data-protection'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) 
                        {
                            switch($action->id){
                                case 'index':
                                case 'expert':
                                case 'data-protection':
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
     * Lists all Variables models.
     * @return mixed
     */
    public function actionIndex()
    {


        $model= new Variables();
        $query=Variables::find()->where(['key' =>"sender_mail"])->one();
        $model->sender_mail=$query->value;
        $query=Variables::find()->where(['key' =>"email_info"])->one();
        $model->email_info=$query->value;
        $query=Variables::find()->where(['key' =>"phone_info"])->one();
        $model->phone_info=$query->value;


        if (Yii::$app->request->post()){
            $post=Yii::$app->request->post();
            Yii::$app->db->createCommand()
            ->update('variables', ['value' => $post['Variables']['sender_mail']], ['key' =>"sender_mail"])
            ->execute();
            Yii::$app->db->createCommand()
            ->update('variables', ['value' => $post['Variables']['email_info']], ['key' =>"email_info"])
            ->execute();
            Yii::$app->db->createCommand()
            ->update('variables', ['value' => $post['Variables']['phone_info']], ['key' =>"phone_info"])
            ->execute();

            if ($model->sender_mail !=  $post['Variables']['sender_mail']) {
                BackendHelper::saveUserLog('variables', Yii::$app->user->identity->id, 1, 'update sender email', 'แก้ไขอีเมลผู้ส่ง');
            }


            Yii::$app->getSession()->setFlash('alert',[
                'body'=>'บันทึกข้อมูลเสร็จเรียบร้อยแล้ว',
                'options'=>['class'=>'alert-success']
            ]);

            return $this->redirect(['/setting']);
        }
        return $this->render('index', [
            'model' => $model,
        ]);
        
    }

    public function actionDataProtection()
    {


        $model= new Variables();
        $query=Variables::find()->where(['key' =>"data_protection"])->one();
        $model->data_protection=$query->value;
       


        if (Yii::$app->request->post()){
            $post=Yii::$app->request->post();
            Yii::$app->db->createCommand()
            ->update('variables', ['value' => $post['Variables']['data_protection']], ['key' =>"data_protection"])
            ->execute();


            Yii::$app->getSession()->setFlash('alert',[
                'body'=>'บันทึกข้อมูลเสร็จเรียบร้อยแล้ว',
                'options'=>['class'=>'alert-success']
            ]);

            return $this->redirect(['/setting/data-protection']);
        }
        return $this->render('data-protection', [
            'model' => $model,
        ]);
        
    }

    public function actionExpert()
    {

        $expert = Variables::find()->where(['key'=>'expert'])->one();
        if(!empty($expert)){
            $expert->expert = $expert->value;
        }

        if(Yii::$app->request->post()){
            $expert->load(Yii::$app->request->post());

            if (!empty($expert->expert)) {
                $experts = $expert->expert;
                $expertSelected = "";
                foreach ($experts as $key => $expertName) {
                    if (empty($expertSelected)) {
                        $expertSelected = $expertName;
                    } else {
                        $expertSelected = $expertSelected.','.$expertName;
                    }
                }

                $expert->value = $expertSelected;
                
            }else{
                $expert->value = "";
            }

            if ($expert->save()) {
                if ($expert->expert != $expert->getOldAttribute('value')) {
                    BackendHelper::saveUserLog('variables', Yii::$app->user->identity->id, 1, 'update expert show', 'แก้ไขข้อมูลแสดงรายการของภูมิปัญญา/ปราชญ์');
                }

                Yii::$app->getSession()->set('success', 'แก้ไขข้อมูลสำเร็จ');
                return $this->redirect(['expert']);
            }
        }


        if(!empty($expert->value)){
            $expert->expert = explode(',', $expert->value);
        }

        return $this->render('expert', [
                'expert' => $expert,
        ]);
        
    }

    /**
     * Displays a single Variables model.
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
     * Creates a new Variables model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Variables();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Variables model.
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
     * Deletes an existing Variables model.
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

    protected function getNumberUsername($dataUser, $start)
    {
        $arr=array($dataUser);
        $data = array();
        $result = array();
        foreach ($dataUser as $key => $value) {
            $code = substr($value, $start, $start+4);
            $data[] = $code;
        }
       
        sort($data);

        for($i=0;$i<=max($data);$i++){

            if(!in_array($i,$data) && $i !='0'){
               $result[] = $i;

            }
        }

         
        return $result;
    }


    /**
     * Finds the Variables model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Variables the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Variables::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
