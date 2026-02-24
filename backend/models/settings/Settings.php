<?php

namespace backend\models\settings;

use Yii;

/**
 * This is the model class for table "settings".
 *
 * @property int $id
 * @property string $setting_key
 * @property string $setting_value
 * @property int $last_updated_by
 * @property string $created_at
 * @property string $updated_at
 */
class Settings extends \yii\db\ActiveRecord
{
    // facebook
    public $facebook_application_id,
        $facebook_application_secrete,
        $facebook_access_token,
        $facebook_auto_post;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'settings';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['setting_key', 'setting_value', 'updated_by_user_id', 'created_at', 'updated_at'], 'required'],
            [['setting_value'], 'string'],
            [['updated_by_user_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['setting_key'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'setting_key' => 'Setting Key',
            'setting_value' => 'Setting Value',
            'updated_by_user_id' => 'Last Updated By Id',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'facebook_application_id' => 'Application Id',
            'facebook_application_secrete' => 'Application Secret',
            'facebook_auto_post' => 'โพสต์อัตโนมัติ',
            'facebook_access_token' => 'Access Token'
        ];
    }
}
