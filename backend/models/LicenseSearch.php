<?php

namespace backend\models;

use backend\models\License;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * LicenseSearch represents the model behind the search form of `backend\models\License`.
 */
class LicenseSearch extends License
{
  /**
   * {@inheritdoc}
   */
  public function rules()
  {
    return [
      [['id'], 'integer'],
      [['name', 'description', 'url', 'created_at', 'updated_at'], 'safe'],
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
    $query = License::find();

    // add conditions that should always apply here

    $dataProvider = new ActiveDataProvider([
      'query' => $query,
    ]);

    $this->load($params);

    if (!$this->validate()) {
      return $dataProvider;
    }

    // grid filtering conditions
    $query->andFilterWhere([
      'id' => $this->id,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
    ]);

    $query
      ->andFilterWhere(['like', 'name', $this->name])
      ->andFilterWhere(['like', 'description', $this->description])
      ->andFilterWhere(['like', 'url', $this->url]);

    return $dataProvider;
  }
}
