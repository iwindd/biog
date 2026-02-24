<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\ExpertCategory */

$this->title = 'เพิ่มหมวดหมู่ภูมิปัญญา';
$this->params['breadcrumbs'][] = ['label' => 'หมวดหมู่ภูมิปัญญา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="expert-category-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
