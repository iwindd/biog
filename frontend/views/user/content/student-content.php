<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use frontend\components\FrontendHelper;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use common\components\Upload;
use frontend\components\PermissionAccess;
//$listUser = BackendHelper::getUserList();
/* @var $this yii\web\View */
/* @var $searchModel backend\models\ContentAnimalSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = "การนำเข้าข้อมูลของฉัน";

use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'Manage Content'])->one();

$backgroundImage = '/images/banner/Upload_Banner.png';

if(!empty($banner->picture_path)){
    $backgroundImage = '/files/banner/'.$banner->picture_path;
    $this->registerCss("nav {background-image: url('" . $backgroundImage . "'); }");
}else{
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
            <li class="breadcrumb-item"><a href="/content/views/student">การนำเข้าข้อมูลของฉัน</a></li>
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
            <div class="title-content-student">
                <div>
                    <p class="menu mb-4 mr-5">ข้อมูลของฉัน</p>
                </div>
                <div class="mr-auto">
                    <p class="summary-all mb-4">จำนวนข้อมูลทั้งหมด <?=$dataProvider->getTotalCount()?> รายการ</p>
                </div>

                <?php if(PermissionAccess::FrontendAccess('student_create_content', 'function')): ?>
                    <div class="justify-content-right align-items-right">
                        <p class="btn-import-content">
                            <a href="/content/create/plant" class="btn btn-purple float-right"><img src="/images/icon/Upload_Data.svg" class="icon-btn">การนำเข้าข้อมูลของฉัน</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="table-responsive">
            <?php 
            $dataProvider->pagination = ['pageSize' => 20];
            ?>
            <?php //Pjax::begin(['id' => 'pjax-id-explanation']); ?>
            <?php echo GridView::widget([
                'tableOptions' => [
                    'class' => 'table content-table',
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
                        'label' => 'วันที่นำเข้าข้อมูล',
                        'attribute' => 'created_at',
                        'format' => 'html',
                        'headerOptions' => [
                            'class' => 'created_at'
                        ],
                        'contentOptions' => [
                            'class' => 'created_at',
                        ],
                        'filter'=>DatePicker::widget([
                            'model' => $searchModel,
                            'attribute'=>'created_at',
                            'language' => 'th',
                            'options' => ['placeholder' => 'เลือกวันที่ ...'],
                            'pluginOptions' => [
                                'todayHighlight' => true,
                                'todayBtn' => true,
                                'format' => 'yyyy-mm-dd',
                                'autoclose' => true,
                            ]
                        ]),
                        'value'=>function($model){
                            $value = FrontendHelper::getDateThai($model->created_at);
                            return $value;
                        },
                        // 'value'=>function($model){
                        //     $value = FrontendHelper::getDateThai($model->created_at);
                        //     return $value;
                        // },
                        'contentOptions' => [
                            'class' => 'created_at',
                            'style' => 'width:250px;max-width: 250px; overflow-x: hidden; text-overflow: ellipsis;'
                        ],
                    ],
                    // [
                    //     'label' => 'เจ้าของข้อมูล',
                    //     'attribute' => 'created_by_user_id',
                    //     'format' => 'text',
                    //     'headerOptions' => [
                    //         'class' => 'created_by_user_id'
                    //     ],
                    //     'contentOptions' => [
                    //         'class' => 'created_by_user_id',
                    //         'style' => 'max-width: 300px; overflow-x: hidden; text-overflow: ellipsis;'
                    //     ],
                    //     'value'=>function($model){
                    //         $value = FrontendHelper::getProfileName($model->created_by_user_id);
                    //         return $value;
                    //     },
                    //     'filterInputOptions' => [
                    //         'class'       => 'form-control',
                    //         'placeholder' => 'ใส่คำค้นหาที่นี่'
                    //     ],
                    //     'contentOptions' => [
                    //         'class' => 'created_by_user_id',
                    //         'style' => 'width:200px;max-width: 200px; overflow-x: hidden; text-overflow: ellipsis;'
                    //     ],
                    // ],
                    [
                        'label' => 'ชื่อเรื่อง',
                        'attribute' => 'name',
                        'format' => 'text',
                        'headerOptions' => [
                            'class' => 'name'
                        ],
                        'filterInputOptions' => [
                            'class'       => 'form-control',
                            'placeholder' => 'ใส่คำค้นหาที่นี่'
                        ],
                        'contentOptions' => [
                            'class' => 'created_by_user_id',
                            'style' => 'width:150px;max-width: 150px; overflow-x: hidden; text-overflow: ellipsis;'
                        ],
                    ],
                    [
                        'label' => 'ประเภทข้อมูล',
                        'attribute' => 'type_id',
                        'format' => 'raw',
                        'filter'=>Html::activeDropDownList($searchModel, 'type_id', 
                        array(1=>'พืช',2=> 'สัตว์',3=> 'จุลินทรีย์',4=>'ภูมิปัญญา',5=>'การท่องเที่ยวเชิงนิเวศ',6=>'ผลิตภัณฑ์ชุมชน'),['class'=>'form-control','prompt' => 'ทั้งหมด']),
                        'value'=>function($model, $key, $index, $column){
                            $status = $model->status;
                            if($status == "pending"){
                                return '<i class="fas fa-circle status-pending text-warning"></i> <span class="text-warning">รออนุมัติ</span>';
                            
                            }else if($status == "approved"){
                                return '<i class="fas fa-circle status-approved text-success"></i> <span class="text-success">อนุมัติ</span>';
                            }else{
                                return '<i class="fas fa-circle status-rejected text-danger"></i> <span class="text-danger">ไม่อนุมัติ</span>';
                            }
                          
                        },
                        'headerOptions' => [
                            'class' => 'type_id'
                        ],
                        'contentOptions' => [
                            'class' => 'type_id',
                            'style' => 'width: 280px;max-width: 280px; overflow-x: hidden; text-overflow: ellipsis;'
                        ],
                        'value'=>function($model, $key, $index, $column){
                            $type = $model->type_id;
                            if($type == 1){
                                return '<div class="d-flex"><div class="bg-icon"><img src="/images/icon/S_Plant.svg"></div>
                                <div class="d-flex ml-2"><span class="mt-2">พืช</span></div></div>';
                            }
                            else if($type == 2){
                                return '<div class="d-flex"><div class="bg-icon"><img src="/images/icon/S_Animals.svg" style="
                                margin-bottom: 7px;
                            "></div><div class="d-flex ml-2"><span class="mt-2">สัตว์</span></div></div>';
                            }
                            else if ($type==3){
                                return '<div class="d-flex"><div class="bg-icon"><img src="/images/icon/S_Funji.svg"></div>
                                <div class="d-flex ml-2"><span class="mt-2">จุลินทรีย์</span></div></div>';
                            }
                            else if ($type==4){
                                return '<div class="d-flex"><div class="bg-icon"><img src="/images/icon/S_Expert.svg"></div>
                                <div class="d-flex ml-2"><span class="mt-2">ภูมิปัญญา / ปราชญ์ Expert</span></div></div>';
                            }
                            else if ($type==5){
                                return '<div class="d-flex"><div class="bg-icon"><img src="/images/icon/S_Ecotourism.svg" style="margin-bottom: 7px;"></div>
                                <div class="d-flex ml-2" ><span class="mt-2">การท่องเที่ยวเชิงนิเวศ</span></div></div>';
                                
                            }
                            else{
                                return '<div class="d-flex"><div class="bg-icon"><img src="/images/icon/S_Product.svg"></div>
                                <div class="d-flex ml-2"><span class="mt-2">ผลิตภัณฑ์ชุมชน</span></div></div>';
                                
                            }
                        }
                    ],
                    [
                        'label' => 'สถานะ',
                        'attribute' => 'status',
                        'format' => 'raw',
                        'filter'=>Html::activeDropDownList($searchModel, 'status', 
                        array('pending'=>'รออนุมัติ','approved'=> 'อนุมัติแล้ว','rejected'=> 'ไม่อนุมัติ'),['class'=>'form-control','prompt' => 'ทั้งหมด']),
                        'value'=>function($model, $key, $index, $column){
                            $status = $model->status;
                            if($status == "pending"){
                                return '<i class="fas fa-circle status-pending text-warning"></i> <span class="text-warning">รออนุมัติ</span>';
                            
                            }else if($status == "approved"){
                                return '<i class="fas fa-circle status-approved text-success"></i> <span class="text-success">อนุมัติ</span>';
                            }else{
                                return '<i class="fas fa-circle status-rejected text-danger"></i> <span class="text-danger">ไม่อนุมัติ</span>';
                            }
                          
                        },
                        'headerOptions' => [
                            'class' => 'status'
                        ],
                        'contentOptions' => [
                            'class' => 'status',
                            'style' => 'width:150px;max-width: 150px; overflow-x: hidden; text-overflow: ellipsis;'
                        ],
                    ],
                
                    [
                        'label' => 'หมายเหตุ',
                        'attribute'=>'note',
                        'filter'=>false,
                        'value'=>function($model){
                            return empty($model->note) ? '-' : $model->note;
                        },
                        'headerOptions' => [
                            'class' => 'status'
                        ],
                        'contentOptions' => [
                            'class' => 'status',
                            'style' => 'color: #F02F67;width:150px;max-width: 150px; overflow-x: hidden; text-overflow: ellipsis;'
                        ],
                    ],
                

                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template'=>' {view} {update} {delete}',
                        'header' => '',
                        'contentOptions' => [
                            'class' => 'text-center action',
                            'style' => 'width:120px;max-width: 120px;'
                        ],
                        'buttons'=>[
                            
                            'view' => function($url,$model,$key){
                                $type = $model->type_id;
                                $status = $model->status;
                                if($type == 1) {
                                    $url = "plant";
                                }
                                else if($type == 2) {
                                    $url = "animals";
                                }
                                else if($type == 3) {
                                    $url = "fungi";
                                }
                                else if($type == 4) {
                                    $url = "expert";
                                }
                                else if($type == 5) {
                                    $url = "ecotourism";
                                }
                                else {
                                    $url = "product";
                                }
                                //if($status == "approved" || $status == "pending") {
                                return Html::a('<i class="far fa-eye"></i>',['content-'.$url.'/'.$model['id']],[ 'aria-label'=> "View",'title'=> "View",]);
                                //}
                                //return "";
                            },
        
                            'update' => function($url,$model,$key){
                                $type = $model->type_id;
                                $status = $model->status;
                                if($type == 1) {
                                    $url = "plant";
                                }
                                else if($type == 2) {
                                    $url = "animal";
                                }
                                else if($type == 3) {
                                    $url = "fungi";
                                }
                                else if($type == 4) {
                                    $url = "expert";
                                }
                                else if($type == 5) {
                                    $url = "ecotourism";
                                }
                                else {
                                    $url = "product";
                                }
                               
                                if($status == "approved" || $status == "pending") {
                                    return Html::a('<i class="fas fa-pencil-alt"></i>',['content/update/'.$url.'/'.$model['id']],[ 'aria-label'=> "View",'title'=> "View",]);
                                }
                                return "";
                                
                            },

                            'delete' => function($url, $model){
                                return Html::a('<i class="fas fa-trash-alt"></i>', ['content/delete', 'id' => $model['id']], [
                                    'class' => '',
                                    'aria-label'=> "Delete",
                                    'title'=> "Delete",
                                    'data' => [
                                        'confirm' => 'ต้องการลบเนื้อหานี้ใช่หรือไม่',
                                        'method' => 'post',
                                    ],
                                ]);
                                
                                
                            }
                        ]
        
                    ],
                ],
            ]); ?>
        </div>
        </div>



    </div>

</div>

