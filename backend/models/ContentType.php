<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "content_type".
 *
 * @property int $id
 * @property string $name
 * @property string $title
 * @property int $is_visible
 * @property string $created_at
 * @property string $updated_at
 */
class ContentType extends \yii\db\ActiveRecord
{
  /**
   * {@inheritdoc}
   */
  public static function tableName()
  {
    return 'content_type';
  }

  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['name', 'title', 'created_at', 'updated_at'], 'required'],
      [['is_visible'], 'integer'],
      [['created_at', 'updated_at'], 'safe'],
      [['name', 'title'], 'string', 'max' => 255],
      [['name'], 'unique'],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function attributeLabels()
  {
    return [
      'id' => 'ID',
      'name' => 'Name',
      'title' => 'Title',
      'is_visible' => 'Is Visible',
      'created_at' => 'Created At',
      'updated_at' => 'Updated At',
    ];
  }
}
