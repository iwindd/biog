<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\License */

$this->title = 'เพิ่มสัญญาอนุญาต';
$this->params['breadcrumbs'][] = ['label' => 'จัดการสัญญาอนุญาต', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="license-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
      'model' => $model,
    ]) ?>

</div>
