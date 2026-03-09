<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use frontend\models\Users;
use frontend\models\Profile;
use yii\helpers\Url;

class ThaidController extends Controller
{
    private function getThaidBaseUrl()
    {
        $env = getenv('THAID_ENV') ?: 'sandbox';
        if ($env === 'production') {
            return 'https://imauth.bora.dopa.go.th';
        }
        return 'https://imauthsbx.bora.dopa.go.th';
    }

    public function actionAuth()
    {
        $clientId = getenv('THAID_CLIENT_ID');
        $redirectUri = Url::to(['/thaid/callback'], true);
        
        $state = Yii::$app->security->generateRandomString(16);
        Yii::$app->session->set('thaid_state', $state);

        $baseUrl = $this->getThaidBaseUrl();
        $authUrl = $baseUrl . '/api/v2/oauth2/auth/?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'pid name birthdate',
            'state' => $state
        ]);

        return $this->redirect($authUrl);
    }

    public function actionCallback()
    {
        $request = Yii::$app->request;
        $code = $request->get('code');
        $state = $request->get('state');

        $savedState = Yii::$app->session->get('thaid_state');
        if (!$state || $state !== $savedState) {
            Yii::$app->session->setFlash('alert-login', [
                'body' => 'Invalid state parameter.',
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/login']);
        }

        Yii::$app->session->remove('thaid_state');

        // Exchange code for token
        $baseUrl = $this->getThaidBaseUrl();
        $tokenUrl = $baseUrl . '/api/v2/oauth2/token/';
        $redirectUri = Url::to(['/thaid/callback'], true);
        $basicToken = getenv('THAID_BASIC_TOKEN');

        if (!$basicToken) {
            $clientId = getenv('THAID_CLIENT_ID');
            $clientSecret = getenv('THAID_SECRET');
            $basicToken = base64_encode($clientId . ':' . $clientSecret);
        }

        $postData = http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . $basicToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Yii::$app->session->setFlash('alert-login', [
                'body' => 'Failed to authenticate with ThaID.',
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/login']);
        }

        $data = json_decode($response, true);
        if (!isset($data['pid']) || !isset($data['name'])) {
            Yii::$app->session->setFlash('alert-login', [
                'body' => 'Incomplete data received from ThaID.',
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/login']);
        }

        $pid = $data['pid'];
        $name = $data['name'];
        // Name usually comes as "ชื่อ นามสกุล"
        $nameParts = explode(' ', $name, 2);
        $firstname = $nameParts[0];
        $lastname = isset($nameParts[1]) ? $nameParts[1] : '';

        // Check user_thaid instead of users
        $userThaid = \common\models\UserThaid::findOne(['pid' => $pid]);

        if (!Yii::$app->user->isGuest) {
            // Flow: Existing logged-in user connecting to ThaID
            if ($userThaid) {
                if ($userThaid->user_id == Yii::$app->user->id) {
                    Yii::$app->session->setFlash('edit_success', [
                        'body'=> 'บัญชีนี้เชื่อมต่อกับ ThaID เรียบร้อยแล้ว',
                        'options'=>['class'=>'alert-info']
                    ]);
                }
                return $this->redirect(['/profile']);
            }

            // Create new linkage
            $newConnection = new \common\models\UserThaid();
            $newConnection->user_id = Yii::$app->user->id;
            $newConnection->pid = $pid;
            $newConnection->created_at = date('Y-m-d H:i:s');
            
            if ($newConnection->save(false)) {
                \frontend\components\FrontendHelper::saveUserLog('user', Yii::$app->user->id, Yii::$app->user->id, 'connect_thaid', 'เชื่อมต่อบัญชีกับ ThaID สำเร็จ IP: '.$_SERVER['REMOTE_ADDR']);
                Yii::$app->session->setFlash('edit_success', [
                    'body'=> 'เชื่อมต่อกับ ThaID สำเร็จแล้ว!',
                    'options'=>['class'=>'alert-success']
                ]);
            } else {
                Yii::$app->session->setFlash('alert-home', [
                'body' => 'ระบบเกิดข้อผิดพลาดในการเชื่อมต่อ',
                    'options'=>['class'=>'alert-danger']
                ]);
            }
            return $this->redirect(['/profile']);
        }

        // Flow: Guest context (Login or Registration)
        if (!$userThaid) {
            // Store ThaID raw data to session and redirect to registration page to complete
            Yii::$app->session->set('thaid_register_data', [
                'pid' => $pid,
                'name' => $name,
                'firstname' => $firstname,
                'lastname' => $lastname
            ]);
            return $this->redirect(['/thaid/register']);
        }

        // dektrium/user login via web user identity
        $identity = \Yii::createObject(\dektrium\user\models\User::className())::findOne($userThaid->user_id);
        if ($identity && Yii::$app->user->login($identity, 3600 * 24 * 30)) {
            \frontend\components\FrontendHelper::saveUserLog('user', $userThaid->user_id, $userThaid->user_id, 'login', 'Login ด้วย ThaID IP: '.$_SERVER['REMOTE_ADDR']);
            return $this->redirect(['/profile']);
        } else {
            Yii::$app->session->setFlash('alert-login', [
                'body' => 'Could not login user identity.',
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/login']);
        }
    }

    public function actionRegister()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/profile']);
        }

        $thaidData = Yii::$app->session->get('thaid_register_data');
        if (!$thaidData) {
            return $this->redirect(['/login']);
        }

        $error = array();
        $userModel = new \frontend\models\Users();
        $profileModel = new \frontend\models\Profile();
        $schoolModel = new \frontend\models\School();

        $userModel->scenario = 'create';
        // set default from ThaID
        if (empty(Yii::$app->request->post())) {
            $profileModel->firstname = $thaidData['firstname'];
            $profileModel->lastname = $thaidData['lastname'];
            $profileModel->display_name = $thaidData['name'];
        }

        if ($userModel->load(Yii::$app->request->post())) {
            
            // Generate random password to pass validation
            $tempPass = Yii::$app->security->generateRandomString(12);
            $userModel->new_password = $tempPass;
            $userModel->confirm_password = $tempPass;

            $transaction = \Yii::$app->db->beginTransaction();
            try {
                if (empty($userModel->usersValidate())) {
                    $profileModel->load(Yii::$app->request->post());
                    $schoolModel->load(Yii::$app->request->post());

                    // handle picture
                    $mainPicture = \common\components\Upload::uploadPictureNoPermission($profileModel, 'profile');
                    if (!empty($mainPicture) && $mainPicture != 'error') {
                        $profileModel->picture = $mainPicture;
                    } elseif ($mainPicture == 'error') {
                        $error[] = 'อัพโหลดรูปภาพไม่สำเร็จ';
                    }

                    // Hybrid invite
                    $checkUpdate = true;
                    if(!empty($profileModel->invite_friend)){
                        $friend = \frontend\models\Users::find()
                            ->joinWith('profile', true, 'INNER JOIN')
                            ->where(['user.email' => $profileModel->invite_friend])
                            ->orWhere(['profile.invite_code' => $profileModel->invite_friend])
                            ->one();
                        if(!empty($friend)){
                            $userModel->invited_by_user_id = $friend->id;
                        } else {
                            $profileModel->addError('invite_friend', 'ไม่พบรหัสหรืออีเมลผู้แนะนำนี้ในระบบ');
                            $checkUpdate = false;
                        }
                    }

                    if ($checkUpdate) {
                        $now = date('Y-m-d H:i:s');
                        // Generate a unique username based on email or random string
                        $userModel->username = 'thaid_' . uniqid(); 
                        $userModel->created_at = $now;
                        $userModel->updated_at = $now;
                        $userModel->confirmed_at = $now;
                        $userModel->auth_key = md5('Active'.$now);
                        $userModel->registration_ip = $_SERVER['REMOTE_ADDR'] ?? '';
                        $userModel->password_hash = \Yii::$app->security->generatePasswordHash($tempPass);

                        if ($userModel->save(false)) { 
                            $uid = $userModel->id;

                            // assign role
                            $userRole = new \frontend\models\UserRole();
                            $userRole->user_id = $uid;
                            $role = $userModel->role;
                            if (!empty($role)) {
                                if($role == 'student'){
                                    $userRole->role_id = 7;
                                }elseif($role == 'teacher'){
                                    $userRole->role_id = 6;
                                }
                            } else {
                                $userRole->role_id = 5;
                            }
                            $userRole->save();

                            // assign school
                            if (!empty($role) && !empty($schoolModel->name)) {
                                $checkDupSchool = \frontend\models\School::find()->where(['name' => $schoolModel->name])->one();
                                if (!empty($checkDupSchool)) {
                                    $userSchool = new \frontend\models\UserSchool();
                                    $userSchool->user_id = $uid;
                                    $userSchool->school_id = $checkDupSchool->id;
                                    $userSchool->created_at = date('Y-m-d H:i:s');
                                    $userSchool->updated_at = date('Y-m-d H:i:s');
                                    $userSchool->save();
                                }
                            }

                            // save profile
                            if(!empty($profileModel->birthdate)){
                                $bd  = '';
                                $birthdate = explode('/', $profileModel->birthdate);
                                if (!empty($birthdate[2])) {
                                    $bd = $birthdate[2].'-'.$birthdate[1].'-'.$birthdate[0];
                                }
                                $profileModel->birthdate = $bd;
                            }

                            $profileModel->user_id = $uid;
                            $profileModel->updated_at = date('Y-m-d H:i:s');
                            if($profileModel->save(false)) {
                                
                                // Save UserThaid link
                                $userThaid = new \common\models\UserThaid();
                                $userThaid->user_id = $uid;
                                $userThaid->pid = $thaidData['pid'];
                                $userThaid->created_at = $now;
                                if (!$userThaid->save(false)) {
                                    $error[] = 'บันทึกการเชื่อมต่อ ThaID ไม่สำเร็จ';
                                    $transaction->rollBack();
                                    return $this->render('/thaid/register', [
                                        'userModel' => $userModel,
                                        'profileModel' => $profileModel,
                                        'schoolModel' => $schoolModel,
                                        'case_error' => $error
                                    ]);
                                }

                                $transaction->commit();
                                Yii::$app->session->remove('thaid_register_data');

                                \frontend\components\FrontendHelper::saveUserLog('user', $uid, $uid, 'register', 'ThaID Register IP: '.($_SERVER['REMOTE_ADDR'] ?? ''));
                                \common\components\Helper::sendMailNewRegistrationToAdmin($uid, $userModel->role);

                                $identity = \Yii::createObject(\dektrium\user\models\User::className())::findOne($uid);
                                Yii::$app->user->login($identity, 3600 * 24 * 30);
                                
                                \Yii::$app->getSession()->setFlash('REGISTER_SUCCESS',[
                                    'body'=> 'สมัครสมาชิกผ่าน ThaID สำเร็จ',
                                    'options'=>['class'=>'alert-success']
                                ]);

                                return $this->redirect(['/profile']);
                            } else {
                                $error[] = 'บันทึก Profile ไม่สำเร็จ';
                                $transaction->rollBack();
                            }
                        } else {
                            $error[] = 'บันทึก User ไม่สำเร็จ';
                            $transaction->rollBack();
                        }
                    }
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                $error[] = $e->getMessage();
            }
        } 

        return $this->render('/thaid/register', [
            'userModel' => $userModel,
            'profileModel' => $profileModel,
            'schoolModel' => $schoolModel,
            'case_error' => $error
        ]);
    }

    public function actionDisconnect()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/login']);
        }

        $uid = Yii::$app->user->id;
        $userThaid = \frontend\models\UserThaid::findOne(['user_id' => $uid]);

        if ($userThaid) {
            if ($userThaid->delete()) {
                \frontend\components\FrontendHelper::saveUserLog('user', $uid, $uid, 'disconnect_thaid', 'ยกเลิกการเชื่อมต่อกับ ThaID สำเร็จ IP: '.$_SERVER['REMOTE_ADDR']);
                Yii::$app->session->setFlash('edit_success', [
                    'body'=> 'ยกเลิกการเชื่อมต่อกับ ThaID สำเร็จแล้ว',
                    'options'=>['class'=>'alert-success']
                ]);
            } else {
                Yii::$app->session->setFlash('edit_success', [
                    'body'=> 'ไม่สามารถยกเลิกการเชื่อมต่อได้ในขณะนี้ กรุณาลองใหม่ภายหลัง',
                    'options'=>['class'=>'alert-danger']
                ]);
            }
        }

        return $this->redirect(['/profile']);
    }
}
