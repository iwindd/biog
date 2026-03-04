<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\components\BackendHelper;
use kartik\select2\Select2;
use common\components\Upload;
use kartik\date\DatePicker;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;
use yii\web\JsExpression;
$listUser = \yii\helpers\Url::to(['/api/userslist']);
$listEditUser = \yii\helpers\Url::to(['/api/editslist']);
$listApprovedUser = \yii\helpers\Url::to(['/api/approverlist']);

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ContentExpertSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการข้อมูลภูมิปัญญา/ปราชญ์';
$this->params['breadcrumbs'][] = $this->title;

$url = "";
if(!empty($_GET['ContentExpertSearch'])){
    if(!empty($_GET['ContentExpertSearch']['name'])){
        $url = "?name=".$_GET['ContentExpertSearch']['name'];
    }

    if(!empty($_GET['ContentExpertSearch']['created_by_user_id'])){
        if (!empty($url)) {
            $url = $url."&created_by_user_id=".$_GET['ContentExpertSearch']['created_by_user_id'];
        }else{
            $url = "?created_by_user_id=".$_GET['ContentExpertSearch']['created_by_user_id'];
        }
    }

    if(!empty($_GET['ContentExpertSearch']['updated_by_user_id'])){
        if (!empty($url)) {
            $url = $url."&updated_by_user_id=".$_GET['ContentExpertSearch']['updated_by_user_id'];
        }else{
            $url = "?updated_by_user_id=".$_GET['ContentExpertSearch']['updated_by_user_id'];
        }
    }



    if(!empty($_GET['ContentExpertSearch']['approved_by_user_id'])){
        if (!empty($url)) {
            $url = $url."&approved_by_user_id=".$_GET['ContentExpertSearch']['approved_by_user_id'];
        }else{
            $url = "?approved_by_user_id=".$_GET['ContentExpertSearch']['approved_by_user_id'];
        }
    }

    if(!empty($_GET['ContentExpertSearch']['note'])){
        if (!empty($url)) {
            $url = $url."&note=".$_GET['ContentExpertSearch']['note'];
        }else{
            $url = "?note=".$_GET['ContentExpertSearch']['note'];
        }
    }

    if(!empty($_GET['ContentExpertSearch']['status'])){
        if (!empty($url)) {
            $url = $url."&status=".$_GET['ContentExpertSearch']['status'];
        }else{
            $url = "?status=".$_GET['ContentExpertSearch']['status'];
        }
    }

    if(!empty($_GET['ContentExpertSearch']['updated_at'])){
        if (!empty($url)) {
            $url = $url."&updated_at=".$_GET['ContentExpertSearch']['updated_at'];
        }else{
            $url = "?updated_at=".$_GET['ContentExpertSearch']['updated_at'];
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
<div class="content-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่มข้อมูลภูมิปัญญา/ปราชญ์', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Import Excel', ['import'], ['class' => 'btn btn-primary']) ?>

        <?= Html::a(  Html::img('/images/csv.png', ['class' => 'csv-export']).'Export ข้อมูลภูมิปัญญา/ปราชญ์', ['export'.$url], ['class' => 'btn btn-info export-bakcground f-right ','title' => 'Export Excel']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="table-responsive">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions'   => function ($model, $key, $index, $grid) {
            return ['data-id' => $model->id];
        },
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'format'=>'raw',
                'attribute'=>'picture_path',
                'filter'=>false,
                'value'=>function($model){
                    $image = Upload::readfilePictureNoPermission('content-expert',$model->picture_path);
                    if(empty($image)){
                        return '<img width="100%" src="/admin/images/BIOG_default_expert.png" />';
                    }
                    return $image;
                }
            ],
            'name',

            [
                'label' => 'จำนวนผู้เข้าชม',
                'format'=>'raw',
                'filter'=>false,
                'value'=>function($model){
                    if($model->content_root_id == 0){
                        $model->content_root_id = $model->id;
                    }
                    return  BackendHelper::getPageview($model->content_root_id);
                }
            ],
            //'description:ntext',
            [
                'attribute'=>'created_by_user_id',
                'format' => 'raw',
                'filter'=>Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'created_by_user_id',
                    'initValueText'=> BackendHelper::getName($searchModel->created_by_user_id),
                    'options' => [
                        'placeholder' => '',
                    ],
                    'pluginOptions' => [
                        'minimumInputLength' => 2,
                        'allowClear' => true,
                        'tags' => true,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                        ],
                        'ajax' => [
                            'url' => $listUser,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(taxonomy) { return taxonomy.text; }'),
                        'templateSelection' => new JsExpression('function (taxonomy) { return taxonomy.text; }'),
                    ],

                ]),
                'value'=>function($model){
                    return BackendHelper::getName($model->created_by_user_id);
                }
            ],


            [
                'attribute'=>'updated_by_user_id',
                'format' => 'raw',
                'filter'=>Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'updated_by_user_id',
                    'initValueText'=> BackendHelper::getName($searchModel->updated_by_user_id),
                    'options' => [
                        'placeholder' => '',
                    ],
                    'pluginOptions' => [
                        'minimumInputLength' => 2,
                        'allowClear' => true,
                        'tags' => true,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                        ],
                        'ajax' => [
                            'url' => $listEditUser,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(taxonomy) { return taxonomy.text; }'),
                        'templateSelection' => new JsExpression('function (taxonomy) { return taxonomy.text; }'),
                    ],

                ]),
                'value'=>function($model){
                    return BackendHelper::getName($model->updated_by_user_id);
                }
            ],

            [
                'attribute'=>'approved_by_user_id',
                'format' => 'raw',
                'filter'=>Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'approved_by_user_id',
                    'initValueText'=> BackendHelper::getName($searchModel->approved_by_user_id),
                    'options' => [
                        'placeholder' => '',
                    ],
                    'pluginOptions' => [
                        'minimumInputLength' => 2,
                        'allowClear' => true,
                        'tags' => true,
                        'language' => [
                            'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                        ],
                        'ajax' => [
                            'url' => $listApprovedUser,
                            'dataType' => 'json',
                            'data' => new JsExpression('function(params) { return {q:params.term}; }')
                        ],
                        'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                        'templateResult' => new JsExpression('function(taxonomy) { return taxonomy.text; }'),
                        'templateSelection' => new JsExpression('function (taxonomy) { return taxonomy.text; }'),
                    ],

                ]),
                'value'=>function($model){
                    return BackendHelper::getName($model->approved_by_user_id);
                }
            ],
            [
                'attribute'=>'note',
            ],
            [
                'format' => 'html',
                'attribute' => 'status',
                'filter' => Html::activeDropDownList($searchModel, 'status', array('pending' => 'รอตรวจสอบ', 'approved' => 'อนุมัติแล้ว', 'rejected' => 'ไม่อนุมัติ'), ['class' => 'form-control', 'prompt' => 'ทั้งหมด']),
                'value' => function ($model) {
                    return \backend\components\BackendHelper::getStatusBadge($model->status);
                }
            ],
            [
                'format' => 'html',
                'attribute' => 'is_hidden',
                'filter' => Html::activeDropDownList($searchModel, 'is_hidden', array('0' => 'แสดงผล', '1' => 'ซ่อน'), ['class' => 'form-control', 'prompt' => 'ทั้งหมด']),
                'value' => function ($model) {
                    if ($model->is_hidden == '0') {
                        return "<span class='label label-success'>แสดงผล</span>";
                    } elseif ($model->is_hidden == '1') {
                        return "<span class='label label-warning'>ซ่อน</span>";
                    }
                }
            ],
            [
                'label' => 'วันที่แก้ไขล่าสุด',
                'attribute' => 'updated_at',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'updated_at',
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
                    return $model->updated_at;
                }
            ],


            ['class' => 'yii\grid\ActionColumn'],
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
                    location.href = '" . Url::to(['/content-expert/']) . "/' + id;
            }
        });

    ");
?>