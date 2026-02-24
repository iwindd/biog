<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "blog_file".
 *
 * @property int $id
 * @property int $blog_id
 * @property string $application_type
 * @property string $name
 * @property string $path
 * @property string $created_at
 * @property string $updated_at
 */
class BlogFile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blog_file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['blog_id', 'application_type', 'name', 'path', 'created_at', 'updated_at'], 'required'],
            [['blog_id'], 'integer'],
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
            'blog_id' => 'Blog ID',
            'application_type' => 'Application Type',
            'name' => 'Name',
            'path' => 'Path',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
