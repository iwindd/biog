<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "news".
 *
 * @property int $id
 * @property int $news_source_id
 * @property int $news_root_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $picture_path
 * @property int|null $promote
 * @property int|null $post_facebook
 * @property int $created_by_user_id
 * @property int $updated_by_user_id
 * @property int $active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class News extends \yii\db\ActiveRecord
{
    public $files;
    public $document;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [


            [['news_source_id', 'news_root_id', 'promote', 'post_facebook', 'created_by_user_id', 'updated_by_user_id', 'active'], 'integer'],
            [['description'], 'string'],

            ['title', 'required','message' => '{attribute}ต้องไม่เป็นค่าว่าง'],

            [['created_by_user_id', 'updated_by_user_id', 'active'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['title', 'picture_path'], 'string', 'max' => 255],

            [['public_date'], 'required' ],
            [['public_date'], 'string' ],

            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'news_source_id' => 'News Source ID',
            'news_root_id' => 'News Root ID',
            'title' => 'หัวข้อข่าว',
            'description' => 'รายละเอียด',
            'picture_path' => 'รูปภาพปก',
            'promote' => 'Promote',
            'post_facebook' => 'Post Facebook',
            'created_by_user_id' => 'ผู้สร้าง',
            'updated_by_user_id' => 'ผู้แก้ไขข้อมูล',
            'active' => 'Active',
            'created_at' => 'วันที่สร้าง',
            'updated_at' => 'วันที่แก้ไขล่าสุด',
            'public_date' => 'วันที่เผยแพร่',
            'files' => 'รูปประกอบ (แสดงเป็น Gallery)',
            'document' => 'ไฟล์ประกอบ'
        ];
    }
}
