<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'ตั้งค่าทั่วไป';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="variables-index">

<h1><?= Html::encode($this->title) ?></h1>
<?php if(Yii::$app->session->hasFlash('alert')):?>
        <?= \yii\bootstrap\Alert::widget([
        'body'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'body'),
        'options'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert'), 'options'),
        ])?>
    <?php endif; ?>
<?php $form = ActiveForm::begin(); ?>

    <input type="hidden" name="<?=Yii::$app->request->csrfParam?>" value="<?=Yii::$app->request->csrfToken?>">

    <div class="panel setting-page panel-default">
       
        <div class="panel-body">
            
            <div class="form-group required">

                <label class="label-value">

                    <?= $form->field($model, 'sender_mail')->textInput(['maxlength' => true])->label('อีเมลสำหรับส่งข้อความไปยังอีเมลอื่นๆ ในระบบ') ?>

                </label>  
                <hr>
                <div class="help-block"></div>
            </div>

            <div class="form-group">

                <label class="label-value">

                    <?= $form->field($model, 'email_info')->textInput(['maxlength' => true])->label('อีเมลสำหรับแสดงหน้าเว็บไซต์') ?>

                </label>  
                <hr>
                
            </div>

            <div class="form-group">

                <label class="label-value">

                    <?= $form->field($model, 'phone_info')->textInput(['maxlength' => true])->label('เบอร์โทรศัพท์แสดงหน้าเว็บไซต์') ?>

                </label>  
                <hr>
                
            </div>

            <div class="form-group">
                <h4>การแจ้งเตือนส่วนตัว</h4>
                <?= $form->field($notificationModel, 'notify_new_registration')->checkbox(['value' => 1, 'uncheck' => 0]) ?>
                <p class="help-block text-muted">เมื่อมีนักเรียนหรือครูสมัครสมาชิกใหม่ ระบบจะส่งอีเมลแจ้งเตือนมาหาคุณ</p>
            </div>

        </div>
    </div>

    <div class="form-group">
        <button type="submit" class="btn btn-success">บันทึก</button>    
    </div>

<?php ActiveForm::end(); ?>

</div>
<?php
Yii::$app->getSession()->remove('success');
?>
