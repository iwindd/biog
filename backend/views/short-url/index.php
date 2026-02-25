<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการลิงก์ย่อ (Short URLs)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="short-url-index ">

    <h1><?= Html::encode($this->title) ?></h1>
    <p>
        <?= Html::a('สร้างลิงก์ย่อใหม่ ', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <div class="table-responsive">


        <?php Pjax::begin(); ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                'code',
                [
                    'attribute' => 'target_url',
                    'format' => 'url',
                    'contentOptions' => ['style' => 'max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;']
                ],
                [
                    'label' => 'ลิงก์ย่อ (Short Link)',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $url = Yii::$app->params['shortUrlDomain'] . $model->code;
                        return Html::a($url, $url, ['target' => '_blank', 'data-pjax' => 0]);
                    }
                ],
                'created_at:datetime',
                [
                    'attribute' => 'created_by',
                    'value' => function($model) {
                        return $model->createdBy ? $model->createdBy->username : null;
                    }
                ],
                ['class' => 'yii\grid\ActionColumn'],
            ],
        ]); ?>
        <?php Pjax::end(); ?>
    </div>
</div>
