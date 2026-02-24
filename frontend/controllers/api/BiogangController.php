<?php

namespace frontend\controllers\api;

use yii\helpers\Json;
use yii\web\Controller;
use common\components\_;
use frontend\models\content\Taxonomy;
use frontend\models\content\ContentTaxonomy;
use frontend\components\TaxonomyHelper;

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
                                "url" => $host."/api/biogang-items/micros?page=1&size=10"
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
                                        'type.name as type_id',
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
                                $query['type_id'] = $type;
                                $query['features'] = str_replace('&nbsp;', ' ', strip_tags($query['features']));
                                $query['benefit'] = str_replace('&nbsp;', ' ', strip_tags($query['benefit']));
                                $query['ability'] = str_replace('&nbsp;', ' ', strip_tags($query['ability']));
                                $query['other_information'] = str_replace('&nbsp;', ' ', strip_tags($query['other_information']));
                                $query['source_information'] = str_replace('&nbsp;', ' ', strip_tags($query['source_information']));

                                $query['taxonomy'] = $this->getTaxonomyName($query['id']);

                                $query['gallery'] = $this->getGallery($query['id'], $type);
                        
                                $return['data']= $query;

                                if(empty($query)){
                                    $return['message'] = 'Result not found.';
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
                            'content.type_id',
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

                        $value['type_id'] = 'plant';

                        if(!empty($value['picture'])){
                            $value['picture'] = self::Host()."/files/content-plant/".$value['picture'];
                        }

                        $value['features'] = str_replace('&nbsp;', ' ', strip_tags($value['features']));
                        $value['benefit'] = str_replace('&nbsp;', ' ', strip_tags($value['benefit']));
                        $value['ability'] = str_replace('&nbsp;', ' ', strip_tags($value['ability']));
                        $value['other_information'] = str_replace('&nbsp;', ' ', strip_tags($value['other_information']));
                        $value['source_information'] = str_replace('&nbsp;', ' ', strip_tags($value['source_information']));

                        $value['taxonomy'] = $this->getTaxonomyName($value['id']);

                        $value['gallery'] = $this->getGallery($value['id'], 'plant');

                        $dataResult[] = $value;
                    }

                    $return['data']['total'] = intval($queryCount);
                    $return['data']['items'] = $dataResult;

                    if(empty($dataResult)){
                        $return['message'] = 'Result not found.';
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
                            'content.type_id',
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

                        $value['type_id'] = 'animal';

                        if(!empty($value['picture'])){
                            $value['picture'] = self::Host()."/files/content-animal/".$value['picture'];
                        }

                        $value['features'] = str_replace('&nbsp;', ' ', strip_tags($value['features']));
                        $value['benefit'] = str_replace('&nbsp;', ' ', strip_tags($value['benefit']));
                        $value['ability'] = str_replace('&nbsp;', ' ', strip_tags($value['ability']));
                        $value['other_information'] = str_replace('&nbsp;', ' ', strip_tags($value['other_information']));
                        $value['source_information'] = str_replace('&nbsp;', ' ', strip_tags($value['source_information']));

                        $value['taxonomy'] = $this->getTaxonomyName($value['id']);

                        $value['gallery'] = $this->getGallery($value['id'], 'animal');

                        $dataResult[] = $value;
                    }

                    $return['data']['total'] = intval($queryCount);
                    $return['data']['items'] = $dataResult;

                    if(empty($dataResult)){
                        $return['message'] = 'Result not found.';
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
                            'content.type_id',
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

                        $value['type_id'] = 'fungi';

                        if(!empty($value['picture'])){
                            $value['picture'] = self::Host()."/files/content-fungi/".$value['picture'];
                        }

                        $value['features'] = str_replace('&nbsp;', ' ', strip_tags($value['features']));
                        $value['benefit'] = str_replace('&nbsp;', ' ', strip_tags($value['benefit']));
                        $value['ability'] = str_replace('&nbsp;', ' ', strip_tags($value['ability']));
                        $value['other_information'] = str_replace('&nbsp;', ' ', strip_tags($value['other_information']));
                        $value['source_information'] = str_replace('&nbsp;', ' ', strip_tags($value['source_information']));

                        

                        $value['taxonomy'] = $this->getTaxonomyName($value['id']);

                        $value['gallery'] = $this->getGallery($value['id'], 'fungi');

                        $dataResult[] = $value;
                    }

                    $return['data']['total'] = intval($queryCount);
                    $return['data']['items'] = $dataResult;

                    if(empty($dataResult)){
                        $return['message'] = 'Result not found.';
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

        //return _::toJsonString($type);
    }


    public function actionGetPlant(){
        $get = $_GET;
        $page = 1;
        $size = 10;

        if(!empty($get['page'])){
            if(is_numeric($get['page'])){
                $page = $get['page'];
            }
        }

        if(!empty($get['per-page'])){
            if(is_numeric($get['per-page'])){
                $size = $get['per-page'];
            }
        }

        
        //     $url = "https://tklc.devfunction.com/map";

        //    $url = "http://nuttawut.devfunction.com/payment/import.php?url=http://api.bedo.or.th/api/v1/plants?page=".$page."&size=".$size;
        //     $result = file_get_contents($url);
        // Will dump a beauty json :3


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://api.bedo.or.th/api/v1/plants?page=".$page."&size=".$size);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $output = json_decode($output, true);
        curl_close($curl);

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        //         exit();

    
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

            // foreach($output['data']['items'] as $value){
            //     $model[$index] = $value;
            //     $index++;
            // }
            
        }


        // print '<pre>';
        // print_r($model);
        // print '</pre>';
        // exit();
    
    
        // $provider = new \yii\data\ArrayDataProvider([
    
        //     'allModels' => $model,

        //     'TotalCount' => $output['data']['total'],
    
        //     'key' => 'id',
    
        //     // 'sort' => [
    
        //     //     'attributes' => ['id', 'speed', 'time', 'address'],
    
        //     // ],
    
        //     // 'pagination' => [
    
        //     //     'pageSize' => $size,
    
        //     // ],
    
        // ]);

        $pagination = new Pagination(['totalCount' => $output['data']['total'], 'pageSize'=>$size, 'pageParam' => 'page']);


        return $this->render('index', ['model' => $model, 'type' => 'plants', 'pagination' => $pagination]);

        
    }

    public function actionGetAnimal(){
        $get = $_GET;
        $page = 1;
        $size = 10;

        if(!empty($get['page'])){
            if(is_numeric($get['page'])){
                $page = $get['page'];
            }
        }

        if(!empty($get['per-page'])){
            if(is_numeric($get['per-page'])){
                $size = $get['per-page'];
            }
        }

        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://api.bedo.or.th/api/v1/animals?page=".$page."&size=".$size);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $output = json_decode($output, true);
        curl_close($curl);

    
        $model= array();

        if(!empty($output['data']['items'])){

            $model = $output['data']['items'];
            
        }



        $pagination = new Pagination(['totalCount' => $output['data']['total'], 'pageSize'=>$size, 'pageParam' => 'page']);


        return $this->render('index', ['model' => $model, 'type' => 'animals', 'pagination' => $pagination]);

        
    }

    public function actionGetMicros(){
        $get = $_GET;
        $page = 1;
        $size = 10;

        if(!empty($get['page'])){
            if(is_numeric($get['page'])){
                $page = $get['page'];
            }
        }

        if(!empty($get['per-page'])){
            if(is_numeric($get['per-page'])){
                $size = $get['per-page'];
            }
        }

        
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "http://api.bedo.or.th/api/v1/micros?page=".$page."&size=".$size);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $output = json_decode($output, true);
        curl_close($curl);


    
        $model= array();

        if(!empty($output['data']['items'])){

            $model = $output['data']['items'];
            
        }


        $pagination = new Pagination(['totalCount' => $output['data']['total'], 'pageSize'=>$size, 'pageParam' => 'page']);


        return $this->render('index', ['model' => $model, 'type' => 'micros', 'pagination' => $pagination]);

        
    }

    public function actionGroupList($type, $id){

        $get = $_GET;
        $page = 1;
        $size = 10;

        if(!empty($get['page'])){
            if(is_numeric($get['page'])){
                $page = $get['page'];
            }
        }

        if(!empty($get['per-page'])){
            if(is_numeric($get['per-page'])){
                $size = $get['per-page'];
            }
        }

        
        $urlList = "http://203.114.110.21/api/v1/".$type."/".$id;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $urlList);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $output = json_decode($output, true);
        curl_close($curl);

        // $url = "http://nuttawut.devfunction.com/payment/import.php?url=".$urlList;
        // $result = file_get_contents($url);
        // // Will dump a beauty json :3

        // $output = json_decode($result, true);

        // print '<pre>';
        // print_r($output);
        // print '</pre>';
        // exit();


    
        $model= array();

        if(!empty($output['data']['items'])){

            $model = $output['data']['items'];
            
        }

        return $this->render('list', ['model' => $model]);
    }

    public function actionWordcloudCount(){
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
            $query = (new \yii\db\Query())
                        ->select([
                            'COUNT(content_taxonomy.id) as total',
                            'content_taxonomy.taxonomy_id',
                            'taxonomy.name'
                        ])
                        ->from('content_taxonomy')
                        ->leftJoin('taxonomy', 'taxonomy.id = content_taxonomy.taxonomy_id')
                        ->leftJoin('content', 'content.id = content_taxonomy.content_id')
                        ->where(['NOT LIKE', 'taxonomy.name', 'http'])
                        ->andWhere(['NOT LIKE', 'taxonomy.name', '-'])
                        ->andWhere(['=', 'content.active', 1])
                        ->andWhere(['=', 'content.status', 'approved'])
                        // ->leftJoin('content', 'content.id = content_taxonomy.content_id')
                        ->groupBy(['content_taxonomy.taxonomy_id'])
                        ->orderBy(['total' => SORT_DESC])
                        ->limit(100)
                        ->all(); 

            if (!empty($query)) {

                \Yii::$app
                ->db
                ->createCommand()
                ->delete('word_cloud_statistics')
                ->execute();

                $data = array();
                foreach($query as $word){
                    if (!empty($word['name'])) {
                        $data[] = array($word['name'], $word['total']);
                    }
                }
                if (!empty($data)) {
                    Yii::$app->db->createCommand()->batchInsert('word_cloud_statistics', ['keyword', 'total'], $data)->execute();

                    print "<pre>";
                    print_r("Import Success.");
                    print "</pre>";
                    exit();

                }
            }

            print "<pre>";
            print_r("Import no data.");
            print "</pre>";
            exit();


           
        }catch(\Exception $exception){

            print "<pre>";
            print_r($exception);
            print "</pre>";
            exit();

            $return['message'] = 'เกิดข้อผิดพลาด API ไม่พร้อมใช้งาน';
            $return['errorCode'] = 200;
        }
       
        return json_encode($return);

    }

    public function actionKeywordmapCount(){
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
            \Yii::$app
                ->db
                ->createCommand()
                ->delete('keyword_map_statistics')
                ->execute();
            for ($i = 1; $i <= 6 ; $i++) {
                $query = (new \yii\db\Query())
                            ->select([
                                'COUNT(content_taxonomy.id) as total',
                                'content_taxonomy.taxonomy_id',
                                'content.type_id',
                                'taxonomy.name'
                            ])
                            ->from('content_taxonomy')
                            ->leftJoin('taxonomy', 'taxonomy.id = content_taxonomy.taxonomy_id')
                            ->leftJoin('content', 'content.id = content_taxonomy.content_id')
                            ->where(['NOT LIKE', 'taxonomy.name', 'http'])
                            ->andWhere(['NOT LIKE', 'taxonomy.name', '-'])
                            ->andWhere(['=', 'content.active', 1])
                            ->andWhere(['=', 'content.status', 'approved'])
                            ->andWhere(['=', 'content.type_id', $i])
                            // ->leftJoin('content', 'content.id = content_taxonomy.content_id')
                            ->groupBy(['content_taxonomy.taxonomy_id', 'content.type_id'])
                            ->orderBy(['total' => SORT_DESC])
                            ->limit(30)
                            ->all();


                if (!empty($query)) {
                    $data = array();
                    foreach ($query as $word) {
                        if (!empty($word['name'])) {
                            $data[] = array($word['taxonomy_id'], $word['type_id'], $word['name'], $word['total']);
                        }
                    }
                    if (!empty($data)) {
                        Yii::$app->db->createCommand()->batchInsert('keyword_map_statistics', ['taxonomy_id', 'type', 'keyword', 'total'], $data)->execute();
                       
                    }else{
                        print "<pre>";
                        print_r("No data type = ".$i );
                        print "</pre>";
                    }
                }
            }


            print "<pre>";
            print_r("Import Success.");
            print "</pre>";
            exit();

           
        }catch(\Exception $exception){

            print "<pre>";
            print_r($exception);
            print "</pre>";
            exit();

            $return['message'] = 'เกิดข้อผิดพลาด API ไม่พร้อมใช้งาน';
            $return['errorCode'] = 200;
        }
       
        return json_encode($return);

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
