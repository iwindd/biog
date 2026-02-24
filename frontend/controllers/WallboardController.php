<?php

namespace frontend\controllers;
use Yii;
use yii\filters\AccessControl;
use frontend\models\Wallboard;
use yii\data\Pagination;
use frontend\models\Profile;
use frontend\components\FrontendHelper;


class WallboardController extends \yii\web\Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['index', 'create', 'detele'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            // 'verbs' => [
            //     'class' => VerbFilter::className(),
            //     'actions' => [
            //         'logout' => ['post'],
            //     ],
            // ],
        ];
    }

    public function actionIndex()
    {
        $limit = 10;
        $page = 1;
        if(!empty($_GET['page'])) {
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT);
            if(false === $page) {
                $page = 1;
            }
        }
        $offset = ($page - 1) * $limit;

        $query = Wallboard::find()->where(['active'=>1]);
        $countQuery = clone $query;
        $pagination = new Pagination(['totalCount' => $countQuery->count(), 'pageSize'=>$limit]);
        $wallboard = $query->limit($limit)->offset($offset)->asArray()->orderBy(['updated_at' => SORT_DESC])->all();
        //$wallboard = Wallboard::find()->where(['active' => 1])->asArray()->all();
        return $this->render('index', [
            'wallboard' => $wallboard,
            'pagination' => $pagination
            ]);
    }

    public function actionDelete(){
        $result = array();
        $id = Yii::$app->request->post('id');
        $model = Wallboard::findOne($id);
        if (Yii::$app->user->identity->id == $model['created_by_user_id']) {
            $model->active = 0;
            if ($model->save()) {

                FrontendHelper::saveUserLog('wallboard', Yii::$app->user->identity->id, $model->id, 'delete wallboard', 'ลบข้อมูล Wallboard');

                $this->_response(200, 'success', $result);
                //return $this->redirect(['/']);
            }
        }
        $this->_response(401, 'error', $result);
        //return $this->redirect(['/']);
    }

    public function actionCreate() {
        $model = new Wallboard();
        //$result = array();

        if ($model->load(Yii::$app->request->post())) {
            $post = Yii::$app->request->post();
            $model->created_by_user_id = Yii::$app->user->identity->id;
            $model->updated_by_user_id = Yii::$app->user->identity->id;
            $model->active = 1;
            $model->created_at = date("Y-m-d H:i:s");
            $model->updated_at = date("Y-m-d H:i:s");

            $model->save();

            FrontendHelper::saveUserLog('wallboard', Yii::$app->user->identity->id, $model->id, 'create wallboard', 'เพิ่มข้อมูล Wallboard');

            return  $this->redirect('/');
        }
        
        // if (Yii::$app->request->isAjax) {
        //     if (Yii::$app->request->post()) {
        //         //$user_id = 1;
        //         $user_id = Yii::$app->user->id;
        //         $data = Yii::$app->request->post();
        //         $model->created_by_user_id = $user_id;
        //         $model->updated_by_user_id = $user_id;
        //         $model->description = $data["description"];
        //         $model->created_at = date("Y-m-d H:i:s");
        //         $model->updated_at = date("Y-m-d H:i:s");
        //         $model->save();

        //         $profileImg = Profile::find('picture', 'firstname', 'lastname')->where(['user_id' => $user_id])->asArray()->one();
                
        //         $result['description'] = $model["message"];
        //         $result['created_by_user_id'] = $model["created_by_user_id"];
        //         $result['updated_by_user_id'] = $model["updated_by_user_id"];
        //         $result['time'] = FrontendHelper::getTime($model["created_at"]);
        //         $result['date'] = FrontendHelper::getDate($model["created_at"]);
        //         $result['picture'] = $profileImg["picture"];
        //         $result['fullname'] = $profileImg["firstname"]." ".$profileImg["lastname"] ;
               
        //         $this->_response(200, 'success', $result);
        //     }
        //     else {
        //         $result['error'] = 'permission denied';
        //         $this->_response(401, 'error', $result);
        //     }
        // }
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
