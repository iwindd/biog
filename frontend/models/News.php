<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "news".
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $picture_path
 * @property int|null $promote
 * @property int|null $post_facebook
 * @property int $created_by_user_id
 * @property int $updated_by_user_id
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class News extends \yii\db\ActiveRecord
{
    const UPLOAD_FOLDER_NEWS = 'news';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description'], 'string'],
            [['promote', 'post_facebook', 'created_by_user_id', 'updated_by_user_id'], 'integer'],
            [['created_by_user_id', 'updated_by_user_id'], 'required'],
            [['created_at', 'updated_at' ,'public_date'], 'safe'],
            [['title', 'picture_path'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'description' => 'Description',
            'picture_path' => 'Picture Path',
            'promote' => 'Promote',
            'post_facebook' => 'Post Facebook',
            'created_by_user_id' => 'Created By User ID',
            'updated_by_user_id' => 'Updated By User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
