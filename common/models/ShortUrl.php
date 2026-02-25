<?php

namespace common\models;

use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "short_url".
 *
 * @property int $id
 * @property string $code
 * @property string $target_url
 * @property int $created_at
 * @property int $created_by
 * @property int $updated_at
 */
class ShortUrl extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'short_url';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class' => BlameableBehavior::className(),
                'updatedByAttribute' => false,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['target_url'], 'required'],
            [['target_url'], 'string'],
            [['target_url'], 'url', 'defaultScheme' => 'http'],
            [['created_at', 'created_by', 'updated_at'], 'integer'],
            [['code'], 'string', 'max' => 255],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ลำดับ',
            'code' => 'รหัสย่อ (Short Code)',
            'target_url' => 'ลิงก์ปลายทาง (Target URL)',
            'created_at' => 'สร้างเมื่อ',
            'created_by' => 'สร้างโดย',
            'updated_at' => 'แก้ไขล่าสุดเมื่อ',
        ];
    }
    
    public function generateCode()
    {
        $this->code = Yii::$app->security->generateRandomString(6);
    }

    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
}
