<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Wallboard */

$this->title = 'แก้ไข Wallboard';
$this->params['breadcrumbs'][] = ['label' => 'Wallboard', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'แก้ไข Wallboard', 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="wallboard-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
