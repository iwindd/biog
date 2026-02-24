<?php

use yii\helpers\Html;
use backend\components\BackendHelper;
/* @var $this yii\web\View */
/* @var $model backend\models\Users */

$this->title = 'แก้ไขข้อมูล: ' . $model->email;
$this->params['breadcrumbs'][] = ['label' => 'จัดการผู้ใช้งาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->email, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="users-update">
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
