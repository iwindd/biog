<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use kartik\select2\Select2;
use yii\web\JsExpression;
use yii\web\View;
use common\components\Helper;

$this->title =Yii::t('app','appLogin');
//$this->params['breadcrumbs'][] = $this->title;

$tempplate = [
    'template' => '{label} <div class="form-group-login"> {input}<div class="input-group-addon showPasswordLogin"><i class="fa fa-eye" aria-hidden="true"></i></div> </div> {error}'
];

$tempplate2 = [
    'template' => '{label} <div class="form-group-register"> {input}<div class="input-group-addon showPasswordRegister"><i class="fa fa-eye" aria-hidden="true"></i></div> </div> {error}'
];

$tempplate3 = [
    'template' => '{label} <div class="form-group-register"> {input}<div class="input-group-addon showPasswordConfirmRegister"><i class="fa fa-eye" aria-hidden="true"></i></div> </div> {error}'
];

$this->registerCssFile('@web/css/bootstrap.css', ['depends' =>[yii\bootstrap\BootstrapAsset::className()] ] );


// Templating example of formatting each list element
$url = \Yii::$app->urlManager->baseUrl . '/flags/';
$format = <<< SCRIPT
function format(state) {
    if (!state.id) return state.text; // optgroup
    src = '$url' +  state.id.toLowerCase() + '.png'
    return '<img class="flag" width="25px" src="' + src + '"/> ' + state.text;
}
SCRIPT;

$escape = new JsExpression("function(m) { return m; }");
$this->registerJs($format, View::POS_HEAD);

?>

<div class="container" id='site-login'>
    <div class="row login-layout grid-divider">
        <div class="col-lg-6 col-md-12 col-sm-12 f-left">
            
            <div class="login-form">

                <div class="col-sm-12">
                    <div class="circle-login col-centered">
                        <p class="h-login"><?php echo Yii::t('app','login');?></p>
                    </div>
                </div>

                <?php if($error){ ?>
                    <div class="danger"><?php  echo $error; ?></div>
                <?php } ?>

                <?php $form = ActiveForm::begin([
                        'options' => ['enctype' => 'multipart/form-data']
                ]); ?>

                <?=yii\helpers\Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken)?>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12  col-xxs-12 col-centered">
                    <div class="login">

                        <?php if(Yii::$app->session->hasFlash('alert-login')):?>
                            <?= \yii\bootstrap\Alert::widget([
                            'body'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert-login'), 'body'),
                            'options'=>ArrayHelper::getValue(Yii::$app->session->getFlash('alert-login'), 'options'),
                            ])?>
                            <?php endif; ?>

                        <div class="form-group">
                
                            <?= $form->field($model, 'login')->textInput(['maxlength' => true, 'placeholder' => Yii::t('app','textEmail') ])->label(Yii::t('app','email')) ?>

                        </div>
                        <div class="form-group margin-3">

                            <?= $form->field($model, 'password', $tempplate)->passwordInput(['maxlength' => true, 'placeholder' => Yii::t('app','textPassword') ])->label(Yii::t('app','password')) ?>

                        </div>
                        <div class="form-group">

                        <a href="/forgot-password" class="forgetPassword"><?php echo Yii::t('app','loginForgotPassword');?></a>
                            <!-- <div class="customCheck">
                                <input type="checkbox" name="remember" id="checkOther" ng-model="formData.other"
                                    ng-true-value="'other'" ng-init="formData.other='other'" value="remember"><label
                                    for="checkOther"></label>
                            </div>
                            <label class="label-text"> <?php echo Yii::t('app','loginRememberMe');?></label> -->
                            <button type="submit" class="btn buttonLogin"><?php echo Yii::t('app','login');?></button>
                        </div>

                    </div>
                </div>

                <?php ActiveForm::end(); ?>                     
        
            </div>
        </div>
    
   
    </div>
</div>

<div class="modal" id="modalRule" tabindex="-1" role="dialog" aria-labelledby="requestModal"
    aria-hidden="true">
    <div class="modal-dialog modal-requestModal" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="width100">
                    <h2 class='h-title text-center'><?php echo Yii::t('app','acceptRuleSignup') ?></h2>
                </div>
                <div class="detail">
                    <p> <h2 class='h-title text-left'>สมาชิกทั่วไป</h2> </p>
                    <p> ข้อมูลส่วนตัวของท่านจะถูกเก็บไว้สำหรับดำเนินงานตรวจของศูนย์ตรวจตัวอย่างนิ่ว <br/> โรงพยาบาลสัตว์ คณะสัตวแพทยศาสตร์ ม.เกษตรศาสตร์บางเขน</p>
                    <p> <h2 class='h-title text-left'>สมาชิกโรยัล คานิน</h2> </p>
                    <p>สมาชิกโรยัล คานิน ที่ได้รับสิทธิ์การตรวจโดยไม่เสียค่าบริการในจำนวนจำกัด 
                    คุณยินยอมให้ข้อมูลกับบริษัท โรยัล คานิน (ประเทศไทย) จำกัด และให้ติดต่อผ่านทางช่องทางอื่นๆ 
                    เพื่อรับข้อมูลที่เป็นประโยชน์สำหรับสุนัขหรือแมว หากต้องการเปลี่ยนแปลงข้อมูลหรือยกเลิก กรุณาติดต่อ <br> คอลเซ็นเตอร์ 
                    โทร. (02) 026 2456 คุณยินยอมที่จะผูกพันและรับทราบเงื่อนไขเพิ่มเติม <a target="_blank" href="https://www.mars.com/global/policies/legal/ld-thai"> ที่นี่</a> </p>
                </div>
                <div class="row">
                    <a class="btn btn-request-next margin-auto" href="#tab-form-payment" aria-controls="tab-form-payment" data-dismiss="modal" role="tab"
            data-toggle="tab">ตกลง</a>
                </div>
            </div>
        </div>
    </div>
</div>