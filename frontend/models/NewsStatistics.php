<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "news_statistics".
 *
 * @property int $id
 * @property int $news_root_id
 * @property int|null $pageview
 * @property string|null $updated_at
 */
class NewsStatistics extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_statistics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['news_root_id'], 'required'],
            [['news_root_id', 'pageview'], 'integer'],
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
            'news_root_id' => 'News ID',
            'pageview' => 'Pageview',
            'updated_at' => 'Updated At',
        ];
    }
}
