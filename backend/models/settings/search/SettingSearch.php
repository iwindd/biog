<?php

namespace backend\models\settings;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use backend\models\settings\Settings;

/**
 * SettingSearch represents the model behind the search form of `backend\models\settings\Settings`.
 */
class SettingSearch extends Settings
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'last_updated_by'], 'integer'],
            [['setting_key', 'setting_value', 'created_at', 'updated_at'], 'safe'],
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
        $query = Settings::find();

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
            'last_updated_by' => $this->last_updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'setting_key', $this->setting_key])
            ->andFilterWhere(['like', 'setting_value', $this->setting_value]);

        return $dataProvider;
    }
}
