<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

$this->title = 'ตั้งค่าการแจ้งเตือน';
$this->params['breadcrumbs'][] = ['label' => 'ตั้งค่าทั่วไป', 'url' => ['/setting']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="notification-settings">

<h1><?= Html::encode($this->title) ?></h1>
<?php if(Yii::$app->session->hasFlash('alert')):?>
    <?= \yii\bootstrap\Alert::widget([
        'body'=> ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body'),
        'options'=> ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options'),
    ])?>
<?php endif; ?>

<?php $form = ActiveForm::begin(); ?>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">การแจ้งเตือนส่วนตัว</h3>
        </div>
        <div class="panel-body">
            
            <div class="form-group">
                <?= $form->field($model, 'notify_new_registration')->checkbox(['value' => 1, 'uncheck' => 0]) ?>
                <p class="help-block text-muted">เมื่อมีนักเรียนหรือครูสมัครสมาชิกใหม่ ระบบจะส่งอีเมลแจ้งเตือนมาหาคุณ</p>
            </div>

        </div>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success">บันทึก</button>    
    </div>

<?php ActiveForm::end(); ?>

</div>
