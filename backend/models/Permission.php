<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "permission".
 *
 * @property int $id
 * @property string $permission_key
 * @property string $permission_description
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Permission extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'permission';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['permission_key', 'permission_description'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['permission_key'], 'string', 'max' => 100],
            [['permission_description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'permission_key' => 'Permission Key',
            'permission_description' => 'Permission Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
