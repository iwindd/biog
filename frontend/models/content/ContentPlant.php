<?php

namespace frontend\models\content;

use Yii;


class ContentPlant extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'content_plant';
    }

    public function rules()
    {
        return [
            // require
            [
                [
                    // 'other_name', 
                    'features', 'benefit', 'found_source',
                    // 'other_information',
                    // 'season', 'ability', 'common_name', 'scientific_name', 'family_name'
                ],
                'required',
                'message' => 'กรุณากรอก {attribute}',
            ],


            [['content_id'], 'integer'],
            [['features', 'benefit', 'other_information', 'found_source', 'ability', 'common_name', 'scientific_name', 'family_name'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['other_name', 'season'], 'string', 'max' => 255],
            [['content_id'], 'exist', 'skipOnError' => true, 'targetClass' => Content::className(), 'targetAttribute' => ['content_id' => 'id']],
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
            'other_name' => 'ชื่ออื่น ๆ',
            'features' => 'ลักษณะ',
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

    public function placeholder($field)
    {
        $placeholders = (object) [
            'id' => 'ID',
            'content_id' => 'Content ID',
            'other_name' => 'ชื่ออื่น ๆ',
            'features' => 'ลักษณะ',
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

        return ['placeholder' => $placeholders->$field];
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
