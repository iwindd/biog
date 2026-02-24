<?php

namespace frontend\controllers;
use Yii;
use \yii\web\Controller;
use frontend\models\Blog;
use frontend\models\BlogComment;
use frontend\models\Comment;
use backend\models\Content;
use frontend\models\Profile;
use yii\filters\AccessControl;
use frontend\components\FrontendHelper;

class CommentController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['create-blog-comment', 'create-content-comment', 'delete-content-comment'],
                'rules' => [
                    
                    [
                        'actions' => ['create-blog-comment', 'create-content-comment', 'delete-content-comment'],
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
    public function actionCreateBlogComment()
    {
        $model = new BlogComment();
        $result = array();
        
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->post()) {
                //$user_id = 1;
                $user_id = Yii::$app->user->id;
                $data = Yii::$app->request->post();
                $model->user_id = $user_id;
                $blog = Blog::findOne($data["blog_id"]);
                if (!empty($blog->blog_root_id)) {
                    $model->blog_root_id = $blog->blog_root_id;
                }else{
                    $model->blog_root_id = $data["blog_id"];
                }

                $model->message = $data["message"];
                $model->created_at = date("Y-m-d H:i:s");
                $model->save();

                FrontendHelper::saveUserLog('blog_comment', Yii::$app->user->identity->id, $model->id, 'create blog comment ', 'แสดงความคิดเห็นของ Blog: '.$blog->title);

                $profileImg = Profile::find('picture', 'firstname', 'lastname')->where(['user_id' => $user_id])->asArray()->one();
                $result['id'] = $model["id"];
                $result['user_id'] = $model["user_id"];
                $result['message'] = $model["message"];
                $result['blog_id'] = $model["blog_root_id"];
                $result['time'] = FrontendHelper::getTime($model["created_at"]);
                $result['date'] = FrontendHelper::getDate($model["created_at"]);
                $result['picture'] = FrontendHelper::getProfileUrl($profileImg["picture"]);
                $result['fullname'] = $profileImg["firstname"]." ".$profileImg["lastname"] ;
               
                $this->_response(200, 'success', $result);
            }
            else {
                $result['error'] = 'permission denied';
                $this->_response(401, 'error', $result);
            }
        }
    }

    public function actionCreateContentComment()
    {
        $model = new Comment();
        $result = array();
        
        if (Yii::$app->request->isAjax) {
            if (Yii::$app->request->post()) {
                $user_id = Yii::$app->user->id;
                $data = Yii::$app->request->post();
                if(!empty($data["message"])) {
                    $model->user_id = $user_id;
                    $content = Content::findOne($data["content_id"]);
                    if (!empty($content->content_root_id)) {
                        $model->content_root_id = $content->content_root_id;
                    }else{
                        $model->content_root_id = $data["content_id"];
                    }
                    $model->message = $data["message"];
                    $model->created_at = date("Y-m-d H:i:s");
                    $model->save();

                    FrontendHelper::saveUserLog('comment', Yii::$app->user->identity->id, $model->id, 'create content comment ', 'แสดงความคิดเห็นของ Content: '.$content->name);

                    $profileImg = Profile::find('picture', 'firstname', 'lastname')->where(['user_id' => $user_id])->asArray()->one();
                    $result['id'] = $model["id"];
                    $result['user_id'] = $model["user_id"];
                    $result['message'] = $model["message"];
                    $result['content_id'] = $model["content_root_id"];
                    $result['time'] = FrontendHelper::getTime($model["created_at"]);
                    $result['date'] = FrontendHelper::getDate($model["created_at"]);
                    $result['picture'] = FrontendHelper::getProfileUrl($profileImg["picture"]);
                    $result['fullname'] = $profileImg["firstname"]." ".$profileImg["lastname"] ;
                    $this->_response(200, 'success', $result);
                }
            }
            else {
                $result['error'] = 'permission denied';
                $this->_response(401, 'error', $result);
            }
        }
    }

    public function actionDeleteContentComment() {
        $user_id = Yii::$app->user->id;
        $id = Yii::$app->request->post('id');

        

        $result = array();
        if (!empty($id)) {
            $contentCommtent = Comment::find()->where(['id' => $id])->one();
            $content = Content::findOne($contentCommtent->content_root_id);

            FrontendHelper::saveUserLog('comment', Yii::$app->user->identity->id, $id, 'delete content comment ', 'ลบความคิดเห็นของ Content: '.$content->name);

            
            Yii::$app->db->
            createCommand()
            ->delete('comment', ['id' => $id, 'user_id' => $user_id])
            ->execute();            

            $this->_response(200, 'success', $result);
        }
        else {
            $result['error'] = 'permission denied';
            $this->_response(401, 'error', $result);
        }
    }

    public function actionDeleteBlogComment() {
        $user_id = Yii::$app->user->id;
        $id = Yii::$app->request->post('id');

        $result = array();
        if (!empty($id)) {

            $blogCommtent = BlogComment::find()->where(['id' => $id])->one();
            $blog = Blog::findOne($blogCommtent->blog_root_id);

            FrontendHelper::saveUserLog('blog_comment', Yii::$app->user->identity->id, $id, 'delete blog comment ', 'ลบความคิดเห็นของ Blog: '.$blog->title);

            Yii::$app->db->
            createCommand()
            ->delete('blog_comment', ['id' => $id, 'user_id' => $user_id])
            ->execute();


            $this->_response(200, 'success', $result);
        }
        else {
            $result['error'] = 'permission denied';
            $this->_response(401, 'error', $result);
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
