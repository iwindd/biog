<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\components\BackendHelper;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SchoolSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = "คุณครู: ".$teacher->profile->firstname." ".$teacher->profile->lastname;
$this->params['breadcrumbs'][] = ['label' => 'โรงเรียนทั้งหมด', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="school-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('จัดการนักเรียน', ['/school/teacher-student/'.$teacher->id], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'class' => 'yii\grid\DataColumn',
                'attribute'=>'student_name',
                'label' => 'ชื่อ-นามสกุล',
                'value'=>function($model, $key, $index, $column){
                    return BackendHelper::getName($model['student_id']);
                },
                'format'=>'html'
            ],

            [
                'label'=>'อีเมล',
                'attribute'=>'email',
                'format'=>'html',
                'value'=>function($model, $key, $index, $column){
                  return $model->userStudent->email;
                }
            ],
            //'province_id',
            //'subdistrict_id',
            //'district_id',
            //'created_at',
            //'updated_at',

            // [
            //     'class' => 'yii\grid\ActionColumn',
            //     'template'=>' {view} {update} {delete}',
            //     'header' => '',
            //     'contentOptions' => ['class' => 'text-center action'],
            //     'buttons'=>[
                    
            //         'view' => function($url,$model,$key){
            //             return Html::a('<i class="glyphicon glyphicon-eye-open"></i>',['users/view','id'=>$model['id']],[ 'aria-label'=> "View",'title'=> "View",]);
            //         },

            //         'update' => function($url,$model,$key){
              
            //             return Html::a('<i class="glyphicon glyphicon-pencil"></i>',['users/update','id'=>$model['id']],[ 'aria-label'=> "Update",'title'=> "Update",]);
                        
            //         },

            //         'delete' => function($url, $model){
         
            //             return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['users/delete', 'id' => $model['id']], [
            //                 'class' => '',
            //                 'aria-label'=> "Delete",
            //                 'title'=> "Delete",
            //                 'data' => [
            //                     'confirm' => 'ต้องการลบผู้ใช้คนนี้ใช่หรือไม่',
            //                     'method' => 'post',
            //                 ],
            //             ]);
                        
            //         }
            //     ]

            // ],
        ],
    ]); ?>


</div>
