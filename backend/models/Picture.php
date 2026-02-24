<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "picture".
 *
 * @property int $id
 * @property int $content_id
 * @property string|null $name
 * @property string|null $path
 * @property int $created_by_user_id
 * @property int $updated_by_user_id
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Picture extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'picture';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_id', 'created_by_user_id', 'updated_by_user_id'], 'required'],
            [['content_id', 'created_by_user_id', 'updated_by_user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'path'], 'string', 'max' => 255],
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
            'name' => 'Name',
            'path' => 'Path',
            'created_by_user_id' => 'Created By User ID',
            'updated_by_user_id' => 'Updated By User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
