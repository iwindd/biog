<?php

namespace frontend\controllers\user;

use Yii;
use frontend\models\Users;
use frontend\models\Profile;
use frontend\models\School;
use frontend\models\UserRole;
use frontend\models\UserSchool;
use frontend\models\StudentTeacher;
use yii\web\Controller;
use yii\helpers\ArrayHelper;
use common\components\_;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use frontend\components\PermissionAccess;
use yii\filters\VerbFilter;
use common\components\Upload;
use common\components\Helper;
use frontend\components\FrontendHelper;


use yii\helpers\Url;

class ProfileController extends Controller
{

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
                        'actions' => ['index'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) 
                        {
                            switch($action->id){
                                case 'index':
                                    return PermissionAccess::FrontendAccess('edit_profile', 'controller');
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

    public function actionIndex()
    {

        if (!empty(Yii::$app->user->identity->id)) {
            $error = array();
            $uid = Yii::$app->user->identity->id;
            $userModel = Users::findOne($uid);
            $profileModel = Profile::find()->where(['user_id' => $uid])->one();
            $userSchool = UserSchool::find()->where(['user_id' => $uid])->one();
            $userRole = UserRole::find()->where(['user_id' => $uid])->one();
            $studentTeacherModel = new StudentTeacher();

            $profileModel->invite_friend = Url::base(true)."/register?invite=".base64_encode($userModel->email);

            $schoolModel = new School();
            if (!empty($userSchool->school_id)) {
                $schoolModel = School::findOne($userSchool->school_id);
                if(!empty($schoolModel)){
                    $schoolModel->school_id = $schoolModel->id;
                } 
            }

            if(!empty($userRole)){
                if($userRole->role_id == 4){
                    $userModel->role = "student";
                }else if($userRole->role_id == 3 || $userRole->role_id == 6){
                    $userModel->role = "teacher";
                }
            }

            //get teacher of student
            $teacherOfStudent = array();
            $getTeacher = (new \yii\db\Query())
                ->select(['profile.user_id', 'firstname', 'lastname'])
                ->from('profile')
                ->innerJoin('student_teacher','student_teacher.teacher_id = profile.user_id')
                ->innerJoin('user_role','user_role.user_id = profile.user_id')
                ->where(['user_role.role_id' => 3])
                ->andWhere(['student_teacher.student_id' => $uid, 'student_teacher.active' => 1])
                ->all();
            if(!empty($getTeacher)){
                $teacherOfStudent = ArrayHelper::getColumn($getTeacher,'user_id');
                $studentTeacherModel->teacher = $teacherOfStudent;
            }

            //load post
            if ($userModel->load(Yii::$app->request->post())) {

                $transaction = \Yii::$app->db->beginTransaction();
                try {
                    $checkUpdate = true;
                    if (empty($userModel->usersValidate())) {
                        $profileModel->load(Yii::$app->request->post());
                        $schoolModel->load(Yii::$app->request->post());
                        $studentTeacherModel->load(Yii::$app->request->post());

                        if (!$profileModel->validate()) {
                            $checkUpdate = false;
                        }

                        $post = Yii::$app->request->post();

                        $del = 0;
                        if(!empty($_POST['deletePic'])){
                            $del = $_POST['deletePic'];
                        }

                        $mainPicture = Upload::uploadPictureNoPermission($profileModel, 'profile',  $profileModel->getOldAttribute('picture'), $del);
                        if (!empty($mainPicture)) {
                            if ($mainPicture != 'error') {
                                $profileModel->picture = $mainPicture;
                            } else {
                                $error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                                $checkUpdate = false;
                            }
                        }
    
                        $userModel->username = $userModel->email;
                        $userModel->created_at = date("Y-m-d H:i:s");
                        $userModel->updated_at = date("Y-m-d H:i:s");
                        $userModel->auth_key = md5("Active".date("Y-m-d H:i:s"));
                        if (!empty($userModel->new_password)) {
                            $userModel->password_hash = \Yii::$app->security->generatePasswordHash($userModel->new_password);
                        }
    
            
                        if ($checkUpdate && $userModel->save()) {
                            $uid = $userModel->id;
                            //check add role
                            if (!empty($userModel->role)) {
                                if($userModel->role == 'student'){
                                    $userRole->role_id = 4;
                                }else if($userModel->role == 'teacher'){
                                    //old
                                    if($userRole->role_id != 3){
                                        $userRole->role_id = 6;
                                    }
                                }
                            } else {
                                $userRole->role_id = 5;
                            }
                            $userRole->save();
    
                            //check school
                            if (!empty($userModel->role)) {
                                if (!empty($schoolModel->school_id)) {
                                    if ($schoolModel->school_id != $schoolModel->getOldAttribute('id')) {

                                        if ($userModel->role == 'teacher') {
                                            $this->changeSchoolForteacher($uid, $schoolModel->getOldAttribute('id'));
                                        }
       
                                        $checkDupSchool = School::find()->where(['id' => $schoolModel->school_id])->one();

                                        if (!empty($checkDupSchool)) {
                                            if (!empty($userSchool)) {
                                                $userSchool->school_id = $checkDupSchool->id;
                                                $userSchool->updated_at = date("Y-m-d H:i:s");
                                                $userSchool->save();
                                            } else {
                                                $userSchoolNew = new UserSchool();
                                                $userSchoolNew->user_id = $uid;
                                                $userSchoolNew->school_id = $checkDupSchool->id;
                                                $userSchoolNew->created_at = date("Y-m-d H:i:s");
                                                $userSchoolNew->updated_at = date("Y-m-d H:i:s");
                                                $userSchoolNew->save();
                                            }
                                        }
                                    }
                                } else {
                                    //ลบข้อมูล user ออกจากโรงเรียน
                                    if (!empty($userSchool)) {
                                        $userSchool->delete();
                                    }
                                }
                            }

                    
                            //check student teacher
                            if(!empty($studentTeacherModel->teacher)){
                                $studentTeacherModel->deleteAll(['AND', "student_id = ${uid}", ['NOT IN', 'teacher_id', $studentTeacherModel->teacher]]);
                                foreach($studentTeacherModel->teacher as $teacher_id){
                                    $checkDupTeacher = StudentTeacher::find()->where(['student_id' => $uid, 'teacher_id' => $teacher_id])->one();
                                    if(empty($checkDupTeacher)){
                                        $studentTeacherModelnew = new StudentTeacher();
                                        $studentTeacherModelnew->student_id = $uid;
                                        $studentTeacherModelnew->teacher_id = $teacher_id;
                                        $studentTeacherModelnew->active = 1;
                                        $studentTeacherModelnew->created_at =  date("Y-m-d H:i:s");
                                        $studentTeacherModelnew->updated_at =  date("Y-m-d H:i:s");
                                        $studentTeacherModelnew->save();
                                    }else{
                                        if ($checkDupTeacher->active == 0) {
                                            $checkDupTeacher->student_id = $uid;
                                            $checkDupTeacher->teacher_id = $teacher_id;
                                            $checkDupTeacher->active = 1;
                                            $checkDupTeacher->updated_at =  date("Y-m-d H:i:s");
                                            $checkDupTeacher->save();
                                        }
                                    }
                                }
                            }else{
                                $studentTeacherModel->deleteAll(['AND', "student_id = ${uid}", ['NOT IN', 'teacher_id', $studentTeacherModel->teacher]]);
                            } 
    
                            if(!empty($profileModel->birthdate)){
                                $bd  = "";
                                $birthdate = explode('/', $profileModel->birthdate);
                                if (!empty($birthdate[2])) {
                                    $bd = $birthdate[2].'-'.$birthdate[1].'-'.$birthdate[0];
                                }
    
                                $profileModel->birthdate = $bd;
                    
                            }
    
                            $profileModel->user_id = $uid;
    
                            $profileModel->updated_at = date("Y-m-d H:i:s");
    
                            if (!$profileModel->save()) {
                                $checkUpdate = false;
                            }
    
                            if ($checkUpdate) {
                                $transaction->commit();

                                FrontendHelper::saveUserLog('user', $uid, $uid, 'update profile', 'Update ข้อมูลส่วนตัว IP: '.$_SERVER['REMOTE_ADDR']);

                                Yii::$app->getSession()->setFlash('edit_success',[
                                    'body'=> 'แก้ไขข้อมูลส่วนตัวสำเร็จ',
                                    'options'=>['class'=>'alert-success']
                                ]);    

                                return $this->redirect(['/profile']);
                            }

                        }
    
                    }

                    if ($transaction->getIsActive()) {
                        $transaction->rollBack();
                    }
                }catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            } 

            //convert birthdate
            if(!empty($profileModel->birthdate)){
                $bd  = "";
                $birthdate = explode('-', $profileModel->birthdate);
                if (!empty($birthdate[2])) {
                    $bd = $birthdate[2].'/'.$birthdate[1].'/'.$birthdate[0];
                }

                $profileModel->birthdate = $bd;
    
            }

            //get teacher school
            $dataTeacherSchool = array();
            if(!empty($schoolModel->school_id)){
                $teacher = (new \yii\db\Query())
                    ->select(['profile.user_id', 'firstname', 'lastname'])
                    ->from('profile')
                    ->innerJoin('user_school','user_school.user_id = profile.user_id')
                    ->innerJoin('user_role','user_role.user_id = profile.user_id')
                    ->where(['user_role.role_id' => 3])
                    ->andWhere(['user_school.school_id' => $schoolModel->school_id])
                    ->all();
                if(!empty($teacher)){
                    $dataTeacherSchool = ArrayHelper::map($teacher, 'user_id', function($model){ 
                        return $model['firstname'].' '.$model['lastname'];
                    });
                }
                // print '<pre>';
                // print_r($dataTeacherSchool);
                // print '</pre>';
                // exit();
            }

            

            // print '<pre>';
            // print_r($studentTeacherModel);
            // print '</pre>';
            // exit();

            return $this->render('profile', [
                'userModel' => $userModel,
                'profileModel' => $profileModel,
                'schoolModel' => $schoolModel,
                'studentTeacherModel' => $studentTeacherModel,
                'dataTeacherSchool' => $dataTeacherSchool,
                'userRole' => $userRole,
                'case_error' => $error
            ]);
        }

        return $this->redirect('/');

    }

    private function changeSchoolForteacher($teacherId, $oldSchoolId){
        

        Helper::sendMailStudentTeacherChangeSchool($teacherId);

        StudentTeacher::updateAll(['active' => 0], ['teacher_id' => $teacherId]);
        // if (!empty($oldSchoolId)) {
        //     UserSchool::deleteAll(['AND', "user_id = ${teacherId}", "school_id = ${oldSchoolId}"]);
        // }

        // print '<pre>';
        // print_r($update);
        // print "</pre>";
        // exit();

    }

    private function changeSchoolForstudent($studentId, $oldSchoolId){
        // StudentTeacher::deleteAll(['student_id' => $studentId]);
        // if (!empty($oldSchoolId)) {
        //     UserSchool::deleteAll(['AND', "user_id = ${studentId}", "school_id = ${oldSchoolId}"]);
        // }
    }
}
