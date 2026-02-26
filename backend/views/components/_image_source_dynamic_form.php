<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\jui\AutoComplete;
use yii\web\JsExpression;
use yii\jui\JuiAsset;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $modelImageSource backend\models\ContentImageSource[] */

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
                            'options' => ['placeholder' => 'วันที่เผยแพร่'],
                            'pluginOptions' => [
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd',
                                'todayHighlight' => true
                            ]
                        ]); ?>
                    </td>
                    <td class="vcenter">
                        <?= $form->field($modelSource, "[{$indexImageSource}]reference_url")->label(false)->textInput(['maxlength' => true, 'placeholder' => 'URL อ้างอิง']) ?>
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
