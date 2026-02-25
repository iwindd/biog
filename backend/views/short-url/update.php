<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ShortUrl */

$this->title = 'แก้ไขลิงก์ย่อ (Update): ' . $model->code;
$this->params['breadcrumbs'][] = ['label' => 'จัดการลิงก์ย่อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->code, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="short-url-update box box-primary ">

    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($this->title) ?></h3>
    </div>

    <div class="box-body">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>

</div>
