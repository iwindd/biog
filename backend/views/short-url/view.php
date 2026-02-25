<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\ShortUrl */

$this->title = $model->code;
$this->params['breadcrumbs'][] = ['label' => 'จัดการลิงก์ย่อ', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="short-url-view">
    <h1>ลิงก์ย่อ <?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ลบข้อมูล', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการลบรายการนี้?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <div class="table-responsive">
        <?= DetailView::widget([
            'model' => $model,
            'attributes' => [
                'code',
                [
                    'label' => 'ลิงก์ย่อ (Short Link)',
                    'format' => 'raw',
                    'value' => function ($model) {
                        $url = Yii::$app->params['shortUrlDomain'] . $model->code;
                        return Html::a($url, $url, ['target' => '_blank']);
                    }
                ],
                'target_url:url',
                'created_at:datetime',
                [
                    'attribute' => 'created_by',
                    'value' => function($model) {
                        return $model->createdBy ? $model->createdBy->username : null;
                    },
                ],
                'updated_at:datetime',
            ],
        ]) ?>
    </div>
</div>
