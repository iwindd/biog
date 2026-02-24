<?php

namespace frontend\models\content;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Content;
use frontend\models\StudentTeacher;

/**
 * ContentTeacherSearch represents the model behind the search form of `backend\models\Content`.
 */
class ContentTeacherSearch extends Content
{

    public $fullname;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type_id', 'region_id', 'province_id', 'district_id', 'subdistrict_id', 'zipcode_id', 'approved_by_user_id', 'created_by_user_id', 'updated_by_user_id', 'active'], 'integer'],
            [['name', 'picture_path', 'description', 'other_information', 'source_information', 'latitude', 'longitude', 'note', 'status', 'created_at', 'updated_at', 'fullname'], 'safe'],
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
        $query = Content::find();
        $query->leftjoin('profile', 'profile.user_id=content.created_by_user_id');

        $teacherId = Yii::$app->user->identity->id;
        $studentId = array();
        $student = StudentTeacher::find()->select(['student_id'])->where(['teacher_id' => $teacherId, 'active' => 1])->asArray()->all();
        if(!empty($student)){
            foreach($student as $value){
                $studentId[] = $value['student_id'];
            }
        }

        $query->where(['in', 'created_by_user_id', $studentId]);

        // print '<pre>';
        // print_r($teacherId);
        // print '</pre>';

        // print '<pre>';
        // print_r($studentId);
        // print '</pre>';
        // exit();

        
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {

            print '<pre>';
            print_r($this);
            print '</pre>';
            exit();
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // $dataProvider->setSort([
        //     'attributes' => [
        //         'status' => [
        //             'asc' => 
        //         ],
        //         'default' => SORT_ASC
        //     ]
        // ]);

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'type_id' => $this->type_id,
            'region_id' => $this->region_id,
            'province_id' => $this->province_id,
            'district_id' => $this->district_id,
            'subdistrict_id' => $this->subdistrict_id,
            'zipcode_id' => $this->zipcode_id,
            'approved_by_user_id' => $this->approved_by_user_id,

            'updated_by_user_id' => $this->updated_by_user_id,
            'active' => 1,

            'updated_at' => $this->updated_at,
        ]);

        if (!empty($this->fullname)) {
            $query->andFilterWhere([
                'or',
                ['like', 'profile.firstname', $this->fullname],
                ['like', 'profile.lastname', $this->fullname],
            ]);
        }

        // print '<pre>';
        // print_r($this->fullname);
        // print '</pre>';
        // exit();

       
        

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'picture_path', $this->picture_path])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'other_information', $this->other_information])
            ->andFilterWhere(['like', 'source_information', $this->source_information])
            ->andFilterWhere(['like', 'latitude', $this->latitude])
            ->andFilterWhere(['like', 'longitude', $this->longitude])
            ->andFilterWhere(['like', 'note', $this->note])
            ->andFilterWhere(['like', 'status', $this->status]);
        if(empty($params['sort'])){
            $query->orderBy([new \yii\db\Expression('FIELD (status, "pending", "approved", "rejected")')]);
        }
        return $dataProvider;
    }
}
