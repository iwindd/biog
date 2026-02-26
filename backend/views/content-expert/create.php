<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Content */

$this->title = 'เพิ่มข้อมูลภูมิปัญญา/ปราชญ์';
$this->params['breadcrumbs'][] = ['label' => 'Contents', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="content-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'modelExpert' => $modelExpert,
        'mediaModel' => $mediaModel,
        'modelImageSource' => $modelImageSource,
        'modelDataSource' => $modelDataSource,
        'case_error' => $case_error
    ]) ?>

</div>
