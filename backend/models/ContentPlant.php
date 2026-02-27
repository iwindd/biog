<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "content_plant".
 *
 * @property int $id
 * @property int $content_id
 * @property string|null $other_name
 * @property string|null $features
 * @property string|null $benefit
 * @property string|null $found_source
 * @property string|null $other_information
 * @property string|null $season
 * @property string|null $ability
 * @property string|null $common_name
 * @property string|null $scientific_name
 * @property string|null $family_name
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Content $content
 */
class ContentPlant extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content_plant';
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
                    'features',
                    'scientific_name'
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
            [['features', 'benefit', 'other_information'], 'string'],
    
            // =========================
            // STRING LENGTH
            // =========================
            [
                [
                    'other_name',
                    'season',
                    'found_source',
                    'ability',
                    'common_name',
                    'scientific_name',
                    'family_name'
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
            'other_name' => 'ชื่ออื่น',
            'features' => 'ลักษณะ/คุณสมบัติ',
            'benefit' => 'ประโยชน์',
            'found_source' => 'แหล่งที่พบ',
            'other_information' => 'ข้อมูลอื่น ๆ ที่ฉันรู้',
            'season' => 'ฤดูกาลที่ใช้ประโยชน์ได้',
            'ability' => 'ศักยภาพการใช้งานในชุมชน',
            'common_name' => 'ชื่อสามัญ',
            'scientific_name' => 'ชื่อวิทยาศาสตร์',
            'family_name' => 'ชื่อวงศ์',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
        ];
    }

    /**
     * Gets query for [[Content]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_id']);
    }
}
