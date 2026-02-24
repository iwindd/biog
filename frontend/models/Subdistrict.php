<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "subdistrict".
 *
 * @property int $id
 * @property int $district_id
 * @property string $name_th
 * @property string $name_en
 * @property string $created_at
 * @property string $updated_at
 */
class Subdistrict extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subdistrict';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['district_id', 'name_th', 'name_en', 'created_at'], 'required'],
            [['district_id'], 'integer'],
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
            'district_id' => 'District ID',
            'name_th' => 'Name Th',
            'name_en' => 'Name En',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
