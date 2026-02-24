<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password_hash
 * @property string $auth_key
 * @property int $confirmed_at
 * @property string $unconfirmed_email
 * @property int $blocked_at
 * @property string $registration_ip
 * @property string $role
 * @property string $created_at
 * @property string $updated_at
 */
class Users extends \yii\db\ActiveRecord
{
    public $role_id;
    public $schoolName;
    public $firstname;
    public $lastname;
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
            ['username', 'required','message' => 'ชื่อผู้ใช้ต้องไม่เป็นค่าว่าง'],
            ['username', 'unique', 'targetClass' => '\backend\models\Users', 'message' => 'มีชื่อผู้ใช้นี้อยู่แล้วในระบบ'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'required','message' => 'อีเมลต้องไม่เป็นค่าว่าง'],
            ['email', 'string', 'max' => 255],
            ['email', 'unique', 'targetClass' => '\backend\models\Users', 'message' => 'อีเมลนี้มีผู้ใช้อยู่แล้วในระบบ'],
            [['blocked_at','role_id'], 'integer'],
            [['confirmed_at'], 'string'],
            [['created_at', 'updated_at','firstname','lastname', 'schoolName'], 'safe'],
            [['username', 'email', 'unconfirmed_email'], 'string', 'max' => 255],
            [['password_hash'], 'string', 'max' => 60],
            [['auth_key', 'registration_ip'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'ชื่อผู้ใช้',
            'email' => 'อีเมล',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'confirmed_at' => 'Confirmed At',
            'unconfirmed_email' => 'Unconfirmed Email',
            'blocked_at' => 'Blocked At',
            'registration_ip' => 'Registration Ip',
            'created_at' => 'Created At',
            'updated_at' => 'วันที่แก้ไขล่าสุด',
        ];
    }

    public function usersValidate(){
        if (!$this->validate()) {
            return null;
        }
    }

    public function getProfile(){
        return $this->hasOne(Profile::className(), ['user_id' => 'id']);
     }
   
}
