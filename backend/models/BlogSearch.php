<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\Blog;

/**
 * BlogSearch represents the model behind the search form of `backend\models\Blog`.
 */
class BlogSearch extends Blog
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'type_id', 'created_by_user_id', 'updated_by_user_id'], 'integer'],
            [['title', 'picture_path', 'description', 'video_url', 'source_information', 'created_at', 'updated_at'], 'safe'],
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
        $query = Blog::find();

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
            'type_id' => $this->type_id,
            'created_by_user_id' => $this->created_by_user_id,
            'updated_by_user_id' => $this->updated_by_user_id,
            'active' => 1
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'picture_path', $this->picture_path])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'video_url', $this->video_url])
            ->andFilterWhere(['like', 'source_information', $this->source_information]);


        if(empty($params['sort'])){
            $dataProvider->query->orderBy('updated_at DESC');
        }

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


        if (!empty($this->created_at)) {
            $dateRang = explode('to', $this->created_at);
            if(!empty($dateRang)){
                if(count($dateRang) == 2){

                    $dateStart = trim($dateRang[0]);
                    $dateEnd = trim($dateRang[1]);
                    if ($dateStart != $dateEnd) {

                        $dateEnd = date('Y-m-d', strtotime($dateEnd . ' +1 day'));

                        $query->andFilterWhere(['between', 'created_at', $dateStart, $dateEnd]);
                      
                    }else{
                        $query->andFilterWhere(['like', 'created_at', $dateStart]);
                    }
                }
            }else{
                $query->andFilterWhere(['like', 'created_at', $this->created_at]);
            }
        }


        return $dataProvider;
    }
}
