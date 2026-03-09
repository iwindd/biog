<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "user_thaid".
 *
 * @property int $id
 * @property int $user_id
 * @property string $pid
 * @property string $created_at
 *
 * @property \dektrium\user\models\User $user
 */
class UserThaid extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_thaid';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'pid', 'created_at'], 'required'],
            [['user_id'], 'integer'],
            [['created_at'], 'safe'],
            [['pid'], 'string', 'max' => 13],
            [['pid'], 'unique'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => \dektrium\user\models\User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'pid' => 'Pid',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\dektrium\user\models\User::className(), ['id' => 'user_id']);
    }
}
