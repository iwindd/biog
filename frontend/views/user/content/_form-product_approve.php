<?php

use yii\helpers\Url;
use kartik\date\DatePicker;
use kartik\depdrop\DepDrop;
use kartik\widgets\Select2;
use common\components\_;
use yii\helpers\ArrayHelper;
use frontend\components\ContentHelper;
use frontend\components\KeywordHelper;
use frontend\components\LocationHelper;
use frontend\components\TaxonomyHelper;

?>

<div class="row">
    <div class="col">
        <div class="form-group field-user-email">

            <div id="user-email" role="radiogroup" aria-required="true" aria-invalid="false">
                <?php foreach ($pageList as $key => $value) : ?>

                    <div class="custom-control custom-radio custom-control-inline" >
                        <input type="radio" class="custom-control-input <?php echo $key == $pageType ? 'is-valid' : ''; ?>" name="Content[type_id]" value="<?= $key; ?>" <?php echo $key == $pageType ? 'checked' : ''; ?>>
                        <label class="custom-control-label" for="<?= $key; ?>"><?= $value; ?></label>
                    </div>

                <?php endforeach; ?>
            </div>

        </div>


        <!-- ชื่อผลิตภัณฑ์ -->
        <?= $form->field($model->content, 'name', ['inputOptions' => ['readonly' => true]])->textInput()->input('text', $model->content->placeholder('name')) ?>

        <!-- หมวดหมู่ผลิตภัณฑ์ -->
        <?php
        echo $form->field($model->contentProduct, 'product_category_id', ['inputOptions' => ['readonly' => true]])->widget(Select2::classname(), [
            'data' => ArrayHelper::map(ContentHelper::getProductCategoryList(), 'id', 'name'),
            'options' => [
                // 'id' => 'content_product_category_id',
                'disabled' => true,
                'placeholder' => $model->contentProduct->placeholder('product_category_id')['placeholder']
            ],
        ]);
        ?>

        <!-- จุดเด่น/ประโยชน์ -->
        <?= $form->field($model->contentProduct, 'product_features', ['inputOptions' => ['readonly' => true]])->widget(\yii2jodit\JoditWidget::className(), [
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
            'options' => $model->contentProduct->placeholder('product_features'),

        ]) ?>

        <!-- ราคาขาย -->
        <?= $form->field($model->contentProduct, 'product_price', ['inputOptions' => ['readonly' => true]])->textInput()->input('text', $model->contentProduct->placeholder('product_price'));  ?>

        <!-- สถานที่ผลิต/จำหน่าย -->
        <?= $form->field($model->contentProduct, 'product_distribution_location', ['inputOptions' => ['readonly' => true]])->textInput()->input('text', $model->contentProduct->placeholder('product_distribution_location'));  ?>

        <!-- ที่อยู่ -->
        <?= $form->field($model->contentProduct, 'product_address', ['inputOptions' => ['readonly' => true]])->textInput()->input('text', $model->contentProduct->placeholder('product_address'));  ?>

        <div class="row">
            <div class="col">
                <?php
                echo $form->field($model->content, 'region_id')->widget(Select2::classname(), [
                    'data' => ArrayHelper::map(LocationHelper::getRegionList(), 'id', 'name_th'),
                    'options' => [
                        'id' => 'content_region_id',
                        'disabled' => true,
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
                        'disabled' => true,
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
                        'disabled' => true,
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

                echo $form->field($model->content, 'subdistrict_id', ['inputOptions' => ['readonly' => true]])->widget(DepDrop::classname(), [
                    'data' => ArrayHelper::map(LocationHelper::getSubdistrictByDistrictId($model->content->district_id), 'id', 'name'),
                    'type' => DepDrop::TYPE_SELECT2,
                    'options' => [
                        'id' => 'content_subdistrict_id',
                        'disabled' => true,
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
                        'disabled' => true,
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

        <!-- เบอร์โทรศัพท์ติดต่อ -->
        <?= $form->field($model->contentProduct, 'product_phone', ['inputOptions' => ['readonly' => true]])->textInput()->input('text', $model->contentProduct->placeholder('product_phone'));  ?>


        <span class="h6">พิกัดแผนที่</span>
        <div class="row justify-content-start">
            <div class="col">
                <?= $form->field($model->content, 'latitude', ['inputOptions' => ['readonly' => true]])->textInput()->input('text', $model->content->placeholder('latitude'));  ?>
            </div>
            <div class="col">
                <?= $form->field($model->content, 'longitude', ['inputOptions' => ['readonly' => true]])->textInput(['readonly' => true])->input('text', $model->content->placeholder('longitude'));  ?>
            </div>

        </div>

        <div class="row">
            <div class="col">
                <div id="content-google-map" class="content-google-map" style="min-height:400px;"></div>
            </div>
        </div>

        <span class="h6">รูปภาพ</span>
        <div class="d-flex justify-content-start align-items-start align-items-center">
            <?php if(!empty($model->content->picture_path)){ ?>
                <img width="100%" src="/files/content-product/<?php echo $model->content->picture_path ?>">
            <?php } ?>
        </div>

        <!-- วัตถุดิบหลัก -->
        <?= $form->field($model->contentProduct, 'product_main_material', ['inputOptions' => ['readonly' => true]])->textInput()->input('text', $model->contentProduct->placeholder('product_main_material'));  ?>
        <!-- แหล่งวัตถุดิบ -->
        <?= $form->field($model->contentProduct, 'product_sources_material', ['inputOptions' => ['readonly' => true]])->textInput()->input('text', $model->contentProduct->placeholder('product_sources_material'));  ?>
  
        <!-- ข้อมูลอื่นๆ ที่ฉันรู้ -->
        <?= $form->field($model->content, 'other_information', ['inputOptions' => ['readonly' => true]])->widget(\yii2jodit\JoditWidget::className(), [
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
            'options' => $model->content->placeholder('other_information'),
  

        ]) ?>

        <!-- ข้อมูลการติดต่อ -->
        <?= $form->field($model->contentProduct, 'contact', ['inputOptions' => ['readonly' => true]])->textInput()->input('text', $model->contentProduct->placeholder('contact'));  ?>

        <?php /*
        <!-- แหล่งที่มาของข้อมูล -->
        <?= $form->field($model->content, 'source_information', ['inputOptions' => ['readonly' => true]])->widget(\yii2jodit\JoditWidget::className(), [
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

        <?= $form->field($model->content, 'source_information', ['inputOptions' => ['readonly' => true]])->textarea(['rows' => '3'], $model->content->placeholder('source_information'));  ?>

        <?php
        echo $form->field($model->content, 'taxonomy_list')->widget(Select2::classname(), [
            'data' => ArrayHelper::map(TaxonomyHelper::getTaxonomyList(), 'id', 'name'),
            'options' => ['placeholder' => $model->content->placeholder('taxonomy_list')['placeholder'], 'multiple' => true],
            'pluginOptions' => [
                'tags' => true,
                'disabled' => true,
                // 'tokenSeparators' => [',', ' '],
            ],
        ]);
        ?>
        <br>
        <hr>
        <label>สำหรับอาจารย์</label>
        
        <?= $form->field($model->content, 'note')->textInput()->input('text')->label("หมายเหตุ");  ?>
        
        <?=$form->field($model->content, 'status')->dropDownList([ 'pending' => 'รออนุมัติ', 'approved' => 'อนุมัติ', 'rejected' => 'ไม่อนุมัติ', ], ['prompt' => 'เลือกสถานะ']) ?>
        <div class="d-flex justify-content-end mt-5">
            <div class="mr-3">
                <button class="btn btn-outline-secondary px-5 py-2">ยกเลิก</button>
            </div>
            <div>
                <button class="btn btn-success px-5 py-2">บันทึก</button>
            </div>
        </div>

    </div>

</div>