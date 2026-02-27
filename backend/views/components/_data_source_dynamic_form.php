<?php

use yii\helpers\Html;
use yii\helpers\Url;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\jui\AutoComplete;
use yii\web\JsExpression;
use yii\jui\JuiAsset;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $modelDataSource backend\models\ContentDataSource[] */

$shortUrlBase = Yii::$app->params['shortUrlDomain'] ?? '';
$toggleUrl = Url::to(['/short-url/toggle-short-url']);
$isShort = false;
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><i class="glyphicon glyphicon-book"></i> แหล่งที่มาของข้อมูล</h4>
    </div>
    <div class="panel-body">
         <?php DynamicFormWidget::begin([
            'widgetContainer' => 'dynamicform_data_source',
            'widgetBody' => '.container-data-sources',
            'widgetItem' => '.data-source-item',
            'min' => 0,
            'insertButton' => '.add-data-source',
            'deleteButton' => '.remove-data-source',
            'model' => $modelDataSource[0],
            'formId' => $form->id,
            'formFields' => [
                'source_name',
                'author',
                'published_date',
                'reference_url'
            ],
        ]); ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ชื่อแหล่งที่มา</th>
                    <th>ผู้จัดทำ</th>
                    <th>วันที่เผยแพร่</th>
                    <th>URL อ้างอิง <span style="color:red;">*</span></th>
                    <th class="text-center" style="width: 50px;">
                        <button type="button" class="add-data-source btn btn-success btn-xs"><span class="glyphicon glyphicon-plus"></span></button>
                    </th>
                </tr>
            </thead>
            <tbody class="container-data-sources">
            <?php foreach ($modelDataSource as $indexDataSource => $modelSource): ?>
                <tr class="data-source-item">
                    <td class="vcenter">
                        <?php
                            // necessary for update action.
                            if (! $modelSource->isNewRecord) {
                                echo Html::activeHiddenInput($modelSource, "[{$indexDataSource}]id");
                            }
                        ?>
                        <?= $form->field($modelSource, "[{$indexDataSource}]source_name")->label(false)->textInput(['maxlength' => true, 'placeholder' => 'ชื่อแหล่งที่มา']) ?>
                    </td>
                    <td class="vcenter">
                        <?= $form->field($modelSource, "[{$indexDataSource}]author")->label(false)->textInput(['maxlength' => true, 'placeholder' => 'ผู้จัดทำ']) ?>
                    </td>
                    <td class="vcenter">
                        <?php
                            echo $form->field($modelSource, "[{$indexDataSource}]published_date")->label(false)->widget(
                            \kartik\date\DatePicker::classname(), [
                            'options' => [
                                'placeholder' => 'วันที่เผยแพร่',
                                'class' => 'dynamic-data-date-picker',
                            ],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true
                            ]
                        ]); ?>
                    </td>
                    <td class="vcenter">
                        <?php
                            $currentUrl = $modelSource->reference_url ?? '';
                            $isShort = !empty($shortUrlBase) && !empty($currentUrl) && strpos($currentUrl, $shortUrlBase) === 0;
                            
                            $toggleBtn = Html::button('<i class="glyphicon glyphicon-' . ($isShort ? 'resize-full' : 'resize-small') . '"></i>', [
                                'class' => 'btn btn-' . ($isShort ? 'warning' : 'info') . ' btn-toggle-short-url-data',
                                'title' => $isShort ? 'เปลี่ยนกลับเป็น URL เต็ม' : 'ย่อ URL',
                                'data-mode' => $isShort ? 'expand' : 'shorten',
                                'style' => 'height: 34px;'
                            ]);
                            
                            echo $form->field($modelSource, "[{$indexDataSource}]reference_url", [
                                'template' => '<div class="input-group">{input}<span class="input-group-btn" style="vertical-align: top;">' . $toggleBtn . '</span></div>{error}',
                            ])->textInput([
                                'maxlength' => true,
                                'placeholder' => 'URL อ้างอิง',
                                'class' => 'form-control data-source-url-input',
                                'readonly' => $isShort,
                            ]);
                        ?>
                    </td>
                    <td class="text-center vcenter">
                        <button type="button" class="remove-data-source btn btn-danger btn-xs"><span class="glyphicon glyphicon-minus"></span></button>
                    </td>
                </tr>
             <?php endforeach; ?>
            </tbody>
        </table>

        <?php DynamicFormWidget::end(); ?>
    </div>
</div>

<?php
$js = <<<JS
var dataShortUrlBase = '{$shortUrlBase}';
var dataToggleEndpoint = '{$toggleUrl}';

// Toggle short URL button click handler for data source
$(document).on('click', '.dynamicform_data_source .btn-toggle-short-url-data', function(e) {
    e.preventDefault();
    var \$btn = $(this);
    var \$inputField = \$btn.closest('.input-group').find('.data-source-url-input');
    var currentUrl = \$inputField.val().trim();
    var currentMode = \$btn.data('mode');

    if (!currentUrl) {
        alert('กรุณาใส่ URL ก่อน');
        return;
    }

    \$btn.prop('disabled', true);
    var origHtml = \$btn.html();
    \$btn.html('<i class="glyphicon glyphicon-refresh"></i>');

    $.ajax({
        url: dataToggleEndpoint,
        method: 'POST',
        data: {
            url: currentUrl,
            mode: currentMode,
            _csrf: yii.getCsrfToken()
        },
        success: function(res) {
            if (res.success) {
                \$inputField.val(res.url);
                if (res.mode === 'short') {
                    \$btn.data('mode', 'expand');
                    \$btn.attr('title', 'เปลี่ยนกลับเป็น URL เต็ม');
                    \$btn.removeClass('btn-info').addClass('btn-warning');
                    \$btn.html('<i class="glyphicon glyphicon-resize-full"></i>');
                    \$inputField.prop('readonly', true);
                } else {
                    \$btn.data('mode', 'shorten');
                    \$btn.attr('title', 'ย่อ URL');
                    \$btn.removeClass('btn-warning').addClass('btn-info');
                    \$btn.html('<i class="glyphicon glyphicon-resize-small"></i>');
                    \$inputField.prop('readonly', false);
                }
            } else {
                alert(res.message || 'เกิดข้อผิดพลาด');
                \$btn.html(origHtml);
            }
        },
        error: function() {
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            \$btn.html(origHtml);
        },
        complete: function() {
            \$btn.prop('disabled', false);
        }
    });
});

// DatePicker re-init for dynamically added rows
$(".dynamicform_data_source").on("afterInsert", function(e, item) {
    var \$input = $(item).find('.dynamic-data-date-picker');
    var \$dateGroup = \$input.closest('.input-group.date');

    if (\$dateGroup.length > 0) {
        \$dateGroup.kvDatepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            todayHighlight: true
        });
    } else {
        \$input.kvDatepicker({
            autoclose: true,
            format: 'yyyy-mm-dd',
            todayHighlight: true
        });
    }
});

// Start with 0 rows on create if the row is empty (use timeout to ensure widget is ready)
setTimeout(function() {
    if (window.location.href.indexOf('create') > -1) {
        if ($(".data-source-item").length === 1) {
            var hasValue = false;
            $(".data-source-item:first input").each(function() {
                if ($(this).val() && $(this).attr('type') !== 'hidden') hasValue = true;
            });
            if (!hasValue) {
                $(".remove-data-source:first").click();
            }
        }
    }
}, 100);
JS;
$this->registerJs($js);
?>
