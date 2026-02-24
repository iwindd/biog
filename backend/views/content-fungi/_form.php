<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\widgets\FileInput;
use yii\helpers\ArrayHelper;
use common\components\Upload;


use kartik\select2\Select2;
use yii\web\JsExpression;
$urlTaxonomy = \yii\helpers\Url::to(['/taxonomy/taxonomy']);

use backend\models\Region;
use backend\models\Province;
use backend\models\District;
use backend\models\Subdistrict;
use backend\models\Zipcode;
use frontend\components\GoogleMapHelper;
/* @var $this yii\web\View */
/* @var $model backend\models\Content */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile(Url::base().'/js/content.js', ['depends' => [\backend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base().'/js/location.js', ['depends' => [\backend\assets\AppAsset::className()]]);

$this->registerJsFile(Url::base().'/js/map.js', ['depends' => \yii\web\JqueryAsset::className()]);
$this->registerJsFile(GoogleMapHelper::getGoogleMapApiUrl(), ['depends' => \yii\web\JqueryAsset::className(), 'async' => true, 'defer' => true]);


$initialPreview = "";  
if(!empty($model->picture_path)){
   $initialPreview = '<img src="'.Yii::$app->params['urlWebBiog'].'/files/content-fungi/'.$model->picture_path.'" class="img-responsive img-thumbnail " style="width:200px;" alt="" />';
}


$region = ArrayHelper::map(Region::find()->all(), 'id', 'name_th');
$province = array();
$district = array();
$subdistrict = array();
$zipcode = array();

if (!empty($model['region_id'])) {
    $province = ArrayHelper::map(Province::find()->where(['region_id' => $model['region_id']])->all(), 'id', 'name_th');
}

if (!empty($model['province_id'])) {
    $district = ArrayHelper::map(District::find()->where(['province_id' => $model['province_id']])->all(), 'id', 'name_th');
}

if (!empty($model['district_id'])) {
    $subdistrict = ArrayHelper::map(Subdistrict::find()->where(['district_id' => $model['district_id']])->all(), 'id', 'name_th');
}

if (!empty($model['subdistrict_id'])) {
    $zipcode = ArrayHelper::map(Zipcode::find()->where(['subdistrict_id' => $model['subdistrict_id']])->all(), 'id', 'zipcode');
}

?>

<div class="content-form">

    <?php
    if (!empty($case_error)) {
        foreach ($case_error as $error) {
    ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error['message']; ?>

                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
    <?php
        }
    }
    ?>

    <?php $form = ActiveForm::begin([
      'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <div class="panel panel-default">
        <div class="panel-heading">ข้อมูลจุลินทรีย์</div>
        <div class="panel-body">

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($modelFungi, 'other_name')->textInput(['maxlength' => true]) ?>

            <?php /*

            <?= $form->field($modelFungi, 'features')->widget(\yii2jodit\JoditWidget::className(), [
                    'settings' => [
                    'height'=>'auto',
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

            ]) ?> */ ?>


            <?= $form->field($modelFungi, 'features')->textarea(['rows' => '6', 'class' => 'summernote-features']) ?>

            <?php /*

            <?= $form->field($modelFungi, 'benefit')->widget(\yii2jodit\JoditWidget::className(), [
                    'settings' => [
                    'height'=>'auto',
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

            ]) ?> */ ?>

            <?= $form->field($modelFungi, 'benefit')->textarea(['rows' => '6', 'class' => 'summernote-benefit']) ?>

            <?php /*
            <?= $form->field($model, 'source_information')->widget(\yii2jodit\JoditWidget::className(), [
                    'settings' => [
                    'height'=>'auto',
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

            ]) ?> */ ?>

            


            <?= $form->field($modelFungi, 'found_source')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'latitude')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'longitude')->textInput(['maxlength' => true]) ?>

            <div class="col-12 map-block">
                <div id="content-google-map" class="content-google-map" style="min-height:400px;"></div>
            </div>

            <?= $form->field($model, 'region_id')->dropDownList($region, ['prompt' => 'กรุณาเลือกภูมิภาค']) ?>

            <?= $form->field($model, 'province_id')->dropDownList($province, ['prompt' => 'กรุณาเลือกจังหวัด']) ?>

            <?= $form->field($model, 'district_id')->dropDownList($district, ['prompt' => 'กรุณาเลือกอำเภอ']) ?>

            <?= $form->field($model, 'subdistrict_id')->dropDownList($subdistrict, ['prompt' => 'กรุณาเลือกตำบล']) ?>

            <?= $form->field($model, 'zipcode_id')->dropDownList($zipcode, ['prompt' => 'กรุณาเลือกรหัสไปรษณีย์']) ?>


            <?= $form->field($model, 'picture_path', [
                          'template' => "{label}\n{input}\n<div> รูปภาพควรมีขนาด 1532x800 pixel มีขนาดไม่เกิน 5 MB และ ต้องเป็นไฟล์นามสกุล jpg, jpeg, png หรือ gif เท่านั้น </div>\n{hint}\n{error}",
                          'labelOptions' => [ 'class' => 'control-label' ]
            ])->widget(FileInput::classname(), [
                //'options' => ['accept' => 'image/*'],
                'pluginOptions' => [
                    'initialPreview'=>$initialPreview,
                    'allowedFileExtensions'=>['jpg', 'jpeg', 'png', 'gif', 'PNG'],
                    'showPreview' => true,
                    'showRemove' => false,
                    'showUpload' => false,
                    'maxFileSize'=>5120
                    ]
            ]); ?> 


            <input type="hidden" name="deletePic" id="deletePic" value="0" >
                    
            <?php /*
            <?= $form->field($modelFungi, 'other_information')->widget(\yii2jodit\JoditWidget::className(), [
                    'settings' => [
                    'height'=>'auto',
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

            ]) ?> */ ?>

            <?= $form->field($modelFungi, 'other_information')->textarea(['rows' => '6', 'class' => 'summernote-other_information']) ?>

            <?= $form->field($modelFungi, 'season')->textInput(['maxlength' => true]) ?>

            <?= $form->field($modelFungi, 'ability')->textInput(['maxlength' => true]) ?>

            <?= $form->field($modelFungi, 'common_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($modelFungi, 'scientific_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($modelFungi, 'family_name')->textInput(['maxlength' => true]) ?>

            <?php /*
            <?= $form->field($model, 'description')->widget(\yii2jodit\JoditWidget::className(), [
                    'settings' => [
                    'height'=>'auto',
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

            ]) ?>

            <?= $form->field($model, 'other_information')->widget(\yii2jodit\JoditWidget::className(), [
                    'settings' => [
                    'height'=>'auto',
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

            ]) ?>

            <?= $form->field($model, 'source_information')->widget(\yii2jodit\JoditWidget::className(), [
                    'settings' => [
                    'height'=>'auto',
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

            ]) ?> */ ?>


            <!-- multi images -->
            <input type="hidden" name="removeImage" id="removeImage">
            <input type="hidden" name="removeVideo" id="removeVideo">

            <?php
            //print_r($mediaModel);

            if (!empty($mediaModel)) {
            ?>
                <label class="control-label">รูปประกอบ (แสดงเป็น Gallery)</label>
                <div class="row">
                    <?php
                    foreach ($mediaModel as $value) {
                        if (!empty($value['path']) ) {
                    ?>
                            <div class="col-md-3 card-picture-item" id="image-item-<?php echo $value['id']; ?>">
                                <button onclick="removeImages(<?php echo $value['id']; ?>)" type="button" class="close" aria-label="ลบไฟล์นี้">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <?php echo Upload::readfilePictureNoPermission('content-fungi', $value['path']); ?>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            <?php
            }
            ?>
            <?= $form->field($model, "files", [
                'template' => "{label}\n{input}\n<div> รูปภาพมีขนาดไม่เกิน 5 MB และต้องเป็นไฟล์นามสกุล jpg, jpeg, png หรือ gif เท่านั้น </div>\n{hint}\n{error}",
                'labelOptions' => [ 'class' => 'control-label' ]
            ])->widget(FileInput::classname(), [
                'options' => [
                    //'accept' => 'image/*',
                    'class' => 'caractboxes-img',
                    'multiple' => true
                ],
                'pluginOptions' => [
                    'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'gif'],
                    'showPreview' => true,
                    'showRemove' => true,
                    'showUpload' => false,
                    'maxFileSize' => 5120
                ]
            ]);
            ?>
            <!-- end multi image -->

             <?php 
            echo $form->field($model, 'taxonomy')->widget(Select2::classname(), [
                'maintainOrder' => true,
                'options' => ['placeholder' => 'คำช่วยค้นหา...' , 'multiple' => true ],
                'pluginOptions' => [
                    'allowClear' => true,
                    'tags' => true,
                    'language' => [
                        'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
                    ],
                    'ajax' => [
                        'url' => $urlTaxonomy,
                        'dataType' => 'json',
                        'data' => new JsExpression('function(params) { return {q:params.term}; }')
                    ],
                    'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                    'templateResult' => new JsExpression('function(taxonomy) { return taxonomy.text; }'),
                    'templateSelection' => new JsExpression('function (taxonomy) { return taxonomy.text; }'),
                ],
                'addon' => [
                    'append' => [
                        'content' => Html::tag('div','<i class="fa fa-circle-o"></i>', [
                            'class' => 'btn btn-circle-o',
                            'title' => 'Text autocomplete', 
                            'data-toggle' => 'tooltip'
                        ]),
                        'asButton' => true
                    ]
                ]
            ]);
            ?>

            <input type="hidden" name="removeTaxonomy" id="removeTaxonomy">

            <?= $form->field($model, 'photo_credit')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'source_information')->textarea(['rows' => '3'], 'รายละเอียด');  ?>
            
            <?= $form->field($model, 'note')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'status')->dropDownList([ 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', ], ['prompt' => 'เลือกสถานะ']) ?>
            

            <div class="form-group">
                <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
            </div>

        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
