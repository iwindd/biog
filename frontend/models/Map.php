<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "profile".
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $picture
 * @property string|null $display_name
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string|null $phone
 * @property string|null $gender
 * @property string|null $birthdate
 * @property string|null $invite_code
 * @property string|null $home_number
 * @property string|null $class
 * @property string|null $major
 * @property int|null $zipcode_id
 * @property int|null $subdistrict_id
 * @property int|null $district_id
 * @property int|null $province_id
 * @property string|null $updated_at
 */
class Map extends \yii\db\ActiveRecord
{
    public $region_id;
    public $province_id;
    public $district_id;
    public $subdistrict_id;

    public function rules()
    {
        return [
            [['region_id', 'province_id','district_id','subdistrict_id'], 'safe'],
        ];
    }

}
