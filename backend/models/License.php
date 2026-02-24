<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "licenses".
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string|null $url
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property Content[] $contents
 */
class License extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'licenses';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['name'], 'required'],
      [['description'], 'string'],
      [['created_at', 'updated_at'], 'safe'],
      [['name', 'url'], 'string', 'max' => 255],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => 'ชื่อสัญญาอนุญาต',
      'description' => 'รายละเอียด',
      'url' => 'ลิงก์อ้างอิง',
      'created_at' => 'วันที่สร้าง',
      'updated_at' => 'วันที่แก้ไขล่าสุด',
    ];
  }

  /**
   * Gets query for [[Contents]].
   *
   * @return \yii\db\ActiveQuery
   */
  public function getContents()
  {
    return $this->hasMany(Content::className(), ['license_id' => 'id']);
  }
}
