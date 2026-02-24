<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Blog */

$this->title = 'แก้ไขบล็อก: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'จัดการบล็อก', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="blog-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'case_error' => $case_error,
        'mediaModel' => $mediaModel,
    ]) ?>

</div>
