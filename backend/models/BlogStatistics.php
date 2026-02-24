<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "blog_statistics".
 *
 * @property int $id
 * @property int $blog_root_id
 * @property int|null $pageview
 * @property int|null $like_count
 * @property string|null $updated_at
 */
class BlogStatistics extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blog_statistics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['blog_root_id'], 'required'],
            [['blog_root_id', 'pageview', 'like_count'], 'integer'],
            [['updated_at'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'blog_root_id' => 'Blog ID',
            'pageview' => 'Pageview',
            'like_count' => 'Like Count',
            'updated_at' => 'Updated At',
        ];
    }
}
