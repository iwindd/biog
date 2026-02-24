<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\License */

$this->title = 'แก้ไขสัญญาอนุญาต: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'จัดการสัญญาอนุญาต', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="license-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
      'model' => $model,
    ]) ?>

</div>
