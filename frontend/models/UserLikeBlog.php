<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "user_like_blog".
 *
 * @property int $id
 * @property int $user_id
 * @property int $blog_id
 * @property string|null $created_at
 */
class UserLikeBlog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_like_blog';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'blog_id'], 'required'],
            [['user_id', 'blog_id'], 'integer'],
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
            'blog_id' => 'Blog ID',
            'created_at' => 'Created At',
        ];
    }
}
