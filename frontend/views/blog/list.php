<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use frontend\components\FrontendHelper;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use common\components\Upload;
//$listUser = BackendHelper::getUserList();
/* @var $this yii\web\View */
/* @var $searchModel backend\models\ContentAnimalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = "บล็อกของฉัน";

use frontend\models\Banner;

$banner = Banner::find()->where(['slug_url' => 'Blog'])->one();

$backgroundImage = '/images/banner/Upload_Banner.png';

if (!empty($banner->picture_path)) {
    $backgroundImage = '/files/banner/' . $banner->picture_path;
    $this->registerCss("nav {background-image: url('" . $backgroundImage . "'); }");
} else {
    $this->registerCss("nav {background-image: url('" . $backgroundImage . "'); }");
}
?>
<?php $this->beginBlock('banner') ?>
<div class="section-banner">
    <img src="<?= $backgroundImage; ?>" class="banner">
</div>
<?php $this->endBlock() ?>

<div class="main-breadcrumb" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb pl-0">
            <li class="breadcrumb-item home"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="/content/views/teacher">บล็อกของฉัน</a></li>
        </ol>
    </div>
</div>

<div class="container create-content-container">

    <div class="d-flex flex-column flex-md-row">

        <div class="order-0">
            <div class="menu-sidebar">
                <p class="menu"></p>
                <?php echo $this->render('@frontend/views/layouts/sidebar'); ?>
            </div>
        </div>


        <div class="order-1 flex-fill create-content-form">
            <div class="row mb-1">
                <div class="col-md-3 col-sm-12">
                    <p class="menu mb-4 mr-5">บล็อกของฉัน</p>
                </div>
                <div class="col-md-6 col-sm-6 ">
                    <p class="summary-all mb-4">จำนวนข้อมูลทั้งหมด <?= $dataProvider->getTotalCount() ?> รายการ</p>
                </div>
                <div class="col-md-3 col-sm-6">
                    <?php if (!empty(Yii::$app->user->id)) { ?>
                        <a href="/blog/create">
                            <button class="btn btn-purple float-right"><img src="/images/icon/Create_Blog.svg" class="icon-btn">สร้างบล็อกใหม่</button>
                        </a>
                    <?php }   ?>
                </div>
            </div>

            <div class="table-responsive">
                <?php
                $dataProvider->pagination = ['pageSize' => 20];
                ?>
                <?php //Pjax::begin(['id' => 'pjax-id-explanation']); 
                ?>
                <?php echo GridView::widget([
                    'tableOptions' => [
                        'class' => 'table content-table table-blog-list',
                    ],
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'emptyText' => 'ไม่พบข้อมูล',
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'header' => '',
                            'headerOptions' => [
                                'class' => 'serial'
                            ],
                        ],
                        [
                            'label' => 'ชื่อเรื่อง',
                            'attribute' => 'title',
                            'format' => 'text',
                            'headerOptions' => [
                                'class' => 'title'
                            ],
                            'filterInputOptions' => [
                                'class'       => 'form-control',
                                'placeholder' => 'ใส่คำค้นหาที่นี่'
                            ],
                            'contentOptions' => [
                                'class' => 'created_by_user_id',
                                'style' => 'width:230px;max-width: 230px; overflow-x: hidden; text-overflow: ellipsis;'
                            ],
                        ],
                        [
                            'label' => 'ผู้สร้าง',
                            'attribute' => 'created_fullname',
                            'format' => 'text',
                            'headerOptions' => [
                                'class' => 'created_by_user_id'
                            ],
                            'value' => function ($model) {
                                $value = FrontendHelper::getProfileName($model->created_by_user_id);
                                return $value;
                            },
                            'filterInputOptions' => [
                                'class'       => 'form-control',
                                'placeholder' => 'ใส่คำค้นหาที่นี่'
                            ],
                            'contentOptions' => [
                                'class' => 'created_by_user_id',
                                'style' => 'width:200px;max-width: 200px; overflow-x: hidden; text-overflow: ellipsis;'
                            ],
                        ],
                        [
                            'label' => 'ผู้แก้ไข',
                            'attribute' => 'updated_fullname',
                            'format' => 'text',
                            'headerOptions' => [
                                'class' => 'updated_by_user_id'
                            ],
                            'value' => function ($model) {
                                $value = FrontendHelper::getProfileName($model->updated_by_user_id);
                                return $value;
                            },
                            'filterInputOptions' => [
                                'class'       => 'form-control',
                                'placeholder' => 'ใส่คำค้นหาที่นี่'
                            ],
                            'contentOptions' => [
                                'class' => 'updated_by_user_id',
                                'style' => 'width:200px;max-width: 200px; overflow-x: hidden; text-overflow: ellipsis;'
                            ],
                        ],

                        [
                            'label' => 'วันที่สร้าง',
                            'attribute' => 'created_at',
                            'format' => 'html',
                            'headerOptions' => [
                                'class' => 'created_at'
                            ],
                            'contentOptions' => [
                                'class' => 'created_at',
                            ],
                            'filter' => DatePicker::widget([
                                'model' => $searchModel,
                                'attribute' => 'created_at',
                                'language' => 'th',
                                'options' => ['placeholder' => 'เลือกวันที่ ...'],
                                'pluginOptions' => [
                                    'todayHighlight' => true,
                                    'todayBtn' => true,
                                    'format' => 'yyyy-mm-dd',
                                    'autoclose' => true,
                                ]
                            ]),
                            'value' => function ($model) {
                                $value = FrontendHelper::getDateThai($model->created_at);
                                return $value;
                            },
                            'contentOptions' => [
                                'class' => 'created_at',
                                'style' => 'width:200px;max-width: 200px; overflow-x: hidden; text-overflow: ellipsis;'
                            ],
                        ],
                        [
                            'label' => 'วันที่แก้ไข',
                            'attribute' => 'updated_at',
                            'format' => 'html',
                            'headerOptions' => [
                                'class' => 'updated_at'
                            ],
                            'contentOptions' => [
                                'class' => 'updated_at',
                            ],
                            'filter' => DatePicker::widget([
                                'model' => $searchModel,
                                'attribute' => 'updated_at',
                                'language' => 'th',
                                'options' => ['placeholder' => 'เลือกวันที่ ...'],
                                'pluginOptions' => [
                                    'todayHighlight' => true,
                                    'todayBtn' => true,
                                    'format' => 'yyyy-mm-dd',
                                    'autoclose' => true,
                                ]
                            ]),
                            'value' => function ($model) {
                                $value = FrontendHelper::getDateThai($model->updated_at);
                                return $value;
                            },
                            'contentOptions' => [
                                'class' => 'updated_at',
                                'style' => 'width:200px;max-width: 200px; overflow-x: hidden; text-overflow: ellipsis;'
                            ],
                        ],




                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => ' {view} {update} {delete}',
                            'header' => '',
                            'contentOptions' => [
                                'class' => 'text-center action',
                                'style' => 'width:120px;max-width: 120px;'
                            ],
                            'buttons' => [

                                'view' => function ($url, $model, $key) {

                                    return Html::a('<i class="far fa-eye"></i>', ['blog/view/' . $model['id']], ['aria-label' => "View", 'title' => "View",]);
                                },

                                'update' => function ($url, $model, $key) {
                                    return Html::a('<i class="fas fa-pencil-alt"></i>', ['blog/update/' . $model['id']], ['aria-label' => "Update", 'title' => "Update",]);
                                },
                                'delete' => function ($url, $model, $key) {
                                    return '<span class="delete-blog" onclick="deleteBlog(' . $model['id'] . ')"><i class="fas fa-trash-alt"></i></span>'; //Html::a('<i class="fas fa-trash-alt"></i>',['users/delete','id'=>$model['id']],[ 'aria-label'=> "Delete",'title'=> "Delete",]);

                                }

                                // 'delete' => function($url, $model){

                                //     return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['users/delete', 'id' => $model['id']], [
                                //         'class' => '',
                                //         'aria-label'=> "Delete",
                                //         'title'=> "Delete",
                                //         'data' => [
                                //             'confirm' => 'Are you absolutely sure ? You will lose all the information about this user with this action.',
                                //             'method' => 'post',
                                //         ],
                                //     ]);

                                // }
                            ]

                        ],
                    ],
                ]); ?>
            </div>
        </div>



    </div>

</div>