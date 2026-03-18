<?php

use common\components\Upload;
use kartik\select2\Select2;
use kartik\widgets\FileInput;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\ActiveForm;

$urlTaxonomy = \yii\helpers\Url::to(['/taxonomy/taxonomy']);

use backend\models\District;
use backend\models\License;
use backend\models\ProductCategory;
use backend\models\Province;
use backend\models\Region;
use backend\models\Subdistrict;
use backend\models\Zipcode;
use frontend\components\GoogleMapHelper;

/* @var $this yii\web\View */
/* @var $model backend\models\Content */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile(Url::base() . '/js/content.js', ['depends' => [\backend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base() . '/js/location.js', ['depends' => [\backend\assets\AppAsset::className()]]);

$initialPreview = '';
if (!empty($model->picture_path)) {
    $initialPreview = '<img src="' . Yii::$app->params['urlWebBiog'] . '/files/content-product/' . $model->picture_path . '" class="img-responsive img-thumbnail " style="width:200px;" alt="" />';
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

$categoty = ArrayHelper::map(ProductCategory::find()->where(['active' => 1])->all(), 'id', 'name');
$licenseList = ArrayHelper::map(License::find()->all(), 'id', 'name');
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
        <div class="panel-heading">ข้อมูลผลิตภัณฑ์ชุมชน</div>
        <div class="panel-body">

            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

             <?= $form->field($modelProduct, 'product_category_id')->dropDownList($categoty, ['prompt' => 'กรุณาเลือกหมวดหมู่']) ?>
            <?php  /*
                                                                                                                                                                                                                                                                                                                                                                                             * <?= $form->field($modelProduct, 'product_features')->widget(\yii2jodit\JoditWidget::className(), [
                                                                                                                                                                                                                                                                                                                                                                                             *         'settings' => [
                                                                                                                                                                                                                                                                                                                                                                                             *         'height'=>'auto',
                                                                                                                                                                                                                                                                                                                                                                                             *         'enableDragAndDropFileToEditor'=>new \yii\web\JsExpression("true"),
                                                                                                                                                                                                                                                                                                                                                                                             *         'buttons'=>[
                                                                                                                                                                                                                                                                                                                                                                                             *             'source', '|',
                                                                                                                                                                                                                                                                                                                                                                                             *             'bold','strikethrough','underline','italic','align','|',
                                                                                                                                                                                                                                                                                                                                                                                             *             'ul','ol', '|',
                                                                                                                                                                                                                                                                                                                                                                                             *             'outdent','indent', '|',
                                                                                                                                                                                                                                                                                                                                                                                             *             'font','fontsize','brush','paragraph','eraser','|',
                                                                                                                                                                                                                                                                                                                                                                                             *             'image','video','file','table','link','|',
                                                                                                                                                                                                                                                                                                                                                                                             *             'align','undo','redo',
                                                                                                                                                                                                                                                                                                                                                                                             *             ],
                                                                                                                                                                                                                                                                                                                                                                                             *         ],
                                                                                                                                                                                                                                                                                                                                                                                             *         'options' => ['placeholder' => 'รายละเอียด'],
                                                                                                                                                                                                                                                                                                                                                                                             *
                                                                                                                                                                                                                                                                                                                                                                                             * ]) ?>
                                                                                                                                                                                                                                                                                                                                                                                             */
            ?>

            <?= $form->field($modelProduct, 'product_features')->textarea(['rows' => '6', 'class' => 'summernote-product_features']) ?>

            <?= $form->field($modelProduct, 'product_price')->textInput(['maxlength' => true]) ?>

            <?= $form->field($modelProduct, 'product_distribution_location')->textInput(['maxlength' => true]) ?>

            <?= $form->field($modelProduct, 'product_address')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'region_id')->dropDownList($region, ['prompt' => 'กรุณาเลือกภูมิภาค']) ?>

            <?= $form->field($model, 'province_id')->dropDownList($province, ['prompt' => 'กรุณาเลือกจังหวัด']) ?>

            <?= $form->field($model, 'district_id')->dropDownList($district, ['prompt' => 'กรุณาเลือกอำเภอ']) ?>

            <?= $form->field($model, 'subdistrict_id')->dropDownList($subdistrict, ['prompt' => 'กรุณาเลือกตำบล']) ?>

            <?= $form->field($model, 'zipcode_id')->dropDownList($zipcode, ['prompt' => 'กรุณาเลือกรหัสไปรษณีย์']) ?>

            <?= $form->field($modelProduct, 'product_phone')->textInput(['maxlength' => true]) ?>

            <?= $this->render('../components/_map_component', [
                'model' => $model,
                'form' => $form,
            ]) ?>

            <?= $form->field($modelProduct, 'product_main_material')->textInput(['maxlength' => true]) ?>

            <?= $form->field($modelProduct, 'product_sources_material')->textInput(['maxlength' => true]) ?>


            <?php 
            $picInputId = Html::getInputId($model, 'picture_path');
            echo $form->field($model, 'picture_path', [
                'template' => "{label}\n{input}\n<div id=\"{$picInputId}-preview\" style=\"margin-top: 15px; margin-bottom: 15px;\">{$initialPreview}</div>\n<div> รูปภาพควรมีขนาด 1532x800 pixel มีขนาดไม่เกิน 8 MB และ ต้องเป็นไฟล์นามสกุล jpg, jpeg, png หรือ gif เท่านั้น </div>\n{hint}\n{error}",
                'labelOptions' => ['class' => 'control-label']
            ])->hiddenInput()->label('รูปภาพหน้าปก');
            
            echo \backend\components\FileCenterPickerWidget::widget([
                'inputId' => $picInputId,
                'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'PNG'],
                'clearable' => true,
                'maxSize' => 8 * 1024,
                'multiple' => false,
            ]);
            ?> 


            <input type="hidden" name="deletePic" id="deletePic" value="0" >

            <?php  /*
                                                                                                                        * <?= $form->field($modelProduct, 'other_information')->widget(\yii2jodit\JoditWidget::className(), [
                                                                                                                        *         'settings' => [
                                                                                                                        *         'height'=>'auto',
                                                                                                                        *         'enableDragAndDropFileToEditor'=>new \yii\web\JsExpression("true"),
                                                                                                                        *         'buttons'=>[
                                                                                                                        *             'source', '|',
                                                                                                                        *             'bold','strikethrough','underline','italic','align','|',
                                                                                                                        *             'ul','ol', '|',
                                                                                                                        *             'outdent','indent', '|',
                                                                                                                        *             'font','fontsize','brush','paragraph','eraser','|',
                                                                                                                        *             'image','video','file','table','link','|',
                                                                                                                        *             'align','undo','redo',
                                                                                                                        *             ],
                                                                                                                        *         ],
                                                                                                                        *         'options' => ['placeholder' => 'รายละเอียด'],
                                                                                                                        *
                                                                                                                        * ]) ?>
                                                                                                                        *
                                                                                                                        *
                                                                                                                        * <label>ข้อมูลอื่นๆ ที่ฉันรู้</label>
                                                                                                                        * <?php echo froala\froalaeditor\FroalaEditorWidget::widget([
                                                                                                                        *         'model' => $model,
                                                                                                                        *         'attribute' => 'other_information',
                                                                                                                        *         'options' => [
                                                                                                                        *             // html attributes
                                                                                                                        *
                                                                                                                        *             'id'=>'content-other_information'
                                                                                                                        *         ],
                                                                                                                        *         'clientOptions' => [
                                                                                                                        *             'placeholderText' => 'ข้อมูลอื่นๆ ที่ฉันรู้',
                                                                                                                        *             'toolbarInline' => false,
                                                                                                                        *             'height' => 300,
                                                                                                                        *             'theme' => 'gray', //optional: dark, red, gray, royal
                                                                                                                        *             'language' => 'en_gb', // optional: ar, bs, cs, da, de, en_ca, en_gb, en_us ...
                                                                                                                        *             'imageUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                                                                                                                        *             'fileUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                                                                                                                        *             'toolbarButtons'   => ['fullscreen', 'bold', 'italic', 'underline', 'alignLeft', 'alignCenter', 'formatOLSimple', 'alignRight', 'paragraphFormat', 'fontFamily', 'fontSize', 'textColor', 'insertLink', 'insertImage', 'insertVideo', 'insertFile', 'undo', 'redo']
                                                                                                                        *         ]
                                                                                                                        * ]); ?>
                                                                                                                        * <br/>
                                                                                                                        */
            ?>
            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('../components/_image_source_dynamic_form', [
                        'form' => $form,
                        'modelImageSource' => $modelImageSource,
                    ]) ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <?= $this->render('../components/_data_source_dynamic_form', [
                        'form' => $form,
                        'modelDataSource' => $modelDataSource,
                    ]) ?>
                </div>
            </div>

            <?= $form->field($modelProduct, 'found_source')->textInput(['maxlength' => true]) ?>

            <?= $form->field($modelProduct, 'contact')->textInput(['maxlength' => true]) ?>

            <?php  /*
                                                                                                                                                                                                                                                                                                                                                                       * <?= $form->field($model, 'source_information')->widget(\yii2jodit\JoditWidget::className(), [
                                                                                                                                                                                                                                                                                                                                                                       *         'settings' => [
                                                                                                                                                                                                                                                                                                                                                                       *         'height'=>'auto',
                                                                                                                                                                                                                                                                                                                                                                       *         'enableDragAndDropFileToEditor'=>new \yii\web\JsExpression("true"),
                                                                                                                                                                                                                                                                                                                                                                       *         'buttons'=>[
                                                                                                                                                                                                                                                                                                                                                                       *             'source', '|',
                                                                                                                                                                                                                                                                                                                                                                       *             'bold','strikethrough','underline','italic','align','|',
                                                                                                                                                                                                                                                                                                                                                                       *             'ul','ol', '|',
                                                                                                                                                                                                                                                                                                                                                                       *             'outdent','indent', '|',
                                                                                                                                                                                                                                                                                                                                                                       *             'font','fontsize','brush','paragraph','eraser','|',
                                                                                                                                                                                                                                                                                                                                                                       *             'image','video','file','table','link','|',
                                                                                                                                                                                                                                                                                                                                                                       *             'align','undo','redo',
                                                                                                                                                                                                                                                                                                                                                                       *             ],
                                                                                                                                                                                                                                                                                                                                                                       *         ],
                                                                                                                                                                                                                                                                                                                                                                       *         'options' => ['placeholder' => 'รายละเอียด'],
                                                                                                                                                                                                                                                                                                                                                                       *
                                                                                                                                                                                                                                                                                                                                                                       * ]) ?>
                                                                                                                                                                                                                                                                                                                                                                       */
            ?>



            <!-- multi images -->
            <input type="hidden" name="removeImage" id="removeImage">
            <input type="hidden" name="removeVideo" id="removeVideo">

            <?php
            // print_r($mediaModel);

            if (!empty($mediaModel)) {
                ?>
                <label class="control-label">รูปประกอบ (แสดงเป็น Gallery)</label>
                <div class="row">
                    <?php
                    foreach ($mediaModel as $value) {
                        if (!empty($value['path'])) {
                            ?>
                            <div class="col-md-3 card-picture-item" id="image-item-<?php echo $value['id']; ?>">
                                <button onclick="removeImages(<?php echo $value['id']; ?>)" type="button" class="close" aria-label="ลบไฟล์นี้">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <?php echo Upload::readfilePictureNoPermission('content-product', $value['path']); ?>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            <?php
            }
            ?>
            <?php
            $filesInputId = Html::getInputId($model, 'files');
            echo $form->field($model, 'files', [
                'template' => "{label}\n{input}\n<div id=\"{$filesInputId}-preview\" style=\"margin-top: 15px; margin-bottom: 15px;\"></div>\n<div> รูปภาพมีขนาดไม่เกิน 8 MB และต้องเป็นไฟล์นามสกุล jpg, jpeg, png หรือ gif เท่านั้น </div>\n{hint}\n{error}",
                'labelOptions' => ['class' => 'control-label']
            ])->hiddenInput();
            
            echo \backend\components\FileCenterPickerWidget::widget([
                'inputId' => $filesInputId,
                'buttonText' => '<i class="fa fa-folder-open"></i> เลือกรูปภาพประกอบจาก FileCenter',
                'extensions' => ['jpg', 'jpeg', 'png', 'gif'],
                'clearable' => true,
                'maxSize' => 8 * 1024,
                'multiple' => true,
            ]);
            ?>
            <!-- end multi image -->

             <?php
echo $form->field($model, 'taxonomy')->widget(Select2::classname(), [
    'maintainOrder' => true,
    'options' => ['placeholder' => 'คำช่วยค้นหา...', 'multiple' => true],
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
            'content' => Html::tag('div', '<i class="fa fa-circle-o"></i>', [
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



            <?= $form->field($model, 'note')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'status')->dropDownList([
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
            ], ['prompt' => 'เลือกสถานะ']) ?>

            <?= $form->field($model, 'license_id')->widget(Select2::classname(), [
                'data' => $licenseList,
                'options' => ['placeholder' => 'เลือกสัญญาอนุญาต...'],
                'pluginOptions' => [
                    'allowClear' => true
                ],
            ]); ?>

            <?= $form->field($model, 'is_hidden')->dropDownList([
                '0' => 'แสดงผล',
                '1' => 'ซ่อน',
            ]) ?>


            <div class="form-group">
                <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
            </div>

        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
