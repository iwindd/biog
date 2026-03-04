<?php

namespace frontend\controllers\user;

use Yii;
use yii\web\Controller;
use frontend\models\Users;
use frontend\models\Profile;
use frontend\models\School;
use frontend\models\UserRole;
use frontend\models\UserSchool;
use frontend\components\FrontendHelper;
use common\components\Helper;

use common\components\Upload;

class RegisterController extends Controller
{

    public function actionRegister()
    {

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        
        $error = array();
        $userModel = new Users();
        $profileModel = new Profile();
        $schoolModel = new School();

        $userModel->scenario = 'create';

        if ($userModel->load(Yii::$app->request->post())) {

            $post = Yii::$app->request->post();

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $checkUpdate = true;
                if (empty($userModel->usersValidate())) {
                    $profileModel->load(Yii::$app->request->post());
                    $schoolModel->load(Yii::$app->request->post());

                    $mainPicture = Upload::uploadPictureNoPermission($profileModel, 'profile');
                    if (!empty($mainPicture)) {
                        if ($mainPicture != 'error') {
                            $profileModel->picture = $mainPicture;
                        } else {
                            $error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                            $checkUpdate = false;
                        }
                    }

                    //invite by friend
                    if(!empty($profileModel->invite_friend)){
                        $friend = Users::find()->where(['email' => $profileModel->invite_friend])->one();
                        if(!empty($friend)){
                            $userModel->invited_by_user_id = $friend->id;
                        }
                    }

                    $userModel->username = $userModel->email;
                    $userModel->created_at = date("Y-m-d H:i:s");
                    $userModel->updated_at = date("Y-m-d H:i:s");
                    $userModel->auth_key = md5("Active".date("Y-m-d H:i:s"));
                    $userModel->registration_ip = $_SERVER['REMOTE_ADDR'];

                    if (!empty($userModel->new_password)) {
                        $userModel->password_hash = \Yii::$app->security->generatePasswordHash($userModel->new_password);
                    }

                    if ($userModel->save()) {
                        $uid = $userModel->id;

                        //check add role
                        $userRole = new UserRole();
                        if (!empty($userModel->role)) {

                            if($userModel->role == 'student'){
                                $userRole->user_id = $uid;
                                $userRole->role_id = 7; // Pending Student - ต้องรอแอดมินอนุมัติ
                                $userRole->save();
                            }else if($userModel->role == 'teacher'){
                                $userRole->user_id = $uid;
                                $userRole->role_id = 6;
                                $userRole->save();
                            }
            
                        } else {
                            $userRole->user_id = $uid;
                            $userRole->role_id = 5;
                            $userRole->save();
                        }

                        //check school
                        if (!empty($userModel->role)) {
                            $checkDupSchool = School::find()->where(['name' => $schoolModel->name])->one();
                            if (!empty($checkDupSchool)) {
                                $userSchool = new UserSchool();
                                $userSchool->user_id = $uid;
                                $userSchool->school_id = $checkDupSchool->id;
                                $userSchool->created_at = date("Y-m-d H:i:s");
                                $userSchool->updated_at = date("Y-m-d H:i:s");
                                $userSchool->save();
                            }
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

                        $profileModel->save();

                        if ($checkUpdate) {
                            $transaction->commit();

                            FrontendHelper::saveUserLog('user', $uid, $uid, 'register', 'Register เข้าสู่ระบบ IP: '.$_SERVER['REMOTE_ADDR']);

                            // Notify Admin
                            Helper::sendMailNewRegistrationToAdmin($uid, $userModel->role);

                            \Yii::$app->getSession()->setFlash('REGISTER_SUCCESS',[
                                'body'=> "สมัครสมาชิกสำเร็จ",
                                'options'=>['class'=>'alert-success']
                            ]);

                            return $this->redirect(['/login']);
                        }


                    }
                }
            }catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        } 

        return $this->render('register', [
            'userModel' => $userModel,
            'profileModel' => $profileModel,
            'schoolModel' => $schoolModel,
            'case_error' => $error
        ]);
    }
}
