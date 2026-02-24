<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\components\BackendHelper;

/* @var $this yii\web\View */
/* @var $model backend\models\Wallboard */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Wallboard', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="wallboard-view">

    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ลบข้อมูล', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'ต้องการลบข้อมูลนี้ใช่หรือไม่',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="userstudent">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">ข้อมูล Wallboard</h3>
                </div>
                <div class="panel-body">

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'id',
                            'description:html',
                            [
                                'attribute'=>'created_by_user_id',
                                'value'=>function($model){
                                    return BackendHelper::getName($model->created_by_user_id);
                                }
                            ],
                            [
                                'attribute'=>'updated_by_user_id',
                                'value'=>function($model){
                                    return BackendHelper::getName($model->updated_by_user_id);
                                }
                            ],
                            // 'active',
                            'created_at',
                            'updated_at',
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

</div>
