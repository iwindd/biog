<?php

use kartik\date\DatePicker;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ContentTypeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการการแสดงผล Content';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-type-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่ม Content Type', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions' => function ($model, $key, $index, $grid) {
            return ['data-id' => $model->id];
        },
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'title',
            [
                'attribute' => 'is_visible',
                'format' => 'raw',
                'value' => function ($model) {
                    $options = [1 => 'แสดง', 0 => 'ซ่อน'];
                    return Html::dropDownList('is_visible', $model->is_visible, $options, [
                        'class' => 'form-control visibility-toggle',
                        'data-id' => $model->id,
                    ]);
                },
                'filter' => [1 => 'แสดง', 0 => 'ซ่อน'],
            ],
        ],
    ]); ?>

</div>
<?php
$this->registerJs("
        \$('.visibility-toggle').change(function() {
            var id = \$(this).data('id');
            var is_visible = \$(this).val();
            \$.post('" . Url::to(['/content-type/toggle-visibility']) . "?id=' + id, {is_visible: is_visible}, function(data) {
                // Handle success if needed
            });
        });

        \$('td').click(function (e) {
            var id = \$(this).closest('tr').data('id');
            if(id){
                if(e.target == this)
                    location.href = '" . Url::to(['/content-type/view', 'id' => '']) . "' + id;
            }
        });
    ");
?>
