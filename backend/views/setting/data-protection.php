<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุคคล';
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

              
                <?php /*
                <?= $form->field($model, 'data_protection')->widget(\yii2jodit\JoditWidget::className(), [
                    'settings' => [
                    'height'=>'800',
                    'width'=>'100%',
                    'enableDragAndDropFileToEditor'=>new \yii\web\JsExpression("true"),
                    'buttons'=>[
                        'source', '|',
                        'bold','strikethrough','underline','italic','align','|',
                        'ul','ol', '|',
                        'outdent','indent', '|',
                        'font','fontsize','brush','paragraph','eraser','|',
                        'image','video','file','table','link','|',
                        'align','undo','redo',
                        ],
                    ],
                    'options' => ['placeholder' => 'รายละเอียด'],

                ])->label('เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุคคล') ?> 

                <label>เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุคคล</label>
                    <?php echo froala\froalaeditor\FroalaEditorWidget::widget([
                        'model' => $model,
                        'attribute' => 'data_protection',
                        'options' => [
                            // html attributes
                            
                            'id'=>'setting-data_protection'
                        ],
                        'clientOptions' => [
                            'placeholderText' => 'เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุคคล',
                            'toolbarInline' => false,
                            'height' => 500,
                            'theme' => 'gray', //optional: dark, red, gray, royal
                            'language' => 'en_gb', // optional: ar, bs, cs, da, de, en_ca, en_gb, en_us ...
                            'imageUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                            'fileUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                            'toolbarButtons'   => ['fullscreen', 'bold', 'italic', 'underline', 'alignLeft', 'alignCenter', 'formatOLSimple', 'alignRight', 'paragraphFormat', 'insertLink', 'insertImage', 'insertVideo', 'insertFile', 'undo', 'redo']
                        ]
                ]); ?>
                <br>  */ ?>


                <?= $form->field($model, 'data_protection')->textarea(['rows' => '6', 'class' => 'summernote-data_protection'])->label('เงื่อนไขและนโยบายคุ้มครองข้อมูลส่วนบุคคล') ?>

            
             
        
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
