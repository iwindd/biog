<?php

namespace backend\controllers;

use Yii;
use backend\models\Users;
use backend\models\Profile;
use backend\models\SettingsAccount;
use backend\models\UsersSearch;
use backend\models\UserRole;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\BackendHelper;

use dektrium\user\traits\AjaxValidationTrait;
use dektrium\user\traits\EventTrait;
use dektrium\user\Finder;

use common\components\Upload;
use common\components\Helper;

/**
 * ApprovedStudentController implements the CRUD actions for Users model.
 */
class ApprovedStudentController extends Controller
{
    use AjaxValidationTrait;
    use EventTrait;

    /**
     * Event is triggered before updating user's account settings.
     * Triggered with \dektrium\user\events\FormEvent.
     */
    const EVENT_BEFORE_ACCOUNT_UPDATE = 'beforeAccountUpdate';

    /**
     * Event is triggered after updating user's account settings.
     * Triggered with \dektrium\user\events\FormEvent.
     */
    const EVENT_AFTER_ACCOUNT_UPDATE = 'afterAccountUpdate';



    /** @var Finder */
    protected $finder;

    /**
     * @param string           $id
     * @param \yii\base\Module $module
     * @param Finder           $finder
     * @param array            $config
     */
    public function __construct($id, $module, Finder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($id, $module, $config);
    }
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
                        'actions' => ['index', 'view', 'update', 'delete'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) 
                        {
                            switch($action->id){
                                case 'index':
                                    return PermissionAccess::BackendAccess('user_list', 'controller');
                                break;

                                case 'update':
                                    return PermissionAccess::BackendAccess('user_update', 'controller');
                                break;

                                case 'view':
                                    return PermissionAccess::BackendAccess('user_view', 'controller');
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
     * Lists all Pending Student Users.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsersSearch();
        $searchModel->role_id = 7; // Pending Student
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Users model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'profile' => Profile::find()->where(['user_id' => $id])->one(),
        ]);
    }

    /**
     * Updates an existing Users model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $profile = Profile::find()->where(['user_id' => $id])->one();
        $profileOld = Profile::find()->where(['user_id' => $id])->one();
        $userRole = UserRole::find()->where(['user_id' => $id])->all();
        $error = array();

        $admin = 0;
        $editor = 0;
        $teacher = 0;
        $student = 0;
        $member = 0;

        if(!empty($userRole)){
            foreach($userRole as $value){
                switch ($value['role_id']) {
                    case 1:
                        $admin = 1;
                        break;

                    case 2:
                        $editor = 1;
                        break;

                    case 3:
                        $teacher = 1;
                        break;

                    case 4:
                        $student = 1;
                        break;

                    case 5:
                        $member = 1;
                        break;
                    
                    default:
                        # code...
                        break;
                }
            }
        }


        $user = \Yii::createObject(SettingsAccount::className());
        $event = $this->getFormEvent($user);

        $this->performAjaxValidation($user);
        $this->trigger(self::EVENT_BEFORE_ACCOUNT_UPDATE, $event);
        $password = "";
        if ($user->load(\Yii::$app->request->post()) ) {
            $password = $user->new_password;
        }
        
        if(empty($profile)){
            $profile = new Profile();
        }

        if ($model->load(Yii::$app->request->post()) ) {
           
            if (empty($model->usersValidate())) {

                // Handle student role approval
                $canSave = $this->saveUsesrRole($_POST,'roleStudent',$id,4);
                if($canSave == false){
                    $error[] = "ไม่สามารถบันทึก Role Student";
                }else{
                    // ลบ Pending Student role เมื่ออนุมัติ
                    $userRolePendingStudent = UserRole::find()->where(['user_id' => $id, 'role_id' => 7])->one();
                    if(!empty($userRolePendingStudent)){
                        $userRolePendingStudent->delete();
                    }
                }

                // ถ้าไม่ได้เลือก role ใดเลย และ role เดิมเป็น Pending Student ให้คง Pending Student ไว้
                if(!empty($userRole[0]['role_id'])){
                    if(empty($_POST['roleStudent']) && $userRole[0]['role_id'] == 7){
                        $newUserRole = new UserRole;
                        $newUserRole->user_id = $id;
                        $newUserRole->role_id = 7;
                        $newUserRole->save();
                    }
                }

                $profile->load(Yii::$app->request->post());

                $del = 0;
                if(!empty($_POST['deletePic'])){
                    $del = $_POST['deletePic'];
                }

                $mainPicture = Upload::uploadPictureNoPermission($profile, 'profile', $profileOld->picture, $del);
                if (!empty($mainPicture)) {
                    if ($mainPicture == 'error') {
                        $error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                    }else if($mainPicture == 'remove'){
                        $profile->picture = '';
                    }else{
                        $profile->picture = $mainPicture;
                    }
                }
                    
                $model->updated_at = date("Y-m-d H:i:s");

                if (!empty($password)) {
                    $model->password_hash = \Yii::$app->security->generatePasswordHash($password);
                }

                if ($model->save()) {
    
                    $profile->user_id = $model->id;
                    $profile->updated_at = date('Y-m-d H:i:s');
                    $profile->save();

                    if (empty($error)) {

                        BackendHelper::saveUserLog('user', Yii::$app->user->identity->id, $model->id, 'update user', 'อนุมัตินักเรียน' );

                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
                
            }

        } 

        return $this->render('update', [
            'model' => $model,
            'user' => $user,
            'profile' => $profile,
            'error' => $error,
            'admin' => $admin,
            'editor' => $editor,
            'teacher' => $teacher,
            'student'=> $student,
            'member'=> $member, 
        ]);
    }

    /**
     * Deletes an existing Users model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {

        
        $model = $this->findModel($id);
        $model->email = "temp_".time()."@biogang.com";
        $model->username = "temp_".time()."@biogang.com";
        $model->blocked_at = 1;

        BackendHelper::saveUserLog('user', Yii::$app->user->identity->id, $model->id, 'delete user', 'ลบข้อมูลผู้ใช้ '.$model->getOldAttribute('email') );

        $model->save();

        return $this->redirect(['index']);
    }

    private function saveUsesrRole($dataRole, $rolename, $uid, $roleId){
        try {
            if(!empty($dataRole[$rolename])){
                $userRole = UserRole::find()->where(['user_id' => $uid, 'role_id' => $roleId])->one();
                if(empty($userRole)){
                    $newUserRole = new UserRole;
                    $newUserRole->user_id = $uid;
                    $newUserRole->role_id = $roleId;
                    $newUserRole->save();
                }
            }else{
                \Yii::$app
                ->db
                ->createCommand()
                ->delete('user_role', ['user_id' => $uid, 'role_id' => $roleId])
                ->execute();
            }
    
            return true;
        } catch (\Throwable $th) {
            return false;
        }
        
    }

    /**
     * Finds the Users model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Users the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Users::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
