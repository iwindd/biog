<?php

namespace backend\controllers;

use Yii;
use backend\models\Variables;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

use common\components\Helper;
use backend\models\Users;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\BackendHelper;
use backend\models\UserNotificationSetting;


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
                        'actions' => ['index', 'expert', 'data-protection', 'delete-protection-pdf', 'notification'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) 
                        {
                            switch($action->id){
                                case 'index':
                                case 'expert':
                                case 'data-protection':
                                case 'delete-protection-pdf':
                                case 'notification':
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
                    'delete-protection-pdf' => ['POST'],
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

        // Per-admin notification setting
        $notificationModel = UserNotificationSetting::getOrCreate(Yii::$app->user->identity->id);

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

            // Save per-admin notification setting
            if (isset($post['UserNotificationSetting'])) {
                $notificationModel->notify_new_registration = isset($post['UserNotificationSetting']['notify_new_registration']) ? (int)$post['UserNotificationSetting']['notify_new_registration'] : 0;
                $notificationModel->updated_at = date('Y-m-d H:i:s');
                $notificationModel->save();
            }

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
            'notificationModel' => $notificationModel,
        ]);
        
    }

    public function actionDataProtection()
    {
        $model = new Variables();
        $query = Variables::find()->where(['key' => "data_protection"])->one();
        $model->data_protection = $query->value;

        // Load current PDF path
        $pdfRecord = Variables::find()->where(['key' => 'data_protection_pdf'])->one();
        $currentPdf = !empty($pdfRecord) ? $pdfRecord->value : '';

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post();
            Yii::$app->db->createCommand()
                ->update('variables', ['value' => $post['Variables']['data_protection']], ['key' => "data_protection"])
                ->execute();

            // Handle PDF upload
            $pdfFile = UploadedFile::getInstance($model, 'data_protection_pdf');
            if ($pdfFile) {
                // Validate extension
                if (strtolower($pdfFile->extension) !== 'pdf') {
                    Yii::$app->getSession()->setFlash('alert', [
                        'body' => 'อนุญาตเฉพาะไฟล์ PDF เท่านั้น',
                        'options' => ['class' => 'alert-danger']
                    ]);
                    return $this->redirect(['/setting/data-protection']);
                }

                // Validate size (max 10MB)
                if ($pdfFile->size > 10 * 1024 * 1024) {
                    Yii::$app->getSession()->setFlash('alert', [
                        'body' => 'ขนาดไฟล์เกินกำหนด (สูงสุด 10 MB)',
                        'options' => ['class' => 'alert-danger']
                    ]);
                    return $this->redirect(['/setting/data-protection']);
                }

                $uploadDir = Yii::getAlias('@frontend/web/uploads/data-protection');
                if (!is_dir($uploadDir)) {
                    FileHelper::createDirectory($uploadDir);
                }

                // Delete old file if exists
                if (!empty($currentPdf)) {
                    $oldFilePath = Yii::getAlias('@frontend/web') . $currentPdf;
                    if (file_exists($oldFilePath)) {
                        @unlink($oldFilePath);
                    }
                }

                $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $pdfFile->name);
                $filePath = $uploadDir . '/' . $fileName;
                $relativePath = '/uploads/data-protection/' . $fileName;

                if ($pdfFile->saveAs($filePath)) {
                    // Upsert the PDF path
                    if (!empty($pdfRecord)) {
                        Yii::$app->db->createCommand()
                            ->update('variables', ['value' => $relativePath], ['key' => 'data_protection_pdf'])
                            ->execute();
                    } else {
                        Yii::$app->db->createCommand()
                            ->insert('variables', ['key' => 'data_protection_pdf', 'value' => $relativePath])
                            ->execute();
                    }
                }
            }

            Yii::$app->getSession()->setFlash('alert', [
                'body' => 'บันทึกข้อมูลเสร็จเรียบร้อยแล้ว',
                'options' => ['class' => 'alert-success']
            ]);

            return $this->redirect(['/setting/data-protection']);
        }

        return $this->render('data-protection', [
            'model' => $model,
            'currentPdf' => $currentPdf,
        ]);
    }

    /**
     * Delete the data protection PDF file
     */
    public function actionDeleteProtectionPdf()
    {
        $pdfRecord = Variables::find()->where(['key' => 'data_protection_pdf'])->one();
        if (!empty($pdfRecord) && !empty($pdfRecord->value)) {
            // Delete file from disk
            $filePath = Yii::getAlias('@frontend/web') . $pdfRecord->value;
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            // Clear value in DB
            Yii::$app->db->createCommand()
                ->update('variables', ['value' => ''], ['key' => 'data_protection_pdf'])
                ->execute();
        }

        Yii::$app->getSession()->setFlash('alert', [
            'body' => 'ลบไฟล์ PDF เสร็จเรียบร้อยแล้ว',
            'options' => ['class' => 'alert-success']
        ]);

        return $this->redirect(['/setting/data-protection']);
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

    /**
     * Per-admin notification settings.
     */
    public function actionNotification()
    {
        $userId = Yii::$app->user->identity->id;
        $model = UserNotificationSetting::getOrCreate($userId);

        if (Yii::$app->request->post()) {
            $post = Yii::$app->request->post();
            $model->notify_new_registration = isset($post['UserNotificationSetting']['notify_new_registration']) ? (int)$post['UserNotificationSetting']['notify_new_registration'] : 0;
            $model->updated_at = date('Y-m-d H:i:s');
            $model->save();

            Yii::$app->getSession()->setFlash('alert', [
                'body' => 'บันทึกการตั้งค่าการแจ้งเตือนเรียบร้อยแล้ว',
                'options' => ['class' => 'alert-success']
            ]);

            return $this->redirect(['/setting/notification']);
        }

        return $this->render('notification', [
            'model' => $model,
        ]);
    }
}
