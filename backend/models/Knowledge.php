<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "knowledge".
 *
 * @property int $id
 * @property int $knowledge_source_id
 * @property int $knowledge_root_id
 * @property string|null $type
 * @property string|null $title
 * @property string|null $picture_path
 * @property string|null $path
 * @property string|null $description
 * @property int $created_by_user_id
 * @property int $updated_by_user_id
 * @property int $active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Knowledge extends \yii\db\ActiveRecord
{

    public $files;
    public $document;
    public $path_url;
    public $path_picture;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'knowledge';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['knowledge_source_id', 'knowledge_root_id', 'created_by_user_id', 'updated_by_user_id', 'active'], 'integer'],
            [['type', 'description'], 'string'],
            [['created_by_user_id', 'updated_by_user_id', 'active'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'picture_path', 'path'], 'string', 'max' => 255],

            [['title', 'type'], 'required','message' => '{attribute}ต้องไม่เป็นค่าว่าง'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'knowledge_source_id' => 'Knowledge Source ID',
            'knowledge_root_id' => 'Knowledge Role ID',
            'type' => 'ประเภท',
            'title' => 'ชื่อเรื่อง',
            'picture_path' => 'รูปภาพปก',
            'path' => 'ลิงก์สำหรับ Youtube',
            'description' => 'รายละเอียด',
            'created_by_user_id' => 'ผู้สร้าง',
            'updated_by_user_id' => 'ผู้แก้ไขข้อมูล',
            'active' => 'Active',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไขล่าสุด',
            'files' => 'รูปประกอบ (แสดงเป็น Gallery)',
            'document' => 'ไฟล์ประกอบ'
        ];
    }
}
