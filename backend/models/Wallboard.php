<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "wallboard".
 *
 * @property int $id
 * @property string|null $description
 * @property int $created_by_user_id
 * @property int $updated_by_user_id
 * @property int $active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Wallboard extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'wallboard';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['created_by_user_id', 'updated_by_user_id'], 'required'],
            [['created_by_user_id', 'updated_by_user_id', 'active'], 'integer'],
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
            'description' => 'รายละเอียด',
            'created_by_user_id' => 'ผู้สร้าง',
            'updated_by_user_id' => 'ผู้แก้ไขข้อมูล',
            'active' => 'Active',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไขล่าสุด',
        ];
    }
}
