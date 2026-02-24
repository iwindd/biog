<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\grid\GridView;
use backend\components\BackendHelper;


/* @var $this yii\web\View */
/* @var $model backend\models\School */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Schools', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="school-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ลบออก', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            'phone',
            'email:email',
            'address',
            [
                'attribute'=>'province_id',
                'value'=>function($model){
                    return BackendHelper::getNameProvince($model->province_id);
                }
            ],
             [
                'attribute'=>'subdistrict_id',
                'value'=>function($model){
                    return BackendHelper::getNameSubdistrict($model->subdistrict_id);
                }
            ],
             [
                'attribute'=>'district_id',
                'value'=>function($model){
                    return BackendHelper::getNameDistrict($model->district_id);
                }
            ],
            [
                'attribute'=>'created_at',
                'value'=>function($model){
                    return $model->created_at;
                }
            ],
            [
                'attribute'=>'updated_at',
                'value'=>function($model){
                    return $model->updated_at;
                }
            ],
        ],
    ]) ?>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="userstudent">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">รายชื่อคุณครู</h3>
                </div>
                <div class="panel-body">
                    <?= GridView::widget([
                        'dataProvider' => $dataProviderTeacher,
                        'filterModel' => $searchModelTeacher,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            [
                                'class' => 'yii\grid\DataColumn',
                                'attribute'=>'fullname',
                                'label' => 'ชื่อ-นามสกุล',
                                'value'=>function($model, $key, $index, $column){
                                    return BackendHelper::getName($model['user_id']);
                                },
                                'format'=>'html'
                            ],

                            [
                                'label'=>'อีเมล',
                                'attribute'=>'email',
                                'format'=>'html',
                                'value'=>function($model, $key, $index, $column){
                                return $model->user->email;
                                }
                            ],


                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template'=>' {view} {update} {delete}',
                                'header' => '',
                                'contentOptions' => ['class' => 'text-center action'],
                                'buttons'=>[
                                    
                                    'view' => function($url,$model,$key){
                                        if (!empty($model['user_id'])) {
                                            return Html::a('<i class="glyphicon glyphicon-eye-open"></i>', ['school/teacher','id'=>$model['user_id']], [ 'aria-label'=> "View",'title'=> "View",]);
                                        }
                                    },

                                    'update' => function($url,$model,$key){
                                        if (!empty($model['user_id'])) {
                                            return Html::a('<i class="glyphicon glyphicon-pencil"></i>', ['school/teacher-student','id'=>$model['user_id']], [ 'aria-label'=> "Update",'title'=> "Update",]);
                                        }
                                    },

                                    'delete' => function($url, $model){
                                        if (!empty($model['user_id'])) {
                                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['school/teacher-delete', 'id' => $model['user_id']], [
                                            'class' => '',
                                            'aria-label'=> "Delete",
                                            'title'=> "Delete",
                                            'data' => [
                                                'confirm' => 'ต้องการลบครูคนนี้ใช่หรือไม่',
                                                'method' => 'post',
                                            ],
                                        ]);
                                        }
                                        
                                    }
                                ]

                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="userstudent">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">รายชื่อนักเรียน</h3>
                </div>
                <div class="panel-body">                    
                    <?= GridView::widget([
                        'dataProvider' => $dataProviderStudent,
                        'filterModel' => $searchModelStudent,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],

                            [
                                'class' => 'yii\grid\DataColumn',
                                'attribute'=>'fullname',
                                'label' => 'ชื่อ-นามสกุล',
                                'value'=>function($model, $key, $index, $column){
                                    return BackendHelper::getName($model['user_id']);
                                },
                                'format'=>'html'
                            ],

                            [
                                'label'=>'อีเมล',
                                'attribute'=>'email',
                                'format'=>'html',
                                'value'=>function($model, $key, $index, $column){
                                return $model->user->email;
                                }
                            ],


                            [
                                'class' => 'yii\grid\ActionColumn',
                                'template'=>'',
                                'header' => '',
                                'contentOptions' => ['class' => 'text-center action'],
                                'buttons'=>[
                                    
                                    'view' => function($url,$model,$key){
                                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i>',['school/student','id'=>$model['user_id']],[ 'aria-label'=> "View",'title'=> "View",]);
                                    },

                                    'delete' => function($url, $model){
                                        if (!empty($model['user_id'])) {
                                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['school/student-delete', 'id' => $model['user_id']], [
                                            'class' => '',
                                            'aria-label'=> "Delete",
                                            'title'=> "Delete",
                                            'data' => [
                                                'confirm' => 'ต้องการลบผู้ใช้คนนี้ใช่หรือไม่',
                                                'method' => 'post',
                                            ],
                                        ]);
                                        }
                                        
                                    }
                                ]

                            ],
                        ],
                    ]); ?>
                </div>
            </div>
        </div>
    </div>

</div>
