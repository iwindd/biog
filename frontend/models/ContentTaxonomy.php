<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "content_taxonomy".
 *
 * @property int $id
 * @property int $content_id
 * @property int $taxonomy_id
 * @property string|null $created_at
 */
class ContentTaxonomy extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content_taxonomy';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_id', 'taxonomy_id'], 'required'],
            [['content_id', 'taxonomy_id'], 'integer'],
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
            'content_id' => 'Content ID',
            'taxonomy_id' => 'Taxonomy ID',
            'created_at' => 'Created At',
        ];
    }
}
