<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\bootstrap\ActiveField;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'ตั้งค่าภูมิปัญญา';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="variables-index">

<h1><?= Html::encode($this->title) ?></h1>
 <?php 
  if(Yii::$app->getSession()->has('success')){
    echo '<div class="alert alert-success">'.Yii::$app->getSession()->get('success').'</div>';
  }

  ?>
<?php $form = ActiveForm::begin(); ?>

    <input type="hidden" name="<?=Yii::$app->request->csrfParam?>" value="<?=Yii::$app->request->csrfToken?>">

    <div class="panel panel-default">
       
        <div class="panel-body">
            
            <div class="form-group expert-form ">
    
                <?= $form->field($expert, 'expert')->checkboxList(
                    [
                        'expert_firstname'=>'แสดงชื่อ ภูมิปัญญา/ปราชญ์', 
                        'expert_lastname' => 'แสดงนามสกุล ภูมิปัญญา/ปราชญ์',
                        'expert_birthdate' => 'แสดงวันเดือนปีเกิด ผู้เชียวชาญ',
                        'expert_expertise' => 'แสดงความชำนาญ',
                        'expert_occupation' => 'แสดงอาชีพ',
                        'expert_card_id' => 'แสดงรหัสประจำตัวประชาชนของภูมิปัญญา/ปราชญ์',
                        'phone' => 'แสดงเบอร์โทร',
                        'address' => 'แสดงที่อยู่',
                    ])->label(false)
                ?>

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
