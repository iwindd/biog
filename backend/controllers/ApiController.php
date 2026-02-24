<?php
namespace backend\controllers;


use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;
use yii\data\Sort;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\base\Component;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use backend\models\Province;
use backend\models\District;
use backend\models\Subdistrict;
use backend\models\Zipcode;
use backend\models\Users;
use backend\models\Profile;
use backend\models\UserSchool;

use DOMDocument;
use DOMElement;
use DOMException;
use DOMText;

use frontend\models\UploadForm;
use yii\web\UploadedFile;
use yii\web\Response;

//use yii\base\Arrayable;
//use yii\base\Component;
//use yii\helpers\StringHelper;

class ApiController extends \yii\web\Controller


{
   // public $layout='';

    //Const APPLICATION_ID = 'APIVOCAB';

    //public $viewPath = '@dektrium/user/views/mail';

    /** @var string|array Default: `Yii::$app->params['adminEmail']` OR `no-reply@example.com` */
    public $sender;

    /** @var \yii\mail\BaseMailer Default: `Yii::$app->mailer` */
    public $mailerComponent;

    public function behaviors()

    {

        return [

            'access' => [

                'class' => AccessControl::className(),


                'only' => ['logout'],


                'rules' => [
                    [

                        'actions' => ['test','sendcontact'],

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


    public function beforeAction($action) {

        $this->enableCsrfValidation = false;

        return parent::beforeAction($action);

    }


    public function actionUpload()
    {
        $base_path = realpath(dirname(__FILE__) . '/../../') . '/frontend/web/';
        $web_path = Yii::getAlias('@webroot');
        $model = new UploadForm();

        if (Yii::$app->request->isPost) {
            $model->file = UploadedFile::getInstanceByName('file');

            if ($model->validate()) {
                $model->file->saveAs($base_path . '/uploads/' . $model->file->baseName . '.' . $model->file->extension);
            }
        }

        $hostname = Yii::$app->params['urlWebBiog'];

        if(empty($hostname)){
            $hostname = "localhost:8080";
        }

        // Get file link
        $res = [
            'link' => $hostname.'/uploads/' . $model->file->baseName . '.' . $model->file->extension,
        ];

        // Response data
        Yii::$app->response->format = Yii::$app->response->format = Response::FORMAT_JSON;
        return $res;
    }


    public function actionSummerupload(){
        if ($_FILES['file']['name']) {

            $base_path = realpath(dirname(__FILE__) . '/../../') . '/frontend/web';

            $hostname = Yii::$app->params['urlWebBiog'];
            //$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';

            if (!$_FILES['file']['error']) {
                $name = md5(rand(100, 200));
                $ext = explode('.', $_FILES['file']['name']);
                $filename = time()."_".$name . '.' . $ext[1];
                $destination = $base_path. '/uploads/' . $filename; //change this directory
                $location = $_FILES["file"]["tmp_name"];
                move_uploaded_file($location, $destination);
                echo $hostname.'uploads/'.$filename;//change this URL
            }
            else
            {
              echo  $message = 'Ooops!  Your upload triggered the following error:  '.$_FILES['file']['error'];
            }
        }
    }


    public function actionUsersall($q = null, $id = null) {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $name = explode(" ",$q);
            $q2 = "";
            if(COUNT($name) > 1 ){
                $q = $name[0];
                $q2 = $name[1];
            }else{
                $q2 = $q;
            }
            $profile = Users::find()
                ->select([
                        'user.id',
                        'profile.firstname as firstname',
                        'profile.lastname as lastname',
                    ])
                ->leftjoin('profile', 'profile.user_id = user.id')
                ->leftjoin('user_role', 'user_role.user_id = user.id')
                ->leftjoin('role', 'role.id = user_role.role_id')
                ->where([
                    'IN','user_role.role_id',[1,2,4,3,5,6]
                ])
                ->andWhere([
                    'or',
                    ['like', 'firstname', $q],
                    ['like', 'lastname', $q2],
                ])
                ->limit(20)
                ->all();
            //$profile = Profile::find()->select(['user_id','firstname','lastname'])->all();
            $array=[];
            foreach ($profile as $key => $value) {
                //print_r($value);
                $array[$key]=[
                    'id'=>$value['id'],
                    'text'=>$value['firstname']." ".$value['lastname'],
                ];
            }

            $out['results'] = $array;
        }
        elseif ($id > 0) {
            $data = Profile::find()->where(['user_id' => $id])->one();
            $out['results'] = ['id' => $id, 'text' => $data['firstname']." ".$data['lastname'] ];
        }

        return $out;

        //return ArrayHelper::map($array, 'id', 'name');

        // $out = ['results' => ['id' => '', 'text' => '']];
        // if (!is_null($q)) {
        //     $query = new Query;
        //     $query->select('id, name AS text')
        //         ->from('taxonomy')
        //         ->where(['like', 'name', $q])
        //         ->limit(20);
        //     $command = $query->createCommand();
        //     $data = $command->queryAll();
        //     $out['results'] = array_values($data);
        // }
        // elseif ($id > 0) {
        //     $out['results'] = ['id' => $id, 'text' => Profile::find($id)->name];
        // }
        // return $out;
    }

    public function actionUserslist($q = null, $id = null){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $name = explode(" ",$q);
            $q2 = "";
            if(COUNT($name) > 1 ){
                $q = $name[0];
                $q2 = $name[1];
            }else{
                $q2 = $q;
            }
            $profile = Users::find()
            ->select([
                    'user.id',
                    'profile.firstname as firstname',
                    'profile.lastname as lastname',
                ])
                ->leftjoin('profile', 'profile.user_id = user.id')
                ->leftjoin('user_role', 'user_role.user_id = user.id')
                ->leftjoin('role', 'role.id = user_role.role_id')
                ->where([
                    'IN','user_role.role_id',[1,2,4]
                ])
                ->andWhere([
                    'or',
                    ['like', 'firstname', $q],
                    ['like', 'lastname', $q2],
                ])
                ->limit(20)
                ->all();
            //$profile = Profile::find()->select(['user_id','firstname','lastname'])->all();
            $array=[];
            foreach ($profile as $key => $value) {
                //print_r($value);
                $array[$key]=[
                    'id'=>$value['id'],
                    'text'=>$value['firstname']." ".$value['lastname'],
                ];
            }

            $out['results'] = $array;

        }elseif ($id > 0) {
            $data = Profile::find()->where(['user_id' => $id])->one();
            $out['results'] = ['id' => $id, 'text' => $data['firstname']." ".$data['lastname'] ];
        }

        return $out;
    }


    public function actionEditslist($q = null, $id = null){

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $name = explode(" ",$q);
            $q2 = "";
            if(COUNT($name) > 1 ){
                $q = $name[0];
                $q2 = $name[1];
            }else{
                $q2 = $q;
            }
            $profile = Users::find()
            ->select([
                'user.id',
                'profile.firstname as firstname',
                'profile.lastname as lastname',
            ])
            ->leftjoin('profile', 'profile.user_id = user.id')
            ->leftjoin('user_role', 'user_role.user_id = user.id')
            ->leftjoin('role', 'role.id = user_role.role_id')
            ->where([
                'IN','user_role.role_id',[1,2,4,3]
            ])->andWhere([
                'or',
                ['like', 'firstname', $q],
                ['like', 'lastname', $q2],
            ])
            ->limit(20)->all();
            //$profile = Profile::find()->select(['user_id','firstname','lastname'])->all();
            $array=[];
            foreach ($profile as $key => $value) {
                //print_r($value);
                $array[$key]=[
                    'id'=>$value['id'],
                    'text'=>$value['firstname']." ".$value['lastname'],
                ];
            }

            $out['results'] = $array;

        }elseif ($id > 0) {
            $data = Profile::find()->where(['user_id' => $id])->one();
            $out['results'] = ['id' => $id, 'text' => $data['firstname']." ".$data['lastname'] ];
        }

        return $out;
    }

    public function actionApproverlist($q = null, $id = null){
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $name = explode(" ",$q);
            $q2 = "";
            if(COUNT($name) > 1 ){
                $q = $name[0];
                $q2 = $name[1];
            }else{
                $q2 = $q;
            }
            $profile = Users::find()
            ->select([
                    'user.id',
                    'profile.firstname as firstname',
                    'profile.lastname as lastname',
                ])
                ->leftjoin('profile', 'profile.user_id = user.id')
                ->leftjoin('user_role', 'user_role.user_id = user.id')
                ->leftjoin('role', 'role.id = user_role.role_id')
                ->where([
                    'IN','user_role.role_id',[1,2,3]
                ])->andWhere([
                    'or',
                    ['like', 'firstname', $q],
                    ['like', 'lastname', $q2],
                ])
                ->limit(20)->all();
            //$profile = Profile::find()->select(['user_id','firstname','lastname'])->all();
            $array=[];
            foreach ($profile as $key => $value) {
                //print_r($value);
                $array[$key]=[
                    'id'=>$value['id'],
                    'text'=>$value['firstname']." ".$value['lastname'],
                ];
            }

            $out['results'] = $array;

        }elseif ($id > 0) {
            $data = Profile::find()->where(['user_id' => $id])->one();
            $out['results'] = ['id' => $id, 'text' => $data['firstname']." ".$data['lastname'] ];
        }

        return $out;
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

    public function actionTeacher(){
        if (!empty(Yii::$app->user->identity->id)) {
            $data = Users::find()
                ->select(['email', 'user.id'])
                ->leftJoin('user_role', 'user_role.user_id = user.id')
                ->where(['user_role.role_id' => 3 ])
                ->asArray()
                ->all();

            
            $source = array();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $dataUserJoin = UserSchool::find()
                    ->where(['user_id' => $value['id'] ])
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


    public function actionStudent(){
        if (!empty(Yii::$app->user->identity->id)) {
            $data = Users::find()
            ->select(['email', 'user.id'])
            ->leftJoin('user_role', 'user_role.user_id = user.id')
            ->where(['user_role.role_id' => 4 ])
            ->asArray()
            ->all();

        
            $source = array();
            if (!empty($data)) {
                foreach ($data as $value) {
                    $dataUserJoin = UserSchool::find()
                ->where(['user_id' => $value['id'] ])
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


    public function actionStudentall(){
        if (!empty(Yii::$app->user->identity->id)) {
            $data = Users::find()
            ->select(['email', 'user.id'])
            ->leftJoin('user_role', 'user_role.user_id = user.id')
            ->where(['user_role.role_id' => 4 ])
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


    public function actionStudentschoolall(){
        if (!empty(Yii::$app->user->identity->id)) {
            $data = Users::find()
            ->select(['email', 'user.id'])
            ->leftJoin('user_role', 'user_role.user_id = user.id')
            ->leftJoin('user_school', 'user_school.user_id = user.id')
            ->where(['user_role.role_id' => 4 , 'user_school.school_id' => $_GET['school_id'] ])
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



