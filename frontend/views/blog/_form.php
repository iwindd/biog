<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Url;
use kartik\widgets\FileInput;

use common\components\Upload;
use frontend\components\BlogHelper;

/* @var $this yii\web\View */
/* @var $model backend\models\Blog */
/* @var $form yii\widgets\ActiveForm */

//$this->registerJsFile(Url::base().'/js/content.js', ['depends' => [\backend\assets\AppAsset::className()]]);

$this->registerJsFile('@web/js/blog.js', ['depends' => \yii\web\JqueryAsset::className()]);

?>

<div class="blog-form mt-3">


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



            <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'picture_path', [
                          'template' => "{label}\n{input}\n<div> รูปภาพควรมีขนาด 766x400 pixel มีขนาดไม่เกิน 2 MB และ ต้องเป็นไฟล์นามสกุล jpg, jpeg, png หรือ gif เท่านั้น </div>\n{hint}\n{error}",
                          'labelOptions' => [ 'class' => 'control-label' ]
            ])->widget(FileInput::classname(), [
                //'options' => ['accept' => 'image/*'],
                'pluginOptions' => [
                    'initialPreview' => BlogHelper::getInitialPreviewFiles($model->picture_path, $pageType),
                    'initialPreviewConfig' => BlogHelper::getInitialPreviewConfigFiles($model->picture_path),
                    'browseLabel' =>  'เลือกไฟล์รูปภาพ',
                    'browseClass' => 'btn btn-purple btn-block',
                    'browseIcon' => '<i class="fas fa-upload mr-2"></i>',
                    'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'gif'],
                    'showCaption' => false,
                    'showPreview' => true,
                    'showRemove' => false,
                    'showUpload' => false,
                    'msgPlaceholder' => 'เลือกไฟล์...',
                    'maxFileSize' => 2048
                 ]
            ])?> 

            <input type="hidden" name="deletePic" id="deletePic" value="0" >

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
                    
                    'id'=>'blog-description'
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
            ]); ?> */ ?>

            <?= $form->field($model, 'description')->textarea(['rows' => '6', 'class' => 'summernote']) ?>

            <?= $form->field($model, 'video_url')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'source_information')->textInput(['maxlength' => true]) ?>


            <!-- multi images -->
            <input type="hidden" name="removeImage" id="removeImage">
            <label class="control-label">รูปประกอบ (แสดงเป็น Gallery)</label>
            <?php
                if (!empty($data->pictureList)) {
                ?>
                    
                    <div class="row">
                        <?php
                        foreach ($data->pictureList as $value) {
                            if (!empty($value['path']) ) {
                        ?>
                                <div class="col-md-3 card-picture-item" id="image-item-<?php echo $value['id']; ?>">
                                    <button onclick="removeImages(<?php echo $value['id']; ?>)" type="button" class="close" aria-label="ลบไฟล์นี้">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                    <?php echo Upload::readfilePictureNoPermission('blog', $value['path']); ?>
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
                    'accept' => 'image/*',
                    'class' => 'caractboxes-img',
                    'multiple' => true
                ],
                'pluginOptions' => [
                    // 'initialPreview' => BlogHelper::getInitialPreviewFiles($data->pictureList, $pageType),
                    // 'initialPreviewConfig' => BlogHelper::getInitialPreviewConfigFiles($data->pictureList),
                    'browseLabel' =>  'เลือกไฟล์รูปภาพ',
                    'browseClass' => 'btn btn-purple btn-block',
                    'browseIcon' => '<i class="fas fa-upload mr-2"></i>',
                    'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'gif'],
                    'showCaption' => false,
                    'showPreview' => true,
                    'showRemove' => false,
                    'showUpload' => false,
                    'overwriteInitial' => false,
                    'msgPlaceholder' => 'เลือกไฟล์...',
                    'maxFileSize' => 2048
                ]
            ])->label(false);
            ?>
            <!-- end multi image -->


            <!-- อัปโหลดไฟล์ -->

            <input type="hidden" name="removeDocument" id="removeDocument">


            <label class="control-label">ไฟล์ประกอบ</label>
            <?php
            //print_r($mediaModel);
            if (!empty($data->documentList)) {
            ?>
                
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        foreach ($data->documentList as $value) {
                            if (!empty($value['path']) &&  $value['application_type'] == 'file') {

                                if ( $value['application_type'] = 'file') {
                                    $documentPath =  Upload::readFileDocumentNoPermission('document', $value['path']);
                                }
                        ?>
                                <div class="document-list" id="document-item-<?php echo $value['id']; ?>">

                                    <a href="<?php echo $documentPath ?>" target="_blank"> <?php echo $value['name']; ?></a>

                                    <button onclick="removeDocuments(<?php echo $value['id']; ?>)" type="button" class="btn btn-sm btn-danger" aria-label="ลบไฟล์นี้">
                                        <i class="fas fa-trash-alt"></i>
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
                    // 'initialPreview' => BlogHelper::getInitialPreviewFiles($data->documentList, $pageType),
                    // 'initialPreviewConfig' => BlogHelper::getInitialPreviewConfigFiles($data->documentList),
                    'browseLabel' =>  'เลือกไฟล์เอกสาร',
                    'browseClass' => 'btn btn-purple btn-block',
                    'browseIcon' => '<i class="fas fa-upload mr-2"></i>',
                    'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
                    'showCaption' => false,
                    'showPreview' => true,
                    'showRemove' => false,
                    'showUpload' => false,
                    'overwriteInitial' => false,
                    'msgPlaceholder' => 'เลือกไฟล์...',
                    'maxFileSize' => 2048
                ]
            ])->label(false);
            ?>

            <div class="clearfix"></div>
            

            <div class="d-flex justify-content-end mt-5">

                <div>
                    <button class="btn btn-success px-5 py-2">บันทึก</button>
                </div>
            </div>

        

    <?php ActiveForm::end(); ?>

</div>
