<?php

namespace frontend\controllers;

//use frontend\models\user\login\User;
use frontend\models\LoginForm;
use frontend\models\Users;


use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use dektrium\user\models\RegistrationForm;
use dektrium\user\models\User;
use dektrium\user\helpers\Password;
use dektrium\user\Module;
use dektrium\user\traits\AjaxValidationTrait;
use dektrium\user\traits\EventTrait;
use dektrium\user\Finder;
use dektrium\user\models\Account;
use frontend\components\PermissionAccess;

use frontend\components\FrontendHelper;

class LoginController extends Controller
{


    public function actionIndex()
    {

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $data = $_POST;
        $error = array();
        $model = \Yii::createObject(LoginForm::className());
        $modelUser = new Users;
        $modelUser->scenario = 'login';

        if(!empty($data)){

            // print '<pre>';
            // print_r($data);
            // print "</pre>";
            // exit();
            //ตรวจสอบ username และ password
            if (!empty($data['login-form']['login']) && !empty($data['login-form']['password'])) {
                $_csrf = Yii::$app->request->csrfToken;
                $post = array(
                    '_csrf-frontend' => $_csrf,
                    'login-form' => array(
                            'login' => $data['login-form']['login'],
                            'password' => $data['login-form']['password'],
                            'rememberMe' => 0,
                        )

                );

                if (!\Yii::$app->user->isGuest) {
                    $this->goHome();
                }

                
                if ($model->load($post)) {

           
                    if (!empty($post['login-form']['login'])) {

                        if (!empty($post['login-form']['password'])) {

                            // print '<pre>';
                            // print_r($model);
                            // print '</pre>';
                            // exit();
                            
                            if ($model->login()) {

                                // print '<pre>';
                                // print_r($model->login);
                                // print '</pre>';
                                // exit();

                                FrontendHelper::saveUserLog('user', Yii::$app->user->identity->id, Yii::$app->user->identity->id, 'login', 'Login เข้าสู่ระบบ IP: '.$_SERVER['REMOTE_ADDR']);

                                $session = Yii::$app->session;
                                //$this->trigger(self::EVENT_AFTER_LOGIN, $event);
                                if(!empty($session['currentUrl'])){
                                    return $this->redirect($_SESSION['currentUrl']);
                                }
                                unset($session['currentUrl']);

                                // print_r(Yii::$app->request->referrer);
                                // exit();

                                return $this->redirect('/profile');
                                
                                //return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
                            } else {
                                $error['message'] = $model->getErrors();

                                // print "<pre>";
                                // print_r($model->getErrors() );
                                // print "</pre>";
                                // exit();
                                
                                Yii::$app->getSession()->setFlash('alert-login',[
                                    'body'=> 'เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้ง',
                                    'options'=>['class'=>'alert-danger']
                                ]);    
                            }
          

                        }else{
                            Yii::$app->getSession()->setFlash('alert-login',[
                                'body'=>yii::t('app','รหัสผ่านต้องไม่เป็นค่าว่าง'),
                                'options'=>['class'=>'alert-danger']
                            ]);
                        }
                        
                    }else{
                        Yii::$app->getSession()->setFlash('alert-login',[
                            'body'=>yii::t('app','อีเมลผู้ใช้ต้องไม่เป็นค่าว่าง'),
                            'options'=>['class'=>'alert-danger']
                        ]);
                    }
                }

                
            }
            // else{
            //     //$error['message'] = Yii::t('app','valiLoginFromEmpty');
            //     Yii::$app->getSession()->setFlash('alert-login',[
            //    'body'=>yii::t('app','valiLoginFromEmpty'),
            //    'options'=>['class'=>'alert-danger']
            //     ]);
            // }
        }


        if(!empty($error)){
            if(!empty($error['message']['login'])){
                $error = $error['message']['login'][0];
            }
        }
        return $this->render('/user/login', [
            'model' => $model,
            'modelUser' => $modelUser

        ]);
    }
}
