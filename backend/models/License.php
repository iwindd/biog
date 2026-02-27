<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "licenses".
 *
 * @property int $id
 * @property string $name
 * @property string $code
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
      [['name', 'code'], 'required'],
      [['description'], 'string'],
      [['created_at', 'updated_at'], 'safe'],
      [['name', 'url'], 'string', 'max' => 255],
      [['code'], 'string', 'max' => 50],
      [['code'], 'unique'],
      [['code'], 'match', 'pattern' => '/^\S+$/', 'message' => 'ห้ามเว้นวรรค (No spaces allowed)'],
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
      'code' => 'รหัสย่อ',
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
