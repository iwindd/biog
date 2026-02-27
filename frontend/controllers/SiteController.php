<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\models\Knowledge;
use frontend\models\BlogStatistics;
use frontend\models\ContentStatistics;
use frontend\models\UserLikeBlog;
use frontend\models\UserLikeContent;
use frontend\models\Learningcenter;
use frontend\models\LearningcenterInformation;

use backend\models\Users;
use backend\models\Profile;
use frontend\models\Wallboard;
use frontend\models\Blog;
use frontend\models\Content;

use yii\web\UploadedFile;


/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup', 'submit-like'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'submit-like'],
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

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        //$modelWallboard = Wallboard::find()->asArray()->all();
        $modelWallboard = new Wallboard();
        $wallbaord = Wallboard::find()->select(['id', 'description', 'created_at', 'created_by_user_id'])->where(['active' => 1])->orderBy(['updated_at' => SORT_DESC])->limit(5)->asArray()->all();
        $knowledge_infographic = Knowledge::find()->select(['id', 'title', 'description', 'picture_path', 'created_at'])->where(['type' => 'Infographic', 'active' => 1])->orderBy(['updated_at' => SORT_DESC])->limit(8)->asArray()->all();
        return $this->render('index', [
            'knowledge_infographic' => $knowledge_infographic,
            'modelWallboard' => $modelWallboard,
            'wallbaord' => $wallbaord
        ]);
    }

    public function actionPlant()
    {
        return $this->render('plant');
    }

    public function actionCreateWallboard() {
        $model = new Wallboard();
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        $session = Yii::$app->session;
        unset($session['views_content']);

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        return $this->render('contact');
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    public function actionCalendar()
    {
        return $this->render('calendar');
    }

    public function actionPrivacy()
    {
        return $this->render('privacy');
    }

    public function actionProtection()
    {
        return $this->render('protection');
    }

    public function actionSubmitLike() {
        //UserLikeBlog
        $user_id = Yii::$app->user->id;


        $id = Yii::$app->request->post('id');
        $site = Yii::$app->request->post('site');
        $active = Yii::$app->request->post('active');
        if (!empty($id)) {

            if($site == "blog") {
                $blog = Blog::findOne($id);
                if (!empty($blog->blog_root_id)) {
                    $id = $blog->blog_root_id;
                }
                if($active == "like") {
                    $likeBlogStatistics = BlogStatistics::find()->where(['blog_root_id' => $id])->asArray()->one();
                    if (!empty($likeBlogStatistics)) {
                        $like_count = $likeBlogStatistics['like_count'] + 1;
                        Yii::$app->db->createCommand()
                            ->update('blog_statistics', ['like_count' => $like_count], 'blog_root_id = ' . $id)
                            ->execute();
                    }
                    else {
                        $count = new BlogStatistics;
                        $count->blog_root_id = $id;
                        $count->like_count = 1;
                        $count->updated_at = date("Y-m-d H:i:s");
                        $count->save();
                    }

                    $userLikeBlog = UserLikeBlog::find()->where(['user_id' => $user_id, 'blog_id' => $id])->asArray()->one();
                    if (empty($userLikeBlog)) {
                        $model = new UserLikeBlog;
                        $model->user_id = $user_id;
                        $model->blog_id = $id;
                        $model->created_at = date("Y-m-d H:i:s");
                        $model->save();
                    }
                } 
                else {
                    $likeBlogStatistics = BlogStatistics::find()->where(['blog_root_id' => $id])->asArray()->one();
                    if (!empty($likeBlogStatistics)) {
                        $like_count = $likeBlogStatistics['like_count'] - 1;
                        Yii::$app->db->createCommand()
                            ->update('blog_statistics', ['like_count' => $like_count], 'blog_root_id = ' . $id)
                            ->execute();
                    }

                    $userLikeBlog = UserLikeBlog::find()->where(['user_id' => $user_id, 'blog_id' => $id])->asArray()->one();
                    
                    if (!empty($userLikeBlog)) {
                        Yii::$app->db->
                        createCommand()
                        ->delete('user_like_blog', ['id' => $userLikeBlog["id"]])
                        ->execute();
                    }
                    
                }
                
            }
            else if($site == "content") {

                $content = Content::findOne($id);
                if (!empty($content->content_root_id)) {
                    $id = $content->content_root_id;
                }

                if($active == "like") {
                    $likeContentStatistics = ContentStatistics::find()->where(['content_root_id' => $id])->asArray()->one();
                    if (!empty($likeContentStatistics)) {
                        $like_count = $likeContentStatistics['like_count'] + 1;
                        Yii::$app->db->createCommand()
                            ->update('content_statistics', ['like_count' => $like_count], 'content_root_id = ' . $id)
                            ->execute();
                    }
                    else {
                        $count = new ContentStatistics;
                        $count->content_root_id = $id;
                        $count->like_count = 1;
                        $count->updated_at = date("Y-m-d H:i:s");
                        $count->save();
                    }

                    $userLikeContent = UserLikeContent::find()->where(['user_id' => $user_id, 'content_id' => $id])->asArray()->one();
                    if (empty($userLikeContent)) {
                        $model = new UserLikeContent;
                        $model->user_id = $user_id;
                        $model->content_id = $id;
                        $model->created_at = date("Y-m-d H:i:s");
                        $model->save();
                    }
                    print_r($active);
                } 
                else {
                    $likeBlogStatistics = ContentStatistics::find()->where(['content_root_id' => $id])->asArray()->one();
                    if (!empty($likeBlogStatistics)) {
                        $like_count = $likeBlogStatistics['like_count'] - 1;
                        Yii::$app->db->createCommand()
                            ->update('content_statistics', ['like_count' => $like_count], 'content_root_id = ' . $id)
                            ->execute();
                    }

                    $userLikeContent = UserLikeContent::find()->where(['user_id' => $user_id, 'content_id' => $id])->asArray()->one();
                    
                    if (!empty($userLikeContent)) {
                        Yii::$app->db->
                        createCommand()
                        ->delete('user_like_content', ['id' => $userLikeContent["id"]])
                        ->execute();
                    }
                    
                }
            }
        }
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if ($user = $model->verifyEmail()) {
            if (Yii::$app->user->login($user)) {
                Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
                return $this->goHome();
            }
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }

    public function actionUpload()
    {
        $uploadedFile = UploadedFile::getInstanceByName('upload');
        $mime = \yii\helpers\FileHelper::getMimeType($uploadedFile->tempName);
        $file = time()."_".$uploadedFile->name;

        $user_id = Yii::$app->user->getId();

        $url = Yii::$app->urlManager->createAbsoluteUrl('/uploads/'.$file);
        $uploadPath = Yii::getAlias('@webroot').'/uploads/'.$file;

        if (!is_dir(Yii::getAlias('@webroot').'/uploads/')) { //ถ้ายังไม่มี folder ให้สร้าง folder ตาม user id
            mkdir(Yii::getAlias('@webroot').'/uploads/');
        }

        //ตรวจสอบ
        if ($uploadedFile==null)
        {
            $message = "ไม่มีไฟล์ที่ Upload";
        }
        else if ($uploadedFile->size == 0)
        {
            $message = "ไฟล์มีขนาด 0";
        }
        else if ($mime!="image/jpeg" && $mime!="image/png" && $mime != "image/gif")
        {
            $message = "รูปภาพควรเป็น JPG หรือ PNG";
        }
        else if ($uploadedFile->tempName==null)
        {
            $message = "มีข้อผิดพลาด";
        }
        else {
            $message = "";
            $move = $uploadedFile->saveAs($uploadPath);
            if(!$move)
            {
                $message = "ไม่สามารถนำไฟล์ไปไว้ใน Folder ได้กรุณาตรวจสอบ Permission Read/Write/Modify";
            }
        }
        $funcNum = $_GET['CKEditorFuncNum'] ;
        echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";
    }

}
