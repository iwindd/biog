<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "user_log".
 *
 * @property int $id
 * @property string|null $type
 * @property int $user_id
 * @property int|null $content_id
 * @property string|null $action_name
 * @property string|null $description
 * @property string|null $created_at
 */
class UserLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'content_id'], 'integer'],
            [['description'], 'string'],
            [['created_at'], 'safe'],
            [['type'], 'string', 'max' => 255],
            [['action_name'], 'string', 'max' => 45],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'user_id' => 'User ID',
            'content_id' => 'Content ID',
            'action_name' => 'Action Name',
            'description' => 'Description',
            'created_at' => 'Created At',
        ];
    }
}
