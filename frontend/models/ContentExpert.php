<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "content_expert".
 *
 * @property int $id
 * @property int $content_id
 * @property int $expert_category_id
 * @property string|null $expert_firstname
 * @property string|null $expert_lastname
 * @property string|null $expert_birthdate
 * @property string|null $expert_expertise
 * @property string|null $expert_occupation
 * @property string|null $expert_card_id
 * @property string|null $phone
 * @property string|null $address
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class ContentExpert extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content_expert';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_id', 'expert_category_id'], 'required'],
            [['content_id', 'expert_category_id'], 'integer'],
            [['expert_birthdate', 'created_at', 'updated_at'], 'safe'],
            [['expert_firstname', 'expert_lastname', 'expert_occupation', 'expert_card_id', 'address'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 64],
            [['expert_expertise'], 'string'],
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
            'expert_category_id' => 'หมวดหมู่ภูมิปัญญา',
            'expert_firstname' => 'ชื่อ ผู้รู้/ปราชญ์',
            'expert_lastname' => 'นามสกุล ผู้รู้/ปราชญ์',
            'expert_birthdate' => 'วัน/เดือน/ปีเกิด ของผู้รู้/ปราชญ์',
            'expert_expertise' => 'ภูมิปัญญาที่เชี่ยวชาญ',
            'expert_occupation' => 'อาชีพ ผู้รู้/ปราชญ์',
            'expert_card_id' => 'เลขบัตรประชาชน ผู้รู้/ปราชญ์',
            'phone' => 'โทรศัพท์ ผู้รู้/ปราชญ์',
            'address' => 'ที่อยู่',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
        ];
    }

    public function placeholder($field)
    {
        $placeholders = (object) [
            'id' => 'ID',
            'content_id' => 'Content ID',
            'expert_category_id' => 'หมวดหมู่ภูมิปัญญา',
            'expert_firstname' => 'ชื่อ ผู้รู้/ปราชญ์',
            'expert_lastname' => 'นามสกุล ผู้รู้/ปราชญ์',
            'expert_birthdate' => 'วัน/เดือน/ปีเกิด ของผู้รู้/ปราชญ์',
            'expert_expertise' => 'ภูมิปัญญาที่เชี่ยวชาญ',
            'expert_occupation' => 'อาชีพ ผู้รู้/ปราชญ์',
            'expert_card_id' => 'เลขบัตรประชาชน ผู้รู้/ปราชญ์',
            'phone' => 'โทรศัพท์ ผู้รู้/ปราชญ์',
            'address' => 'ที่อยู่',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
        ];

        return ['placeholder' => $placeholders->$field];
    }
}
