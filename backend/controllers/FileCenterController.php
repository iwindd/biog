<?php

namespace backend\controllers;

use Yii;
use common\models\FileCenter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\data\ActiveDataProvider;

/**
 * FileCenterController implements the CRUD actions for FileCenter model.
 */
class FileCenterController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                        'upload' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all FileCenter models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $query = FileCenter::find()->orderBy(['created_at' => SORT_DESC]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Upload action for FileCenter.
     * Handles file uploads via AJAX (e.g. Dropzone or manual)
     * 
     * @return \yii\web\Response
     */
    public function actionUpload()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $uploadFile = UploadedFile::getInstanceByName('file');
        
        if ($uploadFile) {
            
            // Validate limits
            $limits = isset(Yii::$app->params['fileCenterUploadLimits']) ? Yii::$app->params['fileCenterUploadLimits'] : null;
            $allowedExtensions = [];
            $maxSize = 0;
            $fileTypeConfig = null;
            
            if ($limits) {
                // Determine file category (image or document) based on extension
                $ext = strtolower($uploadFile->extension);
                foreach ($limits as $type => $config) {
                    if (in_array($ext, $config['extensions'])) {
                        $fileTypeConfig = $config;
                        $allowedExtensions = $config['extensions'];
                        $maxSize = $config['maxSize'];
                        break;
                    }
                }
                
                if (!$fileTypeConfig) {
                     return ['status' => 'error', 'message' => 'ประเภทไฟล์ไม่ได้รับอนุญาต (รองรับเฉพาะ ' . implode(', ', array_merge($limits['image']['extensions'], $limits['document']['extensions'])) . ')'];
                }
                
                if ($uploadFile->size > $maxSize) {
                    $maxMb = $maxSize / (1024 * 1024);
                    return ['status' => 'error', 'message' => 'ขนาดไฟล์เกินกำหนด (สูงสุด ' . $maxMb . ' MB)'];
                }
            }

            $uploadDir = Yii::getAlias('@frontend/web/uploads/filecenter/' . date('Y/m'));
            if (!is_dir($uploadDir)) {
                FileHelper::createDirectory($uploadDir);
            }

            $fileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $uploadFile->name);
            $filePath = $uploadDir . '/' . $fileName;
            
            // Relative path for web access
            $relativePath = '/uploads/filecenter/' . date('Y/m') . '/' . $fileName;

            if ($uploadFile->saveAs($filePath)) {
                $model = new FileCenter();
                $model->file_name = $uploadFile->name;
                $model->file_path = $relativePath;
                $model->file_type = $uploadFile->type;
                $model->file_size = $uploadFile->size;
                
                if ($model->save()) {
                    return [
                        'status' => 'success',
                        'message' => 'File uploaded successfully',
                        'file' => [
                            'id' => $model->id,
                            'name' => $model->file_name,
                            'path' => $model->file_path,
                            'type' => $model->file_type,
                            'size_text' => $model->getFileSizeText(),
                        ]
                    ];
                } else {
                    @unlink($filePath);
                    return [
                        'status' => 'error',
                        'message' => 'Failed to save file record',
                        'errors' => $model->errors
                    ];
                }
            } else {
                return ['status' => 'error', 'message' => 'Failed to move uploaded file'];
            }
        }
        
        return ['status' => 'error', 'message' => 'No file uploaded'];
    }

    /**
     * Deletes an existing FileCenter model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Remove file from disk
        $filePath = Yii::getAlias('@frontend/web') . $model->file_path;
        if (file_exists($filePath)) {
            @unlink($filePath);
        }

        $model->delete();

        Yii::$app->session->setFlash('success', 'File deleted successfully.');
        return $this->redirect(['index']);
    }
    
    /**
     * API Endpoint for File Picker to fetch files
     */
    public function actionListApi($page = 1, $q = '')
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $query = FileCenter::find();
        
        if (!empty($q)) {
            $query->andFilterWhere(['like', 'file_name', $q]);
        }
        
        $query->orderBy(['created_at' => SORT_DESC]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 30,
                'page' => $page - 1,
            ],
        ]);
        
        $models = $dataProvider->getModels();
        $files = [];
        
        $urlFrontend = isset(Yii::$app->params['urlFrontend']) ? Yii::$app->params['urlFrontend'] : '';
        foreach ($models as $model) {
            $files[] = [
                'id' => $model->id,
                'file_name' => $model->file_name,
                'file_path' => $model->file_path,
                'file_url' => $urlFrontend . $model->file_path,
                'file_type' => $model->file_type,
                'file_size_text' => $model->getFileSizeText(),
                'is_image' => strpos($model->file_type, 'image/') === 0,
            ];
        }

        return [
            'status' => 'success',
            'data' => $files,
            'pagination' => [
                'currentPage' => $page,
                'pageCount' => $dataProvider->pagination->getPageCount(),
                'totalCount' => $dataProvider->pagination->totalCount,
            ]
        ];
    }

    /**
     * Finds the FileCenter model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return FileCenter the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = FileCenter::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
