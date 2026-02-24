<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\School */

$this->title = 'แก้ไขข้อมูลนักเรียนของคุณครู: ' . $model->profile->firstname." ".$model->profile->lastname;
$this->params['breadcrumbs'][] = ['label' => 'โรงเรียนทั้งหมด', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->profile->firstname." ".$model->profile->lastname, 'url' => ['teacher', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="school-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_from_teacher_student', [
        'model' => $model,
        'school' => $school,
        'modelStudent' => $modelStudent,
    ]) ?>

</div>
