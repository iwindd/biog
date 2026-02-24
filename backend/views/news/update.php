<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\News */

$this->title = 'แก้ไขข้อมูลข่าวสาร: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'จัดการข่าวสาร', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="news-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'case_error' => $case_error,
        'mediaModel' => $mediaModel,
    ]) ?>

</div>
