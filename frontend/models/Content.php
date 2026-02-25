<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "content".
 *
 * @property int $id
 * @property int $content_source_id
 * @property int $content_root_id
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
            [['content_source_id', 'content_root_id', 'type_id', 'region_id', 'province_id', 'district_id', 'subdistrict_id', 'zipcode_id', 'approved_by_user_id', 'created_by_user_id', 'updated_by_user_id', 'active'], 'integer'],
            [['type_id', 'created_by_user_id', 'updated_by_user_id'], 'required'],
            [['description', 'other_information', 'source_information', 'status'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'picture_path', 'note'], 'string', 'max' => 255],
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
            'content_source_id' => 'Content Source ID',
            'content_root_id' => 'Content Root ID',
            'type_id' => 'Type ID',
            'name' => 'Name',
            'picture_path' => 'Picture Path',
            'description' => 'Description',
            'other_information' => 'Other Information',
            'source_information' => 'Source Information',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'region_id' => 'Region ID',
            'province_id' => 'Province ID',
            'district_id' => 'District ID',
            'subdistrict_id' => 'Subdistrict ID',
            'zipcode_id' => 'Zipcode ID',
            'approved_by_user_id' => 'Approved By User ID',
            'created_by_user_id' => 'Created By User ID',
            'updated_by_user_id' => 'Updated By User ID',
            'note' => 'Note',
            'status' => 'Status',
            'active' => 'Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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

    public function getLicense()
    {
        return $this->hasOne(License::className(), ['id' => 'license_id']);
    }
}
