<?php

namespace frontend\models;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Blog;

use Yii;

/**
 * This is the model class for table "blog".
 *
 * @property int $id
 * @property int $blog_source_id
 * @property int $blog_root_id
 * @property int $type_id
 * @property string|null $title
 * @property string|null $picture_path
 * @property string|null $description
 * @property string|null $video_url
 * @property string|null $source_information
 * @property int $created_by_user_id
 * @property int $updated_by_user_id
 * @property int $active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class BlogSearch extends Blog
{
    public $created_fullname;
    public $updated_fullname;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'blog';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['blog_source_id', 'blog_root_id', 'type_id', 'created_by_user_id', 'updated_by_user_id', 'active'], 'integer'],
            [['type_id', 'created_by_user_id', 'updated_by_user_id'], 'required'],
            [['description'], 'string'],
            [['created_at', 'updated_at', 'created_fullname', 'updated_fullname'], 'safe'],
            [['title', 'picture_path', 'video_url', 'source_information'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'blog_source_id' => 'Blog Source ID',
            'blog_root_id' => 'Blog Root ID',
            'type_id' => 'Type ID',
            'title' => 'Title',
            'picture_path' => 'Picture Path',
            'description' => 'Description',
            'video_url' => 'Video Url',
            'source_information' => 'Source Information',
            'created_by_user_id' => 'Created By User ID',
            'updated_by_user_id' => 'Updated By User ID',
            'active' => 'Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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

    public function search($params)
    {
        $query = Blog::find();
        $query->leftjoin('profile', 'profile.user_id=blog.created_by_user_id');

        $uid = Yii::$app->user->identity->id;
       

        $query->where(['created_by_user_id' => $uid]);
        

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
        // grid filtering conditions
        $query->andFilterWhere([
            // 'id' => $this->id,
            // 'title' => $this->title,
            // 'description' => $this->description,

            'active' => 1,
            'updated_by_user_id' => $this->updated_by_user_id,
            'created_by_user_id' => $this->created_by_user_id,

            // 'created_at' => $this->created_at,
            // 'updated_at' => $this->updated_at,
        ]);


        if (!empty($this->created_fullname)) {
           
            $query->andFilterWhere([
                'or',
                [
                    'like', 'profile.firstname', $this->created_fullname
                ],
                [
                    'like', 'profile.lastname', $this->created_fullname
                ],
            ]);
        }

        if (!empty($this->updated_fullname)) {
           
            $query->andFilterWhere([
                'or',
                [
                    'like', 'profile.firstname', $this->updated_fullname
                ],
                [
                    'like', 'profile.lastname', $this->updated_fullname
                ],
            ]);
        }
    

       
        

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'updated_at', $this->updated_at])
            ->andFilterWhere(['like', 'description', $this->description]);

           $query->orderBy(['updated_at' => SORT_DESC]);

        return $dataProvider;
    }
}
