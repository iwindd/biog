<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "profile".
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $picture
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string|null $phone
 * @property string|null $gender
 * @property string|null $birthdate
 * @property string|null $invite_code
 * @property string|null $home_number
 * @property string|null $class
 * @property int $subdistrict_id
 * @property int $district_id
 * @property int $province_id
 * @property string|null $updated_at
 */
class Profile extends \yii\db\ActiveRecord
{
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
            [['user_id', 'subdistrict_id', 'district_id', 'province_id'], 'integer'],
            [['gender'], 'string'],
            [['birthdate', 'updated_at'], 'safe'],
            [['picture', 'firstname', 'lastname', 'class'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 50],
            [['invite_code'], 'string', 'max' => 6],
            [['home_number'], 'string', 'max' => 150],
            [['id'], 'unique'],

            [['display_name', 'firstname', 'lastname'], 'required','message' => '{attribute}ต้องไม่เป็นค่าว่าง'],
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
            'firstname' => 'ชื่อ',
            'lastname' => 'นามสกุล',
            'display_name' => 'ชื่อที่ใช้แสดง',
            'phone' => 'หมายเลขโทรศัพท์',
            'gender' => 'เพศ',
            'birthdate' => 'วันเกิด',
            'invite_code' => 'Invite Code',
            'home_number' => 'เลขที่บ้าน',
            'class' => 'Class',
            'subdistrict_id' => 'ตำบล',
            'district_id' => 'อำเภอ',
            'province_id' => 'จังหวัด',
            'region_id' => 'ภาค',
            'updated_at' => 'Updated At',
        ];
    }
}
