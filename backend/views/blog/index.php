<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\components\BackendHelper;
use common\components\Upload;
use yii\helpers\Url;
use kartik\date\DatePicker;
use kartik\select2\Select2;
use kartik\daterange\DateRangePicker;

use yii\web\JsExpression;
$listUser = \yii\helpers\Url::to(['/api/usersall']);

// $listUser = BackendHelper::getUserAllList();

/* @var $this yii\web\View */
/* @var $searchModel backend\models\BlogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'บล็อก';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="blog-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่มบล็อกใหม่', ['create'], ['class' => 'btn btn-success']) ?>
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
                    return Upload::readfilePictureNoPermission('blog',$model->picture_path);
                }
            ],
            'title',

            [
                'label' => 'ชื่อผู้เขียน',
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
            //'description:ntext',
            //'video_url:url',
            //'source_information',
            //'created_by_user_id',
            //'updated_by_user_id',
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

            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>' {view} {update} {delete}',
                'header' => '',
                'contentOptions' => ['class' => 'text-center action'],
                'buttons'=>[
                    
                    'view' => function($url,$model,$key){
                        return Html::a('<i class="glyphicon glyphicon-eye-open"></i>',['blog/view','id'=>$model['id']],[ 'aria-label'=> "View",'title'=> "View",]);
                    },

                    'update' => function($url,$model,$key){
              
                        return Html::a('<i class="glyphicon glyphicon-pencil"></i>',['blog/update','id'=>$model['id']],[ 'aria-label'=> "Update",'title'=> "Update",]);
                        
                    },

                    'delete' => function($url, $model){
         
                        return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['blog/delete', 'id' => $model['id']], [
                            'class' => '',
                            'aria-label'=> "Delete",
                            'title'=> "Delete",
                            'data' => [
                                'confirm' => 'ต้องการลบบล็อกนี้ใช่หรือไม่',
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
                    location.href = '" . Url::to(['/blog/']) . "/' + id;
            }
        });

    ");
?>