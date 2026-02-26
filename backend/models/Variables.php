<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "variables".
 *
 * @property int $id
 * @property string|null $key
 * @property string|null $value
 */
class Variables extends \yii\db\ActiveRecord
{
    public $sender_mail;
    public $data_protection;
    public $data_protection_pdf;
    public $email_info;
    public $phone_info;
    public $expert;
    public $expert_firstname;
    public $expert_lastname;
    public $expert_birthdate;
    public $expert_expertise;
    public $expert_occupation;
    public $expert_card_id;
    public $phone;
    public $address;
    public $about2;
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
            [['value', 'sender_mail', 'about2', 'email_info', 'phone_info', 'data_protection'], 'string'],
            ['expert', 'safe'],
            ['data_protection_pdf', 'safe'],
            [['key'], 'string', 'max' => 100],
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
        ];
    }
}
