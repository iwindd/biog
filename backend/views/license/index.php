<?php

use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\LicenseSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการสัญญาอนุญาต (Licenses)';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="license-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่มสัญญาอนุญาต', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <div class="table-responsive">
    <?= GridView::widget([
      'dataProvider' => $dataProvider,
      'filterModel' => $searchModel,
      'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        'name',
        'description:ntext',
        'url:url',
        ['class' => 'yii\grid\ActionColumn'],
      ],
    ]); ?>
    </div>

</div>
