<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\School */

$this->title = 'เพิ่มโรงเรียนใหม่';
$this->params['breadcrumbs'][] = ['label' => 'Schools', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="school-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'modelTeacher' => $modelTeacher,
        'modelStudent' => $modelStudent,
    ]) ?>

</div>
