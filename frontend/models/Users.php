<?php

namespace frontend\models;

use dektrium\user\helpers\Password;
use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $auth_key
 * @property int|null $confirmed_at
 * @property string|null $unconfirmed_email
 * @property int|null $blocked_at
 * @property string|null $registration_ip
 * @property int $created_at
 * @property int $updated_at
 * @property int $flags
 * @property int|null $last_login_at
 * @property string $role
 *
 * @property Profile $profile
 * @property SocialAccount[] $socialAccounts
 * @property Token[] $tokens
 */
class Users extends \yii\db\ActiveRecord
{
    public $reCaptcha;
    public $province;
    public $district;
    public $subdistrict;
    public $zipcode;
    public $role;
    public $current_password;
    public $new_password;
    public $confirm_password;
    public $accept_biog;
    public $accept_condition;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['email', 'required', 'message' => '{attribute}ต้องไม่เป็นค่าว่าง'],
            ['email', 'unique', 'targetClass' => '\frontend\models\Users', 'message' => 'อีเมลนี้มีอยู่แล้วในระบบ'],
            'emailPattern' => ['email', 'email', 'message' => 'รูปแบบอีเมลไม่ถูกต้อง'],
            ['accept_biog', 'required', 'on' => 'create', 'message' => 'กรุณายอมรับเงื่อนไขและนโยบายของ BIOGANG.NET'],
            ['accept_condition', 'required', 'on' => 'create', 'message' => 'กรุณายอมรับเงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุลคล'],
            ['confirm_password', 'required', 'on' => 'create', 'message' => 'ยืนยันรหัสผ่านต้องไม่เป็นค่าว่าง'],
            [['username', 'email', 'password_hash', 'auth_key', 'created_at', 'updated_at'], 'required'],
            [['confirmed_at', 'blocked_at', 'invited_by_user_id'], 'integer'],
            [['role', 'created_at', 'updated_at', 'last_login_at'], 'string'],
            [['username', 'email', 'unconfirmed_email'], 'string', 'max' => 255],
            [['password_hash'], 'string', 'max' => 60],
            [['auth_key'], 'string', 'max' => 32],
            [['registration_ip'], 'string', 'max' => 45],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [
                ['reCaptcha'],
                \himiklab\yii2\recaptcha\ReCaptchaValidator2::className(),
                'uncheckedMessage' => 'Please confirm that you are not a bot.',
                'on' => 'create',
            ],
            [
                ['reCaptcha'],
                \himiklab\yii2\recaptcha\ReCaptchaValidator2::className(),
                'uncheckedMessage' => 'Please confirm that you are not a bot.',
                'on' => 'login',
            ],
            'newPasswordLength' => ['new_password', 'string', 'max' => 72, 'min' => 6],
            ['new_password', 'required', 'on' => 'create', 'message' => 'รหัสผ่านต้องไม่เป็นค่าว่าง'],
            'confirmPasswordLength' => ['confirm_password', 'string', 'max' => 72, 'min' => 6],
            //  'currentPasswordRequired' => ['current_password', 'required'],
            ['confirm_password', 'compare', 'compareAttribute' => 'new_password', 'skipOnEmpty' => false, 'message' => 'ยืนยันรหัสผ่านไม่ถูกต้อง'],
            'currentPasswordValidate' => ['current_password', function ($attr) {
                if (!Password::validate($this->$attr, $this->user->password_hash)) {
                    $this->addError($attr, Yii::t('user', 'Current password is not valid'));
                }
            }],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'email' => 'อีเมล',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'confirmed_at' => 'Confirmed At',
            'unconfirmed_email' => 'Unconfirmed Email',
            'blocked_at' => 'Blocked At',
            'registration_ip' => 'Registration Ip',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'last_login_at' => 'Last Login At',
            'role' => 'Role',
            'confirm_password' => 'ยืนยันรหัสผ่าน',
            'new_password' => 'รหัสผ่าน',
            'invited_by_user_id' => 'ได้รับคำเชิญจาก'
        ];
    }

    public function usersValidate()
    {
        if (!$this->validate()) {
            return $this->getErrors();
        }
        return [];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserThaid()
    {
        return $this->hasOne(\common\models\UserThaid::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSocialAccounts()
    {
        return $this->hasMany(SocialAccount::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTokens()
    {
        return $this->hasMany(Token::className(), ['user_id' => 'id']);
    }

    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id]);
    }
}
