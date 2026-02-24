<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "product_category".
 *
 * @property int $id
 * @property string|null $name
 * @property int $active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class ProductCategory extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'required', 'message' => '{attribute} ต้องไม่เป็นค่าว่าง'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'ชื่อหมวดหมู่ผลิตภัณฑ์',
            'active' => 'Active',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไขล่าสุด',
        ];
    }
}
