<?php

namespace backend\widgets;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;

/**
 * AsyncExportModal Widget (Client-Side Fetch + Generate)
 * Renders a reusable export modal that fetches data in batches and generates XLSX/ZIP client-side.
 * 
 * Usage:
 * <?= AsyncExportModal::widget([
 *     'contentType' => 'fungi',
 *     'modalTitle' => 'Export ข้อมูลจุลินทรีย์',
 *     'fetchDataUrl' => Url::to(['/export/fetch-data']),
 *     'searchParams' => $_GET['ContentFungiSearch'] ?? [],
 * ]) ?>
 */
class AsyncExportModal extends Widget
{
    /**
     * @var string Content type identifier (e.g., 'fungi', 'animal', 'product')
     */
    public $contentType;

    /**
     * @var string Modal title in Thai
     */
    public $modalTitle;

    /**
     * @var string URL for fetching paginated export data
     */
    public $fetchDataUrl;

    /**
     * @var array Search parameters to pass to export
     */
    public $searchParams = [];

    /**
     * @var int Page size for data fetching (rows per request)
     */
    public $pageSize = 3000;

    /**
     * @var int Maximum concurrent requests for parallel fetching
     */
    public $maxConcurrentRequests = 3;

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
        if (empty($this->fetchDataUrl)) {
            // Default to the export controller's fetch-data action
            $this->fetchDataUrl = \yii\helpers\Url::to(['/export/fetch-data']);
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
     * Register JavaScript assets (CDN dependencies + module)
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        
        // Register CDN dependencies for client-side XLSX/ZIP generation
        // SheetJS (XLSX) - for Excel generation
        $view->registerJsFile(
            'https://cdn.sheetjs.com/xlsx-0.18.5/package/dist/xlsx.full.min.js',
            ['depends' => [\yii\web\JqueryAsset::class], 'position' => \yii\web\View::POS_HEAD]
        );
        
        // JSZip - for ZIP creation
        $view->registerJsFile(
            'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js',
            ['depends' => [\yii\web\JqueryAsset::class], 'position' => \yii\web\View::POS_HEAD]
        );
        
        // FileSaver - for browser download trigger
        $view->registerJsFile(
            'https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js',
            ['depends' => [\yii\web\JqueryAsset::class], 'position' => \yii\web\View::POS_HEAD]
        );
        
        // Register the modal JavaScript module
        $jsFile = \Yii::getAlias('@web/js/async-export-modal.js');
        $view->registerJsFile($jsFile, ['depends' => [\yii\web\JqueryAsset::class]]);
        
        // Initialize the modal with configuration
        $config = Json::encode([
            'contentType' => $this->contentType,
            'modalTitle' => $this->modalTitle,
            'fetchDataUrl' => $this->fetchDataUrl,
            'searchParams' => $this->searchParams,
            'pageSize' => $this->pageSize,
            'maxConcurrentRequests' => $this->maxConcurrentRequests,
        ]);
        
        $js = <<<JS
$(document).ready(function() {
    new AsyncExportModal($config);
});
JS;
        
        $view->registerJs($js);
    }
}
