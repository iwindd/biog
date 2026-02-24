<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "knowledge".
 *
 * @property int $id
 * @property string|null $type
 * @property string|null $title
 * @property string|null $path
 * @property string|null $description
 * @property int $created_by_user_id
 * @property int $updated_by_user_id
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Knowledge extends \yii\db\ActiveRecord
{
    const UPLOAD_FOLDER_KNOWLEDGE = 'knowledge';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'knowledge';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'description'], 'string'],
            [['created_by_user_id', 'updated_by_user_id'], 'required'],
            [['created_by_user_id', 'updated_by_user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'path'], 'string', 'max' => 255],
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
            'title' => 'Title',
            'path' => 'Path',
            'description' => 'Description',
            'created_by_user_id' => 'Created By User ID',
            'updated_by_user_id' => 'Updated By User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
