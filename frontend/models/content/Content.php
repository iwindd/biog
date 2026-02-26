<?php

namespace frontend\models\content;

use Yii;


class Content extends \yii\db\ActiveRecord
{
    public $taxonomy_list;
    public $files;
    public $stack_id_remove_file;

    const UPLOAD_FOLDER_CONTENT_PLANT = 'content-plant';
    const UPLOAD_FOLDER_CONTENT_ANIMAL = 'content-animal';
    const UPLOAD_FOLDER_CONTENT_FUNGI = 'content-fungi';
    const UPLOAD_FOLDER_CONTENT_EXPERT = 'content-expert';
    const UPLOAD_FOLDER_CONTENT_ECOTOURISM = 'content-ecotourism';
    const UPLOAD_FOLDER_CONTENT_PRODUCT = 'content-product';
    public static function tableName()
    {
        return 'content';
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        // $scenarios[Constants::SCENARIO_PLANT] = $this->attributes();
        return $scenarios;
    }

    public function rules()
    {
        return [
            // require
            [
                [
                    'name'
                ],
                'required',
                'on' => [Constants::SCENARIO_PLANT, Constants::SCENARIO_ANIMAL, Constants::SCENARIO_FUNGAI, Constants::SCENARIO_PRODUCT, Constants::SCENARIO_EXPERT, Constants::SCENARIO_ECOTOURISM],
                'message' => 'กรุณากรอก {attribute}',
            ],
            [
                [
                    'type_id', 'created_by_user_id', 'updated_by_user_id',
                    // 'source_information'
                ],
                'required',
                'message' => 'กรุณากรอก {attribute}',
            ],
            [
                [
                    'region_id', 'province_id', 'district_id', 'subdistrict_id',
                    // 'zipcode_id',
                ],
                'required',
                'message' => 'กรุณาเลือก {attribute}',
            ],
            [
                [
                    'picture_path',
                ],
                'file',
                'maxFiles' => 1,
                'skipOnEmpty' => true,
                'extensions' => 'png, jpg, jpeg, gif',
                'maxSize' => 1024 * 1024 * 2,
                'uploadRequired' => 'กรุณาอัปโหลด {attribute}',
                'tooBig' => 'ไฟล์ {attribute} ต้องไม่เกิน 2 mb.',
                'wrongExtension' => 'เฉพาะไฟล์นามสกุล {extensions} เท่านั้น สำหรับ {attribute}.',

            ],
            [
                [
                    'files',
                ],
                'file',
                'maxFiles' => 20,
                'skipOnEmpty' => true,
                'extensions' => 'png, jpg, jpeg, gif',
                'maxSize' => 1024 * 1024 * 2,
                'uploadRequired' => 'กรุณาอัปโหลด {attribute}',
                'tooBig' => 'ไฟล์ {attribute} ต้องไม่เกิน 2 mb.',
                'wrongExtension' => 'เฉพาะไฟล์นามสกุล {extensions} เท่านั้น สำหรับ {attribute}.',

            ],
            

            [['type_id', 'region_id', 'province_id', 'district_id', 'subdistrict_id', 'zipcode_id', 'approved_by_user_id', 'created_by_user_id', 'updated_by_user_id', 'active'], 'integer'],
            [['description', 'other_information', 'source_information', 'status'], 'string'],
            [['created_at', 'updated_at', 'files', 'picture_path', 'stack_id_remove_file'], 'safe'],
            [['name', 'note', 'photo_credit'], 'string', 'max' => 255],
            //[['latitude', 'longitude'], 'string', 'max' => 100],

            //[['latitude', 'longitude'], 'myfunction' ],

           
        ];
    }

    /**
     * {@inheritdoc} 
     */
    public function attributeLabels()
    {
        $attributeLabel = [
            'id' => 'ID',
            'type_id' => 'Type ID',
            'name' => 'ชื่อที่เรียก',
            'picture_path' => 'รูปภาพปก',
            'files' => 'รูปภาพประกอบ (แสดงเป็น Gallery)',
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
            'photo_credit' => 'แหล่งที่มาของภาพ',
            'status' => 'สถานะ',
            'active' => 'Active',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'taxonomy_list' => 'คำช่วยค้นหา (keyword)'
        ];

        switch ($this->scenario) {
            case Constants::SCENARIO_PRODUCT:
                $attributeLabel['name'] = 'ชื่อผลิตภัณฑ์';
                break;

            case Constants::SCENARIO_EXPERT:
                $attributeLabel['name'] = 'ชื่อเรื่อง';
                break;

            case Constants::SCENARIO_ECOTOURISM:
                $attributeLabel['name'] = 'แหล่งท่องเที่ยวเชิงนิเวศ';
                break;

            default:
                break;
        }

        return $attributeLabel;
    }


    public function placeholder($field)
    {
        $placeholders = (object) [
            'id' => 'ID',
            'type_id' => 'Type ID',
            'name' => 'ชื่อที่เรียก',
            'picture_path' => 'รูปภาพ',
            'files' => 'รูปภาพประกอบ',
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
            'photo_credit' => 'แหล่งที่มาของภาพ',
            'status' => 'Status',
            'active' => 'Active',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
            'taxonomy_list' => 'คำช่วยค้นหา (keyword)'
        ];

        switch ($this->scenario) {
            case Constants::SCENARIO_PRODUCT:
                $placeholders->name = 'ชื่อผลิตภัณฑ์';
                break;

            case Constants::SCENARIO_ECOTOURISM:
                $placeholders->name = 'แหล่งท่องเที่ยวเชิงนิเวศ';
                break;

            default:
                break;
        }

        return ['placeholder' => $placeholders->$field];
    }

    public function plantPlaceholder($field)
    {
        $placeholders = (object) [
            'name' => 'ชื่อที่เรียก',
        ];

        return ['placeholder' => $placeholders->$field];
    }

    public function animalPlaceholder($field)
    {
        $placeholders = (object) [
            'name' => 'ชื่อที่เรียก',
        ];

        return ['placeholder' => $placeholders->$field];
    }

    public function productPlaceholder($field)
    {
        $placeholders = (object) [
            'name' => 'ชื่อผลิตภัณฑ์',
        ];

        return ['placeholder' => $placeholders->$field];
    }

    public function expertPlaceholder($field)
    {
        $placeholders = (object) [
            'name' => 'ชื่อเรื่อง',
        ];

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

    /**
     * Gets query for [[ContentImageSources]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContentImageSources()
    {
        return $this->hasMany(ContentImageSource::className(), ['content_id' => 'id']);
    }

    public function getContentTaxonomy()
    {
        return $this->hasMany(ContentTaxonomy::className(), ['taxonomy_id' => 'id'])->select(['id', 'content_id', 'taxonomy_id']);
    }
}
