<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\ShortUrl */

$this->title = 'สร้างลิงก์ย่อ (Create Short URL)';
$this->params['breadcrumbs'][] = ['label' => 'จัดการลิงก์ย่อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="short-url-create box box-primary">

    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($this->title) ?></h3>
    </div>

    <div class="box-body">
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>

</div>
