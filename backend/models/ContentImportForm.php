<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * Generic import form for all content types (plant, animal, etc.)
 */
class ContentImportForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $importFile;

    public function rules()
    {
        return [
            [['importFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xlsx', 'checkExtensionByMimeType' => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'importFile' => 'ไฟล์ Excel (.xlsx)',
        ];
    }
}
