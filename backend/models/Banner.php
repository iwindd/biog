<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "banner".
 *
 * @property int $id
 * @property string|null $slug_url
 * @property string|null $picture_path
 * @property int|null $active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Banner extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'banner';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['slug_url', 'picture_path'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'slug_url' => 'สำหรับหน้า',
            'picture_path' => 'รูปภาพ',
            'active' => 'เปิดใช้งาน',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไขล่าสุด',
        ];
    }
}
