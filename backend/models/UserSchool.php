<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "user_school".
 *
 * @property int $id
 * @property int $user_id
 * @property int $school_id
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class UserSchool extends \yii\db\ActiveRecord
{
    public $teacherName;
    public $studentName;
    public $name_th;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_school';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'school_id'], 'required'],
            [['user_id', 'school_id'], 'integer'],
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
            'user_id' => 'User ID',
            'school_id' => 'School ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getUser(){
        return $this->hasOne(Users::className(), ['id' => 'user_id']);
    }
}
