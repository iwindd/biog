<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "news_file".
 *
 * @property int $id
 * @property int $news_id
 * @property string|null $application_type
 * @property string|null $name
 * @property string|null $path
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class NewsFile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['news_id'], 'required'],
            [['news_id'], 'integer'],
            [['application_type'], 'string'],
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
            'news_id' => 'News ID',
            'application_type' => 'Application Type',
            'name' => 'Name',
            'path' => 'Path',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
