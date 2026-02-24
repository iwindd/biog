<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "content_statistics".
 *
 * @property int $id
 * @property int $content_root_id
 * @property int|null $pageview
 * @property int|null $like_count
 * @property string|null $updated_at
 */
class ContentStatistics extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content_statistics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_root_id'], 'required'],
            [['content_root_id', 'pageview', 'like_count'], 'integer'],
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
            'content_root_id' => 'Content ID',
            'pageview' => 'Pageview',
            'like_count' => 'Like Count',
            'updated_at' => 'Updated At',
        ];
    }
}
