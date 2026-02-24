<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "school".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property int|null $province_id
 * @property int|null $subdistrict_id
 * @property int|null $district_id
 * @property int|null $zipcode_id
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class School extends \yii\db\ActiveRecord
{
    public $school_id;
    public $add_new;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'school';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['province_id', 'subdistrict_id', 'district_id', 'zipcode_id'], 'integer'],
            [['created_at', 'updated_at', 'add_new', 'school_id'], 'safe'],
            [['name', 'email', 'address'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'phone' => 'Phone',
            'email' => 'Email',
            'address' => 'Address',
            'province_id' => 'Province ID',
            'subdistrict_id' => 'Subdistrict ID',
            'district_id' => 'District ID',
            'zipcode_id' => 'Zipcode ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
