<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use kartik\date\DatePicker;
use common\components\Upload;
/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการแบนเนอร์';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="banner-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="table-responsive">    

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions'   => function ($model, $key, $index, $grid) {
            return ['data-id' => $model->id];
        },
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],

            'id',
            'slug_url',
            [
                'format'=>'raw',
                'attribute'=>'picture_path',
                'filter'=>false,
                'value'=>function($model){
                    return Upload::readfilePictureNoPermission('banner',$model->picture_path);
                }
            ],
            // 'active',
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
            //'updated_at',

            [
                'class' => 'yii\grid\ActionColumn',
                'template'=>' {view} {update}',
                'header' => '',
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
                    location.href = '" . Url::to(['/banner/']) . "/' + id;
            }
        });

    ");
?>