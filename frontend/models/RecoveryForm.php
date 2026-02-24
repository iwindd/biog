<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace frontend\models;

use dektrium\user\Finder;
use dektrium\user\Mailer;
use yii\base\Model;

/**
 * Model for collecting data on password recovery.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
class RecoveryForm extends Model
{
    const SCENARIO_REQUEST = 'request';
    const SCENARIO_RESET = 'reset';

    /**
     * @var string
     */
    public $email;

    /**
     * @var string
     */
    public $password;

    public $confirm_password;

    /**
     * @var Mailer
     */
    protected $mailer;

    /**
     * @var Finder
     */
    protected $finder;

    /**
     * @param Mailer $mailer
     * @param Finder $finder
     * @param array  $config
     */
    public function __construct(Mailer $mailer, Finder $finder, $config = [])
    {
        $this->mailer = $mailer;
        $this->finder = $finder;
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'email'    => \Yii::t('user', 'อีเมล'),
            'password' => \Yii::t('user', 'รหัสผ่าน'),
            'confirm_password' => \Yii::t('app', 'ยืนยันรหัสผ่าน'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_REQUEST => ['email'],
            self::SCENARIO_RESET => ['password', 'confirm_password'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            'emailTrim' => ['email', 'trim'],
            'emailRequired' => ['email', 'required', 'message' => " {attribute} ".\Yii::t('app','ต้องไม่เป็นค่าว่าง')],
            'emailPattern' => ['email', 'email'],
            'passwordRequired' => [
                'password', 
                'required' , 
                'message' => " {attribute} ".\Yii::t('app','ต้องไม่เป็นค่าว่าง')],
            'passwordLength' => [
                'password', 
                'string', 
                'max' => 72, 'min' => 6],
            'confirmPasswordRequired' => [
                'confirm_password', 
                'required' , 
                'message' => \Yii::t('app','ยืนยันรหัสผ่าน')." ".\Yii::t('app','ต้องไม่เป็นค่าว่าง')],
            'confirmPasswordLength' => [
                'confirm_password', 
                'string', 
                'max' => 72, 
                'min' => 6 , 
                'message' => \Yii::t('app','ยืนยันรหัสผ่าน')." ".\Yii::t('app','ควรประกอบด้วยอักขระอย่างน้อย 6 อักขระ')
            ],
            ['confirm_password', 'compare', 
                'compareAttribute' => 'password' , 
                'message' => \Yii::t('app','ยืนยันรหัสผ่าน')."".\Yii::t('app','ต้องตรงกับ')."".\Yii::t('app','รหัสผ่าน')."ใหม่"
            ],
        ];
    }



    /**
     * Resets user's password.
     *
     * @param Token $token
     *
     * @return bool
     */
    public function resetPassword($token)
    {
        // print_r($token);
        // exit();
        if (!$this->validate() || $token->user === null) {
            return false;
        }

        if ($token->user->resetPassword($this->password)) {
            \Yii::$app->session->setFlash('success', \Yii::t('user', 'Your password has been changed successfully.'));
            $token->delete();
        } else {
            \Yii::$app->session->setFlash(
                'danger',
                \Yii::t('user', 'An error occurred and your password has not been changed. Please try again later.')
            );
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return 'recovery-form';
    }
}
