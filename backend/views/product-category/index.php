<?php

use yii\helpers\Html;
use yii\grid\GridView;
use kartik\date\DatePicker;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ProductCategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'หมวดหมู่ผลิตภัณฑ์';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="product-category-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่มหมวดหมู่ผลิตภัณฑ์', ['create'], ['class' => 'btn btn-success']) ?>
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

            // 'id',
            'name',
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

            [ 
                'label'=>'วันที่แก้ไขล่าสุด',
                'attribute'=>'updated_at',
                'filter'=>DatePicker::widget([
                    'model' => $searchModel,
                    'attribute'=>'updated_at',
                    'options' => ['placeholder' => 'เลือกวันที่ ...'],
                    'pluginOptions' => [
                        'todayHighlight' => true,
                        'todayBtn' => true,
                        'format' => 'yyyy-mm-dd',
                        'autoclose' => true,
                    ]
                ]),
                'value'=>function($model, $key, $index, $column){
                  return $model->updated_at;
                }   
            ],
            // 'updated_at',

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
                    location.href = '" . Url::to(['/product-category/']) . "/' + id;
            }
        });

    ");
?>
