<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\School;

/**
 * SchoolSearch represents the model behind the search form of `backend\models\School`.
 */
class SchoolSearch extends School
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'province_id', 'subdistrict_id', 'district_id'], 'integer'],
            [['name', 'phone', 'email', 'address', 'created_at', 'updated_at'], 'safe'],
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
        $query = School::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'province_id' => $this->province_id,
            'subdistrict_id' => $this->subdistrict_id,
            'district_id' => $this->district_id,
            'created_at' => $this->created_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'phone', $this->phone])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'address', $this->address]);

        if (!empty($this->updated_at)) {
            $dateRang = explode('to', $this->updated_at);
            if(!empty($dateRang)){
                if(count($dateRang) == 2){

                    $dateStart = trim($dateRang[0]);
                    $dateEnd = trim($dateRang[1]);
                    if ($dateStart != $dateEnd) {

                        $dateEnd = date('Y-m-d', strtotime($dateEnd . ' +1 day'));

                        $query->andFilterWhere(['between', 'updated_at', $dateStart, $dateEnd]);
                      
                    }else{
                        $query->andFilterWhere(['like', 'updated_at', $dateStart]);
                    }
                }
            }else{
                $query->andFilterWhere(['like', 'updated_at', $this->updated_at]);
            }
        }


        if(empty($params['sort'])){
            $dataProvider->query->orderBy('updated_at DESC');
        }

        return $dataProvider;
    }
}
