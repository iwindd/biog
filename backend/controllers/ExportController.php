<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use backend\components\PermissionAccess;
use backend\components\BackendHelper;

class ExportController extends Controller
{
    /**
     * Maximum rows per page for fetch-data API
     * Reduced from 3000 to 1000 to avoid memory exhaustion
     */
    const PAGE_SIZE = 1000;

    /**
     * Warning threshold for large datasets
     */
    const LARGE_DATASET_THRESHOLD = 50000;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className()
                ],
                'rules' => [
                    [
                        'actions' => ['fetch-data'],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return PermissionAccess::BackendAccess('content_list', 'controller');
                        }
                    ],
                ],
            ],
        ];
    }

    /**
     * Fetch paginated export data as JSON for client-side processing.
     * 
     * GET /export/fetch-data?content_type=content_fungi&date_from=2024-01-01&date_to=2024-12-31&page=1&per_page=3000
     * 
     * Returns: { status, total, page, per_page, total_pages, headers, rows, base_file_name, large_dataset_warning }
     */
    public function actionFetchData()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Increase memory limit for export operations
        ini_set('memory_limit', '256M');

        // Disable Yii debug logging to reduce memory usage
        if (Yii::$app->has('log', true)) {
            $log = Yii::$app->getLog();
            $log->targets = [];
        }

        $contentType = Yii::$app->request->get('content_type');
        $dateFrom = Yii::$app->request->get('date_from');
        $dateTo = Yii::$app->request->get('date_to');
        $page = (int) Yii::$app->request->get('page', 1);
        $perPage = (int) Yii::$app->request->get('per_page', self::PAGE_SIZE);

        // Validate content type
        $validContentTypes = [
            'content_plant', 'content_animal', 'content_fungi',
            'content_ecotourism', 'content_expert', 'content_product'
        ];

        if (empty($contentType) || !in_array($contentType, $validContentTypes)) {
            return [
                'status' => 'error',
                'message' => 'ไม่พบประเภทข้อมูลที่ต้องการ Export',
            ];
        }

        if (empty($dateFrom) || empty($dateTo)) {
            return [
                'status' => 'error',
                'message' => 'กรุณาเลือกช่วงวันที่ให้ครบถ้วน',
            ];
        }

        if ($dateFrom > $dateTo) {
            return [
                'status' => 'error',
                'message' => 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด',
            ];
        }

        // Clamp per_page to reasonable limits
        if ($perPage < 100) {
            $perPage = 100;
        }
        if ($perPage > 5000) {
            $perPage = self::PAGE_SIZE;
        }

        // Validate page number
        if ($page < 1) {
            $page = 1;
        }

        // Build filters
        $filters = $this->getExportFilters($contentType);
        $filters['date_from'] = $dateFrom;
        $filters['date_to'] = $dateTo;

        // Build query
        $query = BackendHelper::buildExportQuery($contentType, $filters);
        if ($query === null) {
            return [
                'status' => 'error',
                'message' => 'ไม่สามารถสร้างคำสั่งสอบถามได้',
            ];
        }

        try {
            $total = (int) (clone $query)->count();
            $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 0;

            // Get rows for current page
            $offset = ($page - 1) * $perPage;
            $rawRows = (clone $query)
                ->offset($offset)
                ->limit($perPage)
                ->asArray()
                ->all();

            // Batch preload name/location lookups to avoid N+1 queries
            $userIds = [];
            $regionIds = [];
            $provinceIds = [];
            $districtIds = [];
            $subdistrictIds = [];
            $zipcodeIds = [];
            foreach ($rawRows as $row) {
                if (!empty($row['created_by_user_id'])) $userIds[] = $row['created_by_user_id'];
                if (!empty($row['approved_by_user_id'])) $userIds[] = $row['approved_by_user_id'];
                if (!empty($row['region_id'])) $regionIds[] = $row['region_id'];
                if (!empty($row['province_id'])) $provinceIds[] = $row['province_id'];
                if (!empty($row['district_id'])) $districtIds[] = $row['district_id'];
                if (!empty($row['subdistrict_id'])) $subdistrictIds[] = $row['subdistrict_id'];
                if (!empty($row['zipcode_id'])) $zipcodeIds[] = $row['zipcode_id'];
            }
            BackendHelper::preloadNames(array_unique($userIds));
            BackendHelper::preloadLocations(array_unique($regionIds), array_unique($provinceIds), array_unique($districtIds), array_unique($subdistrictIds), array_unique($zipcodeIds));

            // Format rows for export (uses cached lookups)
            $formattedRows = BackendHelper::formatExportRows($contentType, $rawRows);

            // Clear raw rows to free memory
            unset($rawRows);

            // Get headers
            $headers = BackendHelper::getExportHeaders($contentType);

            // Get base file name
            $baseFileName = BackendHelper::getExportBaseFileName($contentType);

            // Large dataset warning
            $largeDatasetWarning = $total > self::LARGE_DATASET_THRESHOLD;

            // Clear export cache to free memory
            BackendHelper::clearExportCache();

            return [
                'status' => 'success',
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
                'total_pages' => $totalPages,
                'headers' => $headers,
                'rows' => $formattedRows,
                'base_file_name' => $baseFileName,
                'content_type' => $contentType,
                'large_dataset_warning' => $largeDatasetWarning,
            ];
        } catch (\Exception $e) {
            // Clear export cache on error too
            BackendHelper::clearExportCache();
            Yii::error('Export fetch-data error: ' . $e->getMessage(), 'export');
            return [
                'status' => 'error',
                'message' => 'เกิดข้อผิดพลาดในการดึงข้อมูล: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get export filters from request parameters based on content type
     */
    private function getExportFilters($contentType)
    {
        $request = Yii::$app->request;
        $filters = [];

        // Search model name based on content type
        $searchModelMap = [
            'content_plant' => 'ContentPlantSearch',
            'content_animal' => 'ContentAnimalSearch',
            'content_fungi' => 'ContentFungiSearch',
            'content_ecotourism' => 'ContentEcotourismSearch',
            'content_expert' => 'ContentExpertSearch',
            'content_product' => 'ContentProductSearch',
        ];

        $searchModelName = $searchModelMap[$contentType] ?? null;

        // Get search parameters from GET
        if ($searchModelName) {
            $searchParams = $request->get($searchModelName, []);
            if (!empty($searchParams)) {
                if (!empty($searchParams['name'])) {
                    $filters['name'] = $searchParams['name'];
                }
                if (!empty($searchParams['created_by_user_id'])) {
                    $filters['created_by_user_id'] = $searchParams['created_by_user_id'];
                }
                if (!empty($searchParams['updated_by_user_id'])) {
                    $filters['updated_by_user_id'] = $searchParams['updated_by_user_id'];
                }
                if (!empty($searchParams['approved_by_user_id'])) {
                    $filters['approved_by_user_id'] = $searchParams['approved_by_user_id'];
                }
                if (!empty($searchParams['status'])) {
                    $filters['status'] = $searchParams['status'];
                }
                if (!empty($searchParams['note'])) {
                    $filters['note'] = $searchParams['note'];
                }
            }
        }

        // Content-type specific filters from GET params
        switch ($contentType) {
            case 'content_product':
                $productCategoryId = $request->get('product_category_id');
                if (!empty($productCategoryId)) {
                    $filters['product_category_id'] = $productCategoryId;
                }
                break;
            case 'content_expert':
                $expertCategoryId = $request->get('expert_category_id');
                if (!empty($expertCategoryId)) {
                    $filters['expert_category_id'] = $expertCategoryId;
                }
                break;
        }

        // Also check direct GET params for filters
        $name = $request->get('name');
        if (!empty($name) && empty($filters['name'])) {
            $filters['name'] = $name;
        }
        $status = $request->get('status');
        if (!empty($status) && empty($filters['status'])) {
            $filters['status'] = $status;
        }

        return $filters;
    }
}
