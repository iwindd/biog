<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "blog_comment".
 *
 * @property int $id
 * @property int $user_id
 * @property string $message
 * @property string|null $created_at
 * @property int $blog_root_id
 */
class BlogComment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blog_comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'message', 'blog_root_id'], 'required'],
            [['id', 'user_id', 'blog_root_id'], 'integer'],
            [['message'], 'string'],
            [['created_at'], 'safe'],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'message' => 'Message',
            'created_at' => 'Created At',
            'blog_root_id' => 'Blog ID',
        ];
    }
}
