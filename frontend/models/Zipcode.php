<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "zipcode".
 *
 * @property int $id
 * @property string $zipcode
 * @property int $subdistrict_id
 * @property string $created_at
 * @property string $updated_at
 */
class Zipcode extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'zipcode';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['zipcode', 'subdistrict_id', 'created_at'], 'required'],
            [['subdistrict_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['zipcode'], 'string', 'max' => 30],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'zipcode' => 'Zipcode',
            'subdistrict_id' => 'Subdistrict ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
