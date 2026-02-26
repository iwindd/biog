<?php

use yii\helpers\Html;
use yii\helpers\Url;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\jui\AutoComplete;
use yii\web\JsExpression;
use yii\jui\JuiAsset;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $modelImageSource backend\models\ContentImageSource[] */

$shortUrlBase = Yii::$app->params['shortUrlDomain'] ?? '';
$toggleUrl = Url::to(['/short-url/toggle-short-url']);
$isShort = false;
?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h4><i class="glyphicon glyphicon-picture"></i> แหล่งที่มาของภาพ</h4>
    </div>
    <div class="panel-body">
         <?php DynamicFormWidget::begin([
            'widgetContainer' => 'dynamicform_image_source',
            'widgetBody' => '.container-image-sources',
            'widgetItem' => '.image-source-item',
            'min' => 1,
            'insertButton' => '.add-image-source',
            'deleteButton' => '.remove-image-source',
            'model' => $modelImageSource[0],
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
                    <th>URL อ้างอิง</th>
                    <th class="text-center" style="width: 50px;">
                        <button type="button" class="add-image-source btn btn-success btn-xs"><span class="glyphicon glyphicon-plus"></span></button>
                    </th>
                </tr>
            </thead>
            <tbody class="container-image-sources">
            <?php foreach ($modelImageSource as $indexImageSource => $modelSource): ?>
                <tr class="image-source-item">
                    <td class="vcenter">
                        <?php
                            // necessary for update action.
                            if (! $modelSource->isNewRecord) {
                                echo Html::activeHiddenInput($modelSource, "[{$indexImageSource}]id");
                            }
                        ?>
                        <?= $form->field($modelSource, "[{$indexImageSource}]source_name")->label(false)->textInput(['maxlength' => true, 'placeholder' => 'ชื่อแหล่งที่มา']) ?>
                    </td>
                    <td class="vcenter">
                        <?= $form->field($modelSource, "[{$indexImageSource}]author")->label(false)->textInput(['maxlength' => true, 'placeholder' => 'ผู้จัดทำ']) ?>
                    </td>
                    <td class="vcenter">
                        <?= $form->field($modelSource, "[{$indexImageSource}]published_date")->label(false)->widget(
                            \kartik\date\DatePicker::classname(), [
                            'options' => [
                                'placeholder' => 'วันที่เผยแพร่',
                                'class' => 'dynamic-date-picker',
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
                        ?>
                        <div class="input-group">
                            <?= $form->field($modelSource, "[{$indexImageSource}]reference_url", [
                                'template' => '{input}',
                                'options' => ['tag' => false],
                            ])->textInput([
                                'maxlength' => true,
                                'placeholder' => 'URL อ้างอิง',
                                'class' => 'form-control img-source-url-input',
                                'readonly' => $isShort,
                            ]) ?>
                            <span class="input-group-btn" style="vertical-align: top;">
                                <button type="button"
                                    class="btn btn-<?= $isShort ? 'warning' : 'info' ?> btn-toggle-short-url"
                                    title="<?= $isShort ? 'เปลี่ยนกลับเป็น URL เต็ม' : 'ย่อ URL' ?>"
                                    data-mode="<?= $isShort ? 'expand' : 'shorten' ?>"
                                    style="height: 34px;">
                                    <i class="glyphicon glyphicon-<?= $isShort ? 'resize-full' : 'resize-small' ?>"></i>
                                </button>
                            </span>
                        </div>
                    </td>
                    <td class="text-center vcenter">
                        <button type="button" class="remove-image-source btn btn-danger btn-xs"><span class="glyphicon glyphicon-minus"></span></button>
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
var shortUrlBase = '{$shortUrlBase}';
var toggleEndpoint = '{$toggleUrl}';

// Toggle short URL button click handler
$(document).on('click', '.dynamicform_image_source .btn-toggle-short-url', function(e) {
    e.preventDefault();
    var \$btn = $(this);
    var \$inputField = \$btn.closest('.input-group').find('.img-source-url-input');
    var currentUrl = \$inputField.val().trim();
    var currentMode = \$btn.data('mode'); // 'shorten' or 'expand'

    if (!currentUrl) {
        alert('ไม่พบ URL สำหรับย่อ');
        return;
    }

    \$btn.prop('disabled', true);
    var origHtml = \$btn.html();
    \$btn.html('<i class="glyphicon glyphicon-refresh"></i>');

    $.ajax({
        url: toggleEndpoint,
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
$(".dynamicform_image_source").on("afterInsert", function(e, item) {
    var \$input = $(item).find('.dynamic-date-picker');
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
JS;
$this->registerJs($js);
?>
