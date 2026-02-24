<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "knowledge_file".
 *
 * @property int $id
 * @property int $knowledge_id
 * @property string|null $application_type
 * @property string|null $name
 * @property string|null $path
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class KnowledgeFile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'knowledge_file';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['knowledge_id'], 'required'],
            [['knowledge_id'], 'integer'],
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
            'knowledge_id' => 'Knowledge ID',
            'application_type' => 'Application Type',
            'name' => 'Name',
            'path' => 'Path',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
