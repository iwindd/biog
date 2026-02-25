<?php
namespace backend\components;

use yii\base\Widget;
use yii\helpers\Html;

class FileCenterPickerWidget extends Widget
{
    public $inputId; 
    public $buttonText = '<i class="fa fa-folder-open"></i> เลือกจาก FileCenter';
    public $buttonOptions = ['class' => 'btn btn-info'];

    public function run()
    {
        $html = Html::button($this->buttonText, array_merge($this->buttonOptions, [
            'data-toggle' => 'modal',
            'data-target' => '#fileCenterModal',
            'onClick' => 'window.currentFilePickerInput = "' . $this->inputId . '"'
        ]));
        
        $view = $this->getView();
        if (!isset($view->params['fileCenterModalRegistered'])) {
            $view->params['fileCenterModalRegistered'] = true;
            $html .= $this->renderFile('@backend/views/file-center/_picker_modal.php');
        }
        
        return $html;
    }
}
