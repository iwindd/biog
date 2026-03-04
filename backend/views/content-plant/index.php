<?php

use backend\components\BackendHelper;
use common\components\Upload;
use kartik\daterange\DateRangePicker;
use kartik\select2\Select2;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

$listUser = \yii\helpers\Url::to(['/api/userslist']);
$listEditUser = \yii\helpers\Url::to(['/api/editslist']);
$listApprovedUser = \yii\helpers\Url::to(['/api/approverlist']);

$total = $dataProvider->totalCount;  // total records // 15
$totalPage = ceil($total / 25000);

// print '<pre>';
// print_r($totalPage);
// print '</pre>';
// exit();

use kartik\date\DatePicker;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ContentPlantSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการข้อมูลพืช';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="content-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่มข้อมูลพืช', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Import Excel', ['import'], ['class' => 'btn btn-primary']) ?>

        <?php if ($totalPage > 0):
            for ($i = 1; $i <= $totalPage; $i++): ?>
        <?php

        $url = '';
        if (!empty($_GET['ContentPlantSearch'])) {
            if (!empty($_GET['ContentPlantSearch']['name'])) {
                $url = '?name=' . $_GET['ContentPlantSearch']['name'];
            }

            if (!empty($_GET['ContentPlantSearch']['created_by_user_id'])) {
                if (!empty($url)) {
                    $url = $url . '&created_by_user_id=' . $_GET['ContentPlantSearch']['created_by_user_id'];
                } else {
                    $url = '?created_by_user_id=' . $_GET['ContentPlantSearch']['created_by_user_id'];
                }
            }

            if (!empty($_GET['ContentPlantSearch']['updated_by_user_id'])) {
                if (!empty($url)) {
                    $url = $url . '&updated_by_user_id=' . $_GET['ContentPlantSearch']['updated_by_user_id'];
                } else {
                    $url = '?updated_by_user_id=' . $_GET['ContentPlantSearch']['updated_by_user_id'];
                }
            }

            if (!empty($_GET['ContentPlantSearch']['approved_by_user_id'])) {
                if (!empty($url)) {
                    $url = $url . '&approved_by_user_id=' . $_GET['ContentPlantSearch']['approved_by_user_id'];
                } else {
                    $url = '?approved_by_user_id=' . $_GET['ContentPlantSearch']['approved_by_user_id'];
                }
            }

            if (!empty($_GET['ContentPlantSearch']['note'])) {
                if (!empty($url)) {
                    $url = $url . '&note=' . $_GET['ContentPlantSearch']['note'];
                } else {
                    $url = '?note=' . $_GET['ContentPlantSearch']['note'];
                }
            }

            if (!empty($_GET['ContentPlantSearch']['status'])) {
                if (!empty($url)) {
                    $url = $url . '&status=' . $_GET['ContentPlantSearch']['status'];
                } else {
                    $url = '?status=' . $_GET['ContentPlantSearch']['status'];
                }
            }

            if (!empty($_GET['ContentPlantSearch']['updated_at'])) {
                if (!empty($url)) {
                    $url = $url . '&updated_at=' . $_GET['ContentPlantSearch']['updated_at'];
                } else {
                    $url = '?updated_at=' . $_GET['ContentPlantSearch']['updated_at'];
                }
            }
        }

        if (!empty($url)) {
            $url = $url . '&file=' . $i;
        } else {
            $url = '?file=' . $i;
        }
        ?>
        <?= Html::a(Html::img('/images/csv.png', ['class' => 'csv-export']) . 'Export ข้อมูลพืช ไฟล์ที่ ' . ($i), ['export' . $url], ['class' => 'btn btn-info export-bakcground f-right ', 'title' => 'Export Excel']) ?>
        <?php endfor;
        endif; ?>
    
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="table-responsive">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function ($model, $key, $index, $grid) {
            return ['data-id' => $model->id];
        },
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'format' => 'raw',
                'attribute' => 'picture_path',
                'filter' => false,
                'value' => function ($model) {
                    $image = Upload::readfilePictureNoPermission('content-plant', $model->picture_path);
                    if (empty($image)) {
                        return '<img width="100%" src="/admin/images/BIOG_default_plant.png" />';
                    }
                    return $image;
                }
            ],
            'name',
            [
                'label' => 'จำนวนผู้เข้าชม',
                'format' => 'raw',
                'filter' => false,
                'value' => function ($model) {
                    if ($model->content_root_id == 0) {
                        $model->content_root_id = $model->id;
                    }
                    return BackendHelper::getPageview($model->content_root_id);
                }
            ],
            // 'description:ntext',
            [
                'attribute' => 'created_by_user_id',
                'format' => 'raw',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'created_by_user_id',
                    'initValueText' => BackendHelper::getName($searchModel->created_by_user_id),
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
                'value' => function ($model) {
                    return BackendHelper::getName($model->created_by_user_id);
                }
            ],
            [
                'attribute' => 'updated_by_user_id',
                'format' => 'raw',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'updated_by_user_id',
                    'initValueText' => BackendHelper::getName($searchModel->updated_by_user_id),
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
                'value' => function ($model) {
                    return BackendHelper::getName($model->updated_by_user_id);
                }
            ],
            [
                'attribute' => 'approved_by_user_id',
                'format' => 'raw',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'approved_by_user_id',
                    'initValueText' => BackendHelper::getName($searchModel->approved_by_user_id),
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
                'value' => function ($model) {
                    return BackendHelper::getName($model->approved_by_user_id);
                }
            ],
            [
                'attribute' => 'note',
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
                    'convertFormat' => true,
                    // 'useWithAddon'=>true,
                    'pluginOptions' => [
                        'locale' => [
                            'format' => 'Y-m-d',
                            'separator' => ' to ',
                        ],
                        'opens' => 'left'
                    ]
                ]),
                'value' => function ($model, $key, $index, $column) {
                    return $model->updated_at;
                }
            ],
            // 'other_information:ntext',
            // 'source_information:ntext',
            // 'latitude',
            // 'longitude',
            // 'region_id',
            // 'province_id',
            // 'district_id',
            // 'subdistrict_id',
            // 'zipcode_id',
            // 'approved_by_user_id',
            // 'created_by_user_id',
            // 'updated_by_user_id',
            // 'active',
            // 'created_at',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    </div>


</div>

<?php

$this->registerJs("

        \$('td').click(function (e) {
            var id = \$(this).closest('tr').data('id');
            if(id){
                if(e.target == this)
                    location.href = '" . Url::to(['/content-plant/']) . "/' + id;
            }
        });

    ");
?>
