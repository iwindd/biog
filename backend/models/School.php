<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "school".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property int $province_id
 * @property int $subdistrict_id
 * @property int $district_id
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class School extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'school';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['province_id', 'subdistrict_id', 'district_id'], 'required'],
            [['province_id', 'subdistrict_id', 'district_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'required'],
            [['name', 'email', 'address'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ชื่อโรงเรียน',
            'phone' => 'เบอร์โทรศัพท์',
            'email' => 'อีเมล',
            'address' => 'ที่อยู่',
            'province_id' => 'จังหวัด',
            'subdistrict_id' => 'ตำบล',
            'district_id' => 'อำเภอ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไขล่าสุด',
        ];
    }
}
