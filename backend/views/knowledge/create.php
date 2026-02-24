<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Knowledge */

$this->title = 'เพิ่มข้อมูลองค์ความรู้ออนไลน์';
$this->params['breadcrumbs'][] = ['label' => 'จัดการองค์ความรู้ออนไลน์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="knowledge-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'case_error' => $case_error,
    ]) ?>

</div>
