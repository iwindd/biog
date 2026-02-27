<?php

namespace backend\models;

use Yii;
use common\components\UrlValidatorHelper;

/**
 * This is the model class for table "content_data_source".
 *
 * @property int $id
 * @property int $content_id
 * @property string|null $source_name
 * @property string|null $author
 * @property string|null $published_date
 * @property string $reference_url
 *
 * @property Content $content
 */
class ContentDataSource extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content_data_source';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [

            // REQUIRED
            [['content_id', 'reference_url'], 'required'],
    
            // INTEGER
            [['content_id'], 'integer'],
    
            // DATE
            [['published_date'], 'date', 'format' => 'php:Y-m-d'],
            [
                ['published_date'],
                'compare',
                'compareValue' => date('Y-m-d'),
                'operator' => '<=',
                'message' => '{attribute} ห้ามเกินวันที่ปัจจุบัน'
            ],
    
            // STRING
            [['source_name', 'author'], 'string', 'max' => 255],
    
            // URL
            UrlValidatorHelper::devFriendly('reference_url'),
            [['reference_url'], 'string', 'max' => 255],
    
            // FK
            [
                ['content_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Content::class,
                'targetAttribute' => ['content_id' => 'id']
            ],
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
            'source_name' => 'ชื่อแหล่งที่มา',
            'author' => 'ผู้จัดทำ',
            'published_date' => 'วันที่เผยแพร่',
            'reference_url' => 'URL อ้างอิง',
        ];
    }

    /**
     * Gets query for [[Content]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContent()
    {
        return $this->hasOne(Content::className(), ['id' => 'content_id']);
    }
}
