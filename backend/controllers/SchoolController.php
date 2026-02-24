<?php

namespace backend\controllers;

use Yii;
use backend\models\School;
use backend\models\SchoolSearch;
use backend\models\StudentTeacherSearch;
use backend\models\UserSchool;
use backend\models\UserSchoolSeacrh;
use backend\models\StudentSchoolSearch;
use backend\models\Users;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\BackendHelper;
use backend\models\StudentTeacher;

/**
 * SchoolController implements the CRUD actions for School model.
 */
class SchoolController extends Controller
{
    /**
     * {@inheritdoc}
     */
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
                        'actions' => ['index', 'view', 'create', 'update', 'delete', 'teacher-student', 'teacher', 'export'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) 
                        {
                            switch($action->id){
                                case 'index':
                                case 'export':
                                    return PermissionAccess::BackendAccess('school_list', 'controller');
                                break;

                                case 'create':
                                    return PermissionAccess::BackendAccess('school_create', 'controller');
                                break;

                                case 'update':
                                case 'teacher-student':
                                    return PermissionAccess::BackendAccess('school_update', 'controller');
                                break;

                                case 'view':
                                case 'teacher':
                                    return PermissionAccess::BackendAccess('school_view', 'controller');
                                break;

                                case 'delete':
                                    return PermissionAccess::BackendAccess('school_delete', 'controller');
                                break;

                                default:
                                    return false;
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

    /**
     * Lists all School models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SchoolSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single School model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {

        $searchModelTeacher = new UserSchoolSeacrh();
        $searchModelTeacher->school_id = $id;
        $searchModelTeacher->roleId = 3;
        $dataProviderTeacher = $searchModelTeacher->search(Yii::$app->request->queryParams);

        $searchModelStudent = new StudentSchoolSearch();
        $searchModelStudent->school_id = $id;
        $searchModelStudent->roleId = 4;
        $dataProviderStudent = $searchModelStudent->search(Yii::$app->request->queryParams);

        return $this->render('view', [
            'model' => $this->findModel($id),

            'searchModelTeacher' => $searchModelTeacher,
            'dataProviderTeacher' => $dataProviderTeacher,

            'searchModelStudent' => $searchModelStudent,
            'dataProviderStudent' => $dataProviderStudent,

        ]);
    }

    /**
     * Creates a new School model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new School();
        $modelTeacher = [];
        $modelStudent = [];
        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $post = Yii::$app->request->post();
                // print '<pre>';
                // print_r($post);
                // print '</pre>';
                // exit();

                $model->created_at  = date('Y-m-d H:i:s');
                $model->updated_at  = date('Y-m-d H:i:s');
                

                if ($model->save()) {
                    if (isset($post['UserSchool'])) {
                        foreach ($post['UserSchool'] as $indexRef => $person) {
                            $dataName= $person['teacherName'];
                            if (!empty($dataName)) {
                                $dataPersonNew = Users::find()
                                    ->where(['=', 'email', $dataName])
                                    ->one();
                            }

                            $dataStudentName= $person['studentName'];
                            if (!empty($dataStudentName)) {
                                $dataStudentNew = Users::find()
                                    ->where(['=', 'email', $dataStudentName])
                                    ->one();
                            }

                            // print "<pre>";
                            // print_r($dataPersonNew);
                            // print "</pre>";
                            // exit;

                            if (!empty($dataPersonNew)) {
                                $modelsTeacherCheck = UserSchool::find()->where(['user_id' => $dataPersonNew['id']])->one();

                                if (empty($modelsTeacherCheck)) {
                                    $UserSchoolNew = new UserSchool();
                                    $UserSchoolNew->user_id = $dataPersonNew['id'];
                                    $UserSchoolNew->school_id  = $model->id;
                                    $UserSchoolNew->created_at  = date('Y-m-d H:i:s');
                                    $UserSchoolNew->updated_at  = date('Y-m-d H:i:s');
                                    $UserSchoolNew->save();
                                }
                            }

                            if (!empty($dataStudentNew)) {
                                $modelsStudentCheck = UserSchool::find()->where(['user_id' => $dataStudentNew['id']])->one();

                                if (empty($modelsStudentCheck)) {
                                    $UserSchoolNew = new UserSchool();
                                    $UserSchoolNew->user_id = $dataStudentNew['id'];
                                    $UserSchoolNew->school_id  = $model->id;
                                    $UserSchoolNew->created_at  = date('Y-m-d H:i:s');
                                    $UserSchoolNew->updated_at  = date('Y-m-d H:i:s');
                                    $UserSchoolNew->save();
                                }
                            }
                        }
                    }
                
                    $transaction->commit();

                    BackendHelper::saveUserLog('school', Yii::$app->user->identity->id, $model->id, 'create school', 'เพิ่มข้อมูลโรงเรียน');

                    return $this->redirect(['view', 'id' => $model->id]);

                }


            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->render('create', [
            'model' => $model,
            'modelTeacher' => (empty($modelTeacher)) ? [new UserSchool] : $modelTeacher,
            'modelStudent' => (empty($modelStudent)) ? [new UserSchool] : $modelStudent,
        ]);
    }

    /**
     * Updates an existing School model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);


        $modelTeacher = [];
        $oldTeacherIDs = [];
        $currentTeacherId = [];

        $modelStudent = [];
        $oldStudentIDs = [];
        $currentStudentId = [];

        $schoolTeacher = UserSchool::find()
            ->select(['user_school.*'])
            ->leftJoin('user_role', 'user_role.user_id = user_school.user_id')
            ->where([
                'user_school.school_id' => $id,
                'user_role.role_id' => 3,
                ])
            ->all();
        foreach ($schoolTeacher  as $value) {
            $dataTeacher = Users::find()->where(['id' => $value->user_id])->one();
            if (!empty($dataTeacher->email)) {
                $value->teacherName = $dataTeacher->email;
                $modelTeacher[] = $value;
                $oldTeacherIDs[] = $value->id;
            }
        }

        $schoolStudent = UserSchool::find()
            ->select(['user_school.*'])
            ->leftJoin('user_role', 'user_role.user_id = user_school.user_id')
            ->where([
                'user_school.school_id' => $id,
                'user_role.role_id' => 4,
                ])
            ->all();
        foreach ($schoolStudent  as $value) {
            $dataStudent = Users::find()->where(['id' => $value->user_id])->one();
            if (!empty($dataStudent->email)) {
                $value->studentName = $dataStudent->email;
                $modelStudent[] = $value;
                $oldStudentIDs[] = $value->id;
            }
        }

        //    print '<pre>';
        //     print_r($modelStudent);
        //     print '</pre>';
        //     exit();

        if ($model->load(Yii::$app->request->post())) {
            $transaction = \Yii::$app->db->beginTransaction();
            try {
                $post = Yii::$app->request->post();

                // print '<pre>';
                // print_r($post);
                // print '</pre>';
                // exit();

                $model->updated_at  = date('Y-m-d H:i:s');

                if ($model->save()) {
                    if (isset($post['UserSchool'])) {
                        foreach ($post['UserSchool'] as $indexRef => $person) {
                            if (!empty($person['teacherName'])) {
                                $dataName= $person['teacherName'];
                                $dataPersonNew = Users::find()
                                    ->where(['=', 'email', $dataName])
                                    ->one();
                            }

                            
                            if (!empty($person['studentName'])) {
                                $dataStudentName= $person['studentName'];
                                $dataStudentNew = Users::find()
                                    ->where(['=', 'email', $dataStudentName])
                                    ->one();
                            }

                            // print "<pre>";
                            // print_r($dataPersonNew);
                            // print "</pre>";
                            // exit;

                            if (!empty($dataPersonNew)) {
                                $modelsTeacherCheck = UserSchool::find()->where(['user_id' => $dataPersonNew['id']])->one();

                                if (empty($modelsTeacherCheck)) {
                                    $UserSchoolNew = new UserSchool();
                                    $UserSchoolNew->user_id = $dataPersonNew['id'];
                                    $UserSchoolNew->school_id  = $model->id;
                                    $UserSchoolNew->created_at  = date('Y-m-d H:i:s');
                                    $UserSchoolNew->updated_at  = date('Y-m-d H:i:s');
                                    $UserSchoolNew->save();
                                } else {
                                    $currentTeacherId[] = $modelsTeacherCheck['id'];
                                }
                            }

                            if (!empty($dataStudentNew)) {
                                $modelsStudentCheck = UserSchool::find()->where(['user_id' => $dataStudentNew['id']])->one();

                                if (empty($modelsStudentCheck)) {
                                    $UserSchoolNew = new UserSchool();
                                    $UserSchoolNew->user_id = $dataStudentNew['id'];
                                    $UserSchoolNew->school_id  = $model->id;
                                    $UserSchoolNew->created_at  = date('Y-m-d H:i:s');
                                    $UserSchoolNew->updated_at  = date('Y-m-d H:i:s');
                                    $UserSchoolNew->save();
                                } else {
                                    $currentStudentId[] = $modelsStudentCheck['id'];
                                }
                            }
                        }
                    }

                    $deletedTeacherIDs = array_diff($oldTeacherIDs, $currentTeacherId);
            
                    if (!empty($deletedTeacherIDs)) {
                        foreach ($deletedTeacherIDs as $value) {
                            \Yii::$app
                                ->db
                                ->createCommand()
                                ->delete('user_school', ['id' => $value])
                                ->execute();
                        }
                    }


                    $deletedStudentIDs = array_diff($oldStudentIDs, $currentStudentId);
            
                    if (!empty($deletedStudentIDs)) {
                        foreach ($deletedStudentIDs as $value) {
                            \Yii::$app
                                ->db
                                ->createCommand()
                                ->delete('user_school', ['id' => $value])
                                ->execute();
                        }
                    }

                    $transaction->commit();

                    BackendHelper::saveUserLog('school', Yii::$app->user->identity->id, $model->id, 'update school', 'แก้ไขข้อมูลโรงเรียน');
                    
                    return $this->redirect(['view', 'id' => $model->id]);
                }

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->render('update', [
            'model' => $model,
            'modelTeacher' => (empty($modelTeacher)) ? [new UserSchool] : $modelTeacher,
            'modelStudent' => (empty($modelStudent)) ? [new UserSchool] : $modelStudent,
        ]);
    }

    public function actionTeacherStudent($id)
    {
        $teacher = Users::find()->joinWith('profile')->where(['user.id' => $id])->one();

        $modelStudent = [];
        $oldStudentIDs = [];
        $currentStudentId = [];

        $teacherStudent = StudentTeacher::find()
            ->where([
                'student_teacher.teacher_id' => $id,
                ])
            ->all();
        foreach ($teacherStudent  as $value) {
            $dataStudent = Users::find()->where(['id' => $value->student_id])->one();
            if (!empty($dataStudent->email)) {
                $value->studentName = $dataStudent->email;
                $modelStudent[] = $value;
                $oldStudentIDs[] = $value->id;
            }
        }


        if(Yii::$app->request->post()){

            $transaction = \Yii::$app->db->beginTransaction();
            try {

                $post = Yii::$app->request->post();


                if (isset($post['StudentTeacher'])) {
                    
                    // print "<pre>";
                    // print_r($post['StudentTeacher']);
                    // print '</pre>';
                    // exit();
                
                    foreach ($post['StudentTeacher'] as $indexRef => $person) {
                        
                        
                        if (!empty($person['studentName'])) {
                            $dataStudentName= $person['studentName'];
                            $dataStudentNew = Users::find()
                                ->where(['=', 'email', $dataStudentName])
                                ->one();
                        }


                        if (!empty($dataStudentNew)) {
                            $modelsStudentCheck = StudentTeacher::find()->where(['student_id' => $dataStudentNew['id'], 'teacher_id' => $id])->one();

                            if (empty($modelsStudentCheck)) {
                                $StudentTeacherNew = new StudentTeacher();
                                $StudentTeacherNew->student_id = $dataStudentNew['id'];
                                $StudentTeacherNew->teacher_id  = $id;
                                $StudentTeacherNew->active  = 1;
                                $StudentTeacherNew->created_at  = date('Y-m-d H:i:s');
                                $StudentTeacherNew->updated_at  = date('Y-m-d H:i:s');
                                $StudentTeacherNew->save();
                            }else{
                                $currentStudentId[] = $modelsStudentCheck['id'];
                            }
                        }
                    }
                }


                $deletedStudentIDs = array_diff($oldStudentIDs, $currentStudentId);

                // print "<pre>";
                // print_r($oldStudentIDs);
                // print '</pre>';

                // print "<pre>";
                // print_r($currentStudentId);
                // print '</pre>';

                // print "<pre>";
                // print_r($deletedStudentIDs);
                // print '</pre>';
                // exit();

        
                if (!empty($deletedStudentIDs)) {
                    foreach ($deletedStudentIDs as $value) {
                        \Yii::$app
                            ->db
                            ->createCommand()
                            ->delete('student_teacher', ['id' => $value])
                            ->execute();
                    }
                }


                $transaction->commit();
                return $this->redirect(['teacher', 'id' => $id]);

            } catch (\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }

        }

        $school = (new \yii\db\Query())
            ->select(['user_school.*', 'school.name'])
            ->from('user_school')
            ->leftJoin('school', 'school.id = user_school.school_id')
            ->where([
                'user_school.user_id' => $id
                ])
            ->one();

           

        return $this->render('teacher', [
            'model' => $teacher,
            'school' => $school,
            'modelStudent' => (empty($modelStudent)) ? [new StudentTeacher] : $modelStudent,
        ]);

    }


    public function actionTeacher($id)
    {
        $teacher = Users::find()
                        ->joinWith('profile')
                        ->where(['user.id' => $id])
                        ->one();
        
        $searchModel = new StudentTeacherSearch();
        $searchModel->teacher_id = $id; 
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('teacher-view', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'teacher' => $teacher,
        ]);

    }



    /**
     * Deletes an existing School model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {

        \Yii::$app
        ->db
        ->createCommand()
        ->delete('user_school', ['school_id' => $id])
        ->execute();

        $model = $this->findModel($id);
        if (!empty($model)) {
            BackendHelper::saveUserLog('school', Yii::$app->user->identity->id, $id, 'delete school', 'ลบข้อมูลโรงเรียน'.$model->name);
            $model->delete();
        }

        return $this->redirect(['index']);
    }

    public function actionExport(){

        $query = School::find()->select(['school.*', 'province.name_th as province_name', 'district.name_th as district_name', 'subdistrict.name_th as subdistrict_name']);
        $query->leftjoin('province', 'school.province_id = province.id');
        $query->leftjoin('district', 'school.district_id = district.id');
        $query->leftjoin('subdistrict', 'school.subdistrict_id = subdistrict.id');


        if(!empty($_GET['name'])){
            $query->andFilterWhere(['like', 'school.name', $_GET['name'] ]);
        }

        if(!empty($_GET['email'])){
            $query->andFilterWhere(['like', 'school.email', $_GET['email'] ]);
        }

        if(!empty($_GET['phone'])){
            $query->andFilterWhere(['like', 'school.phone', $_GET['phone'] ]);
        }

        if(!empty($_GET['address'])){
            $query->andFilterWhere(['like', 'school.address', $_GET['address'] ]);
        }


        if(!empty($_GET['updated_at'])){

            $dateRang = explode('to', $_GET['updated_at']);
            if (!empty($dateRang)) {
                if (count($dateRang) == 2) {

                    $dateStart = trim($dateRang[0]);
                    $dateEnd = trim($dateRang[1]);
                    if ($dateStart != $dateEnd) {
                        $dateEnd = date('Y-m-d', strtotime($dateEnd . ' +1 day'));

                        $query->andFilterWhere(['between', 'school.updated_at', $dateStart, $dateEnd]);

                    } else {
                        $query->andFilterWhere(['like', 'school.updated_at', $dateStart]);
                    }
                }
            }else{
                $query->andFilterWhere(['like', 'school.updated_at', $_GET['updated_at'] ]);
            }
        }

        if(!empty($_GET['updated_at'])){
            $query->andFilterWhere(['like', 'school.updated_at', $_GET['updated_at'] ]);
        }
        
        

        if(!empty($_GET['sort'])){
            switch($_GET['sort']){
                case 'name':
                    $query->orderBy('school.name ASC');
                break;
                case '-name':
                    $query->orderBy('school.name DESC');
                break;

                case 'phone':
                    $query->orderBy('school.phone ASC');
                break;
                case '-phone':
                    $query->orderBy('school.phone DESC');
                break;

                case 'email':
                    $query->orderBy('school.email ASC');
                break;
                case '-email':
                    $query->orderBy('school.email DESC');
                break;

                case 'address':
                    $query->orderBy('school.address ASC');
                break;
                case '-address':
                    $query->orderBy('school.address DESC');
                break;

                case 'updated_at':
                    $query->orderBy('school.updated_at ASC');
                break;
                case '-updated_at':
                    $query->orderBy('school.updated_at DESC');
                break;

                default:
                    $query->orderBy('school.id DESC');
                break;
            }
        }

        //$query->groupBy('email');

        $result = $query->asArray()->all();

        // print '<pre>';
        // print_r($result);
        // print '</pre>';
        // exit();

        
        ob_start();
        header_remove();
        ob_end_clean();

        // path to admin/
        $this_dir = dirname(__FILE__);

        // admin's parent dir path can be represented by admin/..
        $parent_dir = realpath($this_dir . '/..');

        // concatenate the target path from the parent dir path
        // open the file
        //$objWrite = fopen($target_path, 'wb') or die("can't open file");

    
        //fwrite($objWrite, pack("CCC",0xef,0xbb,0xbf)); 

        $index = 1;

   

        $dataArray[0] = ["Name", "Phone", "Email", "Address", "Province", "District", "Subdistrict", 'Createad At', 'Updated At'];

        foreach ($result as $key => $value) {
            $dataArray[$index] = [ $value['name'], $value['phone'], $value['email'], $value['address'], $value['province_name'], $value['district_name'], $value['subdistrict_name'], $value['created_at'], $value['updated_at'] ];
            $index++;
        
        }

      
        

        //fclose($objWrite);


        $file_export_name = "School_".date('Ymd_His');


        $this->export_data_to_csv($dataArray, $file_export_name);


        // if (file_exists($target_path)) {

        //     Yii::$app->response->sendFile($target_path, $file_export_name );

        // }   
        
    }

    public static function export_data_to_csv($data, $filename = 'export', $delimiter = ',', $enclosure = '"')
    {
        // Tells to the browser that a file is returned, with its name : $filename.csv
        header("Content-disposition: attachment; filename=$filename.csv");
        // Tells to the browser that the content is a csv file
        header("Content-Type: text/csv");

        // I open PHP memory as a file
        $fp = fopen("php://output", 'w');

        // Insert the UTF-8 BOM in the file
        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        // I add the array keys as CSV headers
        // fputcsv($fp, array_keys($data[0]), $delimiter, $enclosure);

        // Add all the data in the file
        foreach ($data as $fields) {
            fputcsv($fp, $fields, $delimiter, $enclosure);
        }

        // Close the file
        fclose($fp);

        // Stop the script
        die();
    }

    /**
     * Finds the School model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return School the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = School::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
