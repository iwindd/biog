<?php

namespace frontend\models;

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
 * @property int $region_id
 * @property int $province_id
 * @property int $district_id
 * @property int $subdistrict_id
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
class ContentTeacher extends \yii\db\ActiveRecord
{
    public $taxonomy_list;
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
            [['type_id', 'region_id', 'province_id', 'district_id', 'subdistrict_id', 'created_by_user_id', 'updated_by_user_id'], 'required'],
            [['type_id', 'region_id', 'province_id', 'district_id', 'subdistrict_id', 'zipcode_id', 'approved_by_user_id', 'created_by_user_id', 'updated_by_user_id', 'active'], 'integer'],
            [['description', 'other_information', 'source_information', 'status'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'picture_path', 'note'], 'string', 'max' => 255],
            [['latitude', 'longitude'], 'string', 'max' => 100],
            [['status'], 'required', 'message' => 'กรุณาเลือกสถานะ'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'Type ID',
            'name' => 'ชื่อที่เรียก',
            'picture_path' => 'Picture Path',
            'description' => 'รายละเอียด',
            'other_information' => 'ข้อมูลอื่น ๆ ที่ฉันรู้',
            'source_information' => 'แหล่งที่มาของข้อมูล',
            'latitude' => 'ละติจูด',
            'longitude' => 'ลองจิจูด',
            'region_id' => 'ภาค',
            'province_id' => 'จังหวัด',
            'district_id' => 'อำเภอ',
            'subdistrict_id' => 'ตำบล/เขต',
            'zipcode_id' => 'รหัสไปรษณีย์',
            'approved_by_user_id' => 'Approved By User ID',
            'created_by_user_id' => 'Created By User ID',
            'updated_by_user_id' => 'Updated By User ID',
            'note' => 'Note',
            'status' => 'สถานะ',
            'active' => 'Active',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'taxonomy_list' => 'คำช่วยคำค้นหา (keyword)'
        ];
    }

    public function placeholder($field)
    {
        $placeholders = (object) [
            'id' => 'ID',
            'type_id' => 'Type ID',
            'name' => 'ชื่อที่เรียก',
            'picture_path' => 'Picture Path',
            'description' => 'รายละเอียด',
            'other_information' => 'ข้อมูลอื่น ๆ ที่ฉันรู้',
            'source_information' => 'แหล่งที่มาของข้อมูล',
            'latitude' => 'ละติจูด',
            'longitude' => 'ลองจิจูด',
            'region_id' => 'ภาค',
            'province_id' => 'เลือกจังหวัด...',
            'district_id' => 'เลือกอำเภอ...',
            'subdistrict_id' => 'เลือกตำบล/เขต...',
            'zipcode_id' => 'รหัสไปรษณีย์',
            'approved_by_user_id' => 'Approved By User ID',
            'created_by_user_id' => 'Created By User ID',
            'updated_by_user_id' => 'Updated By User ID',
            'note' => 'Note',
            'status' => 'Status',
            'active' => 'Active',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'taxonomy_list' => 'คำช่วยคำค้นหา (keyword)'
        ];

        // switch ($this->scenario) {
        //     case Constants::SCENARIO_PRODUCT:
        //         $placeholders->name = 'ชื่อผลิตภัณฑ์';
        //         break;

        //     default:
        //         break;
        // }

        return ['placeholder' => $placeholders->$field];
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
}
