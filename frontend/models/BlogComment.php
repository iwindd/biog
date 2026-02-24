<?php

namespace frontend\models;

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
            [['user_id', 'blog_root_id'], 'required'],
            ['message', 'required', 'message' => "กรุณาระบุข้อความ"],
            [['user_id', 'blog_root_id'], 'integer'],
            [['message'], 'string'],
            [['created_at'], 'safe'],
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
