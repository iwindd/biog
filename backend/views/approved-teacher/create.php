<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Users */

$this->title = 'เพิ่มอาจารย์';
$this->params['breadcrumbs'][] = ['label' => 'อนุมัติอาจารย์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-create">
    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'user' => $user,
        'profile' => $profile,
        'error', $error,
        'admin' => $admin,
        'editor' => $editor,
        'teacher' => $teacher,
        'student'=> $student,
        'member'=> $member, 
    ]) ?>

</div>
