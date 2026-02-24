<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Wallboard */

$this->title = 'เพิ่ม Wallboard';
$this->params['breadcrumbs'][] = ['label' => 'Wallboard', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wallboard-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
