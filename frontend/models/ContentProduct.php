<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "content_product".
 *
 * @property int $id
 * @property int $content_id
 * @property int $product_category_id
 * @property string|null $product_features
 * @property string|null $product_main_material
 * @property string|null $product_sources_material
 * @property float|null $product_price
 * @property string|null $product_distribution_location
 * @property string|null $product_address
 * @property string|null $product_phone
 * @property string|null $other_information
 * @property string|null $found_source
 * @property string|null $contact
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class ContentProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'content_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['content_id', 'product_category_id'], 'required'],
            [['content_id', 'product_category_id'], 'integer'],
            [['product_features', 'other_information'], 'string'],
            [['product_price'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['product_main_material', 'product_sources_material', 'product_distribution_location', 'product_address', 'found_source', 'contact'], 'string', 'max' => 255],
            [['product_phone'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'content_id' => 'Content ID',
            'product_category_id' => 'หมวดหมู่ผลิตภัณฑ์',
            'product_features' => 'จุดเด่น/ประโยชน์',
            'product_main_material' => 'วัตถุดิบหลัก',
            'product_sources_material' => 'แหล่งวัตถุดิบ',
            'product_price' => 'ราคาขาย',
            'product_distribution_location' => 'สถานที่ผลิต/จำหน่าย',
            'product_address' => 'ที่อยุ่',
            'product_phone' => 'เบอร์โทรศัพท์ติดต่อ',
            'other_information' => 'ข้อมูลอื่น ๆ ที่ฉันรู้',
            'found_source' => 'แหล่งที่มาของข้อมูล',
            'contact' => 'ข้อมูลการติดต่อ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
        ];
    }

    public function placeholder($field)
    {
        $placeholders = (object) [
            'id' => 'ID',
            'content_id' => 'Content ID',
            'product_category_id' => 'หมวดหมู่ผลิตภัณฑ์',
            'product_features' => 'จุดเด่น/ประโยชน์',
            'product_main_material' => 'วัตถุดิบหลัก',
            'product_sources_material' => 'แหล่งวัตถุดิบ',
            'product_price' => 'ราคาขาย',
            'product_distribution_location' => 'สถานที่ผลิต/จำหน่าย',
            'product_address' => 'ที่อยุ่',
            'product_phone' => 'เบอร์โทรศัพท์ติดต่อ',
            'other_information' => 'ข้อมูลอื่น ๆ ที่ฉันรู้',
            'found_source' => 'แหล่งที่มาของข้อมูล',
            'contact' => 'ข้อมูลการติดต่อ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
        ];

        return ['placeholder' => $placeholders->$field];
    }
}
