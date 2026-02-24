<?php

use yii\helpers\Url;
use kartik\depdrop\DepDrop;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use common\components\Upload;
use kartik\widgets\FileInput;
use frontend\components\ContentHelper;
use frontend\components\KeywordHelper;
use frontend\components\LocationHelper;
use frontend\components\TaxonomyHelper;
use frontend\components\PermissionAccess;

use yii\web\JsExpression;
use yii\helpers\Html;
$urlTaxonomy = \yii\helpers\Url::to(['/api/taxonomy']);

?>

<div class="row">
    <div class="col">
        <div class="form-group my-2 pl-1">

            <div role="radiogroup" aria-required="true" aria-invalid="false">
                <?php foreach ($pageList as $key => $value) : ?>

                    <?php
                    $urlRadioButton = ($actionType == 'create') ? "onclick=\"window.location='${key}';\"" : '';
                    $checkedRadio = ($key == $pageType) ? 'checked' : '';
                    $validClassRadio = ($key == $pageType) ? 'is-valid' : '';
                    ?>

                    <div class="custom-control custom-radio custom-control-inline" <?= $urlRadioButton; ?>>
                        <input type="radio" class="custom-control-input <?= $validClassRadio; ?>" name="Content[type_id]" value="<?= $key; ?>" <?= $checkedRadio; ?>>
                        <label class="custom-control-label" for="<?= $key; ?>"><?= $value; ?></label>
                    </div>

                <?php endforeach; ?>
            </div>

        </div>

        <div class="d-flex flex-row align-items-center my-4">
            <div class="icon-content ">
                <img src="/images/icon/S_Funji.svg">
            </div>
            <div>
                <span class="ml-3 text-green-1 h5"><?= $text->secondaryTitle; ?></span>
            </div>
        </div>


        <?= $form->field($model->content, 'name')->textInput()->input('text', $model->content->placeholder('name')) ?>

        <?= $form->field($model->contentFungi, 'other_name')->textInput()->input('text', $model->contentFungi->placeholder('other_name'));  ?>
                
        <?php /*
        <?= $form->field($model->contentFungi, 'features')->widget(\yii2jodit\JoditWidget::className(), [
            'settings' => [
                'height' => 'auto',
                'enableDragAndDropFileToEditor' => new \yii\web\JsExpression("true"),
                'buttons' => [
                    'source', '|',
                    'bold', 'strikethrough', 'underline', 'italic', 'align', '|',
                    'ul', 'ol', '|',
                    'outdent', 'indent', '|',
                    'font', 'fontsize', 'brush', 'paragraph', 'eraser', '|',
                    'image', 'video', 'file', 'table', 'link', '|',
                    'align', 'undo', 'redo',
                ],
            ],
            'options' => $model->contentFungi->placeholder('features'),

        ]) ?> 

        <label>ลักษณะ/คุณสมบัติ</label>
        <?php echo froala\froalaeditor\FroalaEditorWidget::widget([
                'model' => $model->contentFungi,
                'attribute' => 'features',
                'options' => [
                    // html attributes
                    
                    'id'=>'content-features'
                ],
                'clientOptions' => [
                    'placeholderText' => 'ลักษณะ/คุณสมบัติ',
                    'toolbarInline' => false,
                    'height' => 300,
                    'theme' => 'gray', //optional: dark, red, gray, royal
                    'language' => 'en_gb', // optional: ar, bs, cs, da, de, en_ca, en_gb, en_us ...
                    'imageUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                    'fileUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                    'toolbarButtons'   => ['fullscreen', 'bold', 'italic', 'underline', 'alignLeft', 'alignCenter', 'formatOLSimple', 'alignRight', 'paragraphFormat', 'fontFamily', 'fontSize', 'textColor', 'insertLink', 'insertImage', 'insertVideo', 'insertFile', 'undo', 'redo']
                ]
        ]); ?>
        <br/> */ ?>

        <?= $form->field($model->contentFungi, 'features')->textarea(['rows' => '6', 'class' => 'summernote-features']) ?>

        <?php /*

        <?= $form->field($model->contentFungi, 'benefit')->widget(\yii2jodit\JoditWidget::className(), [
            'settings' => [
                'height' => 'auto',
                'enableDragAndDropFileToEditor' => new \yii\web\JsExpression("true"),
                'buttons' => [
                    'source', '|',
                    'bold', 'strikethrough', 'underline', 'italic', 'align', '|',
                    'ul', 'ol', '|',
                    'outdent', 'indent', '|',
                    'font', 'fontsize', 'brush', 'paragraph', 'eraser', '|',
                    'image', 'video', 'file', 'table', 'link', '|',
                    'align', 'undo', 'redo',
                ],
            ],
            'options' => $model->contentFungi->placeholder('benefit'),

        ]) ?> 


        <label>ประโยชน์</label>
        <?php echo froala\froalaeditor\FroalaEditorWidget::widget([
                'model' => $model->contentFungi,
                'attribute' => 'benefit',
                'options' => [
                    // html attributes
                    
                    'id'=>'content-benefit'
                ],
                'clientOptions' => [
                    'placeholderText' => 'ประโยชน์',
                    'toolbarInline' => false,
                    'height' => 300,
                    'theme' => 'gray', //optional: dark, red, gray, royal
                    'language' => 'en_gb', // optional: ar, bs, cs, da, de, en_ca, en_gb, en_us ...
                    'imageUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                    'fileUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                    'toolbarButtons'   => ['fullscreen', 'bold', 'italic', 'underline', 'alignLeft', 'alignCenter', 'formatOLSimple', 'alignRight', 'paragraphFormat', 'fontFamily', 'fontSize', 'textColor', 'insertLink', 'insertImage', 'insertVideo', 'insertFile', 'undo', 'redo']
                ]
        ]); ?>
        <br/> */ ?>

        <?= $form->field($model->contentFungi, 'benefit')->textarea(['rows' => '6', 'class' => 'summernote-benefit']) ?>

        <?= $form->field($model->contentFungi, 'found_source')->textInput()->input('text', $model->contentFungi->placeholder('found_source'));  ?>

        <div class="row">
            <div class="col">
                <?php
                echo $form->field($model->content, 'region_id')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(LocationHelper::getRegionList(), 'id', 'name_th'),
                    'options' => [
                        'id' => 'content_region_id',
                        'placeholder' => $model->content->placeholder('region_id')['placeholder']
                    ],
                ]);
                ?>
            </div>
            <div class="col">
                <?php

                echo $form->field($model->content, 'province_id')->widget(DepDrop::classname(), [
                    'data' => ArrayHelper::map(LocationHelper::getProvinceByRegionId($model->content->region_id), 'id', 'name'),
                    'type' => DepDrop::TYPE_SELECT2,
                    'options' => [
                        'id' => 'content_province_id',
                        'placeholder' => $model->content->placeholder('province_id')['placeholder'],
                    ],
                    'pluginOptions' => [
                        'loadingText' => 'Loading...',
                        'depends' => ['content_region_id'],
                        'placeholder' => $model->content->placeholder('province_id')['placeholder'],
                        'url' => Url::to(['/api/location/get-province-list'])
                    ]
                ]);

                ?>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <?php

                echo $form->field($model->content, 'district_id')->widget(DepDrop::classname(), [
                    'data' => ArrayHelper::map(LocationHelper::getDistrictByProvinceId($model->content->province_id), 'id', 'name'),
                    'type' => DepDrop::TYPE_SELECT2,
                    'options' => [
                        'id' => 'content_district_id',
                        'placeholder' => $model->content->placeholder('district_id')['placeholder'],
                    ],
                    'pluginOptions' => [
                        'loadingText' => 'Loading...',
                        'depends' => ['content_province_id'],
                        'placeholder' => $model->content->placeholder('district_id')['placeholder'],
                        'url' => Url::to(['/api/location/get-district-list'])
                    ]
                ]);

                ?>
            </div>
            <div class="col">
                <?php

                echo $form->field($model->content, 'subdistrict_id')->widget(DepDrop::classname(), [
                    'data' => ArrayHelper::map(LocationHelper::getSubdistrictByDistrictId($model->content->district_id), 'id', 'name'),
                    'type' => DepDrop::TYPE_SELECT2,
                    'options' => [
                        'id' => 'content_subdistrict_id',
                        'placeholder' => $model->content->placeholder('subdistrict_id')['placeholder'],
                    ],
                    'pluginOptions' => [
                        'loadingText' => 'Loading...',
                        'depends' => ['content_district_id'],
                        'placeholder' => $model->content->placeholder('subdistrict_id')['placeholder'],
                        'url' => Url::to(['/api/location/get-subdistrict-list'])
                    ]
                ]);

                ?>
            </div>

            <div class="col">
                <?php

                echo $form->field($model->content, 'zipcode_id')->widget(DepDrop::classname(), [
                    'data' => ArrayHelper::map(LocationHelper::getZipcodeBySubdistrictId($model->content->subdistrict_id), 'id', 'name'),
                    'type' => DepDrop::TYPE_SELECT2,
                    'options' => [
                        'id' => 'content_zipcode_id',
                        'placeholder' => $model->content->placeholder('zipcode_id')['placeholder'],
                    ],
                    'pluginOptions' => [
                        'loadingText' => 'Loading...',
                        'depends' => ['content_subdistrict_id'],
                        'placeholder' => $model->content->placeholder('zipcode_id')['placeholder'],
                        'url' => Url::to(['/api/location/get-zipcode-list'])
                    ]
                ]);

                ?>
            </div>
        </div>

        <span class="h6">พิกัดแผนที่</span>
        <div class="row justify-content-start">
            <div class="col">
                <?= $form->field($model->content, 'latitude')->textInput()->input('text', $model->content->placeholder('latitude'));  ?>
            </div>
            <div class="col">
                <?= $form->field($model->content, 'longitude')->textInput()->input('text', $model->content->placeholder('longitude'));  ?>
            </div>

        </div>

        <div class="row">
            <div class="col">
                <div id="content-google-map" class="content-google-map" style="min-height:400px;"></div>
            </div>
        </div>

        <div class="row mt-5 mb-2">
            <div class="col">
                <?= $form->field($model->content, 'picture_path', [
                    'template' => "{label}\n{input}\n<div> รูปภาพควรมีขนาด 1532x800 pixel มีขนาดไม่เกิน 5 MB และ ต้องเป็นไฟล์นามสกุล jpg, jpeg, png หรือ gif เท่านั้น </div>\n{hint}\n{error}",
                    'labelOptions' => ['class' => 'control-label']
                ])->widget(FileInput::classname(), [
                    'options' => ['accept' => 'image/*'],
                    'pluginOptions' => [
                        'initialPreview' => ContentHelper::getInitialPreviewFiles($model->content->picture_path, $pageType),
                        'initialPreviewConfig' => ContentHelper::getInitialPreviewConfigFiles($model->content->picture_path, $pageType),
                        'browseLabel' =>  'เลือกไฟล์รูปภาพ',
                        'browseClass' => 'btn btn-purple btn-block',
                        'browseIcon' => '<i class="fas fa-upload mr-2"></i>',
                        'allowedFileExtensions' => ['jpg', 'jpeg', 'png', 'gif'],
                        'showCaption' => false,
                        'showPreview' => true,
                        'showRemove' => false,
                        'showUpload' => false,
                        'msgPlaceholder' => 'เลือกไฟล์...',
                        'maxFileSize' => 5120
                    ]
                ]); ?>
            </div>
        </div>

        <div class="row mt-4 mb-3">
            <div class="col">

                <?php
                if (!empty($data->pictureList)) {
                ?>
                    <label class="control-label">รูปประกอบ (แสดงเป็น Gallery)</label>
                    <div class="row">
                        <?php
                        foreach ($data->pictureList as $value) {
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

                <input type="hidden" name="stack_id_remove_file" id="stack_id_remove_file">
                <?= $form->field($model->content, 'files', [
                    'template' => "{label}\n{input}\n<div> รูปภาพมีขนาดไม่เกิน 5 MB และต้องเป็นไฟล์นามสกุล jpg, jpeg, png หรือ gif เท่านั้น </div>\n{hint}\n{error}",
                    'labelOptions' => ['class' => 'control-label'],
                ])->widget(FileInput::classname(), [
                    'options' => [
                        'accept' => 'image/*',
                        'id' => 'content_files',
                        'multiple' => true
                    ],
                    'pluginOptions' => [
                        // 'initialPreview' => ContentHelper::getInitialPreviewFiles($data->pictureList, $pageType),
                        // 'initialPreviewConfig' => ContentHelper::getInitialPreviewConfigFiles($data->pictureList),
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
                        'maxFileSize' => 5120
                    ]
                ])->label(false); ?>
            </div>
        </div>

        <!-- <span class="h6">รูปภาพ</span>
        <div class="d-flex justify-content-start align-items-start align-items-center">
            <div class="mr-3">
                <label for="" class="btn btn-purple px-3 py-2" style="width: auto;">
                    <i class="fas fa-upload mr-2"></i> <span>เลือกไลฟ์รูปภาพ</span>
                </label>
            </div>
            <div>
                <span>สามารถอัปโหลดไฟล์ JPEG และ PNG ได้เท่านั้น ขนาดไม่เกิน 2MB</span>
            </div>
        </div> -->
        
        <?php /*
        <?= $form->field($model->contentFungi, 'other_information')->widget(\yii2jodit\JoditWidget::className(), [
            'settings' => [
                'height' => 'auto',
                'enableDragAndDropFileToEditor' => new \yii\web\JsExpression("true"),
                'buttons' => [
                    'source', '|',
                    'bold', 'strikethrough', 'underline', 'italic', 'align', '|',
                    'ul', 'ol', '|',
                    'outdent', 'indent', '|',
                    'font', 'fontsize', 'brush', 'paragraph', 'eraser', '|',
                    'image', 'video', 'file', 'table', 'link', '|',
                    'align', 'undo', 'redo',
                ],
            ],
            'options' => $model->contentFungi->placeholder('other_information'),

        ]) ?> 


        <label>ข้อมูลอื่น ๆ ที่ฉันรู้</label>
        <?php echo froala\froalaeditor\FroalaEditorWidget::widget([
                'model' => $model->contentFungi,
                'attribute' => 'other_information',
                'options' => [
                    // html attributes
                    
                    'id'=>'content-other_information'
                ],
                'clientOptions' => [
                    'placeholderText' => 'ข้อมูลอื่น ๆ ที่ฉันรู้',
                    'toolbarInline' => false,
                    'height' => 300,
                    'theme' => 'gray', //optional: dark, red, gray, royal
                    'language' => 'en_gb', // optional: ar, bs, cs, da, de, en_ca, en_gb, en_us ...
                    'imageUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                    'fileUploadURL' => \yii\helpers\Url::to(['api/upload/']),
                    'toolbarButtons'   => ['fullscreen', 'bold', 'italic', 'underline', 'alignLeft', 'alignCenter', 'formatOLSimple', 'alignRight', 'paragraphFormat', 'fontFamily', 'fontSize', 'textColor', 'insertLink', 'insertImage', 'insertVideo', 'insertFile', 'undo', 'redo']
                ]
        ]); ?>
        <br/> */ ?>

        <?= $form->field($model->contentFungi, 'other_information')->textarea(['rows' => '6', 'class' => 'summernote-other_information']) ?>

        <?= $form->field($model->contentFungi, 'season')->textInput()->input('text', $model->contentFungi->placeholder('season'));  ?>
        <?= $form->field($model->contentFungi, 'ability')->textInput()->input('text', $model->contentFungi->placeholder('ability'));  ?>
        <?= $form->field($model->contentFungi, 'common_name')->textInput()->input('text', $model->contentFungi->placeholder('common_name'));  ?>
        <?= $form->field($model->contentFungi, 'scientific_name')->textInput()->input('text', $model->contentFungi->placeholder('common_name'));  ?>
        <?= $form->field($model->contentFungi, 'family_name')->textInput()->input('text', $model->contentFungi->placeholder('family_name'));  ?>
        

        <?php /*
        <!-- แหล่งที่มาของข้อมูล -->
        <?= $form->field($model->content, 'source_information')->widget(\yii2jodit\JoditWidget::className(), [
            'settings' => [
                'height' => 'auto',
                'enableDragAndDropFileToEditor' => new \yii\web\JsExpression("true"),
                'buttons' => [
                    'source', '|',
                    'bold', 'strikethrough', 'underline', 'italic', 'align', '|',
                    'ul', 'ol', '|',
                    'outdent', 'indent', '|',
                    'font', 'fontsize', 'brush', 'paragraph', 'eraser', '|',
                    'image', 'video', 'file', 'table', 'link', '|',
                    'align', 'undo', 'redo',
                ],
            ],
            'options' => $model->content->placeholder('source_information'),


        ]) ?> */ ?>

        <?= $form->field($model->content, 'photo_credit')->textInput()->input('text', $model->content->placeholder('photo_credit'));  ?>

        <?= $form->field($model->content, 'source_information')->textarea(['rows' => '3'], $model->content->placeholder('source_information'));  ?>

        <?php /*
        <?php
        echo $form->field($model->content, 'taxonomy_list')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(TaxonomyHelper::getTaxonomyList(), 'id', 'name'),
            'options' => ['placeholder' => $model->content->placeholder('taxonomy_list')['placeholder'], 'multiple' => true],
            'pluginOptions' => [
                'tags' => true,
                // 'tokenSeparators' => [',', ' '],
            ],
        ]);
        ?> */ ?>


        <?php 
            echo $form->field($model->content, 'taxonomy_list')->widget(Select2::classname(), [
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


        <?php if(PermissionAccess::FrontendAccess('approved_content', 'function')): ?>
        <br>
        <hr>
        <label>สำหรับอาจารย์</label>
        
        <?= $form->field($model->content, 'note')->textInput()->input('text')->label("หมายเหตุ");  ?>
        
        <?= $form->field($model->content, 'status')->dropDownList([ 'pending' => 'รออนุมัติ', 'approved' => 'อนุมัติ', 'rejected' => 'ไม่อนุมัติ', ], ['prompt' => 'เลือกสถานะ']) ?>
        <?php endif; ?>

        <div class="d-flex justify-content-end mt-5">
            <div class="mr-3">
                <button type="reset" class="btn btn-outline-secondary px-5 py-2">ยกเลิก</button>
            </div>
            <div>
                <button class="btn btn-success px-5 py-2">บันทึก</button>
            </div>
        </div>

    </div>

</div>