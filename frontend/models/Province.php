<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "province".
 *
 * @property int $id
 * @property int|null $region_id
 * @property string $name_th
 * @property string $name_en
 * @property string $name_short
 * @property string $created_at
 * @property string $updated_at
 */
class Province extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'province';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['region_id'], 'integer'],
            [['name_th', 'name_en', 'name_short', 'created_at'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['name_th', 'name_en'], 'string', 'max' => 255],
            [['name_short'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'region_id' => 'Region ID',
            'name_th' => 'Name Th',
            'name_en' => 'Name En',
            'name_short' => 'Name Short',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
