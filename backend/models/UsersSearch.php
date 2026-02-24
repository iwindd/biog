<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Users;

/**
 * UsersSearch represents the model behind the search form of `backend\models\Users`.
 */
class UsersSearch extends Users
{
    /**
     * {@inheritdoc}
     */
    public $fullname;
    public $schoolName;
    public $provinceName;
    public $PageSize;
    public function rules()
    {
        return [
            [['id', 'confirmed_at', 'blocked_at', 'flags', 'last_login_at','role_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['PageSize', 'fullname', 'schoolName', 'provinceName', 'username', 'email', 'password_hash', 'auth_key', 'unconfirmed_email', 'registration_ip'], 'safe'],
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
        $query = Users::find(); //->where(['<>' , 'role', 'student'])->all();
        $query->leftjoin('profile', 'profile.user_id=user.id')
        ->leftjoin('user_role', 'user_role.user_id = user.id')
        ->leftjoin('role', 'role.id = user_role.role_id')
        ->leftjoin('user_school', 'user_school.user_id = user.id')
        ->leftjoin('school', 'user_school.school_id = school.id')
        ->leftjoin('province', 'school.province_id = province.id');
        // $query->where('in','user_role.role_id',[1,2]);
        //$query->where('<>' , 'role', 'student');

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
                'schoolName' => [
                    'asc' => ['school.name' => SORT_ASC],
                    'desc' => ['school.name' => SORT_DESC],
                    'label' => 'School Name',
                    'default' => SORT_ASC
                ],

                'provinceName' => [
                    'asc' => ['province.name_th' => SORT_ASC],
                    'desc' => ['province.name_th' => SORT_DESC],
                    'label' => 'Province Name',
                    'default' => SORT_ASC
                ],
                
                'username' => [
                    'asc' => ['username' => SORT_ASC,],
                    'desc' => ['username' => SORT_DESC,],
                    'label' => 'ชื่อผูเใช้งาน',
                    'default' => SORT_ASC
                ],
                'email' => [
                    'asc' => ['user.email' => SORT_ASC,],
                    'desc' => ['user.email' => SORT_DESC,],
                    'label' => 'อีเมล',
                    'default' => SORT_ASC
                ],
                'blocked_at' => [
                    'asc' => ['blocked_at' => SORT_ASC,],
                    'desc' => ['blocked_at' => SORT_DESC,],
                    'label' => 'Block User',
                    'default' => SORT_ASC
                ],
                // 'role' => [
                //     'asc' => ['role' => SORT_ASC,],
                //     'desc' => ['role' => SORT_DESC,],
                //     'label' => 'Role',
                //     'default' => SORT_ASC
                // ],

                'created_at' => [
                    'asc' => ['user.created_at' => SORT_ASC,],
                    'desc' => ['user.created_at' => SORT_DESC,],
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


        if (!empty($params['UsersSearch']['confirmed_at'])) {
            if ($params['UsersSearch']['confirmed_at'] == 2) {
                $query->andFilterWhere(['=', 'confirmed_at', 0]);
            } else if ($params['UsersSearch']['confirmed_at'] == 1) {
                $query->andFilterWhere(['=', 'confirmed_at', 1]);
            }
        }

        if (!empty($params['UsersSearch']['blocked_at'])) {
            if ($params['UsersSearch']['blocked_at'] == 2) {

                $query->andFilterWhere(['=', 'blocked_at', null]);
            } else if ($params['UsersSearch']['blocked_at'] == 1) {
                $query->andFilterWhere(['=', 'blocked_at', 1]);
            }
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            //'confirmed_at' => $this->confirmed_at,
            //'blocked_at' => $this->blocked_at,
            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
            // 'flags' => $this->flags,
            // 'last_login_at' => $this->last_login_at,
        ]);

        

        if (!empty($this->created_at)) {
            $dateRang = explode('to', $this->created_at);
            if(!empty($dateRang)){
                if(count($dateRang) == 2){
                    //$query->andFilterWhere(['between', 'date(user.created_at)', trim($dateRang[0]), trim($dateRang[1]) ]);

                    $dateStart = trim($dateRang[0]);
                    $dateEnd = trim($dateRang[1]);
                    if ($dateStart != $dateEnd) {

                        $dateEnd = date('Y-m-d', strtotime($dateEnd . ' +1 day'));

                        // print '<pre>';
                        // print_r($dateStart.'-'.$dateEnd);
                        // print '</pre>';
                        // exit();

                        $query->andFilterWhere(['between', 'user.created_at', $dateStart, $dateEnd]);
                        //$query->andFilterWhere(['>=', 'user.created_at', $dateStart])->andFilterWhere(['<=', 'user.created_at',  $dateEnd]);
                    }else{
                        $query->andFilterWhere(['like', 'user.created_at', $dateStart]);
                    }
                }
            }else{
                $query->andFilterWhere(['like', 'user.created_at', $this->created_at]);
            }
        }

        if (!empty($this->updated_at)) {
            $query->andFilterWhere(['like', 'updated_at', $this->updated_at]);
        }

        $query->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'user.email', $this->email])
            ->andFilterWhere(['like', 'password_hash', $this->password_hash])
            ->andFilterWhere(['like', 'auth_key', $this->auth_key])
            ->andFilterWhere(['like', 'unconfirmed_email', $this->unconfirmed_email])
            ->andFilterWhere(['like', 'registration_ip', $this->registration_ip])
            ->andFilterWhere(['like', 'role.id', $this->role_id]);
            //->andFilterWhere(['like', 'role', $this->role]);

        if (!empty($this->fullname)) {
            $query->andFilterWhere([
                'or',
                ['like', 'profile.firstname', $this->fullname],
                ['like', 'profile.lastname', $this->fullname],
            ]);
        }

        if (!empty($this->schoolName)) {
            $query->andFilterWhere(
                ['like', 'school.name', $this->schoolName]
            );
        }

        if (!empty($this->provinceName)) {
            $query->andFilterWhere(
                ['like', 'province.name_th', $this->provinceName]
            );
        }

        $query->andFilterWhere(['is', 'blocked_at', new \yii\db\Expression('null')]);

        $query->groupBy('user.email');

        if(empty($params['sort'])){
            $dataProvider->query->orderBy('user.updated_at DESC');
        }

        $dataProvider->pagination->pageSize = ($this->PageSize !== NULL) ? $this->PageSize : 10;

        return $dataProvider;
    }
}
