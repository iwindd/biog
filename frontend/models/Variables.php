<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "variables".
 *
 * @property int $id
 * @property string|null $key
 * @property string|null $value
 * @property string|null $description
 */
class Variables extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'variables';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value'], 'string'],
            [['key', 'description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'value' => 'Value',
            'description' => 'Description',
        ];
    }
}
