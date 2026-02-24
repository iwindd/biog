<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "district".
 *
 * @property int $id
 * @property int $province_id
 * @property string $name_th
 * @property string $name_en
 * @property string $created_at
 * @property string $updated_at
 */
class District extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'district';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['province_id', 'name_th', 'name_en', 'created_at'], 'required'],
            [['province_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name_th', 'name_en'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'province_id' => 'Province ID',
            'name_th' => 'Name Th',
            'name_en' => 'Name En',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
