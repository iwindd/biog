<?php

namespace backend\components;

use yii;
use backend\models\Profile;
use backend\models\UserLog;
use backend\models\Province;
use backend\models\District;
use backend\models\Subdistrict;
use backend\models\Region;
use backend\models\Zipcode;
use backend\models\ExpertCategory;
use backend\models\ProductCategory;
use backend\models\ContentStatistics;
use common\components\Upload;
use backend\models\Users;
use yii\helpers\ArrayHelper;

class BackendHelper
{
    /** Static caches for export performance — avoids N+1 DB queries */
    private static $_nameCache = [];
    private static $_regionCache = [];
    private static $_provinceCache = [];
    private static $_districtCache = [];
    private static $_subdistrictCache = [];
    private static $_zipcodeCache = [];
    private static $_expertCategoryCache = [];
    private static $_productCategoryCache = null;

    /**
     * Clear all static caches (call after export to free memory)
     */
    public static function clearExportCache()
    {
        self::$_nameCache = [];
        self::$_regionCache = [];
        self::$_provinceCache = [];
        self::$_districtCache = [];
        self::$_subdistrictCache = [];
        self::$_zipcodeCache = [];
        self::$_expertCategoryCache = [];
        self::$_productCategoryCache = null;
    }

    /**
     * Batch preload names for export — loads all needed profiles in 1-2 queries
     * @param array $userIds Array of user IDs to preload
     */
    public static function preloadNames(array $userIds)
    {
        $uncached = array_filter(array_unique($userIds), function($id) {
            return $id !== null && !isset(self::$_nameCache[$id]);
        });
        if (empty($uncached)) return;

        $profiles = Profile::find()
            ->select(['user_id', 'firstname', 'lastname'])
            ->where(['user_id' => $uncached])
            ->asArray()
            ->all();

        foreach ($profiles as $p) {
            self::$_nameCache[$p['user_id']] = trim($p['firstname'] . ' ' . $p['lastname']);
        }
        // Set empty string for user IDs not found
        foreach ($uncached as $uid) {
            if (!isset(self::$_nameCache[$uid])) {
                self::$_nameCache[$uid] = '';
            }
        }
    }

    /**
     * Batch preload location data for export — loads regions, provinces, etc. in bulk
     * @param array $regionIds
     * @param array $provinceIds
     * @param array $districtIds
     * @param array $subdistrictIds
     * @param array $zipcodeIds
     */
    public static function preloadLocations(array $regionIds, array $provinceIds, array $districtIds, array $subdistrictIds, array $zipcodeIds)
    {
        // Regions
        $uncached = array_filter(array_unique($regionIds), function($id) {
            return $id !== null && !isset(self::$_regionCache[$id]);
        });
        if (!empty($uncached)) {
            $models = Region::find()->where(['id' => $uncached])->asArray()->all();
            foreach ($models as $m) { self::$_regionCache[$m['id']] = $m['name_th']; }
            foreach ($uncached as $id) { if (!isset(self::$_regionCache[$id])) self::$_regionCache[$id] = '-'; }
        }

        // Provinces
        $uncached = array_filter(array_unique($provinceIds), function($id) {
            return $id !== null && !isset(self::$_provinceCache[$id]);
        });
        if (!empty($uncached)) {
            $models = Province::find()->where(['id' => $uncached])->asArray()->all();
            foreach ($models as $m) { self::$_provinceCache[$m['id']] = $m['name_th']; }
            foreach ($uncached as $id) { if (!isset(self::$_provinceCache[$id])) self::$_provinceCache[$id] = '-'; }
        }

        // Districts
        $uncached = array_filter(array_unique($districtIds), function($id) {
            return $id !== null && !isset(self::$_districtCache[$id]);
        });
        if (!empty($uncached)) {
            $models = District::find()->where(['id' => $uncached])->asArray()->all();
            foreach ($models as $m) { self::$_districtCache[$m['id']] = $m['name_th']; }
            foreach ($uncached as $id) { if (!isset(self::$_districtCache[$id])) self::$_districtCache[$id] = '-'; }
        }

        // Subdistricts
        $uncached = array_filter(array_unique($subdistrictIds), function($id) {
            return $id !== null && !isset(self::$_subdistrictCache[$id]);
        });
        if (!empty($uncached)) {
            $models = Subdistrict::find()->where(['id' => $uncached])->asArray()->all();
            foreach ($models as $m) { self::$_subdistrictCache[$m['id']] = $m['name_th']; }
            foreach ($uncached as $id) { if (!isset(self::$_subdistrictCache[$id])) self::$_subdistrictCache[$id] = '-'; }
        }

        // Zipcodes
        $uncached = array_filter(array_unique($zipcodeIds), function($id) {
            return $id !== null && !isset(self::$_zipcodeCache[$id]);
        });
        if (!empty($uncached)) {
            $models = Zipcode::find()->where(['id' => $uncached])->asArray()->all();
            foreach ($models as $m) { self::$_zipcodeCache[$m['id']] = $m['zipcode']; }
            foreach ($uncached as $id) { if (!isset(self::$_zipcodeCache[$id])) self::$_zipcodeCache[$id] = '-'; }
        }
    }

    public static function getName($uid)
    {
        if ($uid === null) return '';
        if (isset(self::$_nameCache[$uid])) return self::$_nameCache[$uid];

        $profile = Profile::find()->where(['user_id' => $uid])->one();
        if (!empty($profile)) {
            self::$_nameCache[$uid] = $profile->firstname . " " . $profile->lastname;
            return self::$_nameCache[$uid];
        }
        self::$_nameCache[$uid] = '';
        return '';
    }

    public static function getSchoolName($uid){
        $school = (new \yii\db\Query())
                    ->select(['school.*'])
                    ->from('school')
                    ->leftJoin('user_school', 'user_school.school_id=school.id')
                    ->where(['=', 'user_school.user_id', $uid])
                    ->one();
        $roleText = "";
        if(!empty($school)){
            $roleText = $school['name'];
        }

        return $roleText;
    }

    public static function getSchoolProvinceName($uid){
        $province = (new \yii\db\Query())
                    ->select(['province.*'])
                    ->from('province')
                    ->leftJoin('school', 'school.province_id=province.id')
                    ->leftJoin('user_school', 'user_school.school_id=school.id')
                    ->where(['=', 'user_school.user_id', $uid])
                    ->one();
        $roleText = "";
        if(!empty($province)){
            $roleText = $province['name_th'];
        }

        return $roleText;
    }

    public static function getUserAllList()
    {
        $profile = Users::find()
        ->select([
                'user.id',
                'profile.firstname as firstname',
                'profile.lastname as lastname',
            ])
            ->leftjoin('profile', 'profile.user_id = user.id')
            ->leftjoin('user_role', 'user_role.user_id = user.id')
            ->leftjoin('role', 'role.id = user_role.role_id')
            ->where([
                'IN','user_role.role_id',[1,2,4,3,5,6]
            ])->all();
        //$profile = Profile::find()->select(['user_id','firstname','lastname'])->all();
        $array=[];
        foreach ($profile as $key => $value) {
            //print_r($value);
            $array[$key]=[
                'id'=>$value['id'],
                'name'=>$value['firstname']." ".$value['lastname'],
            ];
        }

        return ArrayHelper::map($array, 'id', 'name');

        //ArrayHelper::map($category, 'id', 'name_th')
    }


    public static function getUserList()
    {
        $profile = Users::find()
        ->select([
                'user.id',
                'profile.firstname as firstname',
                'profile.lastname as lastname',
            ])
            ->leftjoin('profile', 'profile.user_id = user.id')
            ->leftjoin('user_role', 'user_role.user_id = user.id')
            ->leftjoin('role', 'role.id = user_role.role_id')
            ->where([
                'IN','user_role.role_id',[1,2,4]
            ])->all();
        //$profile = Profile::find()->select(['user_id','firstname','lastname'])->all();
        $array=[];
        foreach ($profile as $key => $value) {
            //print_r($value);
            $array[$key]=[
                'id'=>$value['id'],
                'name'=>$value['firstname']." ".$value['lastname'],
            ];
        }

        return ArrayHelper::map($array, 'id', 'name');

        //ArrayHelper::map($category, 'id', 'name_th')
    }

    public static function getUserEditList()
    {
        $profile = Users::find()
        ->select([
                'user.id',
                'profile.firstname as firstname',
                'profile.lastname as lastname',
            ])
            ->leftjoin('profile', 'profile.user_id = user.id')
            ->leftjoin('user_role', 'user_role.user_id = user.id')
            ->leftjoin('role', 'role.id = user_role.role_id')
            ->where([
                'IN','user_role.role_id',[1,2,4,3]
            ])->all();
        //$profile = Profile::find()->select(['user_id','firstname','lastname'])->all();
        $array=[];
        foreach ($profile as $key => $value) {
            //print_r($value);
            $array[$key]=[
                'id'=>$value['id'],
                'name'=>$value['firstname']." ".$value['lastname'],
            ];
        }

        return ArrayHelper::map($array, 'id', 'name');

        //ArrayHelper::map($category, 'id', 'name_th')
    }


    public static function getUserApprovedList()
    {
        $profile = Users::find()
        ->select([
                'user.id',
                'profile.firstname as firstname',
                'profile.lastname as lastname',
            ])
            ->leftjoin('profile', 'profile.user_id = user.id')
            ->leftjoin('user_role', 'user_role.user_id = user.id')
            ->leftjoin('role', 'role.id = user_role.role_id')
            ->where([
                'IN','user_role.role_id',[1,2,3]
            ])->all();
        //$profile = Profile::find()->select(['user_id','firstname','lastname'])->all();
        $array=[];
        foreach ($profile as $key => $value) {
            //print_r($value);
            $array[$key]=[
                'id'=>$value['id'],
                'name'=>$value['firstname']." ".$value['lastname'],
            ];
        }

        return ArrayHelper::map($array, 'id', 'name');

        //ArrayHelper::map($category, 'id', 'name_th')
    }
    public static function getRoleName($uid)
    {
        $roles = (new \yii\db\Query())
                    ->select(['role.*', 'user_role.user_id'])
                    ->from('user_role')
                    ->leftJoin('role', 'user_role.role_id=role.id')
                    ->where(['=', 'user_role.user_id', $uid])
                    ->all();
        $roleText = "";
        if(!empty($roles)){
            foreach($roles as $value){
                if(empty($roleText)){
                    $roleText = '<p>'.$value['name'].'</p>';
                }else{
                    $roleText = $roleText."".'<p>'.$value['name'].'</p>';
                }
            }
        }

        return $roleText;
    }


    public static function getRoleNameText($uid)
    {
        $roles = (new \yii\db\Query())
                    ->select(['role.*', 'user_role.user_id'])
                    ->from('user_role')
                    ->leftJoin('role', 'user_role.role_id=role.id')
                    ->where(['=', 'user_role.user_id', $uid])
                    ->all();
        $roleText = "";
        if(!empty($roles)){
            foreach($roles as $value){
                if(empty($roleText)){
                    $roleText = $value['name'];
                }else{
                    $roleText = $roleText.", ".$value['name'];
                }
            }
        }

        return $roleText;
    }

    public static function getStatus($status)
    {
        if (!empty($status)) {
            return "เปิดใช้งาน";
        }

        return "ปิดใช้งาน";
    }

    public static function getStatusBadge($status)
    {
        if ($status == 'pending') {
            return "<span class='label label-warning'>รอตรวจสอบ</span>";
        } elseif ($status == 'approved') {
            return "<span class='label label-success'>อนุมัติแล้ว</span>";
        } elseif ($status == 'rejected') {
            return "<span class='label label-danger'>ไม่อนุมัติ</span>";
        }
        return "-";
    }

    public static function getDate($date)
    {
        if (!empty($date) && $date != "0000-00-00 00:00:00" && is_numeric($date)) {
            return date('Y-m-d H:i:s', $date);
        }else{
            return "-";
        }
        
    }

    public static function menuActive($site, $action)
    {
        if ($site == "/") {
            if ($action == "site/index" || $action == "/") {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "users") {
            if ($action == "users/index" || $action == "users/view" || $action == "users/create" || $action == "users/update") {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "school") {
            if ($action == "school/index" || $action == "school/view" || $action == "school/create" || $action == "school/update"  || $action == "school/teacher" || $action == "school/teacher-student") {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "teacher") {
            if ($action == "approved-teacher/index" || $action == "approved-teacher/view" || $action == "approved-teacher/create" || $action == "approved-teacher/update" ) {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "student-approve") {
            if ($action == "approved-student/index" || $action == "approved-student/view" || $action == "approved-student/create" || $action == "approved-student/update" ) {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "banner") {
            if ($action == "banner/index" || $action == "banner/view" || $action == "banner/create" || $action == "banner/update") {
                return true;
            } else {
                return false;
            }
        }  elseif ($site == "content-banner") {
            if ($action == "content-banner/index" || $action == "content-banner/view" || $action == "content-banner/update") {
                return true;
            } else {
                return false;
            }
        }elseif ($site == "knowledge") {
            if ($action == "knowledge/index" || $action == "knowledge/view" || $action == "knowledge/create" || $action == "knowledge/update") {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "news") {
            if ($action == "news/index" || $action == "news/view" || $action == "news/create" || $action == "news/update") {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "blog") {
            if ($action == "blog/index" || $action == "blog/view" || $action == "blog/create"  || $action == "blog/update" ) {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "wallboard") {
            if ($action == "wallboard/index" || $action == "wallboard/view" || $action == "wallboard/create"  || $action == "wallboard/update" ) {
                return true;
            } else {
                return false;
            }
        }elseif ($site == "content-animal") {
            if ($action == "content-animal/index" || $action == "content-animal/view" || $action == "content-animal/create"  || $action == "content-animal/update" ) {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "content-plant") {
            if ($action == "content-plant/index" || $action == "content-plant/view" || $action == "content-plant/create"  || $action == "content-plant/update" ) {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "content-ecotourism") {
            if ($action == "content-ecotourism/index" || $action == "content-ecotourism/view" || $action == "content-ecotourism/create"  || $action == "content-ecotourism/update" ) {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "content-expert") {
            if ($action == "content-expert/index" || $action == "content-expert/view" || $action == "content-expert/create"  || $action == "content-expert/update" ) {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "content-fungi") {
            if ($action == "content-fungi/index" || $action == "content-fungi/view" || $action == "content-fungi/create"  || $action == "content-fungi/update" ) {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "content-product") {
            if ($action == "content-product/index" || $action == "content-product/view" || $action == "content-product/create"  || $action == "content-product/update" ) {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "product-category") {
            if ($action == "product-category/index" || $action == "product-category/view" || $action == "product-category/create"  || $action == "product-category/update" ) {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "expert-category") {
            if ($action == "expert-category/index" || $action == "expert-category/view" || $action == "expert-category/create"  || $action == "expert-category/update" ) {
                return true;
            } else {
                return false;
            }
        } elseif ($site == "short-url") {
            if ($action == "short-url/index" || $action == "short-url/view" || $action == "short-url/create" || $action == "short-url/update") {
                return true;
            } else {
                return false;
            }
        }
    }

    public static function getPicture($path)
    {
        $tagsImage = '<img width="400px" src="' . Yii::$app->params['urlWebSakhon'] . '/picture' . $path . '" class="picture-width">';
        return $tagsImage;
    }

    public static function getThumbnail($file, $size)
    {
        if (!empty($file)) {
            $imagePath = \yii::getAlias('@imagesurlcommunity');
            $imagePath = str_replace('\\', '/', $imagePath);

            //print_r($file);
            // exit();
            if (file_exists($imagePath . $file)) {
                return  "<img src='data:image/png;base64, " . base64_encode(file_get_contents($imagePath . $file)) . "' width='" . $size . "px'>";
            } else {
                return "";
            }
        }
        return "";
    }

    public static function readFileNoPermission($picture)
    {
        if (!empty($picture)) {
            return "<img src='/files/images/$picture' style='width:200px;' alt='' />";
        }
        return "";
    }

    public static function saveUserLog($table, $uid, $contentId, $actionName, $description){
        $model = new UserLog();
        $model->type = $table;
        $model->user_id = $uid;
        $model->content_id = $contentId;
        $model->action_name = $actionName;
        $model->description = $description;
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

    }
    public static function getNameProvince($id)
    {
        if ($id === null) return '-';
        if (isset(self::$_provinceCache[$id])) return self::$_provinceCache[$id];
        $model = Province::findOne($id);
        if (!empty($model)) {
            self::$_provinceCache[$id] = $model['name_th'];
            return $model['name_th'];
        }
        self::$_provinceCache[$id] = '-';
        return '-';
    }

    public static function getNameSubdistrict($id)
    {
        if ($id === null) return '-';
        if (isset(self::$_subdistrictCache[$id])) return self::$_subdistrictCache[$id];
        $model = Subdistrict::findOne($id);
        if (!empty($model)) {
            self::$_subdistrictCache[$id] = $model['name_th'];
            return $model['name_th'];
        }
        self::$_subdistrictCache[$id] = '-';
        return '-';
    }

    public static function getNameDistrict($id)
    {
        if ($id === null) return '-';
        if (isset(self::$_districtCache[$id])) return self::$_districtCache[$id];
        $model = District::findOne($id);
        if (!empty($model)) {
            self::$_districtCache[$id] = $model['name_th'];
            return $model['name_th'];
        }
        self::$_districtCache[$id] = '-';
        return '-';
    }

    public static function getNameRegion($id)
    {
        if ($id === null) return '-';
        if (isset(self::$_regionCache[$id])) return self::$_regionCache[$id];
        $model = Region::findOne($id);
        if (!empty($model)) {
            self::$_regionCache[$id] = $model['name_th'];
            return $model['name_th'];
        }
        self::$_regionCache[$id] = '-';
        return '-';
    }

    public static function getNameZipcode($id)
    {
        if ($id === null) return '-';
        if (isset(self::$_zipcodeCache[$id])) return self::$_zipcodeCache[$id];
        $model = Zipcode::findOne($id);
        if (!empty($model)) {
            self::$_zipcodeCache[$id] = $model['zipcode'];
            return $model['zipcode'];
        }
        self::$_zipcodeCache[$id] = '-';
        return '-';
    }

    public static function getNameCategoryExpert($id)
    {
        if ($id === null) return '-';
        if (isset(self::$_expertCategoryCache[$id])) return self::$_expertCategoryCache[$id];
        $model = ExpertCategory::findOne($id);
        if (!empty($model)) {
            self::$_expertCategoryCache[$id] = $model['name'];
            return $model['name'];
        }
        self::$_expertCategoryCache[$id] = '-';
        return '-';
    }

    public static function getNameCategoryProduct($id)
    {
        if ($id === null) return '-';
        if (self::$_productCategoryCache === null) {
            $categories = ProductCategory::find()->asArray()->all();
            self::$_productCategoryCache = [];
            foreach ($categories as $cat) {
                self::$_productCategoryCache[$cat['id']] = $cat['name'];
            }
        }
        return self::$_productCategoryCache[$id] ?? '-';
    }
    public static function getImageContent($folder,$file,$class=''){
        $path = '/files/'.$folder.'/'.$file;
        if(!empty($file) && file_exists($path)){
            return '<img src="'.$path.'" class="'.$class.'" width="100%">';

        }else{
            return '<img src="/images/default-content.jpg" width="100%">';
        }

    }

    public static function getPageview($id){
        $data = ContentStatistics::find()->where(['content_root_id' => $id])->one();
        if(!empty($data)){
            return number_format($data['pageview']);
        }

        return '-';
    }

    /**
     * Build standardized query for plant export data
     * @param array $filters Filter conditions
     * @param bool $addOrderBy Whether to add default ordering
     * @return \yii\db\Query
     */
    public static function buildPlantExportQuery($filters = [], $addOrderBy = false)
    {
        $query = \backend\models\Content::find()->select([
            'content.id as content_id',
            'content.name',
            'content.type_id',
            'content_plant.other_name',
            'content_plant.features',
            'content_plant.benefit',
            'content_plant.found_source',
            'content_plant.common_name',
            'content_plant.scientific_name',
            'content_plant.family_name',
            'content.region_id',
            'content.province_id',
            'content.district_id',
            'content.subdistrict_id',
            'content.zipcode_id',
            'content.created_by_user_id',
            'content.approved_by_user_id',
            'content.status',
            'content.note',
            'content.created_at',
        ]);

        $query->leftJoin('content_plant', 'content_plant.content_id = content.id');
        $query->leftJoin('profile', 'profile.user_id = content.created_by_user_id');
        $query->andFilterWhere(['=', 'content.type_id', 1]);
        $query->andFilterWhere(['=', 'content.active', 1]);

        if (!empty($filters['name'])) {
            $query->andFilterWhere(['like', 'content.name', $filters['name']]);
        }

        if (!empty($filters['created_by_user_id'])) {
            $query->andFilterWhere(['=', 'created_by_user_id', $filters['created_by_user_id']]);
        }

        if (!empty($filters['updated_by_user_id'])) {
            $query->andFilterWhere(['=', 'updated_by_user_id', $filters['updated_by_user_id']]);
        }

        if (!empty($filters['approved_by_user_id'])) {
            $query->andFilterWhere(['=', 'approved_by_user_id', $filters['approved_by_user_id']]);
        }

        if (!empty($filters['note'])) {
            $query->andFilterWhere(['like', 'note', $filters['note']]);
        }

        if (!empty($filters['status'])) {
            $query->andFilterWhere(['like', 'status', $filters['status']]);
        }

        if (!empty($filters['updated_at'])) {
            $query->andFilterWhere(['like', 'updated_at', $filters['updated_at']]);
        }

        // Use robust date filtering logic from controller
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $dateStart = trim($filters['date_from']);
            $dateEnd = date('Y-m-d', strtotime(trim($filters['date_to']) . ' +1 day'));
            $query->andWhere(['between', 'content.created_at', $dateStart, $dateEnd]);
        }

        if ($addOrderBy) {
            $query->orderBy(['content.created_at' => SORT_DESC, 'content.id' => SORT_DESC]);
        }

        return $query;
    }

    public static function buildAnimalExportQuery($filters = [], $addOrderBy = false)
    {
        $query = \backend\models\Content::find()->select([
            'content.id as content_id',
            'content.name',
            'content.type_id',
            'content_animal.other_name',
            'content_animal.features',
            'content_animal.benefit',
            'content_animal.found_source',
            'content_animal.common_name',
            'content_animal.scientific_name',
            'content_animal.family_name',
            'content.region_id',
            'content.province_id',
            'content.district_id',
            'content.subdistrict_id',
            'content.zipcode_id',
            'content.created_by_user_id',
            'content.approved_by_user_id',
            'content.status',
            'content.note',
            'content.created_at',
        ]);

        $query->leftJoin('content_animal', 'content_animal.content_id = content.id');
        $query->leftJoin('profile', 'profile.user_id = content.created_by_user_id');
        $query->andFilterWhere(['=', 'content.type_id', 2]);
        $query->andFilterWhere(['=', 'content.active', 1]);

        if (!empty($filters['name'])) {
            $query->andFilterWhere(['like', 'content.name', $filters['name']]);
        }

        if (!empty($filters['created_by_user_id'])) {
            $query->andFilterWhere(['=', 'created_by_user_id', $filters['created_by_user_id']]);
        }

        if (!empty($filters['updated_by_user_id'])) {
            $query->andFilterWhere(['=', 'updated_by_user_id', $filters['updated_by_user_id']]);
        }

        if (!empty($filters['approved_by_user_id'])) {
            $query->andFilterWhere(['=', 'approved_by_user_id', $filters['approved_by_user_id']]);
        }

        if (!empty($filters['note'])) {
            $query->andFilterWhere(['like', 'note', $filters['note']]);
        }

        if (!empty($filters['status'])) {
            $query->andFilterWhere(['like', 'status', $filters['status']]);
        }

        if (!empty($filters['updated_at'])) {
            $query->andFilterWhere(['like', 'updated_at', $filters['updated_at']]);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $dateStart = trim($filters['date_from']);
            $dateEnd = date('Y-m-d', strtotime(trim($filters['date_to']) . ' +1 day'));
            $query->andWhere(['between', 'content.created_at', $dateStart, $dateEnd]);
        }

        if ($addOrderBy) {
            $query->orderBy(['content.created_at' => SORT_DESC, 'content.id' => SORT_DESC]);
        }

        return $query;
    }

    public static function buildFungiExportQuery($filters = [], $addOrderBy = false)
    {
        $query = \backend\models\Content::find()->select([
            'content.id as content_id',
            'content.name',
            'content.type_id',
            'content_fungi.other_name',
            'content_fungi.features',
            'content_fungi.benefit',
            'content_fungi.found_source',
            'content_fungi.common_name',
            'content_fungi.scientific_name',
            'content_fungi.family_name',
            'content.region_id',
            'content.province_id',
            'content.district_id',
            'content.subdistrict_id',
            'content.zipcode_id',
            'content.created_by_user_id',
            'content.approved_by_user_id',
            'content.status',
            'content.note',
            'content.created_at',
        ]);

        $query->leftJoin('content_fungi', 'content_fungi.content_id = content.id');
        $query->leftJoin('profile', 'profile.user_id = content.created_by_user_id');
        $query->andFilterWhere(['=', 'content.type_id', 3]);
        $query->andFilterWhere(['=', 'content.active', 1]);

        if (!empty($filters['name'])) {
            $query->andFilterWhere(['like', 'content.name', $filters['name']]);
        }

        if (!empty($filters['created_by_user_id'])) {
            $query->andFilterWhere(['=', 'created_by_user_id', $filters['created_by_user_id']]);
        }

        if (!empty($filters['updated_by_user_id'])) {
            $query->andFilterWhere(['=', 'updated_by_user_id', $filters['updated_by_user_id']]);
        }

        if (!empty($filters['approved_by_user_id'])) {
            $query->andFilterWhere(['=', 'approved_by_user_id', $filters['approved_by_user_id']]);
        }

        if (!empty($filters['note'])) {
            $query->andFilterWhere(['like', 'note', $filters['note']]);
        }

        if (!empty($filters['status'])) {
            $query->andFilterWhere(['like', 'status', $filters['status']]);
        }

        if (!empty($filters['updated_at'])) {
            $query->andFilterWhere(['like', 'updated_at', $filters['updated_at']]);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $dateStart = trim($filters['date_from']);
            $dateEnd = date('Y-m-d', strtotime(trim($filters['date_to']) . ' +1 day'));
            $query->andWhere(['between', 'content.created_at', $dateStart, $dateEnd]);
        }

        if ($addOrderBy) {
            $query->orderBy(['content.created_at' => SORT_DESC, 'content.id' => SORT_DESC]);
        }

        return $query;
    }

    public static function buildEcotourismExportQuery($filters = [], $addOrderBy = false)
    {
        $query = \backend\models\Content::find()->select([
            'content.id as content_id',
            'content.name',
            'content.type_id',
            'content_ecotourism.address',
            'content_ecotourism.phone',
            'content_ecotourism.name as contact_name',
            'content_ecotourism.contact',
            'content_ecotourism.travel_information',
            'content.description',
            'content.other_information',
            'content.region_id',
            'content.province_id',
            'content.district_id',
            'content.subdistrict_id',
            'content.zipcode_id',
            'content.created_by_user_id',
            'content.approved_by_user_id',
            'content.status',
            'content.note',
            'content.created_at',
        ]);

        $query->leftJoin('content_ecotourism', 'content_ecotourism.content_id = content.id');
        $query->leftJoin('profile', 'profile.user_id = content.created_by_user_id');
        $query->andFilterWhere(['=', 'content.type_id', 5]);
        $query->andFilterWhere(['=', 'content.active', 1]);

        if (!empty($filters['name'])) {
            $query->andFilterWhere(['like', 'content.name', $filters['name']]);
        }

        if (!empty($filters['created_by_user_id'])) {
            $query->andFilterWhere(['=', 'created_by_user_id', $filters['created_by_user_id']]);
        }

        if (!empty($filters['updated_by_user_id'])) {
            $query->andFilterWhere(['=', 'updated_by_user_id', $filters['updated_by_user_id']]);
        }

        if (!empty($filters['approved_by_user_id'])) {
            $query->andFilterWhere(['=', 'approved_by_user_id', $filters['approved_by_user_id']]);
        }

        if (!empty($filters['note'])) {
            $query->andFilterWhere(['like', 'note', $filters['note']]);
        }

        if (!empty($filters['status'])) {
            $query->andFilterWhere(['like', 'status', $filters['status']]);
        }

        if (!empty($filters['updated_at'])) {
            $query->andFilterWhere(['like', 'updated_at', $filters['updated_at']]);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $dateStart = trim($filters['date_from']);
            $dateEnd = date('Y-m-d', strtotime(trim($filters['date_to']) . ' +1 day'));
            $query->andWhere(['between', 'content.created_at', $dateStart, $dateEnd]);
        }

        if ($addOrderBy) {
            $query->orderBy(['content.created_at' => SORT_DESC, 'content.id' => SORT_DESC]);
        }

        return $query;
    }

    public static function buildExpertExportQuery($filters = [], $addOrderBy = false)
    {
        $query = \backend\models\Content::find()->select([
            'content.id as content_id',
            'content.name',
            'content.type_id',
            'content_expert.expert_firstname',
            'content_expert.expert_lastname',
            'content_expert.expert_birthdate',
            'content_expert.expert_expertise',
            'content_expert.expert_occupation',
            'content_expert.expert_card_id',
            'content_expert.phone',
            'content_expert.address',
            'content_expert.expert_category_id',
            'content.region_id',
            'content.province_id',
            'content.district_id',
            'content.subdistrict_id',
            'content.zipcode_id',
            'content.created_by_user_id',
            'content.approved_by_user_id',
            'content.status',
            'content.note',
            'content.created_at',
        ]);

        $query->leftJoin('content_expert', 'content_expert.content_id = content.id');
        $query->leftJoin('profile', 'profile.user_id = content.created_by_user_id');
        $query->andFilterWhere(['=', 'content.type_id', 4]);
        $query->andFilterWhere(['=', 'content.active', 1]);

        if (!empty($filters['name'])) {
            $query->andFilterWhere(['like', 'content.name', $filters['name']]);
        }

        if (!empty($filters['expert_category_id'])) {
            $query->andFilterWhere(['=', 'content_expert.expert_category_id', $filters['expert_category_id']]);
        }

        if (!empty($filters['created_by_user_id'])) {
            $query->andFilterWhere(['=', 'created_by_user_id', $filters['created_by_user_id']]);
        }

        if (!empty($filters['updated_by_user_id'])) {
            $query->andFilterWhere(['=', 'updated_by_user_id', $filters['updated_by_user_id']]);
        }

        if (!empty($filters['approved_by_user_id'])) {
            $query->andFilterWhere(['=', 'approved_by_user_id', $filters['approved_by_user_id']]);
        }

        if (!empty($filters['note'])) {
            $query->andFilterWhere(['like', 'note', $filters['note']]);
        }

        if (!empty($filters['status'])) {
            $query->andFilterWhere(['like', 'status', $filters['status']]);
        }

        if (!empty($filters['updated_at'])) {
            $query->andFilterWhere(['like', 'updated_at', $filters['updated_at']]);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $dateStart = trim($filters['date_from']);
            $dateEnd = date('Y-m-d', strtotime(trim($filters['date_to']) . ' +1 day'));
            $query->andWhere(['between', 'content.created_at', $dateStart, $dateEnd]);
        }

        if ($addOrderBy) {
            $query->orderBy(['content.created_at' => SORT_DESC, 'content.id' => SORT_DESC]);
        }

        return $query;
    }

    public static function buildProductExportQuery($filters = [], $addOrderBy = false)
    {
        $query = \backend\models\Content::find()->select([
            'content.id as content_id',
            'content.name',
            'content.type_id',
            'content_product.product_category_id',
            'content_product.product_features',
            'content_product.product_main_material',
            'content_product.product_sources_material',
            'content_product.product_price',
            'content_product.product_distribution_location',
            'content_product.product_address',
            'content_product.product_phone',
            'content_product.other_information as product_other_info',
            'content_product.found_source',
            'content_product.contact',
            'content.description',
            'content.other_information as content_other_info',
            'content.region_id',
            'content.province_id',
            'content.district_id',
            'content.subdistrict_id',
            'content.zipcode_id',
            'content.created_by_user_id',
            'content.approved_by_user_id',
            'content.status',
            'content.note',
            'content.created_at',
        ]);

        $query->leftJoin('content_product', 'content_product.content_id = content.id');
        $query->leftJoin('profile', 'profile.user_id = content.created_by_user_id');
        $query->andFilterWhere(['=', 'content.type_id', 6]);
        $query->andFilterWhere(['=', 'content.active', 1]);

        if (!empty($filters['name'])) {
            $query->andFilterWhere(['like', 'content.name', $filters['name']]);
        }

        if (!empty($filters['product_category_id'])) {
            $query->andFilterWhere(['=', 'content_product.product_category_id', $filters['product_category_id']]);
        }

        if (!empty($filters['created_by_user_id'])) {
            $query->andFilterWhere(['=', 'created_by_user_id', $filters['created_by_user_id']]);
        }

        if (!empty($filters['updated_by_user_id'])) {
            $query->andFilterWhere(['=', 'updated_by_user_id', $filters['updated_by_user_id']]);
        }

        if (!empty($filters['approved_by_user_id'])) {
            $query->andFilterWhere(['=', 'approved_by_user_id', $filters['approved_by_user_id']]);
        }

        if (!empty($filters['note'])) {
            $query->andFilterWhere(['like', 'note', $filters['note']]);
        }

        if (!empty($filters['status'])) {
            $query->andFilterWhere(['like', 'status', $filters['status']]);
        }

        if (!empty($filters['updated_at'])) {
            $query->andFilterWhere(['like', 'updated_at', $filters['updated_at']]);
        }

        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            $dateStart = trim($filters['date_from']);
            $dateEnd = date('Y-m-d', strtotime(trim($filters['date_to']) . ' +1 day'));
            $query->andWhere(['between', 'content.created_at', $dateStart, $dateEnd]);
        }

        if ($addOrderBy) {
            $query->orderBy(['content.created_at' => SORT_DESC, 'content.id' => SORT_DESC]);
        }

        return $query;
    }

    /**
     * Get export headers for a content type (Thai labels)
     */
    public static function getExportHeaders($contentType)
    {
        $headersMap = [
            'content_plant' => [
                'ชื่อเรื่อง', 'ชื่ออื่น', 'ลิงก์', 'ลักษณะ', 'ประโยชน์', 'แหล่งที่พบ',
                'ชื่อสามัญ', 'ชื่อวิทยาศาสตร์', 'ชื่อวงศ์', 'ภาค', 'จังหวัด', 'อำเภอ',
                'ตำบล', 'รหัสไปรษณีย์', 'ชื่อผู้นำเข้าข้อมูล', 'ชื่อผู้อนุมัติข้อมูล',
                'สถานะ', 'หมายเหตุ', 'วันที่นำเข้าข้อมูล',
            ],
            'content_animal' => [
                'ชื่อเรื่อง', 'ชื่ออื่น', 'ลิงก์', 'ลักษณะ', 'ประโยชน์', 'แหล่งที่พบ',
                'ชื่อสามัญ', 'ชื่อวิทยาศาสตร์', 'ชื่อวงศ์', 'ภาค', 'จังหวัด', 'อำเภอ',
                'ตำบล', 'รหัสไปรษณีย์', 'ชื่อผู้นำเข้าข้อมูล', 'ชื่อผู้อนุมัติข้อมูล',
                'สถานะ', 'หมายเหตุ', 'วันที่นำเข้าข้อมูล',
            ],
            'content_fungi' => [
                'ชื่อเรื่อง', 'ชื่ออื่น', 'ลิงก์', 'ลักษณะ', 'ประโยชน์', 'แหล่งที่พบ',
                'ชื่อสามัญ', 'ชื่อวิทยาศาสตร์', 'ชื่อวงศ์', 'ภาค', 'จังหวัด', 'อำเภอ',
                'ตำบล', 'รหัสไปรษณีย์', 'ชื่อผู้นำเข้าข้อมูล', 'ชื่อผู้อนุมัติข้อมูล',
                'สถานะ', 'หมายเหตุ', 'วันที่นำเข้าข้อมูล',
            ],
            'content_ecotourism' => [
                'ชื่อเรื่อง', 'ที่อยู่', 'เบอร์โทรศัพท์', 'ชื่อผู้ติดต่อ', 'ข้อมูลการติดต่อ',
                'อธิบายการเดินทาง', 'รายละเอียด', 'ข้อมูลอื่นๆ', 'ลิงก์', 'ภาค', 'จังหวัด',
                'อำเภอ', 'ตำบล', 'รหัสไปรษณีย์', 'ชื่อผู้นำเข้าข้อมูล',
                'ชื่อผู้อนุมัติข้อมูล', 'สถานะ', 'หมายเหตุ', 'วันที่นำเข้าข้อมูล',
            ],
            'content_expert' => [
                'ชื่อ', 'นามสกุล', 'วันเกิด', 'ความเชี่ยวชาญ', 'อาชีพ', 'เลขบัตรประชาชน',
                'เบอร์โทรศัพท์', 'ที่อยู่', 'หมวดหมู่ผู้เชี่ยวชาญ', 'ลิงก์', 'ภาค', 'จังหวัด',
                'อำเภอ', 'ตำบล', 'รหัสไปรษณีย์', 'ชื่อผู้นำเข้าข้อมูล',
                'ชื่อผู้อนุมัติข้อมูล', 'สถานะ', 'หมายเหตุ', 'วันที่นำเข้าข้อมูล',
            ],
            'content_product' => [
                'ชื่อเรื่อง', 'หมวดหมู่ผลิตภัณฑ์', 'จุดเด่น/ประโยชน์', 'วัตถุดิบหลัก',
                'แหล่งวัตถุดิบ', 'ราคาขาย', 'สถานที่ผลิต/จำหน่าย', 'ที่อยู่',
                'เบอร์โทรศัพท์', 'รายละเอียดเพิ่มเติม', 'แหล่งที่พบ', 'ข้อมูลการติดต่อ',
                'รายละเอียด', 'ข้อมูลอื่นๆ', 'ลิงก์', 'ภาค', 'จังหวัด', 'อำเภอ',
                'ตำบล', 'รหัสไปรษณีย์', 'ชื่อผู้นำเข้าข้อมูล', 'ชื่อผู้อนุมัติข้อมูล',
                'สถานะ', 'หมายเหตุ', 'วันที่นำเข้าข้อมูล',
            ],
        ];

        return $headersMap[$contentType] ?? [];
    }

    /**
     * Get base file name for a content type (Thai)
     */
    public static function getExportBaseFileName($contentType)
    {
        $names = [
            'content_plant' => 'รายงานข้อมูลพืช',
            'content_animal' => 'รายงานข้อมูลสัตว์',
            'content_fungi' => 'รายงานข้อมูลจุลินทรีย์',
            'content_ecotourism' => 'รายงานข้อมูลท่องเที่ยวเชิงนิเวศ',
            'content_expert' => 'รายงานผู้เชี่ยวชาญ',
            'content_product' => 'ข้อมูลผลิตภัณฑ์ชุมชน',
        ];
        return $names[$contentType] ?? 'Export';
    }

    /**
     * Build export query for a content type
     */
    public static function buildExportQuery($contentType, $filters = [])
    {
        $methodMap = [
            'content_plant' => 'buildPlantExportQuery',
            'content_animal' => 'buildAnimalExportQuery',
            'content_fungi' => 'buildFungiExportQuery',
            'content_ecotourism' => 'buildEcotourismExportQuery',
            'content_expert' => 'buildExpertExportQuery',
            'content_product' => 'buildProductExportQuery',
        ];

        if (!isset($methodMap[$contentType])) {
            return null;
        }

        return self::{$methodMap[$contentType]}($filters, true);
    }

    /**
     * Format export rows for a content type (resolve IDs to names, generate URLs, clean text)
     */
    public static function formatExportRows($contentType, $rows)
    {
        $methodMap = [
            'content_plant' => 'formatPlantExportRows',
            'content_animal' => 'formatAnimalExportRows',
            'content_fungi' => 'formatFungiExportRows',
            'content_ecotourism' => 'formatEcotourismExportRows',
            'content_expert' => 'formatExpertExportRows',
            'content_product' => 'formatProductExportRows',
        ];

        if (!isset($methodMap[$contentType])) {
            return [];
        }

        return self::{$methodMap[$contentType]}($rows);
    }

    private static function getHostUrl()
    {
        $protocol = 'https://';
        $hostname = 'biog.local';

        if (class_exists('\Yii') && \Yii::$app && isset(\Yii::$app->request) && method_exists(\Yii::$app->request, 'getHostInfo')) {
            $protocol = stripos(\Yii::$app->request->getHostInfo(), 'https://') === 0 ? 'https://' : 'http://';
            $hostname = \Yii::$app->request->getHostName();
            if (empty($hostname)) {
                $hostname = 'localhost:8080';
            }
        }

        return $protocol . $hostname;
    }

    private static function getContentTypeById($typeId)
    {
        $map = [1 => 'plant', 2 => 'animal', 3 => 'fungi', 4 => 'expert', 5 => 'ecotourism', 6 => 'product'];
        return $map[$typeId] ?? 'plant';
    }

    public static function formatPlantExportRows($rows)
    {
        $baseUrl = self::getHostUrl();
        $exportRows = [];
        foreach ($rows as $value) {
            $contentType = self::getContentTypeById($value['type_id'] ?? 1);
            $exportRows[] = [
                $value['name'],
                $value['other_name'],
                $baseUrl . '/content-' . $contentType . '/' . $value['content_id'],
                self::cleanExportText($value['features'] ?? ''),
                self::cleanExportText($value['benefit'] ?? ''),
                $value['found_source'] ?? '',
                $value['common_name'] ?? '',
                $value['scientific_name'] ?? '',
                $value['family_name'] ?? '',
                self::getNameRegion($value['region_id'] ?? null),
                self::getNameProvince($value['province_id'] ?? null),
                self::getNameDistrict($value['district_id'] ?? null),
                self::getNameSubdistrict($value['subdistrict_id'] ?? null),
                self::getNameZipcode($value['zipcode_id'] ?? null),
                self::getName($value['created_by_user_id'] ?? null),
                self::getName($value['approved_by_user_id'] ?? null),
                ucfirst($value['status'] ?? ''),
                $value['note'] ?? '',
                $value['created_at'] ?? '',
            ];
        }
        return $exportRows;
    }

    public static function formatAnimalExportRows($rows)
    {
        $baseUrl = self::getHostUrl();
        $exportRows = [];
        foreach ($rows as $value) {
            $contentType = self::getContentTypeById($value['type_id'] ?? 2);
            $exportRows[] = [
                $value['name'],
                $value['other_name'],
                $baseUrl . '/content-' . $contentType . '/' . $value['content_id'],
                self::cleanExportText($value['features'] ?? ''),
                self::cleanExportText($value['benefit'] ?? ''),
                $value['found_source'] ?? '',
                $value['common_name'] ?? '',
                $value['scientific_name'] ?? '',
                $value['family_name'] ?? '',
                self::getNameRegion($value['region_id'] ?? null),
                self::getNameProvince($value['province_id'] ?? null),
                self::getNameDistrict($value['district_id'] ?? null),
                self::getNameSubdistrict($value['subdistrict_id'] ?? null),
                self::getNameZipcode($value['zipcode_id'] ?? null),
                self::getName($value['created_by_user_id'] ?? null),
                self::getName($value['approved_by_user_id'] ?? null),
                ucfirst($value['status'] ?? ''),
                $value['note'] ?? '',
                $value['created_at'] ?? '',
            ];
        }
        return $exportRows;
    }

    public static function formatFungiExportRows($rows)
    {
        $baseUrl = self::getHostUrl();
        $exportRows = [];
        foreach ($rows as $value) {
            $contentType = self::getContentTypeById($value['type_id'] ?? 3);
            $exportRows[] = [
                $value['name'],
                $value['other_name'],
                $baseUrl . '/content-' . $contentType . '/' . $value['content_id'],
                self::cleanExportText($value['features'] ?? ''),
                self::cleanExportText($value['benefit'] ?? ''),
                $value['found_source'] ?? '',
                $value['common_name'] ?? '',
                $value['scientific_name'] ?? '',
                $value['family_name'] ?? '',
                self::getNameRegion($value['region_id'] ?? null),
                self::getNameProvince($value['province_id'] ?? null),
                self::getNameDistrict($value['district_id'] ?? null),
                self::getNameSubdistrict($value['subdistrict_id'] ?? null),
                self::getNameZipcode($value['zipcode_id'] ?? null),
                self::getName($value['created_by_user_id'] ?? null),
                self::getName($value['approved_by_user_id'] ?? null),
                ucfirst($value['status'] ?? ''),
                $value['note'] ?? '',
                $value['created_at'] ?? '',
            ];
        }
        return $exportRows;
    }

    public static function formatEcotourismExportRows($rows)
    {
        $baseUrl = self::getHostUrl();
        $exportRows = [];
        foreach ($rows as $value) {
            $contentType = self::getContentTypeById($value['type_id'] ?? 5);
            $exportRows[] = [
                $value['name'],
                $value['address'] ?? '',
                $value['phone'] ?? '',
                $value['contact_name'] ?? '',
                $value['contact'] ?? '',
                self::cleanExportText($value['travel_information'] ?? ''),
                self::cleanExportText($value['description'] ?? ''),
                self::cleanExportText($value['other_information'] ?? ''),
                $baseUrl . '/content-' . $contentType . '/' . $value['content_id'],
                self::getNameRegion($value['region_id'] ?? null),
                self::getNameProvince($value['province_id'] ?? null),
                self::getNameDistrict($value['district_id'] ?? null),
                self::getNameSubdistrict($value['subdistrict_id'] ?? null),
                self::getNameZipcode($value['zipcode_id'] ?? null),
                self::getName($value['created_by_user_id'] ?? null),
                self::getName($value['approved_by_user_id'] ?? null),
                ucfirst($value['status'] ?? ''),
                $value['note'] ?? '',
                $value['created_at'] ?? '',
            ];
        }
        return $exportRows;
    }

    public static function formatExpertExportRows($rows)
    {
        $baseUrl = self::getHostUrl();
        $exportRows = [];
        foreach ($rows as $value) {
            $contentType = self::getContentTypeById($value['type_id'] ?? 4);
            $exportRows[] = [
                $value['expert_firstname'] ?? '',
                $value['expert_lastname'] ?? '',
                $value['expert_birthdate'] ?? '',
                $value['expert_expertise'] ?? '',
                $value['expert_occupation'] ?? '',
                $value['expert_card_id'] ?? '',
                $value['phone'] ?? '',
                self::cleanExportText($value['address'] ?? ''),
                self::getNameCategoryExpert($value['expert_category_id'] ?? null),
                $baseUrl . '/content-' . $contentType . '/' . $value['content_id'],
                self::getNameRegion($value['region_id'] ?? null),
                self::getNameProvince($value['province_id'] ?? null),
                self::getNameDistrict($value['district_id'] ?? null),
                self::getNameSubdistrict($value['subdistrict_id'] ?? null),
                self::getNameZipcode($value['zipcode_id'] ?? null),
                self::getName($value['created_by_user_id'] ?? null),
                self::getName($value['approved_by_user_id'] ?? null),
                ucfirst($value['status'] ?? ''),
                $value['note'] ?? '',
                $value['created_at'] ?? '',
            ];
        }
        return $exportRows;
    }

    public static function formatProductExportRows($rows)
    {
        $baseUrl = self::getHostUrl();
        $exportRows = [];
        foreach ($rows as $value) {
            $contentType = self::getContentTypeById($value['type_id'] ?? 6);
            $exportRows[] = [
                $value['name'],
                self::getNameCategoryProduct($value['product_category_id'] ?? null),
                self::cleanExportText($value['product_features'] ?? ''),
                self::cleanExportText($value['product_main_material'] ?? ''),
                self::cleanExportText($value['product_sources_material'] ?? ''),
                $value['product_price'] ?? '',
                self::cleanExportText($value['product_distribution_location'] ?? ''),
                self::cleanExportText($value['product_address'] ?? ''),
                $value['product_phone'] ?? '',
                self::cleanExportText($value['product_other_info'] ?? ''),
                self::cleanExportText($value['found_source'] ?? ''),
                self::cleanExportText($value['contact'] ?? ''),
                self::cleanExportText($value['description'] ?? ''),
                self::cleanExportText($value['content_other_info'] ?? ''),
                $baseUrl . '/content-' . $contentType . '/' . $value['content_id'],
                self::getNameRegion($value['region_id'] ?? null),
                self::getNameProvince($value['province_id'] ?? null),
                self::getNameDistrict($value['district_id'] ?? null),
                self::getNameSubdistrict($value['subdistrict_id'] ?? null),
                self::getNameZipcode($value['zipcode_id'] ?? null),
                self::getName($value['created_by_user_id'] ?? null),
                self::getName($value['approved_by_user_id'] ?? null),
                ucfirst($value['status'] ?? ''),
                $value['note'] ?? '',
                $value['created_at'] ?? '',
            ];
        }
        return $exportRows;
    }

    /**
     * Clean text for export (strip HTML, trim whitespace)
     */
    private static function cleanExportText($text)
    {
        if (empty($text)) {
            return '';
        }
        // Strip HTML tags and decode entities
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Normalize whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
}
