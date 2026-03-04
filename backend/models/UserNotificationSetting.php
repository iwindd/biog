<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "user_notification_settings".
 *
 * @property int $id
 * @property int $user_id
 * @property int $notify_new_registration
 * @property string $created_at
 * @property string $updated_at
 */
class UserNotificationSetting extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_notification_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'notify_new_registration'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['user_id'], 'unique'],
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
            'notify_new_registration' => 'เปิดรับแจ้งเตือนเมื่อมีสมาชิกใหม่ต้องการ Approve',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Get or create notification setting for a user.
     * @param int $userId
     * @return static
     */
    public static function getOrCreate($userId)
    {
        $model = static::find()->where(['user_id' => $userId])->one();
        if (empty($model)) {
            $model = new static();
            $model->user_id = $userId;
            $model->notify_new_registration = 0;
            $model->created_at = date('Y-m-d H:i:s');
            $model->updated_at = date('Y-m-d H:i:s');
            $model->save();
        }
        return $model;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }
}
