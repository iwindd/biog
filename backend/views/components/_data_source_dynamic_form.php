<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\jui\AutoComplete;
use yii\web\JsExpression;
use yii\jui\JuiAsset;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $modelDataSource backend\models\ContentDataSource[] */

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
            'min' => 1,
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
                        <?= $form->field($modelSource, "[{$indexDataSource}]reference_url")->label(false)->textInput(['maxlength' => true, 'placeholder' => 'URL อ้างอิง']) ?>
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
$(".dynamicform_data_source").on("afterInsert", function(e, item) {
    var \$input = $(item).find('.dynamic-data-date-picker');
    var \$dateGroup = \$input.closest('.input-group.date');
    
    // Krajee's DatePicker (used by Kartik DatePicker) initializes with kvDatepicker
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
