<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\License */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'จัดการสัญญาอนุญาต', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="license-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ลบ', ['delete', 'id' => $model->id], [
          'class' => 'btn btn-danger',
          'data' => [
            'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?',
            'method' => 'post',
          ],
        ]) ?>
    </p>

    <?= DetailView::widget([
      'model' => $model,
      'attributes' => [
        'id',
        'name',
        'description:ntext',
        'url:url',
        'created_at',
        'updated_at',
      ],
    ]) ?>

</div>
