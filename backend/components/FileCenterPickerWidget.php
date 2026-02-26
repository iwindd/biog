<?php
namespace backend\components;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;

class FileCenterPickerWidget extends Widget
{
    public $inputId; 
    public $buttonText = '<i class="fa fa-folder-open"></i> เลือกจาก FileCenter';
    public $buttonOptions = ['class' => 'btn btn-info'];
    
    // New properties
    public $extensions = [];
    public $clearable = true;
    public $maxSize = 0; // 0 means no limit (or default)
    public $multiple = false;
    public $maxImages = 0; // 0 means infinity when multiple is true

    public function run()
    {
        $config = [
            'inputId' => $this->inputId,
            'extensions' => $this->extensions,
            'clearable' => $this->clearable,
            'maxSize' => $this->maxSize,
            'multiple' => $this->multiple,
            'maxImages' => $this->maxImages,
        ];

        $html = Html::button($this->buttonText, array_merge($this->buttonOptions, [
            'onClick' => 'window.openFileCenterPicker(' . Json::encode($config) . ')'
        ]));
        
        if ($this->clearable) {
            $html .= ' ' . Html::button('<i class="fa fa-trash"></i> ล้างค่า', [
                'class' => 'btn btn-danger',
                'onClick' => 'window.clearFileCenterPicker("' . $this->inputId . '", ' . Json::encode($config) . ')'
            ]);
        }

        $view = $this->getView();
        if (!isset($view->params['fileCenterModalRegistered'])) {
            $view->params['fileCenterModalRegistered'] = true;
            $html .= $this->renderFile('@backend/views/file-center/_picker_modal.php');
        }
        
        return $html;
    }
}
