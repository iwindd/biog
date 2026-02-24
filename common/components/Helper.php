<?php
namespace common\components;

use yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\db\Query;

use backend\models\Variables;
use backend\models\Content;
use backend\models\ContentPlant;
use backend\models\ContentAnimal;
use backend\models\ContentFungi;
use backend\models\ContentExpert;
use backend\models\ContentEcotourism;
use backend\models\ContentProduct;
use backend\models\ContentTaxonomy;
use backend\models\Picture;
use backend\models\Comment;
use backend\models\Blog;
use backend\models\News;
use backend\models\Knowledge;
use backend\models\BlogFile;
use backend\models\ContentStatistics;
use backend\models\Users;
use backend\models\Profile;
use frontend\models\StudentTeacher;
use frontend\components\FrontendHelper;

class Helper {
     
    public function convertDateThai($strDate)
	{
		$strYear = date("Y",strtotime($strDate))+543;
		$strMonth= date("n",strtotime($strDate));
		$strDay= date("j",strtotime($strDate));
		$strHour= date("H",strtotime($strDate));
		$strMinute= date("i",strtotime($strDate));
		$strSeconds= date("s",strtotime($strDate));
		$strMonthCut = Array("","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค.");
		$strMonthThai=$strMonthCut[$strMonth];
		return "$strDay $strMonthThai $strYear";
	}
	
	public static function getVariables()
    {
        $model = Variables::find()->asArray()->all();
        $result = array();
        foreach($model as $data){
            $result[$data['key']] = $data['value'];
        }
        return $result;

	}
	

	function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}


	public static function getEventIDActive($id)
    {
		$dataId = self::getAllIdEvent($id);

        if (!empty($dataId)) {
            $eventPlan = Content::find()->where(['IN', 'id', $dataId])->andWhere(['active' => 1])->orderBy(['id' => SORT_DESC])->one();
            if (!empty($eventPlan)) {
                return $eventPlan->id;
            }
        }

        return $id;
    }

    public static function getBlogIDActive($id)
    {
		$dataId = self::getAllIdBlog($id);

        if (!empty($dataId)) {
            $data = Blog::find()->where(['IN', 'id', $dataId])->andWhere(['active' => 1])->orderBy(['id' => SORT_DESC])->one();
            if (!empty($data)) {
                return $data->id;
            }
        }

        return $id;
    }

    public static function getNewsIDActive($id)
    {
		$dataId = self::getAllIdNews($id);

        if (!empty($dataId)) {
            $data = News::find()->where(['IN', 'id', $dataId])->andWhere(['active' => 1])->orderBy(['id' => SORT_DESC])->one();
            if (!empty($data)) {
                return $data->id;
            }
        }

        return $id;
    }

    public static function getKnowledgeIDActive($id)
    {
		$dataId = self::getAllIdKnowledge($id);

        if (!empty($dataId)) {
            $data = Knowledge::find()->where(['IN', 'id', $dataId])->andWhere(['active' => 1])->orderBy(['id' => SORT_DESC])->one();
            if (!empty($data)) {
                return $data->id;
            }
        }

        return $id;
    }

    public static function getAllIdEvent($id)
    {
        $idHist = self::getIdEventRoot($id);
        $idFuture = self::getIdEventFuture($id);
        $allId = array();
        if (!empty($idHist)) {
            $idHist = explode('/', $idHist);
            foreach ($idHist as $value) {
                if (!empty($value)) {
                    $allId[] = $value;
                }
            }
        }

        if (!empty($idFuture)) {
            $idFuture = explode('/', $idFuture);
            foreach ($idFuture as $value) {
                if (!empty($value)) {
                    $allId[] = $value;
                }
            }
        }

        return $allId;
    }

    public static function getAllIdBlog($id)
    {
        $idHist = self::getIdBlogRoot($id);
        $idFuture = self::getIdBlogFuture($id);
        $allId = array();
        if (!empty($idHist)) {
            $idHist = explode('/', $idHist);
            foreach ($idHist as $value) {
                if (!empty($value)) {
                    $allId[] = $value;
                }
            }
        }

        if (!empty($idFuture)) {
            $idFuture = explode('/', $idFuture);
            foreach ($idFuture as $value) {
                if (!empty($value)) {
                    $allId[] = $value;
                }
            }
        }

        return $allId;
    }

    public static function getAllIdNews($id)
    {
        $idHist = self::getIdNewsRoot($id);
        $idFuture = self::getIdNewsFuture($id);
        $allId = array();
        if (!empty($idHist)) {
            $idHist = explode('/', $idHist);
            foreach ($idHist as $value) {
                if (!empty($value)) {
                    $allId[] = $value;
                }
            }
        }

        if (!empty($idFuture)) {
            $idFuture = explode('/', $idFuture);
            foreach ($idFuture as $value) {
                if (!empty($value)) {
                    $allId[] = $value;
                }
            }
        }

        return $allId;
    }

    public static function getAllIdKnowledge($id)
    {
        $idHist = self::getIdKnowledgeRoot($id);
        $idFuture = self::getIdKnowledgeFuture($id);
        $allId = array();
        if (!empty($idHist)) {
            $idHist = explode('/', $idHist);
            foreach ($idHist as $value) {
                if (!empty($value)) {
                    $allId[] = $value;
                }
            }
        }

        if (!empty($idFuture)) {
            $idFuture = explode('/', $idFuture);
            foreach ($idFuture as $value) {
                if (!empty($value)) {
                    $allId[] = $value;
                }
            }
        }

        return $allId;
    }

    //content
    public static function getIdEventRoot($id)
    {
        $allID = "";
        $data = Content::find()->where(['id' => $id])->one();
        if (!empty($data)) {
            $allID = $allID . "/" . $data->id;
            if (!empty($data->content_source_id)) {
                $allID = $allID . self::getIdEventRoot($data->content_source_id);
            }
        }
        return $allID;
    }

    public static function getIdEventFuture($id)
    {
        $allID = "";
        $data = Content::find()->select('id')->where(['content_source_id' => $id])->one();
        if (!empty($data)) {
            $allID = $allID . "/" . $data->id;
            if (!empty($data->id)) {
                $allID = $allID . self::getIdEventFuture($data->id);
            }
        }
        return $allID;
    }


    //blog
    public static function getIdBlogRoot($id)
    {
        $allID = "";
        $data = Blog::find()->where(['id' => $id])->one();
        if (!empty($data)) {
            $allID = $allID . "/" . $data->id;
            if (!empty($data->blog_source_id)) {
                $allID = $allID . self::getIdBlogRoot($data->blog_source_id);
            }
        }
        return $allID;
    }

    public static function getIdBlogFuture($id)
    {
        $allID = "";
        $data = Blog::find()->where(['blog_source_id' => $id])->one();
        if (!empty($data)) {
            $allID = $allID . "/" . $data->id;
            if (!empty($data->id)) {
                $allID = $allID . self::getIdBlogFuture($data->id);
            }
        }
        return $allID;
    }

    //news
    public static function getIdNewsRoot($id)
    {
        $allID = "";
        $data = News::find()->where(['id' => $id])->one();
        if (!empty($data)) {
            $allID = $allID . "/" . $data->id;
            if (!empty($data->news_source_id)) {
                $allID = $allID . self::getIdNewsRoot($data->news_source_id);
            }
        }
        return $allID;
    }

    public static function getIdNewsFuture($id)
    {
        $allID = "";
        $data = News::find()->where(['news_source_id' => $id])->one();
        if (!empty($data)) {
            $allID = $allID . "/" . $data->id;
            if (!empty($data->id)) {
                $allID = $allID . self::getIdNewsFuture($data->id);
            }
        }
        return $allID;
    }

    //knowledge
    public static function getIdKnowledgeRoot($id)
    {
        $allID = "";
        $data = Knowledge::find()->where(['id' => $id])->one();
        if (!empty($data)) {
            $allID = $allID . "/" . $data->id;
            if (!empty($data->knowledge_source_id)) {
                $allID = $allID . self::getIdKnowledgeRoot($data->knowledge_source_id);
            }
        }
        return $allID;
    }

    public static function getIdKnowledgeFuture($id)
    {
        $allID = "";
        $data = Knowledge::find()->where(['knowledge_source_id' => $id])->one();
        if (!empty($data)) {
            $allID = $allID . "/" . $data->id;
            if (!empty($data->id)) {
                $allID = $allID . self::getIdKnowledgeFuture($data->id);
            }
        }
        return $allID;
    }



    private static function savePicture($newContentId, $value)
    {
  
        $mediaModel = new Picture();
        $mediaModel->content_id = $newContentId;
        $mediaModel->name = $value['file_display_name'];
        $mediaModel->path = $value['file_key'];
        if(empty($value['created_by_user_id'])){
            $mediaModel->created_by_user_id = Yii::$app->user->identity->id;
        }else{
            $mediaModel->created_by_user_id = $value['created_by_user_id'];
        }
        $mediaModel->updated_by_user_id = Yii::$app->user->identity->id;
        $mediaModel->created_at = date("Y-m-d H:i:s");
        $mediaModel->updated_at = date("Y-m-d H:i:s");
        if (!$mediaModel->save()) {
            return false;
        }

        return true;
    }


    private static function saveContentTaxonomy($newContentId, $value)
    {
        $taxonomyModel = new ContentTaxonomy();
        $taxonomyModel->content_id = $newContentId;
        $taxonomyModel->taxonomy_id = $value['taxonomy_id'];
        $taxonomyModel->created_at = date("Y-m-d H:i:s");
        if (!$taxonomyModel->save()) {
            return false;
        }
        return true;
    }

    public static function saveContentComment($newContentId, $value)
    {
        $commentModel = new Comment();
        $commentModel->user_id = $value['user_id'];
        $commentModel->content_id  = $newContentId;
        $commentModel->message  = $value['message'];
        $commentModel->created_at = $value['created_at'];
        if (!$commentModel->save()) {
            return false;
        }
        return true;
    }

    public static function updateStatusRevisionContent($contentId, $status = "", $del =0, $note = ""){
        $transaction = \Yii::$app->db->beginTransaction();
        $result = array(
            'status' => 'error',
            'error' => array()
        );

        $case_error = array();
        try {

            //find old latest content
            $latestContentId = Helper::getEventIDActive($contentId);

            if(!empty($latestContentId)){
                $modelOld = Content::findOne($latestContentId);
                $dataOld = $modelOld->attributes;
                $modelOld->active = 0;
                $modelOld->save();

                $newModel = new Content();
                $newModel->setAttributes($dataOld);

                if (!empty($status)) {
                    $newModel->status = $status;
                }

                if (!empty($note)) {
                    $newModel->note = $note;
                }

                if( $modelOld->content_root_id == 0){
                    $newModel->content_root_id = $latestContentId;   
                }else{
                    $newModel->content_root_id = $modelOld->content_root_id;   
                }

                Content::updateAll(['active' => '0'], ['content_root_id' => $newModel->content_root_id]);
                Content::updateAll(['active' => '0'], ['id' => $newModel->content_root_id]);

                $newModel->content_source_id = $latestContentId;
                $newModel->updated_at = date("Y-m-d H:i:s");

                if($newModel->status == 'approved'){
                    $newModel->approved_by_user_id = Yii::$app->user->identity->id;
                }
                
                if($del == 1){
                    $newModel->active = 0;
                }else{
                    $newModel->active = 1;
                }

                if ($newModel->save()) {

                    $newContentId = $newModel->id;
                    //clone picture
                    $modelOldPicture = Picture::find()->where(['content_id' => $latestContentId])->asArray()->all();
                    if (!empty($modelOldPicture)) {
                        foreach ($modelOldPicture as $value) {
                            $value['file_display_name'] = $value['name'];
                            $value['file_key'] = $value['path'];
                            $newRecordPicture = self::savePicture($newContentId, $value);
                            if($newRecordPicture == false){
                                $case_error[] = 'เพิ่มไฟล์ '.$value['file_display_name'] . " ไม่สำเร็จ";
                            }
                        }
                    }

                    //clone taxonomy
                    $modelOldTaxonomy = ContentTaxonomy::find()->where(['content_id' => $latestContentId])->asArray()->all();
                    if (!empty($modelOldTaxonomy)) {
                        foreach ($modelOldTaxonomy as $value) {
                            $newRecordTaxonomy = self::saveContentTaxonomy($newContentId, $value);
                            if($newRecordTaxonomy == false){
                                $case_error[] = "เพิ่มคำช่วยค้นหาไม่สำเร็จ";
                            }
                        }
                    }

                    //clone comment
                    // $modelOldComment = Comment::find()->where(['content_id' => $latestContentId])->asArray()->all();
                    // if (!empty($modelOldComment)) {
                    //     foreach ($modelOldComment as $value) {
                    //         $newRecordComment = self::saveContentComment($newContentId, $value);
                    //         if($newRecordComment == false){
                    //             $case_error[] = "เพิ่มการแสดงความคิดเห็นไม่สำเร็จ";
                    //         }
                    //     }
                    // }

                    // $modelStatisticOld = ContentStatistics::find()->where(['content_id' => $latestContentId])->one();
                    // if (!empty($modelStatisticOld)) {
                    //     $dataStatisticOld = $modelStatisticOld->attributes;

                    //     $newStatisticModel = new ContentStatistics();
                    //     $newStatisticModel->setAttributes($dataStatisticOld);
                    //     $newStatisticModel->content_id = $newContentId;
                    //     $newStatisticModel->save();
                    // }


                    if($newModel->type_id == 1){
                        $modelOldPlant = ContentPlant::find()->where(['content_id' => $latestContentId])->one();
                        if (!empty($modelOldPlant)) {
                            $dataOldPlant = $modelOldPlant->attributes;
                            $modelPlant = new ContentPlant();
                            $modelPlant->setAttributes($dataOldPlant);
                            $modelPlant->content_id = $newContentId;
                            $modelPlant->created_at = date("Y-m-d H:i:s");
                            $modelPlant->updated_at = date("Y-m-d H:i:s");
                            if (!$modelPlant->save()) {
                                $case_error[] = "เพิ่มข้อมูลพืชไม่สำเร็จ";
                            }
                        }
                    }else if($newModel->type_id == 2){
                        $modelOldAnimal = ContentAnimal::find()->where(['content_id' => $latestContentId])->one();
                        if (!empty($modelOldAnimal)) {
                            $dataOldAnimal = $modelOldAnimal->attributes;
                            $modelAnimal = new ContentAnimal();
                            $modelAnimal->setAttributes($dataOldAnimal);
                            $modelAnimal->content_id = $newContentId;
                            $modelAnimal->created_at = date("Y-m-d H:i:s");
                            $modelAnimal->updated_at = date("Y-m-d H:i:s");
                            if (!$modelAnimal->save()) {
                                $case_error[] = "เพิ่มข้อมูลสัตว์ไม่สำเร็จ";
                            }
                        }
                    }else if($newModel->type_id == 3){
                        $modelOldFungi = ContentFungi::find()->where(['content_id' => $latestContentId])->one();
                        if (!empty($modelOldFungi)) {
                            $dataOldFungi = $modelOldFungi->attributes;
                            $modelFungi = new ContentFungi();
                            $modelFungi->setAttributes($dataOldFungi);
                            $modelFungi->content_id = $newContentId;
                            $modelFungi->created_at = date("Y-m-d H:i:s");
                            $modelFungi->updated_at = date("Y-m-d H:i:s");
                            if (!$modelFungi->save()) {
                                $case_error[] = "เพิ่มข้อมูลจุลินทรีย์ไม่สำเร็จ";
                            }
                        }
                    }else if($newModel->type_id == 4){
                        $modelOldExpert = ContentExpert::find()->where(['content_id' => $latestContentId])->one();
                        if (!empty($modelOldExpert)) {
                            $dataOldExpert = $modelOldExpert->attributes;
                            $modelExpert = new ContentExpert();
                            $modelExpert->setAttributes($dataOldExpert);
                            $modelExpert->content_id = $newContentId;
                            $modelExpert->created_at = date("Y-m-d H:i:s");
                            $modelExpert->updated_at = date("Y-m-d H:i:s");
                            if (!$modelExpert->save()) {
                                $case_error[] = "เพิ่มข้อมูลผู้เชี่ยวชาญไม่สำเร็จ";
                            }
                        }
                    }else if($newModel->type_id == 5){
                        $modelOldEcotourism = ContentEcotourism::find()->where(['content_id' => $latestContentId])->one();
                        if (!empty($modelOldEcotourism)) {
                            $dataOldEcotourism = $modelOldEcotourism->attributes;
                            $modelEcotourism = new ContentEcotourism();
                            $modelEcotourism->setAttributes($dataOldEcotourism);
                            $modelEcotourism->content_id = $newContentId;
                            $modelEcotourism->created_at = date("Y-m-d H:i:s");
                            $modelEcotourism->updated_at = date("Y-m-d H:i:s");
                            if (!$modelEcotourism->save()) {
                                $case_error[] = "เพิ่มข้อมูลแหล่งท่องเที่ยวเชิงนิเวศไม่สำเร็จ";
                            }
                        }
                    }else if($newModel->type_id == 6){
                        $modelOldProduct = ContentProduct::find()->where(['content_id' => $latestContentId])->one();
                        if (!empty($modelOldProduct)) {
                            $dataOldProduct = $modelOldProduct->attributes;
                            $modelProduct = new ContentProduct();
                            $modelProduct->setAttributes($dataOldProduct);
                            $modelProduct->content_id = $newContentId;
                            $modelProduct->created_at = date("Y-m-d H:i:s");
                            $modelProduct->updated_at = date("Y-m-d H:i:s");
                            if (!$modelProduct->save()) {
                                $case_error[] = "เพิ่มข้อมูลผลิตภัณฑ์ชุมชนไม่สำเร็จ";
                            }
                        }
                    }

                    if (empty($case_error)) {
                        $transaction->commit();

                        if ($newModel->status == 'rejected') {
                            FrontendHelper::saveUserLog('content', Yii::$app->user->identity->id, $newContentId, 'update content '.FrontendHelper::getContentTypeById($newModel->type_id), 'ไม่อนุมัติเนื้อหาหมายเหตุ: '.$note);
                        }else if ($newModel->status == 'approved') {
                            if (!empty($note)) {
                                FrontendHelper::saveUserLog('content', Yii::$app->user->identity->id, $newContentId, 'update content '.FrontendHelper::getContentTypeById($newModel->type_id), 'อนุมัติเนื้อหาหมายเหตุ: '.$note);
                            }else{
                                FrontendHelper::saveUserLog('content', Yii::$app->user->identity->id, $newContentId, 'update content '.FrontendHelper::getContentTypeById($newModel->type_id), 'อนุมัติเนื้อหา');
                            }
                        }

                        if($newModel->active == 1 && $modelOld->status != $newModel->status){
                            self::sendMailUpdateStatusContent($modelOld, $newModel);
                        }

                        $result['status'] = 'success';
                    }
                }
            }

        } catch (\Exception $e) {

            $case_error[] = "เกิดข้อผิดพลาดการดำเนินงานไม่สำเร็จ";

            $transaction->rollBack();
            //throw $e;
        }

        $result['error'] = $case_error;

        return $result;
    }


    public static function sendMailUpdateStatusContent($modelOld, $newModel){
        $user = Users::find()->where(['id' => $modelOld->created_by_user_id])->one();

        if(!empty($user)){

            $profile = Profile::find()->where(['user_id' => $user->id])->one();

            $setSubject = "BIOGANG: Update Status Content";
            $setMessage1 = Variables::find()->where(['key' => 'update_status_content'])->one();
            $setMessage1 = empty($setMessage1['value'])? "":$setMessage1['value'];


            $mail_sender = Variables::find()->where(['key' => 'sender_mail'])->one();
            if(!empty($mail_sender)){
                
                $hostname = getenv('HTTP_HOST');
                $protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';

                if(empty($hostname)){
                    $hostname = "localhost:8080";
                }

                $setMessage = "";
                if(!empty($setMessage1)){

                    if ($newModel->status == 'rejected') {
                        $setMessage =  str_replace("[status-text]", "ไม่อนุมัติเนื่องจาก".$newModel->note, $setMessage1);
                    }else{
                        $setMessage =  str_replace("[status-text]", "อนุมัติเรียบร้อยแล้ว", $setMessage1);
                    }

                    $setMessage =  str_replace("[content-id]",$protocol.$hostname."/content-".FrontendHelper::getContentTypeById($newModel->type_id)."/".$newModel->id, $setMessage);

                    $setMessage =  str_replace("[user-name]",$profile->firstname." ".$profile->lastname,$setMessage);
                }

                \Yii::$app->mailer->compose()
                ->setFrom([$mail_sender['value']=>'BIOGANG'])
                ->setTo($user->email)
                ->setSubject($setSubject)
                ->setTextBody($setMessage)
                ->send();
            }
            
        }
    }

    public static function sendMailStudentTeacherChangeSchool($teacherId){

        $studentAll = StudentTeacher::find()->where(['teacher_id' => $teacherId, 'active' => 1])->all();

        $teacherProfile = Profile::find()->where(['user_id' => $teacherId])->one();
        if (!empty($studentAll)) {

            foreach ($studentAll as $key => $data) {
                $user = Users::find()->where(['id' => $data->student_id])->one();

                if (!empty($user)) {
                    $profile = Profile::find()->where(['user_id' => $user->id])->one();

                    $setSubject = "BIOGANG: ครู/อาจารย์ที่ปรึกษาย้ายโรงเรียน";
                    $setMessage1 = Variables::find()->where(['key' => 'teacher_change_school'])->one();
                    $setMessage1 = empty($setMessage1['value'])? "":$setMessage1['value'];


                    $mail_sender = Variables::find()->where(['key' => 'sender_mail'])->one();
                    if (!empty($mail_sender)) {
                        $hostname = getenv('HTTP_HOST');
                        $protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';

                        if (empty($hostname)) {
                            $hostname = "localhost:8080";
                        }

                        $setMessage = "";
                        if (!empty($setMessage1)) {
                            $setMessage =  str_replace("[teacher-name]", $teacherProfile->firstname." ".$teacherProfile->lastname, $setMessage1);

                            $setMessage =  str_replace("[user-name]", $profile->firstname." ".$profile->lastname, $setMessage);

                            $setMessage =  str_replace("[profile-url]", $protocol.$hostname."/profile", $setMessage);
                        }

                        \Yii::$app->mailer->compose()
                        ->setFrom([$mail_sender['value']=>'BIOGANG'])
                        ->setTo($user->email)
                        ->setSubject($setSubject)
                        ->setTextBody($setMessage)
                        ->send();
                    }
                }
            }
        }
    }

    public static function sendMailApprovedTeacher($teacherId, $status){

        $teacherProfile = Profile::find()->where(['user_id' => $teacherId])->one();
        if (!empty($teacherProfile)) {
            $user = Users::find()->where(['id' => $teacherId])->one();

            $setSubject = "BIOGANG: เปลี่ยนแปลงสถานะครู/อาจารย์ที่ปรึกษา";
            $setMessage1 = Variables::find()->where(['key' => 'approved_teacher'])->one();
            $setMessage1 = empty($setMessage1['value'])? "":$setMessage1['value'];


            $mail_sender = Variables::find()->where(['key' => 'sender_mail'])->one();
            if (!empty($mail_sender)) {
                $hostname = getenv('HTTP_HOST');
                $protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';

                if (empty($hostname)) {
                    $hostname = "localhost:8080";
                }

                $setMessage = "";
                if (!empty($setMessage1)) {
                    $setMessage =  str_replace("[teacher-name]", $teacherProfile->firstname." ".$teacherProfile->lastname, $setMessage1);

                    $setMessage =  str_replace("[status]", $status, $setMessage);

                    $setMessage =  str_replace("[link]", $protocol.$hostname."/login", $setMessage);
                }

                // print "<pre>";
                // print_r($setMessage);
                // print '</pre>';
                // exit();

                \Yii::$app->mailer->compose()
                ->setFrom([$mail_sender['value']=>'BIOGANG'])
                ->setTo($user->email)
                ->setSubject($setSubject)
                ->setTextBody($setMessage)
                ->send();

               
            }
            
        }
    }

   
}