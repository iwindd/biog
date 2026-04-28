<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "profile".
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $picture
 * @property string|null $display_name
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string|null $phone
 * @property string|null $gender
 * @property string|null $birthdate
 * @property string|null $invite_code
 * @property string|null $home_number
 * @property string|null $class
 * @property string|null $major
 * @property int|null $zipcode_id
 * @property int|null $subdistrict_id
 * @property int|null $district_id
 * @property int|null $province_id
 * @property string|null $updated_at
 */
class Profile extends \yii\db\ActiveRecord
{
    public $invite_friend;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'profile';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'zipcode_id', 'subdistrict_id', 'district_id', 'province_id'], 'integer'],
            [['gender'], 'string'],
            [['birthdate', 'updated_at', 'invite_friend'], 'safe'],
            [['picture', 'display_name', 'firstname', 'lastname', 'class', 'major'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 64],
            [['invite_code'], 'string', 'max' => 6],
            [['home_number'], 'string', 'max' => 128],

            [['display_name', 'firstname', 'lastname', 'phone'], 'required','message' => '{attribute}ต้องไม่เป็นค่าว่าง'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'picture' => 'Picture',
            'display_name' => 'ชื่อที่ใช้แสดง',
            'firstname' => 'ชื่อ',
            'lastname' => 'นามสกุล',
            'phone' => 'หมายเลขโทรศัพท์มือถือ',
            'gender' => 'Gender',
            'birthdate' => 'Birthdate',
            'invite_friend' => 'รหัสผู้แนะนำ / อีเมลผู้แนะนำ',
            'invite_code' => 'Invite Code',
            'home_number' => 'Home Number',
            'class' => 'Class',
            'major' => 'Major',
            'zipcode_id' => 'Zipcode ID',
            'subdistrict_id' => 'Subdistrict ID',
            'district_id' => 'District ID',
            'province_id' => 'Province ID',
            'updated_at' => 'Updated At',
        ];
    }
}
