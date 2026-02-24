<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "content_ecotourism".
 *
 * @property int $id
 * @property int $content_id
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $name
 * @property string|null $travel_information
 * @property string|null $contact
 * @property string $created_at
 * @property string $updated_at
 */
class ContentEcotourism extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content_ecotourism';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_id', 'created_at', 'updated_at'], 'required'],
            [['content_id'], 'integer'],
            [['travel_information', 'contact'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['address', 'name', 'phone'], 'string', 'max' => 255],

        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content_id' => 'Content ID',
            'address' => 'ที่อยู่',
            'phone' => 'เบอร์โทรศัพท์',
            'name' => 'ชื่อผู้ติดต่อ',
            'travel_information' => 'อธิบายการเดินทาง',
            'contact' => 'ข้อมูลการติดต่อ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
        ];
    }
}
