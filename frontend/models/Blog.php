<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "blog".
 *
 * @property * @property int $blog_source_id
 * @property int $blog_root_id
 * @property int $type_id
 * @property string|null $title
 * @property string|null $picture_path
 * @property string|null $description
 * @property string|null $video_url
 * @property string|null $source_information
 * @property int $created_by_user_id
 * @property int $updated_by_user_id
 * @property int $active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Blog extends \yii\db\ActiveRecord
{
    const UPLOAD_FOLDER_BLOG = 'blog';
    
    public $files;
    public $document;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blog';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['blog_source_id', 'blog_root_id', 'type_id', 'created_by_user_id', 'updated_by_user_id', 'active'], 'integer'],
            [['type_id', 'created_by_user_id', 'updated_by_user_id', 'active'], 'required'],
            [['description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'picture_path', 'video_url', 'source_information'], 'string', 'max' => 255],
            ['title', 'required','message' => '{attribute}ต้องไม่เป็นค่าว่าง'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'blog_source_id' => 'Blog Source ID',
            'blog_root_id' => 'Blog Root ID',
            'type_id' => 'ประเภท',
            'title' => 'ชื่อเรื่อง',
            'picture_path' => 'รูปภาพปก',
            'description' => 'รายละเอียด',
            'video_url' => 'ลิงก์ของวิดีโอ',
            'source_information' => 'แหล่งข้อมูล',
            'created_by_user_id' => 'ผู้สร้าง',
            'updated_by_user_id' => 'ผู้แก้ไข',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไขล่าสุด',
            'files' => 'รูปภาพประกอบ (แสดงเป็น Gallery)',
            'document' => 'ไฟล์ประกอบ'
        ];
    }
}
