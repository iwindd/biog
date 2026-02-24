<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "student_teacher".
 *
 * @property int $id
 * @property int $student_id
 * @property int $teacher_id
 * @property int|null $active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class StudentTeacher extends \yii\db\ActiveRecord
{
    public $teacher;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'student_teacher';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['student_id', 'teacher_id'], 'required'],
            [['student_id', 'teacher_id', 'active'], 'integer'],
            [['created_at', 'updated_at', 'teacher'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'student_id' => 'Student ID',
            'teacher_id' => 'Teacher ID',
            'active' => 'Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
