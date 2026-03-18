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
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $this->setupUserCredentials($userModel);
                if (empty($userModel->usersValidate())) {
                    $profileModel->load(Yii::$app->request->post());
                    $schoolModel->load(Yii::$app->request->post());

                    $checkUpdate = $this->handleProfilePicture($profileModel, $error);
                    $checkUpdate = $this->handleHybridInvite($profileModel, $userModel) && $checkUpdate;

                    if ($checkUpdate) {

                        if ($userModel->save()) {
                            $uid = $userModel->id;

                            $this->assignUserRole($uid, $userModel->role);
                            $this->assignUserSchool($uid, $userModel->role, $schoolModel);
                            $this->saveProfile($uid, $profileModel);

                            $transaction->commit();

                            FrontendHelper::saveUserLog('user', $uid, $uid, 'register', 'Register เข้าสู่ระบบ IP: '.$_SERVER['REMOTE_ADDR']);
                            Helper::sendMailNewRegistrationToAdmin($uid, $userModel->role);

                            \Yii::$app->getSession()->setFlash('REGISTER_SUCCESS',[
                                'body'=> "สมัครสมาชิกสำเร็จ",
                                'options'=>['class'=>'alert-success']
                            ]);

                            return $this->redirect(['/login']);
                        }
                    }
                }
            } catch (\Exception $e) {
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

    private function handleProfilePicture($profileModel, &$error)
    {
        $mainPicture = Upload::uploadPictureNoPermission($profileModel, 'profile');
        if (!empty($mainPicture)) {
            if ($mainPicture != 'error') {
                $profileModel->picture = $mainPicture;
                return true;
            } else {
                $error[] = "อัพโหลดรูปภาพไม่สำเร็จ";
                return false;
            }
        }
        return true;
    }

    private function handleHybridInvite($profileModel, $userModel)
    {
        if(!empty($profileModel->invite_friend)){
            $friend = Users::find()
                ->joinWith('profile', true, 'INNER JOIN')
                ->where(['user.email' => $profileModel->invite_friend])
                ->orWhere(['profile.invite_code' => $profileModel->invite_friend])
                ->one();
            if(!empty($friend)){
                $userModel->invited_by_user_id = $friend->id;
                return true;
            } else {
                $profileModel->addError('invite_friend', 'ไม่พบรหัสหรืออีเมลผู้แนะนำนี้ในระบบ');
                return false;
            }
        }
        return true;
    }

    private function setupUserCredentials($userModel)
    {
        $now = date("Y-m-d H:i:s");
        $userModel->username = $userModel->email;
        $userModel->created_at = $now;
        $userModel->updated_at = $now;
        $userModel->auth_key = md5("Active".$now);
        $userModel->registration_ip = $_SERVER['REMOTE_ADDR'];

        if (!empty($userModel->new_password)) {
            $userModel->password_hash = \Yii::$app->security->generatePasswordHash($userModel->new_password);
        }
    }

    private function assignUserRole($uid, $role)
    {
        $userRole = new UserRole();
        $userRole->user_id = $uid;

        if (!empty($role)) {
            if($role == 'student'){
                $userRole->role_id = 7; // Pending Student - ต้องรอแอดมินอนุมัติ
            }elseif($role == 'teacher'){
                $userRole->role_id = 6;
            }
        } else {
            $userRole->role_id = 5;
        }
        $userRole->save();
    }

    private function assignUserSchool($uid, $role, $schoolModel)
    {
        if (!empty($role)) {
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
    }

    private function saveProfile($uid, $profileModel)
    {
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
    }
}
