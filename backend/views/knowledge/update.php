<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Knowledge */

$this->title = 'แก้ไของค์ความรู้ออนไลน์: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'จัดการองค์ความรู้ออนไลน์', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="knowledge-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'case_error' => $case_error,
        'mediaModel' => $mediaModel,
    ]) ?>

</div>
