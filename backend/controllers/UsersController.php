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

/**
 * UsersController implements the CRUD actions for Users model.
 */
class UsersController extends Controller
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'export'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) 
                        {
                            switch($action->id){
                                case 'index':
                                case 'export':
                                    return PermissionAccess::BackendAccess('user_list', 'controller');
                                break;

                                case 'create':
                                    return PermissionAccess::BackendAccess('user_create', 'controller');
                                break;

                                case 'update':
                                    return PermissionAccess::BackendAccess('user_update', 'controller');
                                break;

                                case 'view':
                                    return PermissionAccess::BackendAccess('user_view', 'controller');
                                break;

                                case 'delete':
                                    return PermissionAccess::BackendAccess('user_delete', 'controller');
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
     * Lists all Users models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UsersSearch();
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
     * Creates a new Users model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Users();
        $profile = new Profile();
        $error = array();
        
        $user = \Yii::createObject(SettingsAccount::className());
        $user->scenario = 'create';
        $event = $this->getFormEvent($user);

        $this->performAjaxValidation($user);
        $this->trigger(self::EVENT_BEFORE_ACCOUNT_UPDATE, $event);
        $password = "";
        if ($user->load(\Yii::$app->request->post()) ) {
            $password = $user->new_password;
        }

        $admin = 0;
        $editor = 0;
        $teacher = 0;
        $student = 0;
        $member = 0;
    
        if ($model->load(Yii::$app->request->post())) {


            if(empty($model->usersValidate())){

                $profile->load(Yii::$app->request->post());

                $mainPicture = Upload::uploadPictureNoPermission($profile, 'profile');
                if (!empty($mainPicture)) {

                    if ($mainPicture != 'error') {
                        $profile->picture = $mainPicture;
                    }else{
                        $error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                    }
                }

                $model->created_at = date("Y-m-d H:i:s");
                $model->updated_at = date("Y-m-d H:i:s");
                $model->auth_key = md5("Active".date("Y-m-d H:i:s"));
                if(!empty($password)){
                    $model->password_hash = \Yii::$app->security->generatePasswordHash($password);
                }

                if(!empty($password)){
                    $data['password'] = $password;
                }

      
                if($model->save()){
                    $id = $model->id;
                    if(!empty($_POST['roleAdmin'])){
                        $userRole = UserRole::find()->where(['user_id' => $id, 'role_id' => 1])->one();
                        if(empty($userRole)){
                            $newUserRole = new UserRole;
                            $newUserRole->user_id = $id;
                            $newUserRole->role_id = 1;
                            $newUserRole->save();
                        }
                    }
    
                    if(!empty($_POST['roleEditor'])){
                        $userRole = UserRole::find()->where(['user_id' => $id, 'role_id' => 2])->one();
                        if(empty($userRole)){
                            $newUserRole = new UserRole;
                            $newUserRole->user_id = $id;
                            $newUserRole->role_id = 2;
                            $newUserRole->save();
                        }
                    }
    
                    if(!empty($_POST['roleTeacher'])){
                        $userRole = UserRole::find()->where(['user_id' => $id, 'role_id' => 3])->one();
                        if(empty($userRole)){
                            $newUserRole = new UserRole;
                            $newUserRole->user_id = $id;
                            $newUserRole->role_id = 3;
                            $newUserRole->save();
                        }
                    }
    
                    if(!empty($_POST['roleStudent'])){
                        $userRole = UserRole::find()->where(['user_id' => $id, 'role_id' => 4])->one();
                        if(empty($userRole)){
                            $newUserRole = new UserRole;
                            $newUserRole->user_id = $id;
                            $newUserRole->role_id = 4;
                            $newUserRole->save();
                        }
                    }
    
                    if(!empty($_POST['roleMember'])){
                        $userRole = UserRole::find()->where(['user_id' => $id, 'role_id' => 5])->one();
                        if(empty($userRole)){
                            $newUserRole = new UserRole;
                            $newUserRole->user_id = $id;
                            $newUserRole->role_id = 5;
                            $newUserRole->save();
                        }
                    }

                    //$profile->avatar =  Helper::UploadFile('avatar', $profile, '', $profile->avatar, 'picture');
                    $profile->user_id = $model->id;

                    $profile->save();

                    BackendHelper::saveUserLog('user', Yii::$app->user->identity->id, $model->id, 'create user', 'เพิ่มข้อมูลผู้ใช้' );

                    return $this->redirect(['view', 'id' => $model->id]);
                }

                
                
            }
        } 
       
        return $this->render('create', [
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
        
        //$oldPic = $profile->avatar;

        if(empty($profile)){
            $profile = new Profile();
        }

        if ($model->load(Yii::$app->request->post()) ) {
           
            if (empty($model->usersValidate())) {

                $canSave = $this->saveUsesrRole($_POST,'roleAdmin',$id,1);
                if($canSave == false){
                    $error[] = "ไม่สามารถบันทึก Role Admin";
                }

                $canSave = $this->saveUsesrRole($_POST,'roleEditor',$id,2);
                if($canSave == false){
                    $error[] = "ไม่สามารถบันทึก Role Editor";
                }

                $canSave = $this->saveUsesrRole($_POST,'roleTeacher',$id,3);
                if($canSave == false){
                    $error[] = "ไม่สามารถบันทึก Role Teacher";
                }else{
                    $userRolePendingTeacher = UserRole::find()->where(['user_id' => $id, 'role_id' => 6])->one();
                    if(!empty($userRolePendingTeacher)){
                        $userRolePendingTeacher->delete();
                    }
                }

                $canSave = $this->saveUsesrRole($_POST,'roleStudent',$id,4);
                if($canSave == false){
                    $error[] = "ไม่สามารถบันทึก Role Student";
                }

                $canSave = $this->saveUsesrRole($_POST,'roleMember',$id,5);
                if($canSave == false){
                    $error[] = "ไม่สามารถบันทึก Role Member";
                }

                if(!empty($userRole[0]['role_id'])){
                    if(empty($_POST['roleAdmin']) && empty($_POST['roleEditor']) && empty($_POST['roleMember']) && empty($_POST['roleTeacher']) && empty($_POST['roleStudent']) && $userRole[0]['role_id'] == 6){
                        $newUserRole = new UserRole;
                        $newUserRole->user_id = $id;
                        $newUserRole->role_id = 6;
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

                        BackendHelper::saveUserLog('user', Yii::$app->user->identity->id, $model->id, 'update user', 'แก้ไขข้อมูลผู้ใช้' );

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
        $model->email = "temp_".time()."@biogang.net";
        $model->username = "temp_".time()."@biogang.net";
        $model->blocked_at = 1;

        BackendHelper::saveUserLog('user', Yii::$app->user->identity->id, $model->id, 'delete user', 'ลบข้อมูลผู้ใช้ '.$model->getOldAttribute('email') );

        $model->save();

        return $this->redirect(['index']);
    }


    public function actionExport(){

        $special = 'normal';
        if(!empty($_GET['role_id'])){
            if($_GET['role_id'] == 4){
                $special = 'student';
                $query = Users::find()->select(['user.id as user_id', 'profile.firstname', 'profile.lastname', 'user.email', 'user.created_at', 'user.updated_at', 'user.last_login_at', 'role.name as role_name', 'school.name as school_name', 'teacher_profile.firstname as teacher_firstname', 'teacher_profile.lastname as teacher_lastname', 'province.name_th as province_name']);
                $query->leftjoin('profile', 'profile.user_id=user.id');
                $query->leftjoin('user_role', 'user_role.user_id = user.id');
                $query->leftjoin('role', 'role.id = user_role.role_id');
                $query->leftjoin('user_school', 'user_school.user_id = user.id');
                $query->leftjoin('school', 'school.id = user_school.school_id');
                $query->leftjoin('province', 'school.province_id = province.id');
                $query->leftjoin('student_teacher', 'student_teacher.student_id = user.id');
                $query->leftjoin('profile teacher_profile', 'teacher_profile.user_id = student_teacher.teacher_id');
                $query->andFilterWhere(['like', 'user_role.role_id', $_GET['role_id'] ]);
            }else if($_GET['role_id'] == 3){
                $special = 'teacher';
                $query = Users::find()->select(['user.id as user_id', 'profile.firstname', 'profile.lastname', 'user.email', 'user.created_at', 'user.updated_at', 'user.last_login_at', 'role.name as role_name', 'school.name as school_name', 'province.name_th as province_name']);
                $query->leftjoin('profile', 'profile.user_id=user.id');
                $query->leftjoin('user_role', 'user_role.user_id = user.id');
                $query->leftjoin('role', 'role.id = user_role.role_id');
                $query->leftjoin('user_school', 'user_school.user_id = user.id');
                $query->leftjoin('school', 'school.id = user_school.school_id');
                $query->leftjoin('province', 'school.province_id = province.id');
                $query->andFilterWhere(['like', 'user_role.role_id', $_GET['role_id'] ]);
            }else{
                $query = Users::find()->select(['user.id as user_id', 'profile.firstname', 'profile.lastname', 'user.email', 'user.created_at', 'user.updated_at', 'user.last_login_at', 'role.name as role_name', 'school.name as school_name', 'province.name_th as province_name']);//->where(['<>' , 'role', 'student'])->all();
                $query->leftjoin('profile', 'profile.user_id=user.id');
                $query->leftjoin('user_role', 'user_role.user_id = user.id');
                $query->leftjoin('role', 'role.id = user_role.role_id');
                $query->leftjoin('user_school', 'user_school.user_id = user.id');
                $query->leftjoin('school', 'school.id = user_school.school_id');
                $query->leftjoin('province', 'school.province_id = province.id');
                $query->andFilterWhere(['like', 'user_role.role_id', $_GET['role_id'] ]);
            }

        }else{
            $query = Users::find()->select(['user.id as user_id', 'profile.firstname', 'profile.lastname', 'user.email', 'user.created_at', 'user.updated_at', 'user.last_login_at', 'role.name as role_name', 'school.name as school_name', 'province.name_th as province_name']);//->where(['<>' , 'role', 'student'])->all();
            $query->leftjoin('profile', 'profile.user_id=user.id');
            $query->leftjoin('user_role', 'user_role.user_id = user.id');
            $query->leftjoin('role', 'role.id = user_role.role_id');
            $query->leftjoin('user_school', 'user_school.user_id = user.id');
            $query->leftjoin('school', 'school.id = user_school.school_id');
            $query->leftjoin('province', 'school.province_id = province.id');
        }


        if(!empty($_GET['fullname'])){
            $query->andFilterWhere([
                'or',
                ['like', 'profile.firstname', $_GET['fullname'] ],
                ['like', 'profile.lastname', $_GET['fullname'] ],
            ]);
        }


        if(!empty($_GET['email'])){
            $query->andFilterWhere(['like', 'user.email', $_GET['email'] ]);
        }

        if (!empty($_GET['school_name'])) {
            $query->andFilterWhere(
                ['like', 'school.name', $_GET['school_name']]
            );
        }

        if (!empty($_GET['province_name'])) {
            $query->andFilterWhere(
                ['like', 'province.name_th', $_GET['province_name']]
            );
        }


        if(!empty($_GET['created_at'])){

            $dateRang = explode('to', $_GET['created_at']);
            if (!empty($dateRang)) {
                if (count($dateRang) == 2) {

                    $dateStart = trim($dateRang[0]);
                    $dateEnd = trim($dateRang[1]);
                    if ($dateStart != $dateEnd) {
                        $dateEnd = date('Y-m-d', strtotime($dateEnd . ' +1 day'));

                        $query->andFilterWhere(['between', 'user.created_at', $dateStart, $dateEnd]);

                    } else {
                        $query->andFilterWhere(['like', 'user.created_at', $dateStart]);
                    }
                }
            }else{
                $query->andFilterWhere(['like', 'user.created_at', $_GET['created_at'] ]);
            }
        }

        if(!empty($_GET['updated_at'])){
            $query->andFilterWhere(['like', 'user.updated_at', $_GET['updated_at'] ]);
        }
        
        

        if(!empty($_GET['sort'])){
            switch($_GET['sort']){
                case 'fullname':
                    $query->orderBy('profile.firstname ASC');
                break;
                case '-fullname':
                    $query->orderBy('profile.firstname DESC');
                break;

                case 'created_at':
                    $query->orderBy('user.created_at ASC');
                break;
                case '-created_at':
                    $query->orderBy('user.created_at DESC');
                break;

                case 'updated_at':
                    $query->orderBy('user.updated_at ASC');
                break;
                case '-updated_at':
                    $query->orderBy('user.updated_at DESC');
                break;

                case 'schoolName':
                    $query->orderBy('school.name ASC');
                break;
                case '-schoolName':
                    $query->orderBy('school.name DESC');
                break;

                case 'provinceName':
                    $query->orderBy('province.name_th ASC');
                break;
                case '-provinceName':
                    $query->orderBy('province.name_th DESC');
                break;


                default:
                    $query->orderBy('user.id DESC');
                break;
            }
        }

        $query->groupBy('user.email');

        $result = $query->asArray()->all();

        // print '<pre>';
        // print_r($result);
        // print '</pre>';
        // exit();

        
        ob_start();
        header_remove();
        ob_end_clean();

        // path to admin/
        $this_dir = dirname(__FILE__);

        // admin's parent dir path can be represented by admin/..
        $parent_dir = realpath($this_dir . '/..');

        // concatenate the target path from the parent dir path
        $target_path = $parent_dir . '/web/export/export.csv';

        // open the file
        //$objWrite = fopen($target_path, 'wb') or die("can't open file");

    
        //fwrite($objWrite, pack("CCC",0xef,0xbb,0xbf)); 

        $index = 1;

        if ($special == 'student') {
            /* fwrite(
                $objWrite,
                "\"Name\",\"Email\",\"Role\",\"School Name\",\"Province\",\"Teacher Name\",\"Registered at\",\"Last Login at\"\n"
            ); */ 
            

            $dataArray[0] = ["Name", "Email", "Role", "School Name","Province", "Teacher Name", "Registered at", "Last Login at"];

            foreach ($result as $key => $value) {

                $roleName = BackendHelper::getRoleNameText($value['user_id']);
    
                //fwrite($objWrite, "\"".$value['firstname']." ".$value['lastname']."\",\"".$value['email']."\",\"".$roleName."\",\"".$value['school_name']."\",\"".$value['province_name']."\",\"".$value['teacher_firstname']." ".$value['teacher_lastname']."\",\"".$value['created_at']."\",\"".$value['last_login_at']."\"\n"); 
                
                $dataArray[$index] = [ $value['firstname']." ".$value['lastname'] , $value['email'], $roleName, $value['school_name'], $value['province_name'], $value['teacher_firstname']." ".$value['teacher_lastname'], $value['created_at'], $value['last_login_at'] ];
                $index++;
            
            }

        }else if ($special == 'teacher') {
            /* fwrite(
                $objWrite,
                "\"Name\",\"Email\",\"Role\",\"School Name\",\"Province\",\"Registered at\",\"Last Login at\"\n"
            ); */

            $dataArray[0] = ["Name", "Email", "Role", "School Name","Province", "Registered at", "Last Login at"];

            foreach ($result as $key => $value) {

                $roleName = BackendHelper::getRoleNameText($value['user_id']);
    
                //fwrite($objWrite, "\"".$value['firstname']." ".$value['lastname']."\",\"".$value['email']."\",\"".$roleName."\",\"".$value['school_name']."\",\"".$value['province_name']."\",\"".$value['created_at']."\",\"".$value['last_login_at']."\"\n"); 
            
                $dataArray[$index] = [ $value['firstname']." ".$value['lastname'] , $value['email'], $roleName, $value['school_name'], $value['province_name'], $value['created_at'], $value['last_login_at'] ];
                $index++;
            }

        }else{
            /* fwrite(
                $objWrite,
                "\"Name\",\"Email\",\"Role\",\"School Name\",\"Province\",\"Registered at\",\"Last Login at\"\n"
            ); */

            $dataArray[0] = ["Name", "Email", "Role", "School Name","Province", "Registered at", "Last Login at"];

            foreach ($result as $key => $value) {

                $roleName = BackendHelper::getRoleNameText($value['user_id']);
    
                //fwrite($objWrite, "\"".$value['firstname']." ".$value['lastname']."\",\"".$value['email']."\",\"".$roleName."\",\"".$value['school_name']."\",\"".$value['province_name']."\",\"".$value['created_at']."\",\"".$value['last_login_at']."\"\n"); 

                $dataArray[$index] = [ $value['firstname']." ".$value['lastname'] , $value['email'], $roleName, $value['school_name'], $value['province_name'], $value['created_at'], $value['last_login_at'] ];
                $index++;
            }
        }
        
        

        //fclose($objWrite);


        $file_export_name = "Users_".date('Ymd_His');


        $this->export_data_to_csv($dataArray, $file_export_name);


        // if (file_exists($target_path)) {

        //     Yii::$app->response->sendFile($target_path, $file_export_name );

        // }   
        
    }

    public static function export_data_to_csv($data, $filename = 'export', $delimiter = ',', $enclosure = '"')
    {
        // Tells to the browser that a file is returned, with its name : $filename.csv
        header("Content-disposition: attachment; filename=$filename.csv");
        // Tells to the browser that the content is a csv file
        header("Content-Type: text/csv");

        // I open PHP memory as a file
        $fp = fopen("php://output", 'w');

        // Insert the UTF-8 BOM in the file
        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        // I add the array keys as CSV headers
        // fputcsv($fp, array_keys($data[0]), $delimiter, $enclosure);

        // Add all the data in the file
        foreach ($data as $fields) {
            fputcsv($fp, $fields, $delimiter, $enclosure);
        }

        // Close the file
        fclose($fp);

        // Stop the script
        die();
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
