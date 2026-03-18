<?php

namespace backend\models;

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
            // =========================
            // REQUIRED
            // =========================
            [
                [
                    'content_id',
                    'expert_category_id',
                    'expert_firstname',
                    'expert_lastname',
                    'expert_expertise'
                ],
                'required'
            ],
    
            // =========================
            // INTEGER
            // =========================
            [['content_id'], 'integer'],
    
            // =========================
            // TEXT
            // =========================
            [['description'], 'string'],
    
            // =========================
            // STRING LENGTH
            // =========================
            [
                [
                    'expert_firstname',
                    'expert_lastname',
                    'expert_occupation',
                    'expert_card_id',
                    'phone',
                    'address'
                ],
                'string',
                'max' => 255
            ],
    
            // =========================
            // FOREIGN KEY
            // =========================
            [
                ['content_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Content::class,
                'targetAttribute' => ['content_id' => 'id']
            ],
    
            // =========================
            // SAFE TIMESTAMP
            // =========================
            [['created_at', 'updated_at'], 'safe'],
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
            'expert_category_id' => 'หมวดหมู่ภูมิปัญญา/ปราชญ์',
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
}
