<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\StudentTeacher;

/**
 * StudentTeacherSearch represents the model behind the search form of `backend\models\StudentTeacher`.
 */
class StudentTeacherSearch extends StudentTeacher
{

    public $student_name;
    public $email;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'student_id', 'teacher_id', 'active'], 'integer'],
            [['created_at', 'updated_at', 'student_name', 'email'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = StudentTeacher::find();
        $query->leftjoin('profile', 'profile.user_id=student_teacher.student_id');
        $query->leftjoin('user', 'user.id=student_teacher.student_id');


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'student_name' => [
                    'asc' => ['profile.firstname' => SORT_ASC, 'profile.lastname' => SORT_ASC],
                    'desc' => ['profile.firstname' => SORT_DESC, 'profile.lastname' => SORT_DESC],
                    'label' => 'Full Name',
                    'default' => SORT_ASC
                ],
                'email' => [
                    'asc' => ['email' => SORT_ASC,],
                    'desc' => ['email' => SORT_DESC,],
                    'label' => 'อีเมล',
                    'default' => SORT_ASC
                ],

                'created_at' => [
                    'asc' => ['created_at' => SORT_ASC,],
                    'desc' => ['created_at' => SORT_DESC,],
                    'label' => 'Created Date',
                    'default' => SORT_ASC
                ],
                'updated_at' => [
                    'asc' => ['updated_at' => SORT_ASC,],
                    'desc' => ['updated_at' => SORT_DESC,],
                    'label' => 'Updated Date',
                    'default' => SORT_ASC
                ],

            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if (!empty($this->student_name)) {
            $query->andFilterWhere([
                'or',
                ['like', 'profile.firstname', $this->student_name],
                ['like', 'profile.lastname', $this->student_name],
            ]);
        }

        if (!empty($this->email)) {
            $query->andFilterWhere(['like', 'user.email', $this->email]);
        }

        

        // grid filtering conditions
        $query->andFilterWhere([
            // 'id' => $this->id,
            // 'student_id' => $this->student_id,
            'teacher_id' => $this->teacher_id,
            // 'active' => $this->active,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
        ]);

        return $dataProvider;
    }
}
