<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\jui\AutoComplete;
use yii\helpers\Url;

use yii\web\JsExpression;
use yii\jui\JuiAsset;

// print "<pre>";
// print_r($modelTeacher);
// print "</pre>";
// exit();


?>


<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_inner',
    'widgetBody' => '.container-thachers',
    'widgetItem' => '.thacher-item',
    'min' => 1,
    'insertButton' => '.add-thacher'.$indexRef,
    'deleteButton' => '.remove-thacher'.$indexRef,
    'model' => $modelTeacher[0],
    'formId' => 'dynamic-form',
    'formFields' => [
        'description'
    ],
]); ?>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>อีเมลคุณครู</th>
            <th class="text-center add-thacher-auto">
                <?php echo '<button type="button" class="add-thacher'.$indexRef.' add-thacher-autocomplete  btn btn-success btn-xs"><span class="glyphicon glyphicon-plus"></span></button>'; ?>
                
            </th>
        </tr>
    </thead>
    <tbody class="container-thachers">
    <?php foreach ($modelTeacher as $indexPerson => $modelPerson): ?>
        <tr class="thacher-item">
            <td class="vcenter">
                <?php

                    // print "<pre>";
                    // print_r($modelPerson);
                    // print '</pre>';
                    // exit();
                    // necessary for update action.
                    if (! $modelPerson->isNewRecord) {
                        echo Html::activeHiddenInput($modelPerson, "[{$indexPerson}]id");
                    }
                ?>
       
                <?= $form->field($modelPerson, "[{$indexPerson}]teacherName")->label(false)->textInput(['maxlength' => true, 'class' => "form-control thacher-name-autocomplete"]) ?>
    
                <?= $form->field($modelPerson, "[{$indexPerson}]id")->label(false)->hiddenInput(['class' => "field-usercommunity-id"]) ?>

                </td>
            <td class="text-center vcenter" style="width: 150px;">
                <?php if(!empty($modelPerson['user_id'])): ?>
                <a href="<?php echo Url::base(); ?>/school/teacher/<?php echo $modelPerson['user_id']; ?>">
                    <button type="button" class="btn btn-info btn-xs"><span class=" glyphicon glyphicon-eye-open"></span></button>
                </a>
                <?php endif; ?>

                <?php if(!empty($modelPerson['user_id'])): ?>
                <a href="<?php echo Url::base(); ?>/school/teacher-student/<?php echo $modelPerson['user_id']; ?>">
                    <button type="button" class=" btn btn-warning btn-xs"><span class="glyphicon glyphicon-pencil"></span></button>
                </a>
                <?php endif; ?>
                
                <button type="button" class="remove-thacher<?php echo $indexRef; ?> btn btn-danger btn-xs"><span class="glyphicon glyphicon-minus"></span></button>
            </td>
        </tr>
     <?php endforeach; ?>
    </tbody>
</table>
<?php DynamicFormWidget::end(); ?>




