<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\components\BackendHelper;
use kartik\select2\Select2;
use common\components\Upload;
use kartik\date\DatePicker;
use yii\helpers\Url;

use yii\web\JsExpression;
$listUser = \yii\helpers\Url::to(['/api/userslist']);

/* @var $this yii\web\View */
/* @var $searchModel backend\models\WallboardSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Wallboard';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wallboard-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่ม Wallboard', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions'   => function ($model, $key, $index, $grid) {
            return ['data-id' => $model->id];
        },
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            //'id',
            'description:html',

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
            // 'updated_by_user_id',
            // 'active',
            [ 
                'label'=>'วันที่สร้าง',
                'attribute'=>'created_at',
                'filter'=>DatePicker::widget([
                    'model' => $searchModel,
                    'attribute'=>'created_at',
                    'options' => ['placeholder' => 'เลือกวันที่ ...'],
                    'pluginOptions' => [
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'yyyy-mm-dd',
                        'autoclose' => true,
                    ]
                ]),
                'value'=>function($model, $key, $index, $column){
                  return $model->created_at;
                }   
            ],
            //'updated_at',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>

<?php 

    $this->registerJs("

        $('td').click(function (e) {
            var id = $(this).closest('tr').data('id');
            if(id){
                if(e.target == this)
                    location.href = '" . Url::to(['/wallboard/']) . "/' + id;
            }
        });

    ");
?>