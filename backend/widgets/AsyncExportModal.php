<?php

namespace backend\widgets;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * AsyncExportModal Widget
 * Renders a reusable async export modal for content types
 * 
 * Usage:
 * <?= AsyncExportModal::widget([
 *     'contentType' => 'animal',
 *     'modalTitle' => 'Export ข้อมูลสัตว์',
 *     'startExportUrl' => Url::to(['/content-animal/start-export']),
 *     'exportStatusUrl' => Url::to(['/content-animal/export-status']),
 *     'searchParams' => $_GET['ContentAnimalSearch'] ?? [],
 * ]) ?>
 */
class AsyncExportModal extends Widget
{
    /**
     * @var string Content type identifier (e.g., 'animal', 'fungi', 'product')
     */
    public $contentType;

    /**
     * @var string Modal title in Thai
     */
    public $modalTitle;

    /**
     * @var string URL for starting export
     */
    public $startExportUrl;

    /**
     * @var string URL for checking export status
     */
    public $exportStatusUrl;

    /**
     * @var array Search parameters to pass to export
     */
    public $searchParams = [];

    /**
     * @var int Poll interval in milliseconds
     */
    public $pollInterval = 2000;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        if (empty($this->contentType)) {
            throw new \yii\base\InvalidConfigException('The "contentType" property must be set.');
        }
        if (empty($this->modalTitle)) {
            throw new \yii\base\InvalidConfigException('The "modalTitle" property must be set.');
        }
        if (empty($this->startExportUrl)) {
            throw new \yii\base\InvalidConfigException('The "startExportUrl" property must be set.');
        }
        if (empty($this->exportStatusUrl)) {
            throw new \yii\base\InvalidConfigException('The "exportStatusUrl" property must be set.');
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->registerAssets();
        return $this->render('async-export-modal', [
            'contentType' => $this->contentType,
            'modalTitle' => $this->modalTitle,
        ]);
    }

    /**
     * Register JavaScript assets
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        
        // Register the JavaScript module
        $jsFile = \Yii::getAlias('@web/js/async-export-modal.js');
        $view->registerJsFile($jsFile, ['depends' => [\yii\web\JqueryAsset::class]]);
        
        // Initialize the modal with configuration
        $config = Json::encode([
            'contentType' => $this->contentType,
            'modalTitle' => $this->modalTitle,
            'startExportUrl' => $this->startExportUrl,
            'exportStatusUrl' => $this->exportStatusUrl,
            'searchParams' => $this->searchParams,
            'pollInterval' => $this->pollInterval,
        ]);
        
        $js = <<<JS
$(document).ready(function() {
    new AsyncExportModal($config);
});
JS;
        
        $view->registerJs($js);
    }
}
