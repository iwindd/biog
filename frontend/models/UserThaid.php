<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "user_thaid".
 *
 * @property int $id
 * @property int $user_id
 * @property string $pid
 * @property string $created_at
 *
 * @property Users $user
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
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['user_id' => 'id']],
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
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }
}
