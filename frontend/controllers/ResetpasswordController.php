<?php

namespace frontend\controllers;

use frontend\models\Users;
use frontend\models\Profile;
use frontend\models\Variables;
use frontend\models\RecoveryForm;

use yii\web\Controller;

use dektrium\user\Finder;
use dektrium\user\models\Token;
use dektrium\user\traits\AjaxValidationTrait;
use dektrium\user\traits\EventTrait;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

class ResetpasswordController extends Controller
{

    use AjaxValidationTrait;
    use EventTrait;

    /**
     * Event is triggered before requesting password reset.
     * Triggered with \dektrium\user\events\FormEvent.
     */
    const EVENT_BEFORE_REQUEST = 'beforeRequest';

    /**
     * Event is triggered after requesting password reset.
     * Triggered with \dektrium\user\events\FormEvent.
     */
    const EVENT_AFTER_REQUEST = 'afterRequest';

    /**
     * Event is triggered before validating recovery token.
     * Triggered with \dektrium\user\events\ResetPasswordEvent. May not have $form property set.
     */
    const EVENT_BEFORE_TOKEN_VALIDATE = 'beforeTokenValidate';

    /**
     * Event is triggered after validating recovery token.
     * Triggered with \dektrium\user\events\ResetPasswordEvent. May not have $form property set.
     */
    const EVENT_AFTER_TOKEN_VALIDATE = 'afterTokenValidate';

    /**
     * Event is triggered before resetting password.
     * Triggered with \dektrium\user\events\ResetPasswordEvent.
     */
    const EVENT_BEFORE_RESET = 'beforeReset';

    /**
     * Event is triggered after resetting password.
     * Triggered with \dektrium\user\events\ResetPasswordEvent.
     */
    const EVENT_AFTER_RESET = 'afterReset';

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

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'reset'], 'roles' => ['?']],
                ],
            ],
        ];
    }

    public function actionIndex()
    {

        // if (!$this->module->enablePasswordRecovery) {
        //     throw new NotFoundHttpException();
        // }

        /** @var RecoveryForm $model */
        $model = \Yii::createObject([
            'class'    => RecoveryForm::className(),
            'scenario' => RecoveryForm::SCENARIO_REQUEST,
        ]);
        
        if ($model->load(\Yii::$app->request->post()) ) {
            $user = Users::find()->where(['email' => $model->email])->one();
            
            if(!empty($user)){

                $profile = Profile::find()->where(['user_id' => $user->id])->one();


                //$setSubject = Variables::find()->where(['key' => 'forget_password_subject'])->one();
                //$setSubject = empty($setSubject['value'])? "American Learning: Reset Password":strip_tags($setSubject['value']);
                $setSubject = "BIOGANG: Reset Password";
                $setMessage1 = Variables::find()->where(['key' => 'forget_password_message'])->one();
                $setMessage1 = empty($setMessage1['value'])? "":$setMessage1['value'];

                // $setMessage2 = Variables::find()->where(['key' => 'forget_password_message2'])->one();
                // $setMessage2 = empty($setMessage2['value'])? "":$setMessage2['value'];

                $mail_sender = Variables::find()->where(['key' => 'sender_mail'])->one();
                if(!empty($mail_sender)){
                    $token = \Yii::createObject([
                        'class' => Token::className(),
                        'user_id' => $user->id,
                        'type' => Token::TYPE_RECOVERY,
                    ]);

                    if($token->save(false)){
                        $hostname = getenv('HTTP_HOST');
                        $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';

                        if(empty($hostname)){
                            $hostname = "localhost:8080";
                        }

                        $setMessage = "";
                        if(!empty($setMessage1)){
                          $setMessage =  str_replace("[link-reset-password]",$protocol.$hostname."/member/recover/".$user->id."/".$token->code,$setMessage1);

                          $setMessage =  str_replace("[user-name]",$profile->firstname." ".$profile->lastname,$setMessage);
                        }

                        \Yii::$app->mailer->compose()
                        ->setFrom([$mail_sender['value']=>'BIOGANG'])
                        ->setTo($user->email)
                        ->setSubject($setSubject)
                        ->setTextBody($setMessage)
                        ->send();


                        //$this->trigger(self::EVENT_AFTER_REQUEST, $event);

                        \Yii::$app->getSession()->setFlash('REGISTER_SUCCESS',[
                          'body'=> "โปรดตรวจสอบอีเมล เพื่อแก้ไขรหัสผ่านของท่านใหม่",
                          'options'=>['class'=>'alert-info']
                        ]);

                    }
                    
                    return $this->redirect(['/login']);
                }
                
            }else{
                \Yii::$app->getSession()->setFlash('alert-request',[
                  'body'=> \Yii::t('app','ไม่พบข้อมูลที่อยู่อีเมลในระบบ'),
                  'options'=>['class'=>'alert-danger']
                ]);
            }
        }

        return $this->render('/user/request', [
            'userModel' => $model,
        ]);
       
    }

    public function actionReset($id, $code)
    {

        // print "<pre>";
        // print_r($id);
        // print "</pre>";

        // exit();

        if (!$this->module->loadedModules['dektrium\user\Module']->enablePasswordRecovery) {
            throw new NotFoundHttpException();
        }

        /** @var Token $token */
        $token = $this->finder->findToken(['user_id' => $id, 'code' => $code, 'type' => Token::TYPE_RECOVERY])->one();

     

        if (empty($token) || ! $token instanceof Token) {
            throw new NotFoundHttpException();
        }
        //$event = $this->getResetPasswordEvent($token);


        //$this->trigger(self::EVENT_BEFORE_TOKEN_VALIDATE, $event);

        if ($token === null || $token->isExpired || $token->user === null) {
            //$this->trigger(self::EVENT_AFTER_TOKEN_VALIDATE, $event);
            \Yii::$app->session->setFlash(
                'danger',
                \Yii::t('user', 'Recovery link is invalid or expired. Please try requesting a new one.')
            );
            return $this->render('/message', [
                'title'  => \Yii::t('user', 'Invalid or expired link'),
                'module' => $this->module->loadedModules['dektrium\user\Module'],
            ]);
        }

        /** @var RecoveryForm $model */
        $model = \Yii::createObject([
            'class'    => RecoveryForm::className(),
            'scenario' => RecoveryForm::SCENARIO_RESET,
        ]);
        //$event->setForm($model);

        $this->performAjaxValidation($model);
        //$this->trigger(self::EVENT_BEFORE_RESET, $event);

        if ($model->load(\Yii::$app->getRequest()->post()) && $model->resetPassword($token)) {
            // print "<pre>";
            // print_r($model);
            // print "</pre>";
            $user = Users::findOne($id);
            if(!empty($model->password)){
              $user->password_hash = \Yii::$app->security->generatePasswordHash($model->password);

              // print "<pre>";
              // print_r($user);
              // print "</pre>";

              // exit();

              $user->save();
            }
            
            //exit();
            //$this->trigger(self::EVENT_AFTER_RESET, $event);
            \Yii::$app->getSession()->setFlash('REGISTER_SUCCESS',[
              'body'=> \Yii::t('app','เปลี่ยนรหัสผ่านสำเร็จ'),
              'options'=>['class'=>'alert-success reset-success']
            ]);

            return $this->redirect(['/login']);
        }

        return $this->render('/user/reset', [
            'model' => $model,
        ]);
    }
}
