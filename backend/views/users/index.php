<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\components\BackendHelper;
use yii\helpers\Url;
use kartik\date\DatePicker;
use kartik\daterange\DateRangePicker;
use backend\models\Role;
use yii\helpers\ArrayHelper;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\UsersSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$pagesize = $dataProvider->pagination->pageSize;// it will give Per Page data. 
$total = $dataProvider->totalCount; //total records // 15 
$totalPage =  (int) (($total + $pagesize - 1) / $pagesize); 
$currentPage = !empty($_GET['page'])?$_GET['page']:1;

$this->title = 'จัดการผู้ใช้งาน';
$this->params['breadcrumbs'][] = "จัดการผู้ใช้งาน";

$confirmed_at = array('' => "ทั้งหมด", '1' => 'ยืนยัน', '2' => 'ยังไม่ได้ยืนยัน');

$blocked_at = array('' => "ท้ังหมด", '1' => 'บล็อค', '2' => 'ใช้งานปกติ');
// print "<pre>";
// print_r($_GET['UsersSearch']);
// print "</pre>";
// exit();
$role = Role::find()->all();


$url = "";
if(!empty($_GET['UsersSearch'])){
    if(!empty($_GET['UsersSearch']['fullname'])){
        $url = "?fullname=".$_GET['UsersSearch']['fullname'];
    }

    if(!empty($_GET['UsersSearch']['email'])){
        if (!empty($url)) {
            $url = $url."&email=".$_GET['UsersSearch']['email'];
        }else{
            $url = "?email=".$_GET['UsersSearch']['email'];
        }
    }

    if(!empty($_GET['UsersSearch']['role_id'])){
        if (!empty($url)) {
            $url = $url."&role_id=".$_GET['UsersSearch']['role_id'];
        }else{
            $url = "?role_id=".$_GET['UsersSearch']['role_id'];
        }
    }



    if(!empty($_GET['UsersSearch']['created_at'])){
        if (!empty($url)) {
            $url = $url."&created_at=".$_GET['UsersSearch']['created_at'];
        }else{
            $url = "?created_at=".$_GET['UsersSearch']['created_at'];
        }
    }

    if(!empty($_GET['UsersSearch']['updated_at'])){
        if (!empty($url)) {
            $url = $url."&updated_at=".$_GET['UsersSearch']['updated_at'];
        }else{
            $url = "?updated_at=".$_GET['UsersSearch']['updated_at'];
        }
    }

    if(!empty($_GET['UsersSearch']['schoolName'])){
        if (!empty($url)) {
            $url = $url."&school_name=".$_GET['UsersSearch']['schoolName'];
        }else{
            $url = "?school_name=".$_GET['UsersSearch']['schoolName'];
        }
    }

    if(!empty($_GET['UsersSearch']['provinceName'])){
        if (!empty($url)) {
            $url = $url."&province_name=".$_GET['UsersSearch']['provinceName'];
        }else{
            $url = "?province_name=".$_GET['UsersSearch']['provinceName'];
        }
    }
    
}

if (!empty($_GET['sort'])) {
    if (!empty($url)) {
        $url = $url."&sort=".$_GET['sort'];
    } else {
        $url = "?sort=".$_GET['sort'];
    }
}

?>
<div class="users-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่มผู้ใช้งาน', ['create'], ['class' => 'btn btn-success','title' => 'เพิ่มผู้ใช้งาน']) ?>

        <?= Html::a(  Html::img('/images/csv.png', ['class' => 'csv-export']).'Export CSV', ['export'.$url], ['class' => 'btn btn-info export-bakcground f-right ','title' => 'Export CSV']) ?>
    </p>
    <div class="table-responsive">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout'=>'<div class="col-md-12 p0">Page Size : '.Html::activeDropDownList($searchModel, 'PageSize', [10 => 10, 20 => 20, 50 => 50, 100 => 100],['id'=>'PageSize'])." </div> <div class='p0'><div class='col-md-6'> {summary}</div><div class='col-md-6 text-right'> <p class='text-right'> Page $currentPage of $totalPage </p></div></div> {items}<br/>{pager}",
        'filterSelector' => '#PageSize',
        
        'rowOptions'   => function ($model, $key, $index, $grid) {
            return ['data-id' => $model->id];
        },
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'id',
            //'username',

             [
                'class' => 'yii\grid\DataColumn',
                'attribute'=>'fullname',
                'label' => 'ชื่อ-นามสกุล',
                'value'=>function($model, $key, $index, $column){
                    return BackendHelper::getName($model['id']);
                },
                'format'=>'html'
            ],

            [
                'label'=>'อีเมล',
                'attribute'=>'email',
                'format'=>'html',
                'value'=>function($model, $key, $index, $column){
                  return $model->email;
                }
            ],

            [
                'class' => 'yii\grid\DataColumn',
                'attribute'=>'schoolName',
                'label' => 'โรงเรียน',
                'value'=>function($model, $key, $index, $column){
                    return BackendHelper::getSchoolName($model['id']);
                },
                'format'=>'html'
            ],


            [
                'class' => 'yii\grid\DataColumn',
                'attribute'=>'provinceName',
                'label' => 'จังหวัด',
                'value'=>function($model, $key, $index, $column){
                    return BackendHelper::getSchoolProvinceName($model['id']);
                },
                'format'=>'html'
            ],


            //  [
            //     'label'  => 'บทบาท',
            //     'format' => 'html',
            //     'filter'=>Html::activeDropDownList($searchModel, 'role', array('admin'=> 'TK Admin', 'editor' => 'Editor', 'learncenter'=>'เจ้าหน้าที่ชุมชน'),['class'=>'form-control','prompt' => 'ทั้งหมด']),
            //     'value'  =>  function ($model) {
            //                     return BackendHelper::getRoleName($model->role);
            //                 },
            //     'attribute'=>'role',
            // ],

            //'registration_ip',
            [
                'format'=>'html',
                'label' => 'Role',
                'filter'=>Html::activeDropDownList($searchModel, 'role_id', 
               ArrayHelper::map($role, 'id', 'name'),['class'=>'form-control','prompt' => 'ทั้งหมด']),
                'attribute' => 'role_id',
                'value'=>function($model){
                    return BackendHelper::getRoleName($model->id);
                }
            ],
            [
                'label' => 'วันที่สร้าง',
                'attribute' => 'created_at',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'created_at',
                    'convertFormat'=>true,
                    //'useWithAddon'=>true,
                    'pluginOptions'=>[
                        'locale'=>[
                            'format'=>'Y-m-d',
                            'separator'=>' to ',
                        ],
                        'opens'=>'left'
                    ]
                ]),
                'value' => function ($model, $key, $index, $column) {
                    return $model->created_at;
                }
            ],

            // [
            //     'label' => 'วันที่แก้ไขล่าสุด',
            //     'attribute' => 'updated_at',
            //     'filter' => DatePicker::widget([
            //         'model' => $searchModel,
            //         'attribute' => 'updated_at',
            //         'options' => ['placeholder' => 'เลือกวันที่ ...'],
            //         'pluginOptions' => [
            //             'todayHighlight' => true,
            //             'todayBtn' => true,
            //             'format' => 'yyyy-mm-dd',
            //             'autoclose' => true,
            //         ]
            //     ]),
            //     'value' => function ($model, $key, $index, $column) {
            //         return $model->updated_at;
            //     }
            // ],
            //'flags',
            //'last_login_at',

            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>' {view} {update} {delete}',
                'header' => '',
                'contentOptions' => ['class' => 'text-center action'],
                'buttons'=>[
                    
                    'view' => function($url,$model,$key){
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i>',['users/view','id'=>$model['id']],[ 'aria-label'=> "View",'title'=> "View",]);
                    },

                    'update' => function($url,$model,$key){
              
                        return Html::a('<i class="glyphicon glyphicon-pencil"></i>',['users/update','id'=>$model['id']],[ 'aria-label'=> "Update",'title'=> "Update",]);
                        
                    },

                    'delete' => function($url, $model){
         
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['users/delete', 'id' => $model['id']], [
                            'class' => '',
                            'aria-label'=> "Delete",
                            'title'=> "Delete",
                            'data' => [
                                'confirm' => 'ต้องการลบผู้ใช้คนนี้ใช่หรือไม่',
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

<?php 

    $this->registerJs("

        $('td').click(function (e) {
            var id = $(this).closest('tr').data('id');
            if(id){
                if(e.target == this)
                    location.href = '" . Url::to(['/users/']) . "/' + id;
            }
        });

    ");
?>
