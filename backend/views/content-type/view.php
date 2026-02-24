<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model backend\models\ContentType */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'จัดการการแสดงผล Content', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="content-type-view">

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
        'title',
        [
          'attribute' => 'is_visible',
          'value' => function ($model) {
            return $model->is_visible ? 'แสดง' : 'ซ่อน';
          },
        ],
        'created_at',
        'updated_at',
      ],
    ]) ?>

</div>
