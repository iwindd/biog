<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use common\components\Upload;
use backend\components\BackendHelper;

/* @var $this yii\web\View */
/* @var $model backend\models\Banner */

$this->title = "หน้า: ".ucfirst($model->slug_url);
$this->params['breadcrumbs'][] = ['label' => 'จัดการแบนเนอร์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="banner-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
  
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'slug_url',
            [
                'format'=>'raw',
                'attribute'=>'picture_path',
                'filter'=>false,
                'value'=>function($model){
                    return Upload::readfilePictureNoPermission('banner',$model->picture_path);
                }
            ],
            //'active',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
