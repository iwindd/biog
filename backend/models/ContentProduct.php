<?php

namespace backend\models;

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
            // =========================
            // REQUIRED
            // =========================
            [
                [
                    'contact',
                    'product_category_id',
                    'content_id'
                ],
                'required'
            ],
    
            // =========================
            // INTEGER
            // =========================
            [['content_id', 'product_category_id', 'product_price'], 'integer'],
    
            // =========================
            // TEXT
            // =========================
            [['product_features', 'other_information', 'product_main_material', 'found_source'], 'string'],
    
            // =========================
            // STRING LENGTH
            // =========================
            [
                [
                    'contact',
                    'product_sources_material',
                    'product_distribution_location',
                    'product_address',
                ],
                'string',
                'max' => 255
            ],
            [
                ['product_phone'],
                'string',
                'max' => 10
            ],
            // =========================
            // FOREIGN KEY
            // =========================
            [
                ['content_id'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Content::class,
                'targetAttribute' => ['content_id' => 'id']
            ],
    
            // =========================
            // SAFE TIMESTAMP
            // =========================
            [['created_at', 'updated_at'], 'safe'],
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
            'product_address' => 'ที่อยู่',
            'product_phone' => 'เบอร์โทรศัพท์',
            'other_information' => 'รายละเอียดเพิ่มเติม',
            'found_source' => 'แหล่งที่พบ',
            'contact' => 'ข้อมูลการติดต่อ',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไข',
        ];
    }
}
