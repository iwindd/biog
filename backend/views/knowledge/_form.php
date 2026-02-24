<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use kartik\widgets\FileInput;

use common\components\Upload;

/* @var $this yii\web\View */
/* @var $model backend\models\Blog */
/* @var $form yii\widgets\ActiveForm */

$this->registerJsFile(Url::base().'/js/content.js', ['depends' => [\backend\assets\AppAsset::className()]]);

$initialPreview = "";  
if(!empty($model->picture_path)){
   $initialPreview = '<img src="'.Yii::$app->params['urlWebBiog'].'/files/knowledge/'.$model->picture_path.'" class="img-responsive img-thumbnail " style="width:200px;" alt="" />';
}


/* @var $this yii\web\View */
/* @var $model backend\models\Knowledge */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="knowledge-form">

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
        <div class="panel-heading">ข้อมูลองค์ความรู้ออนไลน์</div>
        <div class="panel-body">

        <?= $form->field($model, 'type')->dropDownList([ 'Infographic' => 'Infographic', 'Video' => 'Video', ], ['prompt' => 'เลือกประเภท']) ?>

        <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'picture_path', [
                          'template' => "{label}\n{input}\n<div> รูปภาพควรมีขนาด 766x800 pixel มีขนาดไม่เกิน 2 MB และ ต้องเป็นไฟล์นามสกุล jpg, jpeg, png หรือ gif เท่านั้น </div>\n{hint}\n{error}",
                          'labelOptions' => [ 'class' => 'control-label' ]
        ])->widget(FileInput::classname(), [
            //'options' => ['accept' => 'image/*'],
            'pluginOptions' => [
                'initialPreview'=>$initialPreview,
                'allowedFileExtensions'=>['jpg', 'jpeg', 'png', 'gif', 'PNG'],
                'showPreview' => true,
                'showRemove' => false,
                'showUpload' => false,
                'maxFileSize'=>2048
                ]
        ]); ?> 

        <input type="hidden" name="deletePic" id="deletePic" value="0" >


        <?= $form->field($model, 'path')->textInput(['maxlength' => true]) ?>

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

        <label>รายละเอียด</label>
        <?php echo froala\froalaeditor\FroalaEditorWidget::widget([
            'model' => $model,
            'attribute' => 'description',
            'options' => [
                // html attributes
                
                'id'=>'knowledge-description'
            ],
            'clientOptions' => [
                'placeholderText' => 'รายละเอียด',
                'toolbarInline' => false,
                'height' => 300,
                'theme' => 'gray', //optional: dark, red, gray, royal
                'language' => 'en_gb', // optional: ar, bs, cs, da, de, en_ca, en_gb, en_us ...
                'imageUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                'fileUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                'toolbarButtons'   => ['fullscreen', 'bold', 'italic', 'underline', 'alignLeft', 'alignCenter', 'formatOLSimple', 'alignRight', 'paragraphFormat', 'insertLink', 'insertImage', 'insertVideo', 'insertFile', 'undo', 'redo']
            ]
        ]); ?>
        <br> */ ?>

        <?= $form->field($model, 'description')->textarea(['rows' => '6', 'class' => 'summernote-description']) ?>

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
                    if (!empty($value['path']) &&  $value['application_type'] == 'image') {
                ?>
                        <div class="col-md-3 card-picture-item" id="image-item-<?php echo $value['id']; ?>">
                            <button onclick="removeImages(<?php echo $value['id']; ?>)" type="button" class="close" aria-label="ลบไฟล์นี้">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <?php
                            if( $value['application_type'] = 'image') {
                                echo Upload::readfilePictureNoPermission('knowledge', $value['path']);
                            } ?>
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
            'template' => "{label}\n{input}\n<div> รูปภาพมีขนาดไม่เกิน 2 MB และไฟล์ต้องเป็นนามสกุลดังนี้ jpg, jpeg, png หรือ gif </div>\n{hint}\n{error}",
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
                'maxFileSize' => 2048
            ]
        ]);
        ?>
        <!-- end multi image -->


        <!-- อัปโหลดไฟล์ -->

        <input type="hidden" name="removeDocument" id="removeDocument">

        <?php
        //print_r($mediaModel);
        if (!empty($mediaModel)) {
        ?>
            <label class="control-label">ไฟล์</label>
            <div class="row">
                <div class="col-md-12">
                    <?php
                    foreach ($mediaModel as $value) {
                        if (!empty($value['path']) &&  $value['application_type'] == 'file') {

                            if ( $value['application_type'] = 'file') {
                                $documentPath =  Upload::readFileDocumentNoPermission('document', $value['path']);
                            }
                    ?>
                            <div class="document-list" id="document-item-<?php echo $value['id']; ?>">

                                <a href="/admin/readfile/download-knowledge/<?php echo $value['id'] ?>" target="_blank"> <?php echo $value['name']; ?></a>

                                <button onclick="removeDocuments(<?php echo $value['id']; ?>)" type="button" class="btn btn-sm btn-danger" aria-label="ลบไฟล์นี้">
                                    <i class="glyphicon glyphicon glyphicon-trash"></i>
                                </button>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
        <?php
        }
        ?>

        <?php
        echo $form->field($model, "document", [
            'template' => "{label}\n{input}\n<div> ขนาดไฟล์มากสุด 2 MB นามสกุลไฟล์ต้องเป็น jpg, jpeg, png,  gif, pdf, doc, docx, xls และ xlsx</div>\n{hint}\n{error}",
            'labelOptions' => ['class' => 'control-label']
        ])->widget(FileInput::classname(), [
            'options' => [
                //'accept' => 'image/*',
                'class' => 'caractboxes-img',
                'multiple' => true
            ],
            'pluginOptions' => [
                'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
                'showPreview' => true,
                'showRemove' => true,
                'showUpload' => false,
                'maxFileSize' => 2048
            ]
        ]);
        ?>

        <div class="clearfix"></div>


        <div class="form-group">
                <?= Html::submitButton('บันทึก', ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>
