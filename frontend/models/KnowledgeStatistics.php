<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "knowledge_statistics".
 *
 * @property int $id
 * @property int $knowledge_root_id
 * @property int|null $pageview
 * @property string|null $updated_at
 */
class KnowledgeStatistics extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'knowledge_statistics';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['knowledge_root_id'], 'required'],
            [['knowledge_root_id', 'pageview'], 'integer'],
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
            'knowledge_root_id' => 'Knowledge ID',
            'pageview' => 'Pageview',
            'updated_at' => 'Updated At',
        ];
    }
}
