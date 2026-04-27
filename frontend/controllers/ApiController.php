<?php

namespace frontend\controllers;


use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;
use yii\data\Sort;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use frontend\models\Region;
use frontend\models\Province;
use frontend\models\District;
use frontend\models\Subdistrict;
use frontend\models\Zipcode;
use frontend\models\Users;
use frontend\models\Profile;
use frontend\models\UserSchool;
use frontend\models\School;
use common\components\FileLibrary;

use frontend\models\UploadForm;
use yii\web\UploadedFile;
use yii\web\Response;
use backend\models\Taxonomy;

use DOMDocument;
use DOMElement;
use DOMException;
use DOMText;
use frontend\components\FrontendHelper;

//use yii\base\Arrayable;
//use yii\base\Component;
//use yii\helpers\StringHelper;

class ApiController extends \yii\web\Controller


{
    // public $layout='';

    const APPLICATION_ID = 'APIVOCAB';

    //public $viewPath = '@dektrium/user/views/mail';

    /** @var string|array Default: `Yii::$app->params['adminEmail']` OR `no-reply@example.com` */
    public $sender;

    /** @var \yii\mail\BaseMailer Default: `Yii::$app->mailer` */
    public $mailerComponent;
    public static $sum_content_plant = 0;
    public static $sum_content_animal = 0;
    public static $sum_content_fungi = 0;
    public static $sum_content_expert = 0;
    public static $sum_content_ecotourism = 0;
    public static $sum_content_product = 0;

    public function behaviors()

    {

        return [

            'access' => [

                'class' => AccessControl::className(),


                'only' => ['logout'],


                'rules' => [
                    [

                        'actions' => ['test', 'sendcontact', 'searchcontent'],

                        'allow' => true,

                        'roles' => ['@'],

                    ],

                ],

            ],

            'verbs' => [

                'class' => VerbFilter::className(),

                'actions' => [

                    'delete' => ['POST'],

                ],

            ],

        ];
    }


    public function beforeAction($action)
    {

        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);
    }



    public function actionProvince()
    {
        $request = Yii::$app->request;
        $get    = $request->get();
        $region_id = $get['region_id'];
        if (Yii::$app->language == 'en') {
            $data = Province::find()->select('id ,region_id, name_en as name')->where(['region_id' => $region_id])->asArray()->all();
        } else {
            $data = Province::find()->select('id ,region_id, name_th as name')->where(['region_id' => $region_id])->asArray()->all();
        }
        $this->_response(200, 'success', $data);
    }

    public function actionProvinceInfo()
    {
        $request = Yii::$app->request;
        $get    = $request->get();
        
        if (!isset($get['province_name'])) {
            $this->_response(400, 'province_name is required', []);
        }
        
        $province_name = $get['province_name'];
        $clean_name = str_replace(['จ.', 'จังหวัด', ' '], '', $province_name);
        
        $province = Province::find()
            ->select('id, region_id, name_th, name_en')
            ->where(['name_th' => $province_name])
            ->orWhere(['like', 'name_th', $clean_name])
            ->asArray()
            ->one();
            
        if ($province) {
            $this->_response(200, 'success', $province);
        } else {
            $this->_response(404, 'province not found', []);
        }
    }

    public function actionDistrict()
    {
        $request = Yii::$app->request;
        $get    = $request->get();
        $province_id = $get['province_id'];


        if (Yii::$app->language == 'en') {
            $data = District::find()->select('id, name_en as name, province_id')->where(['province_id' => $province_id])->asArray()->all();
        } else {
            $data = District::find()->select('id, name_th as name, province_id')->where(['province_id' => $province_id])->asArray()->all();
        }
        $this->_response(200, 'success', $data);
    }

    public function actionSubdistrict()
    {
        $request = Yii::$app->request;
        $get    = $request->get();
        $district_id = $get['district_id'];
        if (Yii::$app->language == 'en') {
            $data = Subdistrict::find()->select('id, name_en as name, district_id')->where(['district_id' => $district_id])->asArray()->all();
        } else {
            $data = Subdistrict::find()->select('id, name_th as name, district_id')->where(['district_id' => $district_id])->asArray()->all();
        }
        $this->_response(200, 'success', $data);
    }

    public function actionZipcode()
    {
        $request = Yii::$app->request;
        $get    = $request->get();
        $subdistrict_id = $get['subdistrict_id'];
        $data = Zipcode::find()->where(['subdistrict_id' => $subdistrict_id])->asArray()->all();
        $this->_response(200, 'success', $data);
    }

    public function actionTeacher()
    {
        if (!empty(Yii::$app->user->identity->id)) {
            $data = Users::find()
                ->select(['email', 'user.id'])
                ->leftJoin('user_role', 'user_role.user_id = user.id')
                ->where(['user_role.role_id' => 3])
                ->asArray()
                ->all();


            $source = array();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $dataUserJoin = UserSchool::find()
                        ->where(['user_id' => $value['id']])
                        ->asArray()
                        ->all();
                    if (empty($dataUserJoin)) {
                        $source[] = $value['email'];
                    }
                }
            }

            return json_encode($source);
        }
    }


    public function actionStudent()
    {
        if (!empty(Yii::$app->user->identity->id)) {
            $data = Users::find()
                ->select(['email', 'user.id'])
                ->leftJoin('user_role', 'user_role.user_id = user.id')
                ->where(['user_role.role_id' => 4])
                ->asArray()
                ->all();


            $source = array();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $dataUserJoin = UserSchool::find()
                        ->where(['user_id' => $value['id']])
                        ->asArray()
                        ->all();
                    if (empty($dataUserJoin)) {
                        $source[] = $value['email'];
                    }
                }
            }

            return json_encode($source);
        }
    }


    public function actionStudentall()
    {
        if (!empty(Yii::$app->user->identity->id)) {
            $data = Users::find()
                ->select(['email', 'user.id'])
                ->leftJoin('user_role', 'user_role.user_id = user.id')
                ->where(['user_role.role_id' => 4])
                ->asArray()
                ->all();


            $source = array();
            if (!empty($data)) {
                foreach ($data as $value) {
                    // $dataUserJoin = UserSchool::find()
                    // ->where(['user_id' => $value['id'] ])
                    // ->asArray()
                    // ->all();
                    // if (empty($dataUserJoin)) {
                    $source[] = $value['email'];
                    //}
                }
            }

            return json_encode($source);
        }
    }


    public function actionFindschool()
    {
        $request = Yii::$app->request;
        $get    = $request->get();
        $data = array();
        $data['school'] = array();
        $data['teacher'] = array();
        if (!empty($get['id'])) {
            $schoolId = $get['id'];

            $school = School::find()->where(['id' => $schoolId])->asArray()->one();

            if (!empty($school)) {


                $dataTeacherSchool = array();
                if (!empty($schoolId)) {
                    $teacher = (new \yii\db\Query())
                        ->select(['profile.user_id', 'firstname', 'lastname'])
                        ->from('profile')
                        ->innerJoin('user_school', 'user_school.user_id = profile.user_id')
                        ->innerJoin('user_role', 'user_role.user_id = profile.user_id')
                        ->where(['user_role.role_id' => 3])
                        ->andWhere(['user_school.school_id' => $schoolId])
                        ->all();
                    if (!empty($teacher)) {

                        foreach ($teacher as $value) {
                            $dataTeacherSchool[] = array('id' => $value['user_id'], 'name' => $value['firstname'] . " " . $value['lastname']);
                        }
                        // $dataTeacherSchool = ArrayHelper::map($teacher, 'user_id', function($model){ 
                        //     return $model['firstname'].' '.$model['lastname'];
                        // });
                    }
                    // print '<pre>';
                    // print_r($studentTeacherModel);
                    // print '</pre>';
                    // exit();
                }
                $data['school'] = $school;
                $data['teacher'] = $dataTeacherSchool;
                return json_encode($data);
            }
        }

        return json_encode($data);
    }
    function actionSearchcontent()
    {

        $request = Yii::$app->request;
        $get    = $request->get();
        $resultData = array();

        $text = "";
        $contentType = 'all';
        $region = 'all';
        $province = '';
        $district = '';
        $subdistrict = '';

        $defalutContent = array(
            ['type_id' => 1, 'number' => 0],
            ['type_id' => 2, 'number' => 0],
            ['type_id' => 3, 'number' => 0],
            ['type_id' => 4, 'number' => 0],
            ['type_id' => 5, 'number' => 0],
            ['type_id' => 6, 'number' => 0],
        );


        //print_r($get);
        // $status_get = 0; //0 is ture params
        // if(empty($get['region_id'])||
        //     empty($get['province_id'])||
        //     empty($get['district_id'])||
        //     empty($get['subdistrict_id'])
        // ){
        //     $status_get=1;
        // }
        if (!empty($get) && !empty($get['type'])) {
            $cacheKey = 'searchcontent_api_v3_' . md5(json_encode([
                'region_id' => $get['region_id'] ?? '',
                'province_id' => $get['province_id'] ?? '',
                'district_id' => $get['district_id'] ?? '',
                'subdistrict_id' => $get['subdistrict_id'] ?? '',
                'keyword' => $get['keyword'] ?? '',
                'type' => $get['type'],
                'page' => $get['page'] ?? 1
            ]));
            
            $cachedData = Yii::$app->cache->get($cacheKey);
            if ($cachedData !== false) {
                return $cachedData;
            }

            $query_content = "";
            $array_type = explode(",", $get['type']);

            $resultData['search_type'] = 'content';
            $array = [];

             // Query for Type Counts (Ignores $array_type filter so it counts all types for the map scope)
            $query = (new \yii\db\Query())
                ->select(['content.type_id', 'COUNT(content.id) as count'])
                ->from('content')
                ->leftJoin('content_type', 'content_type.id = content.type_id')
                ->where(['content.active' => 1, 'content.status' => 'approved'])
                ->andWhere(['!=', 'content.is_hidden', 1])
                ->andWhere(['content_type.is_visible' => 1])
                ->andFilterWhere(['content.region_id' => $get['region_id']])
                ->andFilterWhere(['content.province_id' => $get['province_id']])
                ->andFilterWhere(['content.district_id' => $get['district_id']])
                ->andFilterWhere(['content.subdistrict_id' => $get['subdistrict_id']])
                ->andWhere(['!=', 'content.latitude', ''])
                ->andWhere(['!=', 'content.longitude', ''])
                ->andFilterWhere(['like', 'content.name', (!empty($get['keyword']) ? $get['keyword'] : '')]);
            
            // Total Count for Pagination (Includes $array_type filter)
            $query_total = clone $query;
            $query_total->andWhere(['in', 'content.type_id', $array_type]);
            $totalCountResult = $query_total->count();
            
            $query = $query->groupBy('content.type_id')->all();

            $query_conetnt = (new \yii\db\Query())
                ->select([
                    'content.id',
                    'content.picture_path',
                    'content.name',
                    'content.description',
                    'content.latitude',
                    'content.longitude',
                    'content.type_id',
                    'content.region_id',
                    'content.province_id',
                    'content.district_id',
                    'subdistrict_id',
                    'region.name_th as region_name',
                    'province.name_th as province_name',
                    'district.name_th as district_name',
                    'subdistrict.name_th as subdistrict_name',
                ])
                ->from('content')
                ->leftJoin('region', 'region.id = content.region_id')
                ->leftJoin('province', 'province.id = content.province_id')
                ->leftJoin('district', 'district.id = content.district_id')
                ->leftJoin('subdistrict', 'subdistrict.id = content.subdistrict_id')
                ->leftJoin('content_type', 'content_type.id = content.type_id')
                ->where(['content.active' => 1])
                ->andWhere(['content.status' => 'approved'])
                ->andWhere(['!=', 'content.is_hidden', 1])
                ->andWhere(['content_type.is_visible' => 1])
                ->andFilterWhere(['content.region_id' => $get['region_id']])
                ->andFilterWhere(['content.province_id' => $get['province_id']])
                ->andFilterWhere(['content.district_id' => $get['district_id']])
                ->andFilterWhere(['content.subdistrict_id' => $get['subdistrict_id']])
                ->andFilterWhere(['like', 'content.name', (!empty($get['keyword']) ? $get['keyword'] : '')])
                ->andWhere(['in', 'content.type_id', $array_type])
                ->limit(15)
                ->offset((($get['page'] - 1) * 15))
                ->all();
       
            $contentArray = array();
            if(!empty($query_conetnt)){
                foreach($query_conetnt as $contentData){

                    
                    $contentData['description'] = strip_tags($contentData['description']);

                    $contentData['description']  = str_replace("&nbsp;","",$contentData['description'] );

                    if(mb_strlen($contentData['description']) > 1000){
                        $contentData['description'] = mb_substr ( $contentData['description'] , 0, 1000)."...";
                    }


                    $contentData['path_image'] = FileLibrary::getImageFrontend(FrontendHelper::getFolderImageContent($contentData['type_id']), $contentData["picture_path"]);

        
                    $contentArray[] = $contentData;
                }
            }

            $count = $totalCountResult;
            $allPage = ceil($count / 15);
            if ($allPage == 0) $allPage = 1;

            // Re-initialize summation for this request correctly
            self::$sum_content_plant = 0;
            self::$sum_content_animal = 0;
            self::$sum_content_fungi = 0;
            self::$sum_content_expert = 0;
            self::$sum_content_ecotourism = 0;
            self::$sum_content_product = 0;

            $array = [
                'count' => number_format($count, 0, '.', ','),
                'type_count' => self::getTypeCount($query),
                'content' => $contentArray,
                'all_page' => $allPage,
                'page_current' => intval($get['page']),
            ];



            $resultData['data'] = $array;

            $data_type_count = [
                'plant' => self::$sum_content_plant,
                'animal' => self::$sum_content_animal,
                'fungi' => self::$sum_content_fungi,
                'expert' => self::$sum_content_expert,
                'ecotourism' => self::$sum_content_ecotourism,
                'product' => self::$sum_content_product,
            ];

            $resultData['type_count'] = $data_type_count;
            
            $jsonResponse = json_encode($resultData);
            Yii::$app->cache->set($cacheKey, $jsonResponse, 300); // Cache for 5 minutes

            return $jsonResponse;
        }


        // print "<pre>";
        // print_r($resultData);
        // print "</pre>";
        return json_encode($resultData);
    }

    public function actionUpload()
    {
        $base_path = Yii::getAlias('@webroot');
        $web_path = Yii::getAlias('@webroot');
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstanceByName('file');

            if ($model->validate()) {
                $model->file->saveAs($base_path . '/uploads/' . $model->file->baseName . '.' . $model->file->extension);
            }
        }

        $hostname = $_SERVER['HTTP_HOST'];
        $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';

        if(empty($hostname)){
            $hostname = "localhost:8080";
        }

        // Get file link
        $res = [
            'link' => $protocol.$hostname.'/uploads/' . $model->file->baseName . '.' . $model->file->extension,
        ];

        // Response data
        Yii::$app->response->format = Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }

    public function actionSummerupload(){
        if ($_FILES['file']['name']) {

            $base_path = Yii::getAlias('@webroot');

            $hostname = $_SERVER['HTTP_HOST'];
            $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';

            if (!$_FILES['file']['error']) {
                $name = md5(rand(100, 200));
                $ext = explode('.', $_FILES['file']['name']);
                $filename = time()."_".$name . '.' . $ext[1];
                $destination = $base_path. '/uploads/' . $filename; //change this directory
                $location = $_FILES["file"]["tmp_name"];
                move_uploaded_file($location, $destination);
                echo $protocol.$hostname.'/uploads/'.$filename;//change this URL
            }
            else
            {
              echo  $message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['file']['error'];
            }
        }
    }

    public function actionTaxonomy($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = (new \yii\db\Query());
            $query->select('id, name AS text')
                ->from('taxonomy')
                ->where(['like', 'name', $q])
                ->limit(20);
            $command = $query->createCommand();
            $data = $command->queryAll();
            $out['results'] = array_values($data);
        }
        elseif ($id > 0) {
            $out['results'] = ['id' => $id, 'text' => Taxonomy::find($id)->name];
        }
        return $out;
    }



    function actionSearchcontentOld()
    {

        $request = Yii::$app->request;
        $get    = $request->get();
        $resultData = array();

        $text = "";
        $contentType = 'all';
        $region = 'all';
        $province = '';
        $district = '';
        $subdistrict = '';

        $defalutContent = array(
            ['type_id' => 1, 'number' => 0],
            ['type_id' => 2, 'number' => 0],
            ['type_id' => 3, 'number' => 0],
            ['type_id' => 4, 'number' => 0],
            ['type_id' => 5, 'number' => 0],
            ['type_id' => 6, 'number' => 0],
        );


        //print_r($get);
        // $status_get = 0; //0 is ture params
        // if(empty($get['region_id'])||
        //     empty($get['province_id'])||
        //     empty($get['district_id'])||
        //     empty($get['subdistrict_id'])
        // ){
        //     $status_get=1;
        // }
        if (!empty($get) && !empty($get['type'])) {
            $query_content = "";
            $array_type = explode(",", $get['type']);

            if (is_numeric($get['region_id']) == false) { //ระดับประเทศ
                $resultData['search_type'] = 'country';
                $model_region  = Region::find()->all();
                $array = [];

                foreach ($model_region as $key => $value) {
                    $query = (new \yii\db\Query())
                        ->select(['type_id', 'COUNT(region_id) as count'])
                        ->from('content')
                        ->where(['active' => 1, 'status' => 'approved'])
                        ->andWhere(['region_id' => $value['id']])
                        ->andWhere(['!=', 'latitude', ''])
                        ->andWhere(['!=', 'longitude', ''])
                        ->andWhere(['like', 'name', (!empty($get['keyword']) ? $get['keyword'] : '')])
                        ->andWhere(['in', 'type_id', $array_type]);
                    $query_total = clone $query;
                    $query = $query->groupBy('type_id')->all();
                    $query_total = $query_total->groupBy('region_id')->all();


                    $array[$key] = [
                        'region_id' => $value['id'],
                        'region_name' => $value['name_th'],
                        'count' => !empty($query_total[0]) ? number_format($query_total[0]['count'], 0, '.', ',') : 0,
                        'type_count' => self::getTypeCount($query),
                    ];
                }
                $resultData['data'] = $array;
            } else if (is_numeric($get['region_id']) && is_numeric($get['province_id']) && is_numeric($get['district_id']) && is_numeric($get['subdistrict_id'])) { //ระดับตำบล
                $resultData['search_type'] = 'subdistrict';
                $model_subdistrict  = Subdistrict::findOne($get['subdistrict_id']);
                $array = [];

                $query = (new \yii\db\Query())
                    ->select(['type_id', 'COUNT(subdistrict_id) as count'])
                    ->from('content')
                    ->where(['active' => 1])
                    ->andWhere(['region_id' => $get['region_id']])
                    ->andWhere(['province_id' => $get['province_id']])
                    ->andWhere(['district_id' => $get['district_id']])
                    ->andWhere(['subdistrict_id' => $get['subdistrict_id']])
                    ->andWhere(['!=', 'latitude', ''])
                    ->andWhere(['!=', 'longitude', ''])
                    ->andWhere(['like', 'name', (!empty($get['keyword']) ? $get['keyword'] : '')])
                    ->andWhere(['in', 'type_id', $array_type]);
                $query_total = clone $query;
                $query = $query->groupBy('type_id')->all();
                $query_total = $query_total->groupBy('subdistrict_id')->all();

                $query_conetnt = (new \yii\db\Query())
                    ->select(['picture_path', 'name', 'description', 'latitude', 'longitude', 'type_id', 'region_id', 'province_id', 'district_id', 'subdistrict_id'])
                    ->from('content')
                    ->where(['active' => 1])
                    ->andWhere(['region_id' => $get['region_id']])
                    ->andWhere(['province_id' => $get['province_id']])
                    ->andWhere(['district_id' => $get['district_id']])
                    ->andWhere(['subdistrict_id' => $get['subdistrict_id']])
                    ->andWhere(['!=', 'latitude', ''])
                    ->andWhere(['!=', 'longitude', ''])
                    ->andWhere(['like', 'name', (!empty($get['keyword']) ? $get['keyword'] : '')])
                    ->andWhere(['in', 'type_id', $array_type])
                    ->all();

                $array = [
                    'subdistrict_id' => $model_subdistrict['id'],
                    'subdistrict_name' => $model_subdistrict['name_th'],
                    'count' => !empty($query_total[0]) ? number_format($query_total[0]['count'], 0, '.', ',') : 0,
                    'type_count' => self::getTypeCount($query),
                    'content' => $query_conetnt,
                ];

                $resultData['data'] = $array;

                // get detail content 




            } else if (is_numeric($get['region_id']) && is_numeric($get['province_id']) && is_numeric($get['district_id'])) { //ระดับอำเภอ
                $resultData['search_type'] = 'district';
                $model_province  = Subdistrict::find()->where(['district_id' => $get['district_id']])->all();
                $array = [];
                foreach ($model_province as $key => $value) {

                    $query = (new \yii\db\Query())
                        ->select(['type_id', 'COUNT(subdistrict_id) as count'])
                        ->from('content')
                        ->where(['active' => 1])
                        ->andWhere(['region_id' => $get['region_id']])
                        ->andWhere(['province_id' => $get['province_id']])
                        ->andWhere(['district_id' => $get['district_id']])
                        ->andWhere(['subdistrict_id' => $value['id']])
                        ->andWhere(['!=', 'latitude', ''])
                        ->andWhere(['!=', 'longitude', ''])
                        ->andWhere(['like', 'name', (!empty($get['keyword']) ? $get['keyword'] : '')])
                        ->andWhere(['in', 'type_id', $array_type]);
                    $query_total = clone $query;
                    $query = $query->groupBy('type_id')->all();
                    $query_total = $query_total->groupBy('subdistrict_id')->all();


                    $array[$key] = [
                        'subdistrict_id' => $value['id'],
                        'subdistrict_name' => $value['name_th'],
                        'count' => !empty($query_total[0]) ? number_format($query_total[0]['count'], 0, '.', ',') : 0,
                        'type_count' => self::getTypeCount($query),
                    ];
                }
                $resultData['data'] = $array;
            } else if (is_numeric($get['region_id']) && is_numeric($get['province_id'])) { //ระดับจังหวัด

                $resultData['search_type'] = 'province';
                $model_province  = District::find()->where(['province_id' => $get['province_id']])->all();
                $array = [];
                foreach ($model_province as $key => $value) {
                    $query = (new \yii\db\Query())
                        ->select(['type_id', 'COUNT(district_id) as count'])
                        ->from('content')
                        ->where(['active' => 1])
                        ->andWhere(['region_id' => $get['region_id']])
                        ->andWhere(['province_id' => $get['province_id']])
                        ->andWhere(['district_id' => $value['id']])
                        ->andWhere(['!=', 'latitude', ''])
                        ->andWhere(['!=', 'longitude', ''])
                        ->andWhere(['like', 'name', (!empty($get['keyword']) ? $get['keyword'] : '')])
                        ->andWhere(['in', 'type_id', $array_type]);
                    $query_total = clone $query;
                    $query = $query->groupBy('type_id')->all();
                    $query_total = $query_total->groupBy('district_id')->all();


                    $array[$key] = [
                        'district_id' => $value['id'],
                        'district_name' => $value['name_th'],
                        'count' => !empty($query_total[0]) ? number_format($query_total[0]['count'], 0, '.', ',') : 0,
                        'type_count' => self::getTypeCount($query),
                    ];
                }
                $resultData['data'] = $array;
            } else { //ระดับภาค
                $resultData['search_type'] = 'region';
                $model_province  = Province::find()->where(['region_id' => $get['region_id']])->all();
                $array = [];
                foreach ($model_province as $key => $value) {
                    $query = (new \yii\db\Query())
                        ->select(['type_id', 'COUNT(province_id) as count'])
                        ->from('content')
                        ->where(['active' => 1])
                        ->andWhere(['region_id' => $get['region_id']])
                        ->andWhere(['province_id' => $value['id']])
                        ->andWhere(['!=', 'latitude', ''])
                        ->andWhere(['!=', 'longitude', ''])
                        ->andWhere(['like', 'name', (!empty($get['keyword']) ? $get['keyword'] : '')])
                        ->andWhere(['in', 'type_id', $array_type]);
                    $query_total = clone $query;
                    $query = $query->groupBy('type_id')->all();
                    $query_total = $query_total->groupBy('province_id')->all();


                    $array[$key] = [
                        'province_id' => $value['id'],
                        'province_name' => $value['name_th'],
                        'count' => !empty($query_total[0]) ? number_format($query_total[0]['count'], 0, '.', ',') : 0,
                        'type_count' => self::getTypeCount($query),
                    ];
                }
                $resultData['data'] = $array;
            }

            $data_type_count = [
                'plant' => self::$sum_content_plant,
                'animal' => self::$sum_content_animal,
                'fungi' => self::$sum_content_fungi,
                'expert' => self::$sum_content_expert,
                'ecotourism' => self::$sum_content_ecotourism,
                'product' => self::$sum_content_product,
            ];

            $resultData['type_count'] = $data_type_count;
        }
        // print "<pre>";
        // print_r($resultData);
        // print "</pre>";
        return json_encode($resultData);
    }

    private function getTypeCount($query_type)
    {
        $content_plant = 0;
        $content_animal = 0;
        $content_fungi = 0;
        $content_expert = 0;
        $content_ecotourism = 0;
        $content_product = 0;

        foreach ($query_type as $key => $value) {
            $type_content = '';
            if ($value['type_id'] == 1) {

                $content_plant = $value['count'];
            } else if ($value['type_id'] == 2) {

                $content_animal = $value['count'];
            } else if ($value['type_id'] == 3) {

                $content_fungi = $value['count'];
            } else if ($value['type_id'] == 4) {

                $content_expert = $value['count'];
            } else if ($value['type_id'] == 5) {

                $content_ecotourism = $value['count'];
            } else if ($value['type_id'] == 6) {

                $content_product = $value['count'];
            }
        }

        self::$sum_content_plant = self::$sum_content_plant + $content_plant;
        self::$sum_content_animal = self::$sum_content_animal + $content_animal;
        self::$sum_content_fungi = self::$sum_content_fungi + $content_fungi;
        self::$sum_content_expert = self::$sum_content_expert + $content_expert;
        self::$sum_content_ecotourism = self::$sum_content_ecotourism + $content_ecotourism;
        self::$sum_content_product = self::$sum_content_product + $content_product;

        $data_type_count = [
            'plant' => $content_plant,
            'animal' => $content_animal,
            'fungi' => $content_fungi,
            'expert' => $content_expert,
            'ecotourism' => $content_ecotourism,
            'product' => $content_product,
        ];
        return $data_type_count;
    }

    private function getTypeCount2($array_type, $query_content)
    {
        $content_plant = 0;
        $content_animal = 0;
        $content_fungi = 0;
        $content_expert = 0;
        $content_ecotourism = 0;
        $content_product = 0;

        foreach ($array_type as $key => $value) {
            $query = clone $query_content;
            $type_content = '';
            if ($value == 1) {
                $type_content = $query->andWhere(['type_id' => 1])->all();
                if (!empty($type_content)) {
                    $content_plant = count($type_content);
                }
            } else if ($value == 2) {
                $type_content = $query->andWhere(['type_id' => 2])->all();
                if (!empty($type_content)) {
                    $content_animal = count($type_content);
                }
            } else if ($value == 3) {
                $type_content = $query->andWhere(['type_id' => 3])->all();
                if (!empty($type_content)) {
                    $content_fungi = count($type_content);
                }
            } else if ($value == 4) {
                $type_content = $query->andWhere(['type_id' => 4])->all();
                if (!empty($type_content)) {
                    $content_expert = count($type_content);
                }
            } else if ($value == 5) {
                $type_content = $query->andWhere(['type_id' => 5])->all();
                if (!empty($type_content)) {
                    $content_ecotourism = count($type_content);
                }
            } else if ($value == 6) {
                $type_content = $query->andWhere(['type_id' => 6])->all();
                if (!empty($type_content)) {
                    $content_product = count($type_content);
                }
            }
        }

        self::$sum_content_plant = self::$sum_content_plant + $content_plant;
        self::$sum_content_animal = self::$sum_content_animal + $content_animal;
        self::$sum_content_fungi = self::$sum_content_fungi + $content_fungi;
        self::$sum_content_expert = self::$sum_content_expert + $content_expert;
        self::$sum_content_ecotourism = self::$sum_content_ecotourism + $content_ecotourism;
        self::$sum_content_product = self::$sum_content_product + $content_product;

        $data_type_count = [
            'plant' => $content_plant,
            'animal' => $content_animal,
            'fungi' => $content_fungi,
            'expert' => $content_expert,
            'ecotourism' => $content_ecotourism,
            'product' => $content_product,
        ];
        return $data_type_count;
    }

    public function actionHeatmapProvince()
    {
        $cacheKey = 'heatmap_province_count';
        $data = Yii::$app->cache->get($cacheKey);

        if ($data === false) {
            $sql = "SELECT p.name_en, p.name_th, p.id as province_id, COUNT(c.id) as total 
                    FROM province p 
                    LEFT JOIN content c ON c.province_id = p.id AND c.status = 'approved' AND c.active = 1 AND (c.is_hidden = 0 OR c.is_hidden IS NULL)
                    LEFT JOIN content_type ct ON c.type_id = ct.id AND ct.is_visible = 1
                    WHERE c.id IS NULL OR ct.id IS NOT NULL
                    GROUP BY p.id, p.name_en, p.name_th";
            $data = Yii::$app->db->createCommand($sql)->queryAll();

            $formattedData = [];
            foreach ($data as $row) {
                $formattedData[] = [
                    'id' => $row['province_id'],
                    'name_en' => $row['name_en'],
                    'name_th' => $row['name_th'],
                    'total' => (int)$row['total']
                ];
            }
            $data = $formattedData;

            Yii::$app->cache->set($cacheKey, $data, 3600); // Cache for 1 hour
        }

        $this->_response(200, "success", $data);
    }

    public function actionHeatmapDistrict($province_name = null)
    {
        if (empty($province_name)) {
            $this->_response(400, "province_name is required");
        }

        $cacheKey = 'heatmap_district_count_' . $province_name;
        $data = Yii::$app->cache->get($cacheKey);

        if ($data === false) {
            $sql = "SELECT d.name_en, d.name_th, d.id as district_id, COUNT(c.id) as total 
                    FROM district d 
                    INNER JOIN province p ON d.province_id = p.id
                    LEFT JOIN content c ON c.district_id = d.id AND c.status = 'approved' AND c.active = 1 AND (c.is_hidden = 0 OR c.is_hidden IS NULL)
                    LEFT JOIN content_type ct ON c.type_id = ct.id AND ct.is_visible = 1
                    WHERE p.name_th = :province_name AND (c.id IS NULL OR ct.id IS NOT NULL)
                    GROUP BY d.id, d.name_en, d.name_th";
            $data = Yii::$app->db->createCommand($sql)->bindValue(':province_name', $province_name)->queryAll();

            $formattedData = [];
            foreach ($data as $row) {
                $formattedData[] = [
                    'id' => $row['district_id'],
                    'name_en' => $row['name_en'],
                    'name_th' => $row['name_th'],
                    'total' => (int)$row['total']
                ];
            }
            $data = $formattedData;

            Yii::$app->cache->set($cacheKey, $data, 3600); // Cache for 1 hour
        }

        $this->_response(200, "success", $data);
    }

    public function actionHeatmapSubdistrict($district_id = null)
    {
        if (empty($district_id)) {
            $this->_response(400, "district_id is required");
            return;
        }

        $cacheKey = 'heatmap_subdistrict_count_' . $district_id;
        $data = Yii::$app->cache->get($cacheKey);

        if ($data === false) {
            $sql = "SELECT s.name_en, s.name_th, s.id as subdistrict_id, COUNT(c.id) as total
                    FROM subdistrict s
                    LEFT JOIN content c ON c.subdistrict_id = s.id AND c.status = 'approved' AND c.active = 1 AND (c.is_hidden = 0 OR c.is_hidden IS NULL)
                    LEFT JOIN content_type ct ON c.type_id = ct.id AND ct.is_visible = 1
                    WHERE s.district_id = :district_id AND (c.id IS NULL OR ct.id IS NOT NULL)
                    GROUP BY s.id, s.name_en, s.name_th";
            $data = Yii::$app->db->createCommand($sql)->bindValue(':district_id', $district_id)->queryAll();

            $formattedData = [];
            foreach ($data as $row) {
                $formattedData[] = [
                    'id' => $row['subdistrict_id'],
                    'name_en' => $row['name_en'],
                    'name_th' => $row['name_th'],
                    'total' => (int)$row['total']
                ];
            }
            $data = $formattedData;

            Yii::$app->cache->set($cacheKey, $data, 3600); // Cache for 1 hour
        }

        $this->_response(200, "success", $data);
    }

    private function _response($status = 200, $message = "", $content = "", $total = "0")
    {
        header('Access-Control-Allow-Origin: *');
        if (empty($message)) :


            switch ($status) {

                case 200:

                    $message = 'success';

                    break;

                case 401:

                    $message = 'You must be authorized to view this page.';

                    break;

                case 404:

                    $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';

                    break;

                case 500:

                    $message = 'The server encountered an error processing your request.';

                    break;

                case 501:

                    $message = 'The requested method is not implemented.';

                    break;
            }
        endif;


        if (!empty($content) && $status == 200) {

            $data = array(

                "status" => $status,
                "message" => $message,
                "total" => $total,
                "data" => $content

            );

            header('Content-Type: application/json');

            echo json_encode($data, JSON_PRETTY_PRINT);
        } else {

            if (!empty($content)) {

                $data = array(
                    "status" => $status,
                    "message" => $message,
                    "total" => $total,
                    "data" => $content
                );
            } else {

                $data = array(
                    "status" => $status,
                    "message" => $message,
                    "total" => $total,
                    "data" => [],

                );
            }

            header('Content-Type: application/json');
            echo json_encode($data, JSON_PRETTY_PRINT);
        }

        exit();
    }

    private function setHeader($status)
    {

        $status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
        $content_type = "application/json; charset=utf-8";

        header($status_header);
        header('Content-type: ' . $content_type);
        header('Access-Control-Allow-Origin: *');
    }


    private function _getStatusCodeMessage($status)
    {
        $codes = array(
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
        );
        return (isset($codes[$status])) ? $codes[$status] : '';
    }
}
