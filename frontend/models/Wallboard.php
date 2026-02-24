<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "wallboard".
 *
 * @property int $id
 * @property string|null $description
 * @property int $active
 * @property int $created_by_user_id
 * @property int $updated_by_user_id
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
            [['description'], 'required','message' => 'กรุณากรอกข้อความ',],
            [['description'], 'string'],
            [['active', 'created_by_user_id', 'updated_by_user_id'], 'integer'],
            [['created_by_user_id', 'updated_by_user_id'], 'required'],
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
            'description' => 'Description',
            'active' => 'Active',
            'created_by_user_id' => 'Created By User ID',
            'updated_by_user_id' => 'Updated By User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
