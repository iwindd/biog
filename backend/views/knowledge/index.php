<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\components\Upload;
use kartik\date\DatePicker;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\KnowledgeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการองค์ความรู้ออนไลน์';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="knowledge-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่มข้อมูลองค์ความรู้ออนไลน์', ['create'], ['class' => 'btn btn-success']) ?>
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
                'format'=>'html',
                'attribute' => 'type',
                'filter'=>Html::activeDropDownList($searchModel, 'type', array('Infographic'=> 'Infographic','Video'=>'Video'),['class'=>'form-control','prompt' => 'เลือกประเภท']),
                'value' => function ($model) {
                        return $model->type;
                }
            ],
            [
                'format'=>'raw',
                'attribute'=>'picture_path',
                'filter'=>false,
                'value'=>function($model){
                    return Upload::readfilePictureNoPermission('knowledge',$model->picture_path);
                }
            ],
            'title',
            'path',
            //'description:ntext',
            //'created_by_user_id',
            //'updated_by_user_id',
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
                    location.href = '" . Url::to(['/knowledge/']) . "/' + id;
            }
        });

    ");
?>