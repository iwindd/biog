<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Banner */

$this->title = 'แก้ไขแบนเนอร์หน้า: ' . $model->slug_url;
$this->params['breadcrumbs'][] = ['label' => 'จัดการแบนเนอร์', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->slug_url, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="banner-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
