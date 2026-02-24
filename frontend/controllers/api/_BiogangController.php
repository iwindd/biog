<?php

namespace frontend\controllers\api;

use yii\helpers\Json;
use yii\web\Controller;
use common\components\_;
use frontend\models\content\Taxonomy;
use frontend\models\content\ContentTaxonomy;
use frontend\components\TaxonomyHelper;
use yii\imagine\Image;
use Imagine\Image\Box;

use backend\models\Content;
use backend\models\ContentPlant;
use backend\models\ContentAnimal;
use backend\models\ContentExpert;
use backend\models\ContentEcotourism;

use frontend\models\Users;
use frontend\models\Profile;
use backend\models\UserRole;
use backend\models\UserSchool;
use backend\models\School;

use yii\data\Pagination;

use Yii;

class BiogangController extends Controller
{
    public static $sum_content_plant = 0;

    public function actionType()
    {
        $return = array(
            'status' => 200,
            "errorCode" => 0,
            'message' => "Result found.",
            'data' => array(
                'total'=> 0,
                'items' => array()
            )
        );
        
        try{

            $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];

            $request = Yii::$app->request;
            
            // $ipAddress = $request->headers->get('ip-address');
            
            // if(!empty($ipAddress)){
                //if($ipAddress == '118.168.1.45'){

                    $types = array(

                            array(
                                "id" => 'plant',
                                "name" => "พืช",
                                "url" => $host."/api/biogang-items/plants?page=1&size=10"
                            ),
                            array(
                                "id" => 'animal',
                                "name" => "สัตว์",
                                "url" => $host."/api/biogang-items/animals?page=1&size=10"
                            ),
                            array(
                                "id" => 'fungi',
                                "name" => "จุลินทรีย์",
                                "url" => $host."/api/biogang-items/fungis?page=1&size=10"
                            ),
                        );

                    $return['data']['total'] = count($types);
                    $return['data']['items'] = $types;
                // }else{
                //     $return['message'] = 'คุณไม่มีสิทธิ์เข้าถึง | Invalid authorization header';
                //     $return['errorCode'] = 120;
                // }
            // }else{
            //     $return['message'] = 'คุณไม่มีสิทธิ์เข้าถึง | Invalid authorization header';
            //     $return['errorCode'] = 120;
            // }

        }catch(\Exception $exception){
            $return['message'] = 'เกิดข้อผิดพลาด API ไม่พร้อมใช้งาน';
            $return['errorCode'] = 200;
        }
       
        return json_encode($return);

        //return _::toJsonString($type);
    }

    public function actionData(){
        $return = array(
            'status' => 200,
            "errorCode" => 0,
            'message' => "Result found.",
            'data' => array(
                
            )
        );
        
        try{

            $request = Yii::$app->request;

            $get = $request->get();
            
            $ipAddress = '118.168.1.45';//$request->headers->get('ip-address');
            
            if(!empty($ipAddress)){
                if($ipAddress == '118.168.1.45'){

                    if (!empty($get['id'])) {
                        if (is_numeric($get['id'])) {

                            $queryCheckType = (new \yii\db\Query())
                                ->select([
                                    'content.id',
                                    'content.name',
                                    'type.name as type',
                                    ])
                                ->from('content')
                                ->innerJoin('type', 'type.id = content.type_id')
                                ->where(['content.active' => 1, 'content.id' => $get['id'], 'content.status' => 'approved']);
                            
                            $queryType = $queryCheckType->one();
                                
                            if (!empty($queryType['type'])) {
                                $type = mb_strtolower($queryType['type']);

                                $query = (new \yii\db\Query())
                                    ->select([
                                        'content.id',
                                        'content.name',
                                        'type.name as type',
                                        'content.picture_path AS picture',
                                        'content_'.$type.'.other_name',
                                        'content_'.$type.'.features',
                                        'content_'.$type.'.benefit',
                                        'content_'.$type.'.season',
                                        'content_'.$type.'.ability',
                                        'content_'.$type.'.common_name',
                                        'content_'.$type.'.scientific_name',
                                        'content_'.$type.'.family_name',
                                        'content_'.$type.'.other_information',
                                        'content.source_information'
                                        ])
                                    ->from('content')
                                    ->innerJoin('content_'.$type, 'content_'.$type.'.content_id = content.id')
                                    ->innerJoin('type', 'type.id = content.type_id')
                                    ->where(['active' => 1, 'content.id' => $get['id']]);
                                
                                $query = $query->one();
                                        
                                if (!empty($query['picture'])) {
                                    $query['picture'] = self::Host()."/files/content-".$type."/".$query['picture'];
                                }

                                $query['taxonomy'] = $this->getTaxonomyName($query['id']);

                                $query['gallery'] = $this->getGallery($query['id'], $type);
                        
                                $return['data']= $query;
                            }else{
                                $return['message'] = 'ไม่พบข้อมูล';
                                $return['errorCode'] = 404;
                            }
                        }else{
                            $return['message'] = 'ไม่พบข้อมูล';
                            $return['errorCode'] = 404;
                        }
                    }else{
                        $return['message'] = 'ไม่พบข้อมูล';
                        $return['errorCode'] = 404;
                    }
                }else{
                    $return['message'] = 'คุณไม่มีสิทธิ์เข้าถึง | Invalid authorization header';
                    $return['errorCode'] = 120;
                }
            }else{
                $return['message'] = 'คุณไม่มีสิทธิ์เข้าถึง | Invalid authorization header';
                $return['errorCode'] = 120;
            }

        }catch(\Exception $exception){

            $return['message'] = 'เกิดข้อผิดพลาด API ไม่พร้อมใช้งาน';
            $return['errorCode'] = 200;
        }
       
        return json_encode($return);
    }

    public function actionPlants()
    {
        $return = array(
            'status' => 200,
            "errorCode" => 0,
            'message' => "Result found.",
            'data' => array(
                'page' => 1,
                'size' => 10,
                'total'=> 0,
                'items' => array()
            )
        );
        
        try{

            $request = Yii::$app->request;

            $get = $request->get();
            
            $ipAddress = '118.168.1.45';//$request->headers->get('ip-address');
            
            if(!empty($ipAddress)){
                if($ipAddress == '118.168.1.45'){

                    $page = 1;
                    $size = 10;
                    if(!empty($get['page'])){
                        if (is_numeric($get['page'])) {
                            $page = intval($get['page']);
                        }
                    }

                    if(!empty($get['size'])){
                        if (is_numeric($get['size'])) {
                            $size = intval($get['size']);
                        }
                    }

                    $query = (new \yii\db\Query())
                        ->select([
                            'content.id', 
                            'content.name', 
                            'content.picture_path AS picture', 
                            'content_plant.other_name',
                            'content_plant.features',
                            'content_plant.benefit',
                            'content_plant.season',
                            'content_plant.ability',
                            'content_plant.common_name',
                            'content_plant.scientific_name',
                            'content_plant.family_name',
                            'content_plant.other_information',
                            'content.source_information'
                            ])
                        ->from('content')
                        ->innerJoin('content_plant', 'content_plant.content_id = content.id')
                        ->where(['active' => 1, 'type_id' => 1, 'content.status' => 'approved']);
                    
                    $queryCount = $query->distinct()->count();
                    
                    $query = $query->limit($size)
                        ->offset((($page - 1) * $size))
                        ->all();

                    $return['data']['page'] = $page;
                    $return['data']['size'] = $size;

                    $dataResult = array();
                    foreach ($query as $key => $value) {
                        if(!empty($value['picture'])){
                            $value['picture'] = self::Host()."/files/content-plant/".$value['picture'];
                        }

                        $value['taxonomy'] = $this->getTaxonomyName($value['id']);

                        $value['gallery'] = $this->getGallery($value['id'], 'plant');

                        $dataResult[] = $value;
                    }

                    $return['data']['total'] = intval($queryCount);
                    $return['data']['items'] = $dataResult;
                }else{
                    $return['message'] = 'คุณไม่มีสิทธิ์เข้าถึง | Invalid authorization header';
                    $return['errorCode'] = 120;
                }
            }else{
                $return['message'] = 'คุณไม่มีสิทธิ์เข้าถึง | Invalid authorization header';
                $return['errorCode'] = 120;
            }

        }catch(\Exception $exception){
            $return['message'] = 'เกิดข้อผิดพลาด API ไม่พร้อมใช้งาน';
            $return['errorCode'] = 200;
        }
       
        return json_encode($return);

        //return _::toJsonString($type);
    }


    public function actionAnimals()
    {
        $return = array(
            'status' => 200,
            "errorCode" => 0,
            'message' => "Result found.",
            'data' => array(
                'page' => 1,
                'size' => 10,
                'total'=> 0,
                'items' => array()
            )
        );
        
        try{

            $request = Yii::$app->request;

            $get = $request->get();
            
            $ipAddress = '118.168.1.45';//$request->headers->get('ip-address');
            
            if(!empty($ipAddress)){
                if($ipAddress == '118.168.1.45'){

                    $page = 1;
                    $size = 10;
                    if(!empty($get['page'])){
                        if (is_numeric($get['page'])) {
                            $page = intval($get['page']);
                        }
                    }

                    if(!empty($get['size'])){
                        if (is_numeric($get['size'])) {
                            $size = intval($get['size']);
                        }
                    }

                    $query = (new \yii\db\Query())
                        ->select([
                            'content.id', 
                            'content.name', 
                            'content.picture_path AS picture', 
                            'content_animal.other_name',
                            'content_animal.features',
                            'content_animal.benefit',
                            'content_animal.season',
                            'content_animal.ability',
                            'content_animal.common_name',
                            'content_animal.scientific_name',
                            'content_animal.family_name',
                            'content_animal.other_information',
                            'content.source_information'
                            ])
                        ->from('content')
                        ->innerJoin('content_animal', 'content_animal.content_id = content.id')
                        ->where(['active' => 1, 'type_id' => 2, 'content.status' => 'approved']);
                    
                    $queryCount = $query->distinct()->count();
                    
                    $query = $query->limit($size)
                        ->offset((($page - 1) * $size))
                        ->all();

                    $return['data']['page'] = $page;
                    $return['data']['size'] = $size;

                    $dataResult = array();
                    foreach ($query as $key => $value) {
                        if(!empty($value['picture'])){
                            $value['picture'] = self::Host()."/files/content-animal/".$value['picture'];
                        }

                        $value['taxonomy'] = $this->getTaxonomyName($value['id']);

                        $value['gallery'] = $this->getGallery($value['id'], 'animal');

                        $dataResult[] = $value;
                    }

                    $return['data']['total'] = intval($queryCount);
                    $return['data']['items'] = $dataResult;
                }else{
                    $return['message'] = 'คุณไม่มีสิทธิ์เข้าถึง | Invalid authorization header';
                    $return['errorCode'] = 120;
                }
            }else{
                $return['message'] = 'คุณไม่มีสิทธิ์เข้าถึง | Invalid authorization header';
                $return['errorCode'] = 120;
            }

        }catch(\Exception $exception){
            $return['message'] = 'เกิดข้อผิดพลาด API ไม่พร้อมใช้งาน';
            $return['errorCode'] = 200;
        }
       
        return json_encode($return);

        //return _::toJsonString($type);
    }

    public function actionMicros()
    {
        $return = array(
            'status' => 200,
            "errorCode" => 0,
            'message' => "Result found.",
            'data' => array(
                'page' => 1,
                'size' => 10,
                'total'=> 0,
                'items' => array()
            )
        );
        
        try{

            $request = Yii::$app->request;

            $get = $request->get();
            
            $ipAddress = '118.168.1.45';//$request->headers->get('ip-address');
            
            if(!empty($ipAddress)){
                if($ipAddress == '118.168.1.45'){

                    $page = 1;
                    $size = 10;
                    if(!empty($get['page'])){
                        if (is_numeric($get['page'])) {
                            $page = intval($get['page']);
                        }
                    }

                    if(!empty($get['size'])){
                        if (is_numeric($get['size'])) {
                            $size = intval($get['size']);
                        }
                    }

                    $query = (new \yii\db\Query())
                        ->select([
                            'content.id', 
                            'content.name', 
                            'content.picture_path AS picture', 
                            'content_fungi.other_name',
                            'content_fungi.features',
                            'content_fungi.benefit',
                            'content_fungi.season',
                            'content_fungi.ability',
                            'content_fungi.common_name',
                            'content_fungi.scientific_name',
                            'content_fungi.family_name',
                            'content_fungi.other_information',
                            'content.source_information'
                            ])
                        ->from('content')
                        ->innerJoin('content_fungi', 'content_fungi.content_id = content.id')
                        ->where(['active' => 1, 'type_id' => 3, 'content.status' => 'approved']);
                    
                    $queryCount = $query->distinct()->count();
                    
                    $query = $query->limit($size)
                        ->offset((($page - 1) * $size))
                        ->all();

                    $return['data']['page'] = $page;
                    $return['data']['size'] = $size;

                    $dataResult = array();
                    foreach ($query as $key => $value) {
                        if(!empty($value['picture'])){
                            $value['picture'] = self::Host()."/files/content-fungi/".$value['picture'];
                        }

                        $value['taxonomy'] = $this->getTaxonomyName($value['id']);

                        $value['gallery'] = $this->getGallery($value['id'], 'fungi');

                        $dataResult[] = $value;
                    }

                    $return['data']['total'] = intval($queryCount);
                    $return['data']['items'] = $dataResult;
                }else{
                    $return['message'] = 'คุณไม่มีสิทธิ์เข้าถึง | Invalid authorization header';
                    $return['errorCode'] = 120;
                }
            }else{
                $return['message'] = 'คุณไม่มีสิทธิ์เข้าถึง | Invalid authorization header';
                $return['errorCode'] = 120;
            }

        }catch(\Exception $exception){
            $return['message'] = 'เกิดข้อผิดพลาด API ไม่พร้อมใช้งาน';
            $return['errorCode'] = 200;
        }
       
        return json_encode($return);

        //return _::toJsonString($type);
    }

    public function actionImportmember(){
        $page = 1;
        $size = 10;
        $content_type = 'member';

        $request = Yii::$app->request;
        $get = $request->get();

        if(!empty($get['page'])){
            if (is_numeric($get['page'])) {
                $page = intval($get['page']);
            }
        }

        if(!empty($get['size'])){
            if (is_numeric($get['size'])) {
                $size = intval($get['size']);
            }
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://localhost:8086/?page=".$page."&size=".$size."&content_type=".$content_type);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $output = json_decode($output, true);
        curl_close($curl);


        // print '<pre>';
        // print_r($output);
        // print '</pre>';
        // exit();

        if (!empty($output['data']['items'])) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                foreach ($output['data']['items'] as $value) {
                    /*
                    //import school only
                    $schoolModel = new School();

                    if (!empty($value['school_name'])) {

                        $checkDupSchool = School::find()->select(['id'])->where(['name' => $value['school_name']])->one();
                        if (empty($checkDupSchool)) {
                            $schoolModel->name = $value['school_name'];
                            $schoolModel->address = $value['school_addr'];
                            $schoolModel->phone = $value['school_tel'];


                            //find location school
                            if (!empty($value['school_tumbon']) && !empty($value['school_district']) && !empty($value['school_province'])) {
                                $dataLocationSchool = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id',
                                    'district.province_id',
                                    'subdistrict.district_id',
                                    'subdistrict.id as subdistrict_id'
                                    ])
                                ->from('subdistrict')
                                ->leftJoin('district', 'district.id = subdistrict.district_id')
                                ->leftJoin('province', 'province.id = district.province_id')
                                ->where(['LIKE', 'subdistrict.name_th', trim($value['school_tumbon'])])
                                ->andWhere(['LIKE', 'district.name_th', trim($value['school_district'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['school_province'])])
                                ->one();
                            }

                            if (!empty($dataLocationSchool)) {
                                $schoolModel->province_id = $dataLocationSchool['province_id'];
                                $schoolModel->subdistrict_id = $dataLocationSchool['subdistrict_id'];
                                $schoolModel->district_id = $dataLocationSchool['district_id'];
                            }

                            $schoolModel->created_at = date("Y-m-d H:i:s");
                            $schoolModel->updated_at = date("Y-m-d H:i:s");
                            if (!$schoolModel->save()) {
                                print '<pre>';
                                print_r($schoolModel);
                                print '</pre>';
                                exit();
                            }
                        }
                    } */ 

                    $userModel = new Users();
                    $userModel->id = $value['id'];
                    if (!empty($value['email']) && filter_var($value['email'], FILTER_VALIDATE_EMAIL)) {
                        $userModel->email = $value['email'];
                    }else{
                        $userModel->email = $value['user']."@biogang.com";
                    }

                    $findEmailDuplicate = (new \yii\db\Query())
                        ->select([
                            'id'
                            ])
                        ->from('user')
                        ->where(['=', 'email', $userModel->email])
                        ->one();

                    //$findEmailDuplicate = Users::find()->select(['id'])->where(['email' => $userModel->email])->one();

                    if(!empty($findEmailDuplicate)){
                        $userModel->email = 'new_'.time().'_'.$userModel->email;
                    }

                    if (!empty($value['pass']) && mb_strlen($value['pass']) > 6) {
                        $userModel->new_password = $value['pass'];
                        $userModel->confirm_password = $value['pass'];
                    }else{
                        $userModel->new_password = "Biog@pass123456";
                        $userModel->confirm_password = "Biog@pass123456";
                    }

                    $userModel->username = $userModel->email;
                    $userModel->created_at = $value['date_created'];
                    $userModel->updated_at = $value['date_updated'];

                    if(empty($userModel->updated_at)){
                        $userModel->updated_at = date("Y-m-d H:i:s");
                    }
                    $userModel->auth_key = md5("Active".date("Y-m-d H:i:s"));
                    $userModel->registration_ip = $value['ipaddr'];

                    if (!empty($userModel->new_password)) {
                        $userModel->password_hash = \Yii::$app->security->generatePasswordHash($userModel->new_password);
                    }

                    //find location 
                    if(!empty($value['tumbon']) && !empty($value['district']) && !empty($value['province'])){
                        $dataLocation = (new \yii\db\Query())
                        ->select([
                            'province.region_id as region_id', 
                            'district.province_id',
                            'subdistrict.district_id', 
                            'subdistrict.id as subdistrict_id'
                            ])
                        ->from('subdistrict')
                        ->leftJoin('district' ,'district.id = subdistrict.district_id')
                        ->leftJoin('province' ,'province.id = district.province_id')
                        ->where(['LIKE', 'subdistrict.name_th', trim($value['tumbon'])])
                        ->andWhere(['LIKE', 'district.name_th', trim($value['district'])])
                        ->andWhere(['LIKE', 'province.name_th', trim($value['province'])])
                        ->one();
                    }
                    
                    if(empty($dataLocation)){
                        if(!empty($value['district_name']) && !empty($value['subdistrict_name']) && !empty($value['province_name'])){
                            $dataLocation = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id', 
                                    'district.province_id',
                                    'subdistrict.district_id', 
                                    'subdistrict.id as subdistrict_id'
                                    ])
                                ->from('subdistrict')
                                ->leftJoin('district' ,'district.id = subdistrict.district_id')
                                ->leftJoin('province' ,'province.id = district.province_id')
                                ->where(['LIKE', 'subdistrict.name_th', trim($value['subdistrict_name'])])
                                ->andWhere(['LIKE', 'district.name_th', trim($value['district_name'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['province_name'])])
                                ->one();
                        }
                    }

                    if(!empty($dataLocation['subdistrict_id'])){
                        $dataLocationZipcode = (new \yii\db\Query())
                            ->select(['zipcode.id'])
                            ->from('zipcode')
                            ->where(['=', 'subdistrict_id', $dataLocation['subdistrict_id']])
                            ->one();
                    }

                    if (empty($dataLocation)) {
                        if(!empty($value['district_name']) && !empty($value['province_name'])){
                            $dataLocation = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id', 
                                    'district.province_id',
                                    'district.id as district_id',
                                    ])
                                ->from('district')
                                ->leftJoin('province' ,'province.id = district.province_id')
                                ->where(['LIKE', 'district.name_th', trim($value['district_name'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['province_name'])])
                                ->one();

                            if(!empty($dataLocation)){
                                $dataLocation['subdistrict_id'] = '';
                            }
                        }
                    }
                    if ($userModel->save()) {
                        $uid = $userModel->id;


                        $profileModel = new Profile();
                        $profileModel->user_id = $uid;
                        $profileModel->picture = $value['profile_photo'];
                        $profileModel->display_name = $value['name'];
                        if(empty($profileModel->display_name)){
                            $profileModel->display_name = "Biogang Name";
                        }
                        $profileModel->firstname = $value['name'];
                        $profileModel->lastname = $value['lastname'];
                        if ($value['sex'] == 'm') {
                            $profileModel->gender = 'male';
                        }else if ($value['sex'] == 'f') {
                            $profileModel->gender = 'female';
                        }

                        if ($value['birthday'] != '0000-00-00') {
                            $profileModel->birthdate = $value['birthday'];
                        }

                        $profileModel->home_number = trim($value['addr']);
                        $profileModel->class = $value['school_grade'];
                        $profileModel->updated_at = $value['date_updated'];


                        if (!empty($dataLocation)) {
                            $profileModel->region_id = $dataLocation['region_id'];
                            $profileModel->province_id = $dataLocation['province_id'];
                            $profileModel->district_id = $dataLocation['district_id'];
                            $profileModel->subdistrict_id = $dataLocation['subdistrict_id'];
                            if (!empty($dataLocationZipcode)) {
                                $profileModel->zipcode_id = $dataLocationZipcode['id'];
                            }
                        }

                        if($profileModel->save()){

                            $roleId = 4;
                            if ($value['permission'] == 'admin' || $value['permission'] == 'user_admin') {
                                $roleId = 1;
                            } 

                            //UserRole
                            $userRole = array();
                            $userRole[] = array($uid, $roleId);
                            Yii::$app->db->createCommand()->batchInsert('user_role', ['user_id', 'role_id'], $userRole)->execute();  
    
                            if (!empty($value['school_name'])) {
                                /*
                                $checkDupSchool = School::find()->select(['id'])->where(['name' => $value['school_name']])->one();
                                if (empty($checkDupSchool)) {

                                    $schoolModel = new School();
                                    $schoolModel->name = $value['school_name'];
                                    $schoolModel->address = $value['school_addr'];
                                    $schoolModel->phone = $value['school_tel'];


                                    //find location school
                                    if(!empty($value['school_tumbon']) && !empty($value['school_district']) && !empty($value['school_province'])){
                                        $dataLocationSchool = (new \yii\db\Query())
                                        ->select([
                                            'province.region_id as region_id', 
                                            'district.province_id',
                                            'subdistrict.district_id', 
                                            'subdistrict.id as subdistrict_id'
                                            ])
                                        ->from('subdistrict')
                                        ->leftJoin('district' ,'district.id = subdistrict.district_id')
                                        ->leftJoin('province' ,'province.id = district.province_id')
                                        ->where(['LIKE', 'subdistrict.name_th', trim($value['school_tumbon'])])
                                        ->andWhere(['LIKE', 'district.name_th', trim($value['school_district'])])
                                        ->andWhere(['LIKE', 'province.name_th', trim($value['school_province'])])
                                        ->one();
                                    }

                                    if (!empty($dataLocationSchool)) {
                                        $schoolModel->province_id = $dataLocationSchool['province_id'];
                                        $schoolModel->subdistrict_id = $dataLocationSchool['subdistrict_id'];
                                        $schoolModel->district_id = $dataLocationSchool['district_id'];
                                    }
        
                                    $schoolModel->created_at = date("Y-m-d H:i:s");
                                    $schoolModel->updated_at = date("Y-m-d H:i:s");
                                    if ($schoolModel->save()) {
                                        $userSchool = new UserSchool();
                                        $userSchool->user_id = $uid;
                                        $userSchool->school_id = $schoolModel->id;
                                        $userSchool->created_at = date("Y-m-d H:i:s");
                                        $userSchool->updated_at = date("Y-m-d H:i:s");
                                        $userSchool->save();
                                    }else{
                                        print '<pre>';
                                        print_r($schoolModel);
                                        print '</pre>';
                                        exit();
                                    }
                                } else {
                                    $userSchool = new UserSchool();
                                    $userSchool->user_id = $uid;
                                    $userSchool->school_id = $checkDupSchool->id;
                                    $userSchool->created_at = date("Y-m-d H:i:s");
                                    $userSchool->updated_at = date("Y-m-d H:i:s");
                                    $userSchool->save();
                                } */

                                $checkDupSchool = (new \yii\db\Query())
                                                        ->select([
                                                            'school.id'
                                                            ])
                                                        ->from('school')
                                                        ->where(['=', 'name', $value['school_name']])
                                                        ->one();

                                if (!empty($checkDupSchool['id'])) {
                                    //UserSchool
                                    $UserSchool = array();
                                    $UserSchool[] = array($uid, $checkDupSchool['id'], date("Y-m-d H:i:s"), date("Y-m-d H:i:s"));
                                    Yii::$app->db->createCommand()->batchInsert('user_school', ['user_id', 'school_id', 'created_at', 'updated_at'], $UserSchool)->execute();

                                    // $userSchool = new UserSchool();
                                    // $userSchool->user_id = $uid;
                                    // $userSchool->school_id = $checkDupSchool['id'];
                                    // $userSchool->created_at = date("Y-m-d H:i:s");
                                    // $userSchool->updated_at = date("Y-m-d H:i:s");
                                    // $userSchool->save();
                                }
                            }


                            
                        }else{
                            print '<pre>';
                            print_r($profileModel);
                            print '</pre>';
                            exit();
                        }
                    }else{
                        print '<pre>';
                        print_r($userModel);
                        print '</pre>';
                        exit();
                    } 

                   
                }


                //transection commit
                $transaction->commit();

                print '<pre>';
                print_r("จบ 1 page = ".$page);
                print '</pre>';
                exit();
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }else{
            print '<pre>';
            print_r("ไม่มี list ข้อมูล");
            print '</pre>';
            exit();
        }
    }

    public function actionImportecotourism(){
        $page = 1;
        $size = 10;
        $content_type = 'ecotourism';

        $request = Yii::$app->request;
        $get = $request->get();

        if(!empty($get['page'])){
            if (is_numeric($get['page'])) {
                $page = intval($get['page']);
            }
        }

        if(!empty($get['size'])){
            if (is_numeric($get['size'])) {
                $size = intval($get['size']);
            }
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://localhost:8086/?page=".$page."&size=".$size."&content_type=".$content_type);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $output = json_decode($output, true);
        curl_close($curl);


        // print '<pre>';
        // print_r($output);
        // print '</pre>';
        // exit();

        if (!empty($output['data']['items'])) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                foreach ($output['data']['items'] as $value) {
                    $newContent = new Content();
                    $newContent->type_id = 5;
                    $newContent->name = trim($value['name']);
             
                    if(!empty($value['photo1'])){
                        $newContent->picture_path = $value['photo1'];
                    }else{
                        $newContent->picture_path = '';
                    }
                    $newContent->description = $value['message1'];
                    $newContent->other_information = $value['message3'];
                    $newContent->source_information = $value['source2'];
                    $newContent->photo_credit = '';
                    $newContent->latitude = $value['lat'];
                    $newContent->longitude = $value['lng'];

                    //find location 
                    if(!empty($value['tumbon']) && !empty($value['district']) && !empty($value['province'])){
                        $dataLocation = (new \yii\db\Query())
                        ->select([
                            'province.region_id as region_id', 
                            'district.province_id',
                            'subdistrict.district_id', 
                            'subdistrict.id as subdistrict_id'
                            ])
                        ->from('subdistrict')
                        ->leftJoin('district' ,'district.id = subdistrict.district_id')
                        ->leftJoin('province' ,'province.id = district.province_id')
                        ->where(['LIKE', 'subdistrict.name_th', trim($value['tumbon'])])
                        ->andWhere(['LIKE', 'district.name_th', trim($value['district'])])
                        ->andWhere(['LIKE', 'province.name_th', trim($value['province'])])
                        ->one();
                    }
                    
                    if(empty($dataLocation)){
                        if(!empty($value['district_name']) && !empty($value['subdistrict_name']) && !empty($value['province_name'])){
                            $dataLocation = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id', 
                                    'district.province_id',
                                    'subdistrict.district_id', 
                                    'subdistrict.id as subdistrict_id'
                                    ])
                                ->from('subdistrict')
                                ->leftJoin('district' ,'district.id = subdistrict.district_id')
                                ->leftJoin('province' ,'province.id = district.province_id')
                                ->where(['LIKE', 'subdistrict.name_th', trim($value['subdistrict_name'])])
                                ->andWhere(['LIKE', 'district.name_th', trim($value['district_name'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['province_name'])])
                                ->one();
                        }
                    }

                    if(!empty($dataLocation['subdistrict_id'])){
                        $dataLocationZipcode = (new \yii\db\Query())
                            ->select(['zipcode.id'])
                            ->from('zipcode')
                            ->where(['=', 'subdistrict_id', $dataLocation['subdistrict_id']])
                            ->one();
                    }

                    if (empty($dataLocation)) {
                        if(!empty($value['district_name']) && !empty($value['province_name'])){
                            $dataLocation = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id', 
                                    'district.province_id',
                                    'district.id as district_id',
                                    ])
                                ->from('district')
                                ->leftJoin('province' ,'province.id = district.province_id')
                                ->where(['LIKE', 'district.name_th', trim($value['district_name'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['province_name'])])
                                ->one();

                            if(!empty($dataLocation)){
                                $dataLocation['subdistrict_id'] = '';
                            }
                        }
                    }


                    

                    if (!empty($dataLocation)) {
                        $newContent->region_id = $dataLocation['region_id'];
                        $newContent->province_id = $dataLocation['province_id'];
                        $newContent->district_id = $dataLocation['district_id'];
                        $newContent->subdistrict_id = $dataLocation['subdistrict_id'];
                        if (!empty($dataLocationZipcode)) {
                            $newContent->zipcode_id = $dataLocationZipcode['id'];
                        }
                    }

                    $newContent->approved_by_user_id = 1;
                    $newContent->created_by_user_id = $value['member_id'];
                    $newContent->updated_by_user_id = $value['member_id'];
                    $newContent->note = '';
                    if ($value['status'] == 'active') {
                        $newContent->active = 1;
                    }else{
                        $newContent->active = 0;
                    }
                    $newContent->status = 'approved';
                    $newContent->created_at = $value['date_created'];
                    $newContent->updated_at = $value['last_updated'];

                    if ($newContent->save()) {

                        //update root id
                        Yii::$app->db->createCommand()->update('content', ['content_root_id' => $newContent->id], 'content.id = '. $newContent->id)->execute();

                        //content
                        $neContentEcotourism = new ContentEcotourism();
                        $neContentEcotourism->content_id = $newContent->id;
                        $neContentEcotourism->address = $value['addr'];
                        $neContentEcotourism->phone = trim($value['tel']);
                        $neContentEcotourism->contact = trim($value['source']);
                        $neContentEcotourism->travel_information = $value['message2'];
                        $neContentEcotourism->created_at = $value['date_created'];
                        $neContentEcotourism->updated_at = $value['last_updated'];
                        if(!$neContentEcotourism->save()){
                            print '<pre>';
                            print_r($neContentEcotourism);
                            print '</pre>';
                            exit();
                        }

                        //taxonomy
                        if (!empty($value['tag']) && $value['tag'] != 'null') {

                            $modelTax = new ContentTaxonomy();
                            $modelTax->content_id = $newContent->id;

                            $taxId = $this->getTaxonomyInputData($value['tag']);
                            if (!empty($taxId)) {
                                $modelTax->taxonomy_id = $taxId;
                            }

                            $duplicate = (new \yii\db\Query())
                                ->select(['content_id','taxonomy_id'])
                                ->from('content_taxonomy')
                                ->where(['content_id' => $modelTax->content_id])
                                ->andWhere(['taxonomy_id' => $modelTax->taxonomy_id])
                                ->all();
                            if (empty($duplicate)) {
                                $modelTax->created_at = date('Y-m-d H:i:s');
                                $modelTax->save();
                            }
                            
                        }

                        
                        //gallery
                        $gallery = array();
                        if(!empty($value['photo1'])){
                            $name = $value['photo1_name'];
                            if(empty($name)){
                                $name = $value['photo1'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo1'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo2'])){
                            $name = $value['photo2_name'];
                            if(empty($name)){
                                $name = $value['photo2'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo2'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo3'])){
                            $name = $value['photo3_name'];
                            if(empty($name)){
                                $name = $value['photo3'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo3'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo4'])){
                            $name = $value['photo4_name'];
                            if(empty($name)){
                                $name = $value['photo4'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo4'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo5'])){
                            $name = $value['photo5_name'];
                            if(empty($name)){
                                $name = $value['photo5'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo5'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo6'])){
                            $name = $value['photo6_name'];
                            if(empty($name)){
                                $name = $value['photo6'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo6'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if (!empty($gallery)) {
                            Yii::$app->db->createCommand()->batchInsert('picture', ['content_id', 'name', 'path', 'created_by_user_id', 'updated_by_user_id', 'created_at', 'updated_at'], $gallery)->execute();
                        }

                        if (!empty($value['comment'])) {
                            $comment = array();
                            foreach($value['comment'] as $commentDetail){
                                if (!empty($commentDetail['member_id'])) {
                                    $comment[] = array($commentDetail['member_id'], $newContent->id, $commentDetail['message'], $commentDetail['date_created']);
                                }
                            }
                            if (!empty($comment)) {
                                Yii::$app->db->createCommand()->batchInsert('comment', ['user_id', 'content_root_id', 'message', 'created_at'], $comment)->execute();
                            }
                        }

                        //statistic
                        $statistic = array();
                        $statistic[] = array($newContent->id, $value['view'], 0, $value['last_updated']);
                        Yii::$app->db->createCommand()->batchInsert('content_statistics', ['content_root_id', 'pageview', 'like_count', 'updated_at'], $statistic)->execute();

                    }
                    else{
                        print '<pre>';
                        print_r($newContent);
                        print '</pre>';
                        exit();
                    }
                }


                //transection commit
                $transaction->commit();

                print '<pre>';
                print_r("จบ 1 page = ".$page);
                print '</pre>';
                exit();
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }else{
            print '<pre>';
            print_r("ไม่มี list ข้อมูล");
            print '</pre>';
            exit();
        }
    }

    public function actionImportexpert(){
        $page = 1;
        $size = 10;
        $content_type = 'expert';

        $request = Yii::$app->request;
        $get = $request->get();

        if(!empty($get['page'])){
            if (is_numeric($get['page'])) {
                $page = intval($get['page']);
            }
        }

        if(!empty($get['size'])){
            if (is_numeric($get['size'])) {
                $size = intval($get['size']);
            }
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://localhost:8086/?page=".$page."&size=".$size."&content_type=".$content_type);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $output = json_decode($output, true);
        curl_close($curl);


        // print '<pre>';
        // print_r($output);
        // print '</pre>';
        // exit();

        if (!empty($output['data']['items'])) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                foreach ($output['data']['items'] as $value) {
                    $newContent = new Content();
                    $newContent->type_id = 4;
                    $newContent->name = trim($value['name'])." ".trim($value['lastname']);
             
                    if(!empty($value['photo1'])){
                        $newContent->picture_path = $value['photo1'];
                    }else{
                        $newContent->picture_path = '';
                    }
                    $newContent->description = $value['message1'];
                    $newContent->other_information = $value['message3'];
                    $newContent->source_information = $value['source2'];
                    $newContent->photo_credit = '';
                    $newContent->latitude = $value['lat'];
                    $newContent->longitude = $value['lng'];

                    //find location 
                    if(!empty($value['tumbon']) && !empty($value['district']) && !empty($value['province'])){
                        $dataLocation = (new \yii\db\Query())
                        ->select([
                            'province.region_id as region_id', 
                            'district.province_id',
                            'subdistrict.district_id', 
                            'subdistrict.id as subdistrict_id'
                            ])
                        ->from('subdistrict')
                        ->leftJoin('district' ,'district.id = subdistrict.district_id')
                        ->leftJoin('province' ,'province.id = district.province_id')
                        ->where(['LIKE', 'subdistrict.name_th', trim($value['tumbon'])])
                        ->andWhere(['LIKE', 'district.name_th', trim($value['district'])])
                        ->andWhere(['LIKE', 'province.name_th', trim($value['province'])])
                        ->one();
                    }
                    
                    if(empty($dataLocation)){
                        if(!empty($value['district_name']) && !empty($value['subdistrict_name']) && !empty($value['province_name'])){
                            $dataLocation = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id', 
                                    'district.province_id',
                                    'subdistrict.district_id', 
                                    'subdistrict.id as subdistrict_id'
                                    ])
                                ->from('subdistrict')
                                ->leftJoin('district' ,'district.id = subdistrict.district_id')
                                ->leftJoin('province' ,'province.id = district.province_id')
                                ->where(['LIKE', 'subdistrict.name_th', trim($value['subdistrict_name'])])
                                ->andWhere(['LIKE', 'district.name_th', trim($value['district_name'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['province_name'])])
                                ->one();
                        }
                    }

                    if(!empty($dataLocation['subdistrict_id'])){
                        $dataLocationZipcode = (new \yii\db\Query())
                            ->select(['zipcode.id'])
                            ->from('zipcode')
                            ->where(['=', 'subdistrict_id', $dataLocation['subdistrict_id']])
                            ->one();
                    }

                    if (empty($dataLocation)) {
                        if(!empty($value['district_name']) && !empty($value['province_name'])){
                            $dataLocation = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id', 
                                    'district.province_id',
                                    'district.id as district_id',
                                    ])
                                ->from('district')
                                ->leftJoin('province' ,'province.id = district.province_id')
                                ->where(['LIKE', 'district.name_th', trim($value['district_name'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['province_name'])])
                                ->one();

                            if(!empty($dataLocation)){
                                $dataLocation['subdistrict_id'] = '';
                            }
                        }
                    }


                    

                    if (!empty($dataLocation)) {
                        $newContent->region_id = $dataLocation['region_id'];
                        $newContent->province_id = $dataLocation['province_id'];
                        $newContent->district_id = $dataLocation['district_id'];
                        $newContent->subdistrict_id = $dataLocation['subdistrict_id'];
                        if (!empty($dataLocationZipcode)) {
                            $newContent->zipcode_id = $dataLocationZipcode['id'];
                        }
                    }

                    $newContent->approved_by_user_id = 1;
                    $newContent->created_by_user_id = $value['member_id'];
                    $newContent->updated_by_user_id = $value['member_id'];
                    $newContent->note = '';
                    if ($value['status'] == 'active') {
                        $newContent->active = 1;
                    }else{
                        $newContent->active = 0;
                    }
                    $newContent->status = 'approved';
                    $newContent->created_at = $value['date_created'];
                    $newContent->updated_at = $value['last_updated'];

                    if ($newContent->save()) {

                        //update root id
                        Yii::$app->db->createCommand()->update('content', ['content_root_id' => $newContent->id], 'content.id = '. $newContent->id)->execute();

                        //content
                        $neContentExpert = new ContentExpert();
                        $neContentExpert->content_id = $newContent->id;
                        $neContentExpert->expert_category_id = $value['type'];
                        $neContentExpert->expert_firstname = trim($value['name']);
                        $neContentExpert->expert_lastname = trim($value['lastname']);
                        if (trim($value['birthday']) != '0000-00-00') {
                            $neContentExpert->expert_birthdate = $value['birthday'];
                        }
                        $neContentExpert->expert_expertise =  $value['subject'];
                        $neContentExpert->expert_occupation = trim($value['occupy']);
                        $neContentExpert->expert_card_id = trim($value['idcard']);
                        $neContentExpert->phone = trim($value['tel']);
                        $neContentExpert->address = trim($value['addr']);
                        $neContentExpert->created_at = $value['date_created'];
                        $neContentExpert->updated_at = $value['last_updated'];
                        if(!$neContentExpert->save()){
                            print '<pre>';
                            print_r($neContentExpert);
                            print '</pre>';
                            exit();
                        }

                        //taxonomy
                        if (!empty($value['tag']) && $value['tag'] != 'null') {

                            $modelTax = new ContentTaxonomy();
                            $modelTax->content_id = $newContent->id;

                            $taxId = $this->getTaxonomyInputData($value['tag']);
                            if (!empty($taxId)) {
                                $modelTax->taxonomy_id = $taxId;
                            }

                            $duplicate = (new \yii\db\Query())
                                ->select(['content_id','taxonomy_id'])
                                ->from('content_taxonomy')
                                ->where(['content_id' => $modelTax->content_id])
                                ->andWhere(['taxonomy_id' => $modelTax->taxonomy_id])
                                ->all();
                            if (empty($duplicate)) {
                                $modelTax->created_at = date('Y-m-d H:i:s');
                                $modelTax->save();
                            }
                            
                        }

                        
                        //gallery
                        $gallery = array();
                        if(!empty($value['photo1'])){
                            $name = $value['photo1_name'];
                            if(empty($name)){
                                $name = $value['photo1'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo1'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo2'])){
                            $name = $value['photo2_name'];
                            if(empty($name)){
                                $name = $value['photo2'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo2'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo3'])){
                            $name = $value['photo3_name'];
                            if(empty($name)){
                                $name = $value['photo3'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo3'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo4'])){
                            $name = $value['photo4_name'];
                            if(empty($name)){
                                $name = $value['photo4'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo4'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo5'])){
                            $name = $value['photo5_name'];
                            if(empty($name)){
                                $name = $value['photo5'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo5'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo6'])){
                            $name = $value['photo6_name'];
                            if(empty($name)){
                                $name = $value['photo6'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo6'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if (!empty($gallery)) {
                            Yii::$app->db->createCommand()->batchInsert('picture', ['content_id', 'name', 'path', 'created_by_user_id', 'updated_by_user_id', 'created_at', 'updated_at'], $gallery)->execute();
                        }

                        if (!empty($value['comment'])) {
                            $comment = array();
                            foreach($value['comment'] as $commentDetail){
                                if (!empty($commentDetail['member_id'])) {
                                    $comment[] = array($commentDetail['member_id'], $newContent->id, $commentDetail['message'], $commentDetail['date_created']);
                                }
                            }
                            if (!empty($comment)) {
                                Yii::$app->db->createCommand()->batchInsert('comment', ['user_id', 'content_root_id', 'message', 'created_at'], $comment)->execute();
                            }
                        }

                        //statistic
                        $statistic = array();
                        $statistic[] = array($newContent->id, $value['view'], 0, $value['last_updated']);
                        Yii::$app->db->createCommand()->batchInsert('content_statistics', ['content_root_id', 'pageview', 'like_count', 'updated_at'], $statistic)->execute();

                    }
                    else{
                        print '<pre>';
                        print_r($newContent);
                        print '</pre>';
                        exit();
                    }
                }


                //transection commit
                $transaction->commit();

                print '<pre>';
                print_r("จบ 1 page = ".$page);
                print '</pre>';
                exit();
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }else{
            print '<pre>';
            print_r("ไม่มี list ข้อมูล");
            print '</pre>';
            exit();
        }
    }

    public function actionImportanimal(){

        $page = 1;
        $size = 10;
        $content_type = 'animal';

        $request = Yii::$app->request;
        $get = $request->get();

        if(!empty($get['page'])){
            if (is_numeric($get['page'])) {
                $page = intval($get['page']);
            }
        }

        if(!empty($get['size'])){
            if (is_numeric($get['size'])) {
                $size = intval($get['size']);
            }
        }

        if(!empty($get['content_type'])){
            $content_type = $get['content_type'];
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://localhost:8086/?page=".$page."&size=".$size."&content_type=".$content_type);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $output = json_decode($output, true);
        curl_close($curl);


        // print '<pre>';
        // print_r($output);
        // print '</pre>';
        // exit();

        if (!empty($output['data']['items'])) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                foreach ($output['data']['items'] as $value) {
                    $newContent = new Content();
                    $newContent->id = $value['id'];
                    $newContent->content_root_id = $value['id'];
                    $newContent->type_id = 2;
                    $newContent->name = $value['name'];
             
                    if(!empty($value['photo1'])){
                        $newContent->picture_path = $value['photo1'];
                    }else{
                        $newContent->picture_path = '';
                    }
                    $newContent->description = $value['message1'];
                    $newContent->other_information = $value['message3'];
                    $newContent->source_information = $value['source2'];
                    $newContent->photo_credit = '';
                    $newContent->latitude = $value['lat'];
                    $newContent->longitude = $value['lng'];

                    //find location 
                    if(!empty($value['tumbon']) && !empty($value['district']) && !empty($value['province'])){
                        $dataLocation = (new \yii\db\Query())
                        ->select([
                            'province.region_id as region_id', 
                            'district.province_id',
                            'subdistrict.district_id', 
                            'subdistrict.id as subdistrict_id'
                            ])
                        ->from('subdistrict')
                        ->leftJoin('district' ,'district.id = subdistrict.district_id')
                        ->leftJoin('province' ,'province.id = district.province_id')
                        ->where(['LIKE', 'subdistrict.name_th', trim($value['tumbon'])])
                        ->andWhere(['LIKE', 'district.name_th', trim($value['district'])])
                        ->andWhere(['LIKE', 'province.name_th', trim($value['province'])])
                        ->one();
                    }
                    
                    if(empty($dataLocation)){
                        if(!empty($value['district_name']) && !empty($value['subdistrict_name']) && !empty($value['province_name'])){
                            $dataLocation = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id', 
                                    'district.province_id',
                                    'subdistrict.district_id', 
                                    'subdistrict.id as subdistrict_id'
                                    ])
                                ->from('subdistrict')
                                ->leftJoin('district' ,'district.id = subdistrict.district_id')
                                ->leftJoin('province' ,'province.id = district.province_id')
                                ->where(['LIKE', 'subdistrict.name_th', trim($value['subdistrict_name'])])
                                ->andWhere(['LIKE', 'district.name_th', trim($value['district_name'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['province_name'])])
                                ->one();
                        }
                    }

                    if(!empty($dataLocation['subdistrict_id'])){
                        $dataLocationZipcode = (new \yii\db\Query())
                            ->select(['zipcode.id'])
                            ->from('zipcode')
                            ->where(['=', 'subdistrict_id', $dataLocation['subdistrict_id']])
                            ->one();
                    }

                    if (empty($dataLocation)) {
                        if(!empty($value['district_name']) && !empty($value['province_name'])){
                            $dataLocation = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id', 
                                    'district.province_id',
                                    'district.id as district_id',
                                    ])
                                ->from('district')
                                ->leftJoin('province' ,'province.id = district.province_id')
                                ->where(['LIKE', 'district.name_th', trim($value['district_name'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['province_name'])])
                                ->one();

                            if(!empty($dataLocation)){
                                $dataLocation['subdistrict_id'] = '';
                            }
                        }
                    }


                    

                    if (!empty($dataLocation)) {
                        $newContent->region_id = $dataLocation['region_id'];
                        $newContent->province_id = $dataLocation['province_id'];
                        $newContent->district_id = $dataLocation['district_id'];
                        $newContent->subdistrict_id = $dataLocation['subdistrict_id'];
                        if (!empty($dataLocationZipcode)) {
                            $newContent->zipcode_id = $dataLocationZipcode['id'];
                        }
                    }

                    $newContent->approved_by_user_id = 1;
                    $newContent->created_by_user_id = $value['member_id'];
                    $newContent->updated_by_user_id = $value['member_id'];
                    $newContent->note = '';
                    $newContent->active = 1;
                    $newContent->status = 'approved';
                    $newContent->created_at = $value['date_created'];
                    $newContent->updated_at = $value['last_updated'];

                    if ($newContent->save()) {
                        //content
                        $newContentAnimal = new ContentAnimal();
                        $newContentAnimal->content_id = $newContent->id;
                        $newContentAnimal->other_name = $value['other_name'];
                        $newContentAnimal->features = $value['message1'];
                        $newContentAnimal->benefit = $value['message2'];
                        $newContentAnimal->found_source = $value['source'];
                        $newContentAnimal->other_information =  $value['message3'];
                        $newContentAnimal->season = $value['season'];
                        $newContentAnimal->ability = $value['potential'];
                        $newContentAnimal->common_name = $value['common_name'];
                        $newContentAnimal->scientific_name = $value['scientific_name'];
                        $newContentAnimal->family_name = $value['family_name'];
                        $newContentAnimal->created_at = $value['date_created'];
                        $newContentAnimal->updated_at = $value['last_updated'];
                        if(!$newContentAnimal->save()){
                            print '<pre>';
                            print_r($newContentAnimal);
                            print '</pre>';
                            exit();
                        }

                        //taxonomy
                        if (!empty($value['tag'])) {

                            $modelTax = new ContentTaxonomy();
                            $modelTax->content_id = $newContent->id;

                            $taxId = $this->getTaxonomyInputData($value['tag']);
                            if (!empty($taxId)) {
                                $modelTax->taxonomy_id = $taxId;
                            }

                            $duplicate = (new \yii\db\Query())
                                ->select(['content_id','taxonomy_id'])
                                ->from('content_taxonomy')
                                ->where(['content_id' => $modelTax->content_id])
                                ->andWhere(['taxonomy_id' => $modelTax->taxonomy_id])
                                ->all();
                            if (empty($duplicate)) {
                                $modelTax->created_at = date('Y-m-d H:i:s');
                                $modelTax->save();
                            }
                            
                        }

                        
                        //gallery
                        $gallery = array();
                        if(!empty($value['photo1'])){
                            $name = $value['photo1_name'];
                            if(empty($name)){
                                $name = $value['photo1'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo1'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo2'])){
                            $name = $value['photo2_name'];
                            if(empty($name)){
                                $name = $value['photo2'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo2'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo3'])){
                            $name = $value['photo3_name'];
                            if(empty($name)){
                                $name = $value['photo3'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo3'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo4'])){
                            $name = $value['photo4_name'];
                            if(empty($name)){
                                $name = $value['photo4'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo4'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo5'])){
                            $name = $value['photo5_name'];
                            if(empty($name)){
                                $name = $value['photo5'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo5'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo6'])){
                            $name = $value['photo6_name'];
                            if(empty($name)){
                                $name = $value['photo6'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo6'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if (!empty($gallery)) {
                            Yii::$app->db->createCommand()->batchInsert('picture', ['content_id', 'name', 'path', 'created_by_user_id', 'updated_by_user_id', 'created_at', 'updated_at'], $gallery)->execute();
                        }

                        if (!empty($value['comment'])) {
                            $comment = array();
                            foreach($value['comment'] as $commentDetail){
                                if (!empty($commentDetail['member_id'])) {
                                    $comment[] = array($commentDetail['member_id'], $newContent->id, $commentDetail['message'], $commentDetail['date_created']);
                                }
                            }
                            if (!empty($comment)) {
                                Yii::$app->db->createCommand()->batchInsert('comment', ['user_id', 'content_root_id', 'message', 'created_at'], $comment)->execute();
                            }
                        }

                        //statistic
                        $statistic = array();
                        $statistic[] = array($newContent->id, $value['view'], 0, $value['last_updated']);
                        Yii::$app->db->createCommand()->batchInsert('content_statistics', ['content_root_id', 'pageview', 'like_count', 'updated_at'], $statistic)->execute();

                    }
                    else{
                        print '<pre>';
                        print_r($newContent);
                        print '</pre>';
                        exit();
                    }
                }


                //transection commit
                $transaction->commit();

                print '<pre>';
                print_r("จบ 1 page = ".$page);
                print '</pre>';
                exit();
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }else{
            print '<pre>';
            print_r("ไม่มี list ข้อมูล");
            print '</pre>';
            exit();
        }

    }

    public function actionImport(){

        $page = 1;
        $size = 10;
        $content_type = 'plant';

        $request = Yii::$app->request;
        $get = $request->get();

        if(!empty($get['page'])){
            if (is_numeric($get['page'])) {
                $page = intval($get['page']);
            }
        }

        if(!empty($get['size'])){
            if (is_numeric($get['size'])) {
                $size = intval($get['size']);
            }
        }

        if(!empty($get['content_type'])){
            $content_type = $get['content_type'];
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://localhost:8086/?page=".$page."&size=".$size."&content_type=".$content_type);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $output = json_decode($output, true);
        curl_close($curl);


        // print '<pre>';
        // print_r($output);
        // print '</pre>';
        // exit();

        if (!empty($output['data']['items'])) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                foreach ($output['data']['items'] as $value) {
                    $newContent = new Content();
                    $newContent->id = $value['id'];
                    $newContent->content_root_id = $value['id'];
                    $newContent->type_id = 1;
                    $newContent->name = $value['name'];
             
                    if(!empty($value['photo1'])){
                        $newContent->picture_path = $value['photo1'];
                    }else{
                        $newContent->picture_path = '';
                    }
                    $newContent->description = $value['message1'];
                    $newContent->other_information = $value['message3'];
                    $newContent->source_information = $value['source2'];
                    $newContent->photo_credit = '';
                    $newContent->latitude = $value['lat'];
                    $newContent->longitude = $value['lng'];

                    //find location 
                    if(!empty($value['tumbon']) && !empty($value['district']) && !empty($value['province'])){
                        $dataLocation = (new \yii\db\Query())
                        ->select([
                            'province.region_id as region_id', 
                            'district.province_id',
                            'subdistrict.district_id', 
                            'subdistrict.id as subdistrict_id'
                            ])
                        ->from('subdistrict')
                        ->leftJoin('district' ,'district.id = subdistrict.district_id')
                        ->leftJoin('province' ,'province.id = district.province_id')
                        ->where(['LIKE', 'subdistrict.name_th', trim($value['tumbon'])])
                        ->andWhere(['LIKE', 'district.name_th', trim($value['district'])])
                        ->andWhere(['LIKE', 'province.name_th', trim($value['province'])])
                        ->one();
                    }
                    
                    if(empty($dataLocation)){
                        if(!empty($value['district_name']) && !empty($value['subdistrict_name']) && !empty($value['province_name'])){
                            $dataLocation = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id', 
                                    'district.province_id',
                                    'subdistrict.district_id', 
                                    'subdistrict.id as subdistrict_id'
                                    ])
                                ->from('subdistrict')
                                ->leftJoin('district' ,'district.id = subdistrict.district_id')
                                ->leftJoin('province' ,'province.id = district.province_id')
                                ->where(['LIKE', 'subdistrict.name_th', trim($value['subdistrict_name'])])
                                ->andWhere(['LIKE', 'district.name_th', trim($value['district_name'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['province_name'])])
                                ->one();
                        }
                    }

                    if(!empty($dataLocation['subdistrict_id'])){
                        $dataLocationZipcode = (new \yii\db\Query())
                            ->select(['zipcode.id'])
                            ->from('zipcode')
                            ->where(['=', 'subdistrict_id', $dataLocation['subdistrict_id']])
                            ->one();
                    }

                    if (empty($dataLocation)) {
                        if(!empty($value['district_name']) && !empty($value['province_name'])){
                            $dataLocation = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id', 
                                    'district.province_id',
                                    'district.id as district_id',
                                    ])
                                ->from('district')
                                ->leftJoin('province' ,'province.id = district.province_id')
                                ->where(['LIKE', 'district.name_th', trim($value['district_name'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['province_name'])])
                                ->one();

                            if(!empty($dataLocation)){
                                $dataLocation['subdistrict_id'] = '';
                            }
                        }
                    }


                    

                    if (!empty($dataLocation)) {
                        $newContent->region_id = $dataLocation['region_id'];
                        $newContent->province_id = $dataLocation['province_id'];
                        $newContent->district_id = $dataLocation['district_id'];
                        $newContent->subdistrict_id = $dataLocation['subdistrict_id'];
                        if (!empty($dataLocationZipcode)) {
                            $newContent->zipcode_id = $dataLocationZipcode['id'];
                        }
                    }

                    $newContent->approved_by_user_id = 1;
                    $newContent->created_by_user_id = $value['member_id'];
                    $newContent->updated_by_user_id = $value['member_id'];
                    $newContent->note = '';
                    $newContent->active = 1;
                    $newContent->status = 'approved';
                    $newContent->created_at = $value['date_created'];
                    $newContent->updated_at = $value['last_updated'];

                    if ($newContent->save()) {
                        //content
                        $newContentPlant = new ContentPlant();
                        $newContentPlant->content_id = $newContent->id;
                        $newContentPlant->other_name = $value['other_name'];
                        $newContentPlant->features = $value['message1'];
                        $newContentPlant->benefit = $value['message2'];
                        $newContentPlant->found_source = $value['source'];
                        $newContentPlant->other_information =  $value['message3'];
                        $newContentPlant->season = $value['season'];
                        $newContentPlant->ability = $value['potential'];
                        $newContentPlant->common_name = $value['common_name'];
                        $newContentPlant->scientific_name = $value['scientific_name'];
                        $newContentPlant->family_name = $value['family_name'];
                        $newContentPlant->created_at = $value['date_created'];
                        $newContentPlant->updated_at = $value['last_updated'];
                        $newContentPlant->save();

                        if(!$newContentPlant->save()){
                            print '<pre>';
                            print_r($newContentPlant);
                            print '</pre>';
                            exit();
                        }

                        //taxonomy
                        if (!empty($value['tag'])) {

                            $modelTax = new ContentTaxonomy();
                            $modelTax->content_id = $newContent->id;

                            $taxId = $this->getTaxonomyInputData($value['tag']);
                            if (!empty($taxId)) {
                                $modelTax->taxonomy_id = $taxId;
                            }

                            $duplicate = (new \yii\db\Query())
                                ->select(['content_id','taxonomy_id'])
                                ->from('content_taxonomy')
                                ->where(['content_id' => $modelTax->content_id])
                                ->andWhere(['taxonomy_id' => $modelTax->taxonomy_id])
                                ->all();
                            if (empty($duplicate)) {
                                $modelTax->created_at = date('Y-m-d H:i:s');
                                $modelTax->save();
                            }
                            
                        }

                        
                        //gallery
                        $gallery = array();
                        if(!empty($value['photo1'])){
                            $name = $value['photo1_name'];
                            if(empty($name)){
                                $name = $value['photo1'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo1'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo2'])){
                            $name = $value['photo2_name'];
                            if(empty($name)){
                                $name = $value['photo2'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo2'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo3'])){
                            $name = $value['photo3_name'];
                            if(empty($name)){
                                $name = $value['photo3'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo3'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo4'])){
                            $name = $value['photo4_name'];
                            if(empty($name)){
                                $name = $value['photo4'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo4'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo5'])){
                            $name = $value['photo5_name'];
                            if(empty($name)){
                                $name = $value['photo5'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo5'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo6'])){
                            $name = $value['photo6_name'];
                            if(empty($name)){
                                $name = $value['photo6'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo6'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if (!empty($gallery)) {
                            Yii::$app->db->createCommand()->batchInsert('picture', ['content_id', 'name', 'path', 'created_by_user_id', 'updated_by_user_id', 'created_at', 'updated_at'], $gallery)->execute();
                        }

                        if (!empty($value['comment'])) {
                            $comment = array();
                            foreach($value['comment'] as $commentDetail){
                                if (!empty($commentDetail['member_id'])) {
                                    $comment[] = array($commentDetail['member_id'], $newContent->id, $commentDetail['message'], $commentDetail['date_created']);
                                }
                            }
                            if (!empty($comment)) {
                                Yii::$app->db->createCommand()->batchInsert('comment', ['user_id', 'content_root_id', 'message', 'created_at'], $comment)->execute();
                            }
                        }

                        //statistic
                        $statistic = array();
                        $statistic[] = array($newContent->id, $value['view'], 0, $value['last_updated']);
                        Yii::$app->db->createCommand()->batchInsert('content_statistics', ['content_root_id', 'pageview', 'like_count', 'updated_at'], $statistic)->execute();


                        //main picture
                        /*if (!empty($value['image'])) {
                            $url = $value['image'];

                            $imageInfo = pathinfo($url);

                            if (!empty($imageInfo['basename'])) {
                                $save_name = $imageInfo['basename'];

                                $save_directory = realpath(dirname(__FILE__) . '/../../../') . '/frontend/web/files/content-plant/';
                                $localPathThumbnail = realpath(dirname(__FILE__) . '/../../../') . '/frontend/web/files/content-plant/thumbnail/';

                                if (!file_exists($save_directory)) {
                                    mkdir($save_directory, 0777, true);
                                }

                                if (is_writable($save_directory)) {
                                    file_put_contents($save_directory . $save_name, file_get_contents($url));


                                    if (file_exists($save_directory . $save_name)) {
                                        Image::getImagine()
                                                ->open($save_directory . $save_name)
                                                ->thumbnail(new Box(128, 128))
                                                ->save(Yii::getAlias($localPathThumbnail. $save_name), ['quality' => 100]);
                                    }
                                }
                            }
                        }*/
                    }
                    else{
                        print '<pre>';
                        print_r($newContent);
                        print '</pre>';
                        exit();
                    }
                }


                //transection commit
                $transaction->commit();

                print '<pre>';
                print_r("จบ 1 page = ".$page);
                print '</pre>';
                exit();
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }else{
            print '<pre>';
            print_r("ไม่มี list ข้อมูล");
            print '</pre>';
            exit();
        }

    }


    public function actionImportPlant(){
        $get = $_GET;
        $page = 1;
        $size = 10;

        if(!empty($get['page'])){
            if(is_numeric($get['page'])){
                $page = $get['page'];
            }
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://api.bedo.or.th/api/v1/plants?page=".$page."&size=".$size);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $output = json_decode($output, true);
        curl_close($curl);

    
        $model= array();

        if(!empty($output['data']['items'])){

            $model = $output['data']['items'];

            
           
            // $index = 0;
            // if($page == 1){
            //     $index = 0;
            // }else{
            //     $index = ($size * $page) - $size;
            //     $max = ($size * $page);

            //     for ($i=0; $i < $max ; $i++) { 
            //         $model[$i] = array();
            //     }
            // }

           
            $transaction = \Yii::$app->db->beginTransaction();
            try {

                foreach($output['data']['items'] as $value){
                    $newContent = new Content();
                    $newContent->type_id = 1;
                    $newContent->name = 'name';
                    $newContent->picture_path = 'picture_path';
                    $newContent->description = 'description';
                    $newContent->other_information = 'other_information';
                    $newContent->source_information = 'source_information';
                    $newContent->photo_credit = 'photo_credit';
                    $newContent->latitude = 'latitude';
                    $newContent->longitude = 'longitude';
                    $newContent->region_id = 'region_id';
                    $newContent->province_id = 'province_id';
                    $newContent->district_id = 'district_id';
                    $newContent->subdistrict_id = 'subdistrict_id';
                    $newContent->zipcode_id = 'zipcode_id';
                    $newContent->approved_by_user_id = null;
                    $newContent->created_by_user_id = 1;
                    $newContent->updated_by_user_id = 1;
                    $newContent->note = 'note';
                    $newContent->active = 1;
                    $newContent->status = 'approved';
                    $newContent->type_id = 1;
                    $newContent->created_at = date('Y-m-d H:i:s');
                    $newContent->updated_at = date('Y-m-d H:i:s');

                    if($newContent->save()){
                        //content
                        $newContentPlant = new ContentPlant();
                        $newContentPlant->content_id = $newContent->id;
                        $newContentPlant->other_name = 'other_name';
                        $newContentPlant->features = 'features';
                        $newContentPlant->benefit = 'benefit';
                        $newContentPlant->found_source = 'found_source';
                        $newContentPlant->other_information = 'other_information';
                        $newContentPlant->season = 'season';
                        $newContentPlant->ability = 'ability';
                        $newContentPlant->common_name = 'common_name';
                        $newContentPlant->scientific_name = 'scientific_name';
                        $newContentPlant->family_name = 'family_name';
                        $newContentPlant->created_at = date('Y-m-d H:i:s');
                        $newContentPlant->updated_at = date('Y-m-d H:i:s');
                        $newContentPlant->save();

                        //taxonomy
                        if(!empty($value['taxonomy'])){
                            foreach ($value['taxonomy'] as $value) {
                                $modelTax = new ContentTaxonomy();
                                $modelTax->content_id = $newContent->id;
                                if (is_numeric($value)) {
                                    $modelTax->taxonomy_id = $value;
                                }else{
                                    $taxId = $this->getTaxonomyInputData($value);
                                    if(!empty($taxId)){
                                        $modelTax->taxonomy_id = $taxId;
                                    }
                                }
                                $duplicate = (new \yii\db\Query())
                                    ->select(['content_id','taxonomy_id'])
                                    ->from('content_taxonomy')
                                    ->where(['content_id' => $modelTax->content_id])
                                    ->andWhere(['taxonomy_id' => $modelTax->taxonomy_id])
                                    ->all();
                                if (empty($duplicate)) {
                                    $modelTax->created_at = date('Y-m-d H:i:s');
                                    $modelTax->save();
                                }
                            }
                        }

                        //main picture
                        if (!empty($value['image'])) {

                            $url = $value['image'];

                            $imageInfo = pathinfo($url);

                            if (!empty($imageInfo['basename'])) {
                                $save_name = $imageInfo['basename'];

                                $save_directory = realpath(dirname(__FILE__) . '/../../../') . '/frontend/web/files/content-plant/';
                                $localPathThumbnail = realpath(dirname(__FILE__) . '/../../../') . '/frontend/web/files/content-plant/thumbnail/';

                                if (!file_exists($save_directory)) {
                                    mkdir($save_directory, 0777, true);
                                }

                                if (is_writable($save_directory)) {
                                    file_put_contents($save_directory . $save_name, file_get_contents($url));


                                    if (file_exists($save_directory . $save_name)) {
                                        Image::getImagine()
                                                ->open($save_directory . $save_name)
                                                ->thumbnail(new Box(128, 128))
                                                ->save(Yii::getAlias($localPathThumbnail. $save_name), ['quality' => 100]);
                                    }
                                }
                            }

                        }


                    }
                }
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            } 
            
        }


        $pagination = new Pagination(['totalCount' => $output['data']['total'], 'pageSize'=>$size, 'pageParam' => 'page']);


        return $this->render('index', ['model' => $model, 'pagination' => $pagination]);

        
    }

    public function actionImportproduct(){

        $page = 1;
        $size = 10;
        $content_type = 'product';

        $request = Yii::$app->request;
        $get = $request->get();

        if(!empty($get['page'])){
            if (is_numeric($get['page'])) {
                $page = intval($get['page']);
            }
        }

        if(!empty($get['size'])){
            if (is_numeric($get['size'])) {
                $size = intval($get['size']);
            }
        }

        if(!empty($get['content_type'])){
            $content_type = $get['content_type'];
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://localhost:8086/?page=".$page."&size=".$size."&content_type=".$content_type);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $output = json_decode($output, true);
        curl_close($curl);


        // print '<pre>';
        // print_r($output);
        // print '</pre>';
        // exit();

        if (!empty($output['data']['items'])) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                foreach ($output['data']['items'] as $value) {
                    $newContent = new Content();
                    //$newContent->id = $value['id'];
                    $newContent->content_root_id = $value['id'];
                    $newContent->type_id = 6;
                    $newContent->name = $value['name'];
             
                    if(!empty($value['photo1'])){
                        $newContent->picture_path = $value['photo1'];
                    }else{
                        $newContent->picture_path = '';
                    }
                    $newContent->description = $value['message2'];
                    $newContent->other_information = $value['message3'];
                    $newContent->source_information = $value['source2'];
                    $newContent->photo_credit = '';
                    $newContent->latitude = $value['lat'];
                    $newContent->longitude = $value['lng'];

                    //find location 
                    if(!empty($value['tumbon']) && !empty($value['district']) && !empty($value['province'])){
                        $dataLocation = (new \yii\db\Query())
                        ->select([
                            'province.region_id as region_id', 
                            'district.province_id',
                            'subdistrict.district_id', 
                            'subdistrict.id as subdistrict_id'
                            ])
                        ->from('subdistrict')
                        ->leftJoin('district' ,'district.id = subdistrict.district_id')
                        ->leftJoin('province' ,'province.id = district.province_id')
                        ->where(['LIKE', 'subdistrict.name_th', trim($value['tumbon'])])
                        ->andWhere(['LIKE', 'district.name_th', trim($value['district'])])
                        ->andWhere(['LIKE', 'province.name_th', trim($value['province'])])
                        ->one();
                    }
                    
                    if(empty($dataLocation)){
                        if(!empty($value['district_name']) && !empty($value['subdistrict_name']) && !empty($value['province_name'])){
                            $dataLocation = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id', 
                                    'district.province_id',
                                    'subdistrict.district_id', 
                                    'subdistrict.id as subdistrict_id'
                                    ])
                                ->from('subdistrict')
                                ->leftJoin('district' ,'district.id = subdistrict.district_id')
                                ->leftJoin('province' ,'province.id = district.province_id')
                                ->where(['LIKE', 'subdistrict.name_th', trim($value['subdistrict_name'])])
                                ->andWhere(['LIKE', 'district.name_th', trim($value['district_name'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['province_name'])])
                                ->one();
                        }
                    }

                    if(!empty($dataLocation['subdistrict_id'])){
                        $dataLocationZipcode = (new \yii\db\Query())
                            ->select(['zipcode.id'])
                            ->from('zipcode')
                            ->where(['=', 'subdistrict_id', $dataLocation['subdistrict_id']])
                            ->one();
                    }

                    if (empty($dataLocation)) {
                        if(!empty($value['district_name']) && !empty($value['province_name'])){
                            $dataLocation = (new \yii\db\Query())
                                ->select([
                                    'province.region_id as region_id', 
                                    'district.province_id',
                                    'district.id as district_id',
                                    ])
                                ->from('district')
                                ->leftJoin('province' ,'province.id = district.province_id')
                                ->where(['LIKE', 'district.name_th', trim($value['district_name'])])
                                ->andWhere(['LIKE', 'province.name_th', trim($value['province_name'])])
                                ->one();

                            if(!empty($dataLocation)){
                                $dataLocation['subdistrict_id'] = '';
                            }
                        }
                    }


                    

                    if (!empty($dataLocation)) {
                        $newContent->region_id = $dataLocation['region_id'];
                        $newContent->province_id = $dataLocation['province_id'];
                        $newContent->district_id = $dataLocation['district_id'];
                        $newContent->subdistrict_id = $dataLocation['subdistrict_id'];
                        if (!empty($dataLocationZipcode)) {
                            $newContent->zipcode_id = $dataLocationZipcode['id'];
                        }
                    }

                    $newContent->approved_by_user_id = 1;
                    $newContent->created_by_user_id = $value['member_id'];
                    $newContent->updated_by_user_id = $value['member_id'];
                    $newContent->note = '';
                    
                    $newContent->status = 'approved';
                    if($value['status'] == 'active'){
                        $newContent->active = 1;
                    }else{
                        $newContent->active = 0;
                    }
                    $newContent->created_at = $value['date_created'];
                    $newContent->updated_at = $value['last_updated'];

                    if ($newContent->save()) {
                        //content
                        $newContentProduct = new ContentProduct();
                        $newContentProduct->content_id = $newContent->id;
                        $newContentProduct->product_category_id = $value['type'];
                        $newContentProduct->product_features = $value['message2'];
                        $newContentProduct->product_main_material = $value['main'];
                        $newContentProduct->product_sources_material = "";
                        $newContentProduct->product_price = empty($value['price'])? 0.00:floatval($value['price']);
                        $newContentProduct->product_distribution_location = "";
                        $newContentProduct->product_address = $value['addr'];
                        $newContentProduct->product_phone = $value['tel'];
                        $newContentProduct->other_information = $value['message3'];
                        $newContentProduct->found_source = $value['source'];
                        $newContentProduct->contact = "";
                        $newContentProduct->created_at = $value['date_created'];
                        $newContentProduct->updated_at = $value['last_updated'];
                        if(!$newContentProduct->save()){
                            print '<pre>';
                            print_r($newContentProduct);
                            print '</pre>';
                            exit();
                        }

                        //taxonomy
                        if (!empty($value['tag'])) {

                            $modelTax = new ContentTaxonomy();
                            $modelTax->content_id = $newContent->id;

                            $taxId = $this->getTaxonomyInputData($value['tag']);
                            if (!empty($taxId)) {
                                $modelTax->taxonomy_id = $taxId;
                            }

                            $duplicate = (new \yii\db\Query())
                                ->select(['content_id','taxonomy_id'])
                                ->from('content_taxonomy')
                                ->where(['content_id' => $modelTax->content_id])
                                ->andWhere(['taxonomy_id' => $modelTax->taxonomy_id])
                                ->all();
                            if (empty($duplicate)) {
                                $modelTax->created_at = date('Y-m-d H:i:s');
                                $modelTax->save();
                            }
                            
                        }

                        
                        //gallery
                        $gallery = array();
                        if(!empty($value['photo1'])){
                            $name = $value['photo1_name'];
                            if(empty($name)){
                                $name = $value['photo1'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo1'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo2'])){
                            $name = $value['photo2_name'];
                            if(empty($name)){
                                $name = $value['photo2'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo2'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo3'])){
                            $name = $value['photo3_name'];
                            if(empty($name)){
                                $name = $value['photo3'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo3'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo4'])){
                            $name = $value['photo4_name'];
                            if(empty($name)){
                                $name = $value['photo4'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo4'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo5'])){
                            $name = $value['photo5_name'];
                            if(empty($name)){
                                $name = $value['photo5'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo5'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if(!empty($value['photo6'])){
                            $name = $value['photo6_name'];
                            if(empty($name)){
                                $name = $value['photo6'];
                            }
                            $gallery[] = array($newContent->id, $name, $value['photo6'], $value['member_id'], $value['member_id'], $value['date_created'], $value['last_updated']);
                        }

                        if (!empty($gallery)) {
                            Yii::$app->db->createCommand()->batchInsert('picture', ['content_id', 'name', 'path', 'created_by_user_id', 'updated_by_user_id', 'created_at', 'updated_at'], $gallery)->execute();
                        }

                        if (!empty($value['comment'])) {
                            $comment = array();
                            foreach($value['comment'] as $commentDetail){
                                if (!empty($commentDetail['member_id'])) {
                                    $comment[] = array($commentDetail['member_id'], $newContent->id, $commentDetail['message'], $commentDetail['date_created']);
                                }
                            }
                            if (!empty($comment)) {
                                Yii::$app->db->createCommand()->batchInsert('comment', ['user_id', 'content_root_id', 'message', 'created_at'], $comment)->execute();
                            }
                        }

                        //statistic
                        $statistic = array();
                        $statistic[] = array($newContent->id, $value['view'], 0, $value['last_updated']);
                        Yii::$app->db->createCommand()->batchInsert('content_statistics', ['content_root_id', 'pageview', 'like_count', 'updated_at'], $statistic)->execute();

                    }
                    else{
                        print '<pre>';
                        print_r($newContent);
                        print '</pre>';
                        exit();
                    }
                }


                //transection commit
                $transaction->commit();

                print '<pre>';
                print_r("จบ 1 page = ".$page);
                print '</pre>';
                exit();
                
            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }else{
            print '<pre>';
            print_r("ไม่มี list ข้อมูล");
            print '</pre>';
            exit();
        }

    }

    public static function getTaxonomyName($id)
    {
        $text = "";
        $count = 1;
        $nameTaxonomy = TaxonomyHelper::getTaxonomyListByContentName($id);
        if (!empty($nameTaxonomy)) {
            foreach ($nameTaxonomy as $key => $value) {
                if (count($nameTaxonomy) > 0) {
                    if (count($nameTaxonomy) == 1) {
                        $text =  $value;
                    } else {
                        if ($count == count($nameTaxonomy)) {
                            $text .=  $value;
                        } else {
                            $text .= $value . ", ";
                        }
                    }
                } else {
                    $text =  "";
                }
                $count++;
            }
        } else {
            $text =  "";
        }
        return $text;
    }

    private static function getTaxonomyInputData($name)
    {
        $model = Taxonomy::find()->where(['name' => $name])->one();
        if(empty($model)){
            $model = new Taxonomy();
            $model->name = $name;
            $model->created_at = date('Y-m-d H:i:s');
            $model->updated_at = date('Y-m-d H:i:s');
            $model->save();
        }
        return $model->id;
    }

    public static function getGallery($id, $type)
    {

        $query = (new \yii\db\Query())
                ->select([
                    // 'content_id', 
                    'name', 
                    'path as picture'

                    ])
                ->from('picture')
                ->where(['content_id' => $id])
                ->all();
        
        $pictureAll = array();
        foreach ($query as $key => $value) {
            if(!empty($value['picture'])){
                $value['picture'] = self::Host()."/files/content-".$type."/".$value['picture'];
            }

            $pictureAll[] = $value;

        }

        return $pictureAll;
    }

    private static function Host(){
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
    }
}
