<?php

namespace frontend\controllers\content;

use Yii;
use yii\web\Controller;
use common\components\_;
use yii\helpers\ArrayHelper;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use frontend\models\content\Type;
use frontend\models\content\Content;
use frontend\models\content\Picture;
use frontend\models\location\Region;
use frontend\models\user\login\User;
use frontend\models\content\Taxonomy;
use frontend\models\location\Zipcode;
use frontend\components\ContentHelper;
use frontend\components\KeywordHelper;
use frontend\models\content\Constants;
use frontend\models\location\District;
use frontend\models\location\Province;
use frontend\components\TaxonomyHelper;
use frontend\models\content\ContentFungi;
use frontend\models\content\ContentPlant;
use frontend\models\location\Subdistrict;
use frontend\models\content\ContentAnimal;
use frontend\models\content\ContentTaxonomy;
use frontend\models\content\ContentTeacherSearch;
use frontend\models\content\ContentStudentSearch;
use frontend\components\PermissionAccess;
use yii\filters\VerbFilter;

class ViewsController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig'=>[
                    'class'=>AccessRule::className()
                ],
                'rules' => [
                    //dashboard_view
                    [
                        'actions' => ['teacher', 'student'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) 
                        {
                            switch($action->id){
                                case 'teacher':
                                    return PermissionAccess::FrontendAccess('approved_content', 'controller');
                                case 'student':
                                    return PermissionAccess::FrontendAccess('student_views_content', 'controller');
                                
                                break;
                            }
                        }
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

    
    public function actionTeacher()
    {
        $searchModel = new ContentTeacherSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('/user/content/teacher-content', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionStudent()
    {
        $searchModel = new ContentStudentSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('/user/content/student-content', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    // public function actionApproveContent() {
    //     return $this->render('/user/content/approve-content');
    // }


}
