<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\UserSchool;

/**
 * StudentSchoolSearch represents the model behind the search form of `backend\models\UserSchool`.
 */
class StudentSchoolSearch extends UserSchool
{
    public $roleId;
    public $fullname;
    public $email;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'user_id', 'school_id'], 'integer'],
            [['created_at', 'updated_at', 'roleId', 'fullname', 'email'], 'safe'],
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
        $query = UserSchool::find();
        $query->leftjoin('profile', 'profile.user_id=user_school.user_id');
        $query->leftJoin('user_role', 'user_role.user_id = user_school.user_id');
                

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'attributes' => [
                'fullname' => [
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

                // 'created_at' => [
                //     'asc' => ['created_at' => SORT_ASC,],
                //     'desc' => ['created_at' => SORT_DESC,],
                //     'label' => 'Created Date',
                //     'default' => SORT_ASC
                // ],
                // 'updated_at' => [
                //     'asc' => ['updated_at' => SORT_ASC,],
                //     'desc' => ['updated_at' => SORT_DESC,],
                //     'label' => 'Updated Date',
                //     'default' => SORT_ASC
                // ],

            ]
        ]);


        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        // print '<pre>';
        // print_r($this);
        // print "</pre>";
        // exit();

        if(!empty($this->school_id)){
            $query->andFilterWhere([  'user_school.school_id' => $this->school_id ]);
        }

        if(!empty($this->roleId)){
            $query->andFilterWhere([ 'user_role.role_id' => $this->roleId, ]);
        }

        


        // $query->andFilterWhere([
        //     'user_school.school_id' => $id,
        //     'user_role.role_id' => 3,
        // ]);

        // grid filtering conditions
        // $query->andFilterWhere([
        //     'id' => $this->id,
        //     'user_id' => $this->user_id,
        //     'school_id' => $this->school_id,
        //     'created_at' => $this->created_at,
        //     'updated_at' => $this->updated_at,
        // ]);

        return $dataProvider;
    }
}
