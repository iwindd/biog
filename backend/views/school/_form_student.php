<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\jui\AutoComplete;

use yii\web\JsExpression;
use yii\jui\JuiAsset;

// print "<pre>";
// print_r($modelStudent);
// print "</pre>";
// exit();


?>


<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_student',
    'widgetBody' => '.container-students',
    'widgetItem' => '.student-item',
    'min' => 1,
    'insertButton' => '.add-student'.$indexRef,
    'deleteButton' => '.remove-student'.$indexRef,
    'model' => $modelStudent[0],
    'formId' => 'dynamic-form',
    'formFields' => [
        'description'
    ],
]); ?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>อีเมลนักเรียน</th>
            <th class="text-center add-student-auto">
                <?php echo '<button type="button" class="add-student'.$indexRef.' add-student-autocomplete  btn btn-success btn-xs"><span class="glyphicon glyphicon-plus"></span></button>'; ?>
                
            </th>
        </tr>
    </thead>
    <tbody class="container-students">
    <?php foreach ($modelStudent as $indexPerson => $modelPerson): ?>
        <tr class="student-item">
            <td class="vcenter">
                <?php
                    // necessary for update action.
                    if (! $modelPerson->isNewRecord) {
                        echo Html::activeHiddenInput($modelPerson, "[{$indexPerson}]id");
                    }
                ?>
       
                <?= $form->field($modelPerson, "[{$indexPerson}]studentName")->label(false)->textInput(['maxlength' => true, 'class' => "form-control student-name-autocomplete"]) ?>
    
                <?= $form->field($modelPerson, "[{$indexPerson}]id")->label(false)->hiddenInput(['class' => "field-usercommunity-id"]) ?>

                </td>
            <td class="text-center vcenter" style="width: 90px;">
                <button type="button" class="remove-student<?php echo $indexRef; ?> btn btn-danger btn-xs"><span class="glyphicon glyphicon-minus"></span></button>
            </td>
        </tr>
     <?php endforeach; ?>
    </tbody>
</table>
<?php DynamicFormWidget::end(); ?>




