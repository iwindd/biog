<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use backend\models\RolePermission;
use yii\helpers\Url;


/* @var $this yii\web\View */
/* @var $searchModel backend\models\PermissionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */


$this->title = Yii::t('app', 'จัดการสิทธิ์');
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(Url::base().'/js/permission.js', ['depends' => [\backend\assets\AppAsset::className()]]);

?>
<!-- Page Wrapper -->
<div class="permission-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="table-responsive">

            <?php //Pjax::begin(); ?>
            <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    // 'id',
                    // 'permission_key',
                    'permission_description',

                    [ 
                        'label'=>'Admin',
                        'filter'=> false,
                        'format'=>'raw',
                        'contentOptions' => ['class' => 'checkbox-center'],
                        'headerOptions' => ['class' => 'checkbox-fixed-width'],
                        'value'=>function($model, $key, $index, $column){
                            $data = RolePermission::find()->where(['permission_id' => $model->id, 'role_id' => 1])->one();
                            $check = "";
                            if(!empty($data)){
                                $check = 'checked';
                            }
                            return '<input type="checkbox" '.$check.' class="roleClick" value="1" data-role="1" data-permission="'.$model->id.'" id="cehckrole1'.$model->id.'" > ';
                        }   
                    ],

                    [ 
                        'label'=>'Editor',
                        'filter'=> false,
                        'format'=>'raw',
                        'contentOptions' => ['class' => 'checkbox-center'],
                        'headerOptions' => ['class' => 'checkbox-fixed-width'],
                        'value'=>function($model, $key, $index, $column){
                            $data = RolePermission::find()->where(['permission_id' => $model->id, 'role_id' => 2])->one();
                            $check = "";
                            if(!empty($data)){
                                $check = 'checked';
                            }
                            return '<input type="checkbox" '.$check.' class="roleClick" value="2" data-role="2" data-permission="'.$model->id.'" id="cehckrole2'.$model->id.'" > ';
                        }   
                    ],

                    [ 
                        'label'=>'Teacher',
                        'filter'=> false,
                        'format'=>'raw',
                        'contentOptions' => ['class' => 'checkbox-center'],
                        'headerOptions' => ['class' => 'checkbox-fixed-width'],
                        'value'=>function($model, $key, $index, $column){
                            $data = RolePermission::find()->where(['permission_id' => $model->id, 'role_id' => 3])->one();
                            $check = "";
                            if(!empty($data)){
                                $check = 'checked';
                            }
                            return '<input type="checkbox" '.$check.' class="roleClick" value="3" data-role="3" data-permission="'.$model->id.'" id="cehckrole3'.$model->id.'" > ';
                        }   
                    ],

                    [ 
                        'label'=>'Student',
                        'filter'=> false,
                        'format'=>'raw',
                        'contentOptions' => ['class' => 'checkbox-center'],
                        'headerOptions' => ['class' => 'checkbox-fixed-width'],
                        'value'=>function($model, $key, $index, $column){
                            $data = RolePermission::find()->where(['permission_id' => $model->id, 'role_id' => 4])->one();
                            $check = "";
                            if(!empty($data)){
                                $check = 'checked';
                            }
                            return '<input type="checkbox" '.$check.' class="roleClick" value="4" data-role="4" data-permission="'.$model->id.'" id="cehckrole4'.$model->id.'" > ';
                        }   
                    ],

                    [ 
                        'label'=>'Member',
                        'filter'=> false,
                        'format'=>'raw',
                        'contentOptions' => ['class' => 'checkbox-center'],
                        'headerOptions' => ['class' => 'checkbox-fixed-width'],
                        'value'=>function($model, $key, $index, $column){
                            $data = RolePermission::find()->where(['permission_id' => $model->id, 'role_id' => 5])->one();
                            $check = "";
                            if(!empty($data)){
                                $check = 'checked';
                            }
                            return '<input type="checkbox" '.$check.' class="roleClick" value="5" data-role="5" data-permission="'.$model->id.'" id="cehckrole5'.$model->id.'" > ';
                        }   
                    ],

                    //['class' => 'yii\grid\ActionColumn'],
                ],
            ]); ?>

            <?php //Pjax::end(); ?>

    </div>
    <!-- /Page Content -->

</div>
<!-- /Page Wrapper -->