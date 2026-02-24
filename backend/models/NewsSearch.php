<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\News;

/**
 * NewsSearch represents the model behind the search form of `backend\models\News`.
 */
class NewsSearch extends News
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'promote', 'post_facebook', 'created_by_user_id', 'updated_by_user_id'], 'integer'],
            [['title', 'description', 'picture_path', 'created_at', 'updated_at'], 'safe'],
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
        $query = News::find();

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
            'promote' => $this->promote,
            'post_facebook' => $this->post_facebook,
            'created_by_user_id' => $this->created_by_user_id,
            'updated_by_user_id' => $this->updated_by_user_id,
            'active' => 1
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'picture_path', $this->picture_path]);

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
