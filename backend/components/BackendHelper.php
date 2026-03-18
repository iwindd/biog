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
    public static function getName($uid)
    {
        $profile = Profile::find()->where(['user_id' => $uid])->one();
        if (!empty($profile)) {
            return $profile->firstname . " " . $profile->lastname;
        }
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
    public static function getNameProvince($id){
        $model = Province::findOne($id);
        if(!empty($model)){
            return $model['name_th'];
        }else{
            return "-";
        }
    }
    public static function getNameSubdistrict($id){
        $model = Subdistrict::findOne($id);
        if(!empty($model)){
            return $model['name_th'];
        }else{
            return "-";
        }
    }
    public static function getNameDistrict($id){
        $model = District::findOne($id);
        if(!empty($model)){
            return $model['name_th'];
        }else{
            return "-";
        }
    }
    public static function getNameRegion($id){
        $model = Region::findOne($id);
        if(!empty($model)){
            return $model['name_th'];
        }else{
            return "-";
        }
    }

    public static function getNameZipcode($id){
        $model = Zipcode::findOne($id);
        if(!empty($model)){
            return $model['zipcode'];
        }else{
            return "-";
        }
    }

     public static function getNameCategoryExpert($id){
        $model = ExpertCategory::findOne($id);
        if(!empty($model)){
            return $model['name'];
        }else{
            return "-";
        }
    }
     public static function getNameCategoryProduct($id){
        $model = ProductCategory::findOne($id);
        if(!empty($model)){
            return $model['name'];
        }else{
            return "-";
        }
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
}
