<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ExpertCategory */

$this->title = 'แก้ไขหมวดหมู่ภูมิปัญญา: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'หมวดหมู่ภูมิปัญญา', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="expert-category-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
