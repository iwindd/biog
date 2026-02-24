<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\components\BackendHelper;
use kartik\date\DatePicker;
use yii\helpers\Url;
/* @var $this yii\web\View */
/* @var $searchModel backend\models\NewsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
use common\components\Upload;
$this->title = 'จัดการข่าวสาร';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="news-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่มข้อมูลข่าวสาร', ['create'], ['class' => 'btn btn-success']) ?>
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
                    return Upload::readfilePictureNoPermission('news',$model->picture_path);
                }
            ],
            'title',
            //'description:ntext',
            // [
            //     'format'=>'html',
            //     'attribute' => 'promote',
            //     'filter'=>Html::activeDropDownList($searchModel, 'promote', array(1=> 'Promote',0=>'None'),['class'=>'form-control','prompt' => 'เลือกประเภท']),
            //     'value' => function ($model) {
            //         if($model->promote==1){
            //             return "Promote";
            //         }
            //             return "None";
            //     }
            // ],
            //'post_facebook',
            //'created_by_user_id',
            //'updated_by_user_id',
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


</div>


<?php 

    $this->registerJs("

        $('td').click(function (e) {
            var id = $(this).closest('tr').data('id');
            if(id){
                if(e.target == this)
                    location.href = '" . Url::to(['/news/']) . "/' + id;
            }
        });

    ");
?>
