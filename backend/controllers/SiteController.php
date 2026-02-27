<?php
namespace backend\controllers;

use api\models\Folder;
use backend\models\Community;
use Yii;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

use backend\models\Users;
use backend\models\Blog;
use backend\models\Content;

use backend\models\LoginForm;
use dektrium\user\models\RegistrationForm;
use dektrium\user\models\User;
use dektrium\user\helpers\Password;
use dektrium\user\Module;
use dektrium\user\traits\AjaxValidationTrait;
use dektrium\user\traits\EventTrait;
use backend\components\PermissionAccess;

use backend\components\BackendHelper;


/**
 * Site controller
 */
class SiteController extends Controller
{

    use AjaxValidationTrait;
    use EventTrait;

    Const APPLICATION_API = 'APISAC';
    /**
     * Event is triggered before logging user in.
     * Triggered with \dektrium\user\events\FormEvent.
     */
    const EVENT_BEFORE_LOGIN = 'beforeLogin';

    /**
     * Event is triggered after logging user in.
     * Triggered with \dektrium\user\events\FormEvent.
     */
    const EVENT_AFTER_LOGIN = 'afterLogin';

    /**
     * Event is triggered after creating RegistrationForm class.
     * Triggered with \dektrium\user\events\FormEvent.
     */
    const EVENT_BEFORE_REGISTER = 'beforeRegister';

    /**
     * Event is triggered after successful registration.
     * Triggered with \dektrium\user\events\FormEvent.
     */
    const EVENT_AFTER_REGISTER = 'afterRegister';
    
    /**
     * {@inheritdoc}
     */

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index', 'deleteuser', 'export-pdf'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                // 'actions' => [
                //     'logout' => ['post'],
                // ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        // print "1";
        // exit;
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {

        if(!PermissionAccess::BackendAccess('login_backend', 'function')){
            Yii::$app->user->logout();
            return $this->goHome();
        }
       

        $data = array();
        $index = 0;
        $user = Users::find()
                ->select(['role.name as role_name'])
                ->leftJoin('user_role', 'user_role.user_id = user.id')
                ->leftJoin('role', 'role.id = user_role.role_id')
                ->groupBy('user_role.role_id')
                ->asArray()
                ->all();



        if(!empty($user)){
            foreach($user as $value){
                if (!empty($value['role_name'])) {
                    $countRole = Users::find()
                        ->select(['role.name as role_name'])
                        ->leftJoin('user_role', 'user_role.user_id = user.id')
                        ->leftJoin('role', 'role.id = user_role.role_id')
                        ->where(['role.name' => $value['role_name']])
                        ->andWhere(['blocked_at' => null])
                        ->asArray()
                        ->count();
                    if ($countRole != 0) {
                        $data[$index] = array($value['role_name'], intval($countRole));
                        $index++;
                    }
                }
            }
        }

        // Barchart Data grouping by Content Type statuses (Pending, Approved, Rejected etc if needed, or just total)
        $contentStats = [
            'plant' => Content::find()->where(['active' => 1, 'type_id' => 1, 'status' => 'approved'])->count(),
            'animal' => Content::find()->where(['active' => 1, 'type_id' => 2, 'status' => 'approved'])->count(),
            'micro' => Content::find()->where(['active' => 1, 'type_id' => 3, 'status' => 'approved'])->count(),
            'wisdom' => Content::find()->where(['active' => 1, 'type_id' => 4, 'status' => 'approved'])->count(),
            'eco' => Content::find()->where(['active' => 1, 'type_id' => 5, 'status' => 'approved'])->count(),
            'product' => Content::find()->where(['active' => 1, 'type_id' => 6, 'status' => 'approved'])->count(),
        ];
        
        $chartCategories = ['พืช', 'สัตว์', 'จุลินทรีย์', 'ภูมิปัญญา/ปราชญ์', 'ท่องเที่ยวเชิงนิเวศ', 'ผลิตภัณฑ์ชุมชน'];
        $chartSeriesData = [
            ['y' => (int)$contentStats['plant'], 'color' => '#2ecc71'],   
            ['y' => (int)$contentStats['animal'], 'color' => '#f39c12'],  
            ['y' => (int)$contentStats['micro'], 'color' => '#9b59b6'],   
            ['y' => (int)$contentStats['wisdom'], 'color' => '#3498db'],  
            ['y' => (int)$contentStats['eco'], 'color' => '#00bcd4'],     
            ['y' => (int)$contentStats['product'], 'color' => '#e74c3c'], 
        ];


        return $this->render('index', [
            'data' => $data, 
            'chartCategories' => $chartCategories, 
            'chartSeriesData' => $chartSeriesData
        ]);
       
    }

    public function actionExportPdf()
    {
        if(!PermissionAccess::BackendAccess('login_backend', 'function')){
            Yii::$app->user->logout();
            return $this->goHome();
        }

        // Barchart Data grouping by Content Type statuses (Pending, Approved, Rejected etc if needed, or just total)
        $contentStats = [
            'plant' => Content::find()->where(['active' => 1, 'type_id' => 1, 'status' => 'approved'])->count(),
            'animal' => Content::find()->where(['active' => 1, 'type_id' => 2, 'status' => 'approved'])->count(),
            'micro' => Content::find()->where(['active' => 1, 'type_id' => 3, 'status' => 'approved'])->count(),
            'wisdom' => Content::find()->where(['active' => 1, 'type_id' => 4, 'status' => 'approved'])->count(),
            'eco' => Content::find()->where(['active' => 1, 'type_id' => 5, 'status' => 'approved'])->count(),
            'product' => Content::find()->where(['active' => 1, 'type_id' => 6, 'status' => 'approved'])->count(),
        ];
        
        $chartCategories = ['พืช', 'สัตว์', 'จุลินทรีย์', 'ภูมิปัญญา/ปราชญ์', 'ท่องเที่ยวเชิงนิเวศ', 'ผลิตภัณฑ์ชุมชน'];
        
        $contentList = [];
        $i = 0;
        foreach($chartCategories as $category) {
            $key = array_keys($contentStats)[$i];
            $contentList[] = [
                'category' => $category,
                'count' => (int)$contentStats[$key]
            ];
            $i++;
        }

        // Generate PDF
        $content = $this->renderPartial('export-pdf', [
            'contentList' => $contentList
        ]);

        $pdf = new \kartik\mpdf\Pdf([
            'mode' => \kartik\mpdf\Pdf::MODE_UTF8, 
            'format' => \kartik\mpdf\Pdf::FORMAT_A4, 
            'orientation' => \kartik\mpdf\Pdf::ORIENT_PORTRAIT, 
            'destination' => \kartik\mpdf\Pdf::DEST_BROWSER, 
            'content' => $content,  
            'cssInline' => '
                body { font-family: "Garuda", "Kinnari", sans-serif; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                th { background-color: #f5f5f5; font-weight: bold; }
                .text-center { text-align: center; }
                .text-right { text-align: right; }
                h2 { text-align: center; margin-bottom: 5px; }
                .date-info { text-align: center; color: #666; margin-bottom: 20px; }
            ',
            'options' => ['title' => 'สถิติจำนวนเรื่องตามประเภทเนื้อหา'],
            'methods' => [ 
                'SetHeader'=>['สถิติจำนวนเรื่องตามประเภทเนื้อหา||Generated On: ' . date("r")], 
                'SetFooter'=>['|Page {PAGENO}|'],
            ]
        ]);
        
        return $pdf->render(); 
    }

    function readCSV($csvFile, $array)
    {
        $file_handle = fopen($csvFile, 'r');
        while (!feof($file_handle)) {
            $line_of_text[] = fgetcsv($file_handle, 0, $array['delimiter']);
        }
        fclose($file_handle);
        return $line_of_text;
    }


    public function actionDeleteuser(){

        $csvFileName = "del_user.csv";

        $frontendPath = \yii::getAlias('@backend');
        $frontendPath = str_replace('\\', '/', $frontendPath);
        $csvFile = $frontendPath . '/web/export/' . $csvFileName;

        $data = $this->readCSV($csvFile,array('delimiter' => ','));

        
        // print "<pre>";
        // print_r($data);
        // print '</pre>';
        // exit();

        $index = $_GET['index'];
        $max = $_GET['index']+100;
        if($max > COUNT($data)){
            $max = COUNT($data);
        }
        $userId = array();
        if(!empty($data)){

            for($i = $index; $i <= $max; $i++){
                if (!empty($data[$i][1]) && $data[$i][1] != 'Email' && $data[$i][1] != 'admin@biog.co.th') {
                    $dataUser = Users::find()->where(['or',
                        ['=', 'username', $data[$i][1]],
                        ['=', 'email', $data[$i][1]],
                    ])->one();

                    if (!empty($dataUser)) {
                        $userId[] = $dataUser->id;
                        // $blog = Blog::find()->where(['or',
                        //                     ['=', 'created_by_user_id', $dataUser->id],
                        //                     ['=', 'updated_by_user_id', $dataUser->id],
                        //                 ])->all();

                        // if (!empty($blog)) {
                        //     print "<pre>";
                        //     print_r($blog);
                        //     print '</pre>';
                        //     exit();

                        //     foreach ($blog as $valueBlog) {
                        //         if (!empty($valueBlog['id'])) {
                        //             \Yii::$app
                        //             ->db
                        //             ->createCommand()
                        //             ->delete('blog_file', ['blog_id' => $valueBlog['id']])
                        //             ->execute();
                        //         }
                        //     }
                        // }

                        // //content
                        // $content = Content::find()->where(['or',
                        //                     ['=', 'created_by_user_id', $dataUser->id],
                        //                     ['=', 'updated_by_user_id', $dataUser->id],
                        //                     ['=', 'approved_by_user_id', $dataUser->id],
                        //                 ])->all();
                        // if (!empty($content)) {
                        //     print "<pre>";
                        //     print_r($content);
                        //     print '</pre>';
                        //     exit();
                        // }
                    }
                }
            }
        }
        if ($_GET['type'] == 'blog') {
            if (!empty($userId)) {
                $blog = Blog::find()->where(['or',
                            ['IN', 'created_by_user_id', $userId],
                            ['IN', 'updated_by_user_id', $userId],
                        ])->all();

                print "<pre>";
                print_r($blog);
                print '</pre>';

                print "<pre>";
                print_r('blog --------');
                print '</pre>';
            }
        }

        if ($_GET['type'] == 'content') {
            if (!empty($userId)) {
                //content
                $content = Content::find()->where(['or',
                                    ['IN', 'created_by_user_id', $userId],
                                    ['IN', 'updated_by_user_id', $userId],
                                    ['IN', 'approved_by_user_id', $userId],
                                ])->all();
                if (!empty($content)) {
                    
                    foreach ($content as $valueContent) {
                        if (!empty($valueContent['id'])) {
                            \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('content_animal', ['content_id' => $valueContent['id']])
                            ->execute();

                            \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('content_ecotourism', ['content_id' => $valueContent['id']])
                            ->execute();

                            \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('content_expert', ['content_id' => $valueContent['id']])
                            ->execute();

                            \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('content_fungi', ['content_id' => $valueContent['id']])
                            ->execute();

                            \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('content_plant', ['content_id' => $valueContent['id']])
                            ->execute();

                            \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('content_product', ['content_id' => $valueContent['id']])
                            ->execute();

                            \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('content_statistics', ['content_root_id' => $valueContent['id']])
                            ->execute();

                            \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('content_taxonomy', ['content_id' => $valueContent['id']])
                            ->execute();

                            \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('picture', ['content_id' => $valueContent['id']])
                            ->execute();

                            \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('comment', ['content_root_id' => $valueContent['id']])
                            ->execute();

                            \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('content', ['id' => $valueContent['id']])
                            ->execute();
                         
                        }
                    } 
                    print "<pre>";
                    print_r($content);
                    print '</pre>';
                    //exit();
                }

                print "<pre>";
                print_r('content --------');
                print '</pre>';
            }
        }


        //other
        if (!empty($userId)) {
            \Yii::$app
            ->db
            ->createCommand()
            ->delete('user_school', ['user_id' => $userId])
            ->execute();

            \Yii::$app
            ->db
            ->createCommand()
            ->delete('user_role', ['user_id' => $userId])
            ->execute();

            \Yii::$app
            ->db
            ->createCommand()
            ->delete('comment', ['user_id' => $userId])
            ->execute();

            \Yii::$app
            ->db
            ->createCommand()
            ->delete('blog_comment', ['user_id' => $userId])
            ->execute();

            \Yii::$app
            ->db
            ->createCommand()
            ->delete('blog_comment', ['user_id' => $userId])
            ->execute();

            //final

            \Yii::$app
            ->db
            ->createCommand()
            ->delete('profile', ['user_id' => $userId])
            ->execute();

                \Yii::$app
            ->db
            ->createCommand()
            ->delete('user', ['id' => $userId])
            ->execute();
        }


        print "<pre>";
        print_r($userId);
        print '</pre>';
        exit();

        //table ที่เกี่ยวข้อง
        /*
        blog
            blog_file
            blog_comment
            blog_statistics

        

        content
            content_animal
            content_ecotourism
            comment

        */
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $data = $_POST;
        $error = array();
        $model = \Yii::createObject(LoginForm::className());
        if(!empty($data)){

            // print "<pre>";
            // print_r($data);
            // print "</pre>";
            // exit();
            //ตรวจสอบ username และ password
            if (!empty($data['login-form']['login']) && $data['login-form']['password']) {

                $user = Users::find()->where([
                    'or',
                    ['=', 'email', $data['login-form']['login']],
                    ['=', 'username', $data['login-form']['login']],
                ])->one();

                if (!empty($user)) {

                    $_csrf = Yii::$app->request->csrfToken;
                    $post = array(
                        '_csrf-frontend' => $_csrf,
                        'login-form' => array(
                                'login' => $user['username'],
                                'password' => $data['login-form']['password'],
                                'rememberMe' => 0,
                            )

                    );

                    if (!\Yii::$app->user->isGuest) {
                        $this->goHome();
                    }

                
                    $event = $this->getFormEvent($model);

                    $this->performAjaxValidation($model);

                    $this->trigger(self::EVENT_BEFORE_LOGIN, $event);

                    
                    if ($model->load($post)) {
                        if ($model->login()) {
                            $this->trigger(self::EVENT_AFTER_LOGIN, $event);
                            if (!empty($_SESSION['currentUrl'])) {
                                return $this->redirect($_SESSION['currentUrl']);
                            } else {
                                return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
                            }
                            
                            //return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
                        } else {
                            $error['message'] = $model->getErrors();
                            Yii::$app->getSession()->setFlash('alert-login', [
                                'body'=>yii::t('app', 'Username or Password Invalid.'),
                                'options'=>['class'=>'alert-danger']
                            ]);
                        }
                    }
                }else{
                    $error['message'] = $model->getErrors();
                    Yii::$app->getSession()->setFlash('alert-login', [
                        'body'=>yii::t('app', 'ไม่พบข้อมูลผู้ใช้ในระบบ'),
                        'options'=>['class'=>'alert-danger']
                    ]);
                }
            }else{
                //$error['message'] = Yii::t('app','valiLoginFromEmpty');
                Yii::$app->getSession()->setFlash('alert-login',[
               'body'=>yii::t('app','Username or Password cannot be empty.'),
               'options'=>['class'=>'alert-danger']
                ]);
            }
        }

        return $this->render('login', [
            'model' => $model,
            'error' => $error
        ]);
        
    }

    public function actionErrors()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception !== null) {
            $this->layout = 'main-error';
            return $this->render('error', ['exception' => $exception]);
        }
    }

    /**
     * Logout action.
     *
     * @return string
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
    // code for area
    // $result = UserApi::getSubdistrict();

    // if(!empty($result)){

    //     $bulkInsertArray = array();
    //     $index = 1;
    //     foreach($result->data as $value){

    //         $bulkInsertArray[]=[
    //             'id'=> $index,
    //             'zipcode'=> $value->DISTRICT_CODE,
    //             'sub_district_id'=> $value->DISTRICT_ID,
    //             'created_at'=> date('Y-m-d H:i:s'),
    //             'updated_at'=> date('Y-m-d H:i:s'),
    //         ];

    //         $index++;

    //     }
    // }

    // if(count($bulkInsertArray)>0){
    //     $columnNameArray=['id', 'zipcode', 'sub_district_id', 'created_at', 'updated_at'];
    //     // below line insert all your record and return number of rows inserted
    //     $insertCount = Yii::$app->db->createCommand()
    //                    ->batchInsert(
    //                          'zipcode', $columnNameArray, $bulkInsertArray
    //                      )
    //                    ->execute();
    // }

    // print "<pre>";
    // print_r($result);
    // print "</pre>";
    // exit();
}
