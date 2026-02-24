<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "content".
 *
 * @property int $id
 * @property int $type_id
 * @property string|null $name
 * @property string|null $picture_path
 * @property string|null $description
 * @property string|null $other_information
 * @property string|null $source_information
 * @property string|null $latitude
 * @property string|null $longitude
 * @property int|null $region_id
 * @property int|null $province_id
 * @property int|null $district_id
 * @property int|null $subdistrict_id
 * @property int|null $zipcode_id
 * @property int|null $approved_by_user_id
 * @property int $created_by_user_id
 * @property int $updated_by_user_id
 * @property string|null $note
 * @property string|null $status
 * @property int|null $active
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property ContentAnimal[] $contentAnimals
 * @property ContentFungi[] $contentFungis
 * @property ContentPlant[] $contentPlants
 */
class Content extends \yii\db\ActiveRecord
{
    public $files;
    public $taxonomy;
    public $picture;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_id', 'created_by_user_id', 'updated_by_user_id'], 'required'],
            [['name', 'status'], 'required'],
            [['is_hidden'], 'boolean'],
            [['type_id', 'region_id', 'province_id', 'district_id', 'subdistrict_id', 'zipcode_id', 'approved_by_user_id', 'created_by_user_id', 'updated_by_user_id', 'active', 'license_id'], 'integer'],
            [['description', 'other_information', 'source_information', 'status'], 'string'],
            [['created_at', 'updated_at', 'files', 'picture_path', 'content_source_id', 'content_root_id'], 'safe'],
            [['name', 'note', 'photo_credit'], 'string', 'max' => 255],
            [['latitude', 'longitude'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'ประเภท',
            'name' => 'ชื่อเรื่อง',
            'picture_path' => 'รูปภาพปก',
            'description' => 'รายละเอียด',
            'other_information' => 'ข้อมูลอื่น ๆ ที่ฉันรู้',
            'source_information' => 'แหล่งที่มาของข้อมูล',
            'photo_credit' => 'แหล่งที่มาของภาพ',
            'latitude' => 'ละจิจูด',
            'longitude' => 'ลองจิจูด',
            'region_id' => 'ภูมิภาค',
            'province_id' => 'จังหวัด',
            'district_id' => 'อำเภอ',
            'subdistrict_id' => 'ตำบล',
            'zipcode_id' => 'รหัสไปรษณีย์',
            'approved_by_user_id' => 'ผู้อนุมัติ',
            'created_by_user_id' => 'ผู้สร้าง',
            'updated_by_user_id' => 'ผู้แก้ไขข้อมูล',
            'note' => 'หมายเหตุ',
            'status' => 'สถานะ',
            'active' => 'สถานะการใช้งาน',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไขล่าสุด',
            'files' => 'รูปประกอบ (แสดงเป็น Gallery)',
            'taxonomy' => 'คำสำคัญ (Tags)',
            'is_hidden' => 'การแสดงผล',
            'license_id' => 'สัญญาอนุญาต (License)',
        ];
    }

    /**
     * Gets query for [[ContentAnimals]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContentAnimals()
    {
        return $this->hasMany(ContentAnimal::className(), ['content_id' => 'id']);
    }

    /**
     * Gets query for [[ContentFungis]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContentFungis()
    {
        return $this->hasMany(ContentFungi::className(), ['content_id' => 'id']);
    }

    /**
     * Gets query for [[ContentPlants]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContentPlants()
    {
        return $this->hasMany(ContentPlant::className(), ['content_id' => 'id']);
    }

    /**
     * Gets query for [[License]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLicense()
    {
        return $this->hasOne(License::className(), ['id' => 'license_id']);
    }
}
