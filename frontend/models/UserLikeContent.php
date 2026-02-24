<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "user_like_content".
 *
 * @property int $id
 * @property int $user_id
 * @property int $content_id
 * @property string|null $created_at
 */
class UserLikeContent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_like_content';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'content_id'], 'required'],
            [['user_id', 'content_id'], 'integer'],
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
            'content_id' => 'Content ID',
            'created_at' => 'Created At',
        ];
    }
}
