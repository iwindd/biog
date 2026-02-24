<?php

namespace frontend\components;

use backend\models\Zipcode;
use common\components\FileLibrary;
use common\components\Upload;
use frontend\components\TaxonomyHelper;
use frontend\models\content\ContentAnimal;
use frontend\models\content\ContentEcotourism;
use frontend\models\content\ContentExpert;
use frontend\models\content\ContentFungi;
use frontend\models\content\ContentPlant;
use frontend\models\content\ContentProduct;
use frontend\models\content\ExpertCategory;
use frontend\models\Blog;
use frontend\models\BlogStatistics;
use frontend\models\Content;
use frontend\models\ContentStatistics;
use frontend\models\ContentTaxonomy;
use frontend\models\District;
use frontend\models\Knowledge;
use frontend\models\KnowledgeStatistics;
use frontend\models\Learningcenter;
use frontend\models\LearningcenterInformation;
use frontend\models\Media;
use frontend\models\NewsStatistics;
use frontend\models\Profile;
use frontend\models\Province;
use frontend\models\StudentTeacher;
use frontend\models\Subdistrict;
use frontend\models\Taxonomy;
use frontend\models\UserLikeBlog;
use frontend\models\UserLikeContent;
use frontend\models\UserLog;
use frontend\models\Users;
use frontend\models\UserSchool;
use yii\db\query;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use Yii;

class FrontendHelper
{
    public static function getProvinceAll()
    {
        $province = array(
            'กรุงเทพมหานคร' => 'กรุงเทพมหานคร',
            'สมุทรปราการ' => 'สมุทรปราการ',
            'นนทบุรี' => 'นนทบุรี',
            'ปทุมธานี' => 'ปทุมธานี',
            'พระนครศรีอยุธยา' => 'พระนครศรีอยุธยา',
            'อ่างทอง' => 'อ่างทอง',
            'ลพบุรี' => 'ลพบุรี',
            'สิงห์บุรี' => 'สิงห์บุรี',
            'ชัยนาท' => 'ชัยนาท',
            'สระบุรี' => 'สระบุรี',
            'ชลบุรี' => 'ชลบุรี',
            'ระยอง' => 'ระยอง',
            'จันทบุรี' => 'จันทบุรี',
            'ตราด' => 'ตราด',
            'ฉะเชิงเทรา' => 'ฉะเชิงเทรา',
            'ปราจีนบุรี' => 'ปราจีนบุรี',
            'นครนายก' => 'นครนายก',
            'สระแก้ว' => 'สระแก้ว',
            'นครราชสีมา' => 'นครราชสีมา',
            'บุรีรัมย์' => 'บุรีรัมย์',
            'สุรินทร์' => 'สุรินทร์',
            'ศรีสะเกษ' => 'ศรีสะเกษ',
            'อุบลราชธานี' => 'อุบลราชธานี',
            'ยโสธร' => 'ยโสธร',
            'ชัยภูมิ' => 'ชัยภูมิ',
            'อำนาจเจริญ' => 'อำนาจเจริญ',
            'บึงกาฬ' => 'บึงกาฬ',
            'หนองบัวลำภู' => 'หนองบัวลำภู',
            'ขอนแก่น' => 'ขอนแก่น',
            'อุดรธานี' => 'อุดรธานี',
            'เลย' => 'เลย',
            'หนองคาย' => 'หนองคาย',
            'มหาสารคาม' => 'มหาสารคาม',
            'ร้อยเอ็ด' => 'ร้อยเอ็ด',
            'กาฬสินธุ์' => 'กาฬสินธุ์',
            'สกลนคร' => 'สกลนคร',
            'นครพนม' => 'นครพนม',
            'มุกดาหาร' => 'มุกดาหาร',
            'เชียงใหม่' => 'เชียงใหม่',
            'ลำพูน' => 'ลำพูน',
            'ลำปาง' => 'ลำปาง',
            'อุตรดิตถ์' => 'อุตรดิตถ์',
            'แพร่' => 'แพร่',
            'น่าน' => 'น่าน',
            'พะเยา' => 'พะเยา',
            'เชียงราย' => 'เชียงราย',
            'แม่ฮ่องสอน' => 'แม่ฮ่องสอน',
            'นครสวรรค์' => 'นครสวรรค์',
            'อุทัยธานี' => 'อุทัยธานี',
            'กำแพงเพชร' => 'กำแพงเพชร',
            'ตาก' => 'ตาก',
            'สุโขทัย' => 'สุโขทัย',
            'พิษณุโลก' => 'พิษณุโลก',
            'พิจิตร' => 'พิจิตร',
            'เพชรบูรณ์' => 'เพชรบูรณ์',
            'ราชบุรี' => 'ราชบุรี',
            'กาญจนบุรี' => 'กาญจนบุรี',
            'สุพรรณบุรี' => 'สุพรรณบุรี',
            'นครปฐม' => 'นครปฐม',
            'สมุทรสาคร' => 'สมุทรสาคร',
            'สมุทรสงคราม' => 'สมุทรสงคราม',
            'เพชรบุรี' => 'เพชรบุรี',
            'ประจวบคีรีขันธ์' => 'ประจวบคีรีขันธ์',
            'นครศรีธรรมราช' => 'นครศรีธรรมราช',
            'กระบี่' => 'กระบี่',
            'พังงา' => 'พังงา',
            'ภูเก็ต' => 'ภูเก็ต',
            'สุราษฎร์ธานี' => 'สุราษฎร์ธานี',
            'ระนอง' => 'ระนอง',
            'ชุมพร' => 'ชุมพร',
            'สงขลา' => 'สงขลา',
            'สตูล' => 'สตูล',
            'ตรัง' => 'ตรัง',
            'พัทลุง' => 'พัทลุง',
            'ปัตตานี' => 'ปัตตานี',
            'ยะลา' => 'ยะลา',
            'นราธิวาส' => 'นราธิวาส',
        );
        $province_EN = array(
            'Bangkok' => 'Bangkok',
            'Samut Prakarn' => 'Samut Prakarn',
            'Nonthaburi' => 'Nonthaburi',
            'Pathum Thani' => 'Pathum Thani',
            'Phra Nakhon Si Ayutthaya' => 'Phra Nakhon Si Ayutthaya',
            'Ang Thong' => 'Ang Thong',
            'Lop Buri' => 'Lop Buri',
            'Sing Buri' => 'Sing Buri',
            'Chai Nat' => 'Chai Nat',
            'Saraburi' => 'Saraburi',
            'Chon Buri' => 'Chon Buri',
            'Rayong' => 'Rayong',
            'Chanthaburi' => 'Chanthaburi',
            'Trat' => 'Trat',
            'Chachoengsao' => 'Chachoengsao',
            'Prachin Buri' => 'Prachin Buri',
            'Nakhon Nayok' => 'Nakhon Nayok',
            'Sa kaeo' => 'Sa kaeo',
            'Nakhon Ratchasima' => 'Nakhon Ratchasima',
            'Buri Ram' => 'Buri Ram',
            'Surin' => 'Surin',
            'Si Sa Ket' => 'Si Sa Ket',
            'Ubon Ratchathani' => 'Ubon Ratchathani',
            'Yasothon' => 'Yasothon',
            'Chaiyaphum' => 'Chaiyaphum',
            'Amnat Charoen' => 'Amnat Charoen',
            'Bueng Kan' => 'Bueng Kan',
            'Nong Bua Lam Phu' => 'Nong Bua Lam Phu',
            'Khon Kaen' => 'Khon Kaen',
            'Udon Thani' => 'Udon Thani',
            'Loei' => 'Loei',
            'Nong Khai' => 'Nong Khai',
            'Maha Sarakham' => 'Maha Sarakham',
            'Roi Et' => 'Roi Et',
            'Kalasin' => 'Kalasin',
            'Sakon Nakhon' => 'Sakon Nakhon',
            'Nakhon Phanom' => 'Nakhon Phanom',
            'Mukdahan' => 'Mukdahan',
            'Chiang Mai' => 'Chiang Mai',
            'Lamphun' => 'Lamphun',
            'Lampang' => 'Lampang',
            'Uttaradit' => 'Uttaradit',
            'Phrae' => 'Phrae',
            'Nan' => 'Nan',
            'Phayao' => 'Phayao',
            'Chiang Rai' => 'Chiang Rai',
            'Mae Hong Son' => 'Mae Hong Son',
            'Nakhon Sawan' => 'Nakhon Sawan',
            'Uthai Thani' => 'Uthai Thani',
            'Kamphaeng Phet' => 'Kamphaeng Phet',
            'Tak' => 'Tak',
            'Sukhothai' => 'Sukhothai',
            'Phitsanulok' => 'Phitsanulok',
            'Phichit' => 'Phichit',
            'Phetchabun' => 'Phetchabun',
            'Ratchaburi' => 'Ratchaburi',
            'Kanchanaburi' => 'Kanchanaburi',
            'Suphan Buri' => 'Suphan Buri',
            'Nakhon Pathom' => 'Nakhon Pathom',
            'Samut Sakhon' => 'Samut Sakhon',
            'Samut Songkhram' => 'Samut Songkhram',
            'Phetchaburi' => 'Phetchaburi',
            'Prachuap Khiri Khan' => 'Prachuap Khiri Khan',
            'Nakhon Si Thammarat' => 'Nakhon Si Thammarat',
            'Krabi' => 'Krabi',
            'Phang-nga' => 'Phang-nga',
            'Phuket' => 'Phuket',
            'Surat Thani' => 'Surat Thani',
            'Ranong' => 'Ranong',
            'Chumphon' => 'Chumphon',
            'Songkhla' => 'Songkhla',
            'Satun' => 'Satun',
            'Trang' => 'Trang',
            'Phatthalung' => 'Phatthalung',
            'Pattani' => 'Pattani',
            'Yala' => 'Yala',
            'Narathiwat' => 'Narathiwat',
        );
        $province_th_id = array(
            '1' => 'กรุงเทพมหานคร',
            '2' => 'สมุทรปราการ',
            '3' => 'นนทบุรี',
            '4' => 'ปทุมธานี',
            '5' => 'พระนครศรีอยุธยา',
            '6' => 'อ่างทอง',
            '7' => 'ลพบุรี',
            '8' => 'สิงห์บุรี',
            '9' => 'ชัยนาท',
            '10' => 'สระบุรี',
            '11' => 'ชลบุรี',
            '12' => 'ระยอง',
            '13' => 'จันทบุรี',
            '14' => 'ตราด',
            '15' => 'ฉะเชิงเทรา',
            '16' => 'ปราจีนบุรี',
            '17' => 'นครนายก',
            '18' => 'สระแก้ว',
            '19' => 'นครราชสีมา',
            '20' => 'บุรีรัมย์',
            '21' => 'สุรินทร์',
            '22' => 'ศรีสะเกษ',
            '23' => 'อุบลราชธานี',
            '24' => 'ยโสธร',
            '25' => 'ชัยภูมิ',
            '26' => 'อำนาจเจริญ',
            '27' => 'บึงกาฬ',
            '28' => 'หนองบัวลำภู',
            '29' => 'ขอนแก่น',
            '30' => 'อุดรธานี',
            '31' => 'เลย',
            '32' => 'หนองคาย',
            '33' => 'มหาสารคาม',
            '34' => 'ร้อยเอ็ด',
            '35' => 'กาฬสินธุ์',
            '36' => 'สกลนคร',
            '37' => 'นครพนม',
            '38' => 'มุกดาหาร',
            '39' => 'เชียงใหม่',
            '40' => 'ลำพูน',
            '41' => 'ลำปาง',
            '42' => 'อุตรดิตถ์',
            '43' => 'แพร่',
            '44' => 'น่าน',
            '45' => 'พะเยา',
            '46' => 'เชียงราย',
            '47' => 'แม่ฮ่องสอน',
            '48' => 'นครสวรรค์',
            '49' => 'อุทัยธานี',
            '50' => 'กำแพงเพชร',
            '51' => 'ตาก',
            '52' => 'สุโขทัย',
            '53' => 'พิษณุโลก',
            '54' => 'พิจิตร',
            '55' => 'เพชรบูรณ์',
            '56' => 'ราชบุรี',
            '57' => 'กาญจนบุรี',
            '58' => 'สุพรรณบุรี',
            '59' => 'นครปฐม',
            '60' => 'สมุทรสาคร',
            '61' => 'สมุทรสงคราม',
            '62' => 'เพชรบุรี',
            '63' => 'ประจวบคีรีขันธ์',
            '64' => 'นครศรีธรรมราช',
            '65' => 'กระบี่',
            '66' => 'พังงา',
            '67' => 'ภูเก็ต',
            '68' => 'สุราษฎร์ธานี',
            '69' => 'ระนอง',
            '70' => 'ชุมพร',
            '71' => 'สงขลา',
            '72' => 'สตูล',
            '73' => 'ตรัง',
            '74' => 'พัทลุง',
            '75' => 'ปัตตานี',
            '76' => 'ยะลา',
            '77' => 'นราธิวาส'
        );

        $province_en_id = array(
            '1' => 'Bangkok',
            '2' => 'Samut Prakarn',
            '3' => 'Nonthaburi',
            '4' => 'Pathum Thani',
            '5' => 'Phra Nakhon Si Ayutthaya',
            '6' => 'Ang Thong',
            '7' => 'Lop Buri',
            '8' => 'Sing Buri',
            '9' => 'Chai Nat',
            '10' => 'Saraburi',
            '11' => 'Chon Buri',
            '12' => 'Rayong',
            '13' => 'Chanthaburi',
            '14' => 'Trat',
            '15' => 'Chachoengsao',
            '16' => 'Prachin Buri',
            '17' => 'Nakhon Nayok',
            '18' => 'Sa kaeo',
            '19' => 'Nakhon Ratchasima',
            '20' => 'Buri Ram',
            '21' => 'Surin',
            '22' => 'Si Sa Ket',
            '23' => 'Ubon Ratchathani',
            '24' => 'Yasothon',
            '25' => 'Chaiyaphum',
            '26' => 'Amnat Charoen',
            '27' => 'Bueng Kan',
            '28' => 'Nong Bua Lam Phu',
            '29' => 'Khon Kaen',
            '30' => 'Udon Thani',
            '31' => 'Loei',
            '32' => 'Nong Khai',
            '33' => 'Maha Sarakham',
            '34' => 'Roi Et',
            '35' => 'Kalasin',
            '36' => 'Sakon Nakhon',
            '37' => 'Nakhon Phanom',
            '38' => 'Mukdahan',
            '39' => 'Chiang Mai',
            '40' => 'Lamphun',
            '41' => 'Lampang',
            '42' => 'Uttaradit',
            '43' => 'Phrae',
            '44' => 'Nan',
            '45' => 'Phayao',
            '46' => 'Chiang Rai',
            '47' => 'Mae Hong Son',
            '48' => 'Nakhon Sawan',
            '49' => 'Uthai Thani',
            '50' => 'Kamphaeng Phet',
            '51' => 'Tak',
            '52' => 'Sukhothai',
            '53' => 'Phitsanulok',
            '54' => 'Phichit',
            '55' => 'Phetchabun',
            '56' => 'Ratchaburi',
            '57' => 'Kanchanaburi',
            '58' => 'Suphan Buri',
            '59' => 'Nakhon Pathom',
            '60' => 'Samut Sakhon',
            '61' => 'Samut Songkhram',
            '62' => 'Phetchaburi',
            '63' => 'Prachuap Khiri Khan',
            '64' => 'Nakhon Si Thammarat',
            '65' => 'Krabi',
            '66' => 'Phang-nga',
            '67' => 'Phuket',
            '68' => 'Surat Thani',
            '69' => 'Ranong',
            '70' => 'Chumphon',
            '71' => 'Songkhla',
            '72' => 'Satun',
            '73' => 'Trang',
            '74' => 'Phatthalung',
            '75' => 'Pattani',
            '76' => 'Yala',
            '77' => 'Narathiwat'
        );
        if (Yii::$app->language == 'th') {
            return $province_th_id;
        } else {
            return $province_en_id;
        }
    }

    public static function getDefultValue($value)
    {
        if (!empty($value)) {
            return $value;
        } else {
            return '-';
        }
    }

    public static function getProfile($uid)
    {
        $query = new \yii\db\Query();
        $query
            ->select([
                '*'
            ])
            ->from('profile')
            ->andFilterWhere(['=', 'profile.user_id', $uid]);
        $data = $query->one();

        return $data;
    }

    public static function getDateThai($strDate)
    {
        $strYear = date('Y', strtotime($strDate)) + 543;
        $strYear = substr($strYear, -2);
        $strMonth = date('n', strtotime($strDate));
        $strDay = date('j', strtotime($strDate));
        $strHour = date('H', strtotime($strDate));
        $strMinute = date('i', strtotime($strDate));
        $strSeconds = date('s', strtotime($strDate));
        $strMonthCut = array('', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.');
        $strMonthThai = $strMonthCut[$strMonth];
        return "$strDay $strMonthThai $strYear";
    }

    public static function getMonthDateThai($strDate)
    {
        $strMonth = date('n', strtotime($strDate));
        $strMonthCut = array('', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.');
        $strMonthThai = $strMonthCut[$strMonth];
        return $strMonthThai;
    }

    public static function getDate($strDate)
    {
        $strYear = date('Y', strtotime($strDate));
        $strMonth = date('n', strtotime($strDate));
        $strDay = date('j', strtotime($strDate));
        $strHour = date('H', strtotime($strDate));
        $strMinute = date('i', strtotime($strDate));
        $strSeconds = date('s', strtotime($strDate));
        $strMonthCut = array('', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม');
        $strMonthThai = $strMonthCut[$strMonth];
        return "$strDay $strMonthThai $strYear";
    }

    public static function getTime($strDate)
    {
        $strHour = date('H', strtotime($strDate));
        $strMinute = date('i', strtotime($strDate));
        return $strHour . ':' . $strMinute;
    }

    public static function getDateEng($strDate)
    {
        $strYear = date('Y', strtotime($strDate));
        $strYear = substr($strYear, -2);
        $strMonth = date('n', strtotime($strDate));
        $strDay = date('j', strtotime($strDate));
        $strHour = date('H', strtotime($strDate));
        $strMinute = date('i', strtotime($strDate));
        $strSeconds = date('s', strtotime($strDate));
        $strMonthCut = array('', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
        $strMonthThai = $strMonthCut[$strMonth];
        return "$strDay $strMonthThai $strYear";
    }

    public static function getMonthDateEng($strDate)
    {
        $strMonth = date('n', strtotime($strDate));
        $strMonthCut = array('', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
        $strMonthThai = $strMonthCut[$strMonth];
        return $strMonthThai;
    }

    public static function getDayDateThai($strDate)
    {
        $strDay = date('j', strtotime($strDate));
        return $strDay;
    }

    public static function truncateStr($str, $maxChars, $holder = '....')
    {
        // ตรวจสอบความยาวของประโยค
        if (mb_strlen($str) > $maxChars) {
            return trim(mb_substr($str, 0, $maxChars)) . $holder;
        } else {
            return $str;
        }
    }

    public static function renderProfileImage($uid)
    {
        // $profile = Profile::find()->where(['user_id' => $uid])->one();
        // if (!empty($profile->file_key)) {
        //     return  Upload::readfilePictureAddClass('profile', $profile->file_key, 'profile-icon');
        // }
        // return  '<img class="profile-icon" src="/images/admin_icon.png" />';
    }

    public static function profileImage($filename)
    {
        $frontendPath = \yii::getAlias('@frontend');
        $imagePath = str_replace('\\', '/', $frontendPath);
        $imageFilePath = $imagePath . '/web/' . 'files' . '/profile/' . $filename;
        // print '<pre>';
        // print_r($filename);
        // exit();
        if (!empty($filename)) {
            if (file_exists($imageFilePath)) {
                return '<img src="/files/profile/' . $filename . '" alt="" class="img-profile">';
            }
        }

        return '';
    }

    public static function contentImage($filename, $type)
    {
        $frontendPath = \yii::getAlias('@frontend');
        $imagePath = str_replace('\\', '/', $frontendPath);
        $imageFilePath = $imagePath . '/web/' . 'files' . '/content-' . $type . '/' . $filename;
        // print '<pre>';
        // print_r($filename);
        // exit();
        if (!empty($filename)) {
            if (file_exists($imageFilePath)) {
                return '/files/content-' . $type . '/' . $filename;
            }
        }

        switch ($type) {
            case 'plant':
                return '/images/BIOG_default_plant.png';
                break;

            case 'animal':
                return '/images/BIOG_default_animal.png';
                break;

            case 'fungi':
                return '/images/BIOG_default_fungi.png';
                break;

            case 'expert':
                return '/images/BIOG_default_expert.png';
                break;

            case 'ecotourism':
                return '/images/BIOG_default_ecotourism.png';
                break;

            case 'product':
                return '/images/BIOG_default_product.png';
                break;

            default:
                return '/images/BIOG_default_plant.png';
                break;
        }
    }

    public static function blogImage($filename, $type)
    {
        $frontendPath = \yii::getAlias('@frontend');
        $imagePath = str_replace('\\', '/', $frontendPath);
        $imageFilePath = $imagePath . '/web/' . 'files' . '/' . $type . '/' . $filename;
        // print '<pre>';
        // print_r($filename);
        // exit();
        if (!empty($filename)) {
            if (file_exists($imageFilePath)) {
                return '/files/' . $type . '/' . $filename;
            }
        }

        return '/images/BIOG_default_blog.png';
    }

    public static function profileImageComent($uid)
    {
        $picture = self::getProfileImage($uid);

        $frontendPath = \yii::getAlias('@frontend');
        $imagePath = str_replace('\\', '/', $frontendPath);
        $imageFilePath = $imagePath . '/web/' . 'files' . '/profile/' . $picture;
        // print '<pre>';
        // print_r($filename);
        // exit();
        if (!empty($picture)) {
            if (file_exists($imageFilePath)) {
                return '/files/profile/' . $picture;
            }
        }

        return '/images/default-user.png';
    }

    public function getTagAllFeedLearning($id)
    {
        // $result = Taxonomy::find()
        //     ->select(["taxonomy.id AS tagId", "taxonomy.name AS tagName"])
        //     ->innerjoin("content_taxonomy", "taxonomy.id=content_taxonomy.taxonomy_id")
        //     ->leftjoin("learningcenter_feed", "learningcenter_feed.content_id=content_taxonomy.content_id")
        //     ->andWhere('learningcenter_feed.learningcenter_id =' . $id)
        //     ->distinct()->asArray()->all();
        // $tags = "";
        // if (!empty($result)) {
        //     foreach ($result as $value) {
        //         $color = FrontendHelper::random_color();
        //         $style = 'color: #' . $color . ';border: 1px solid #' . $color . ';';
        //         $tags = $tags . '<a href="/learning-center/feed/' . $id . '?tag=' . $value['tagName'] . '" class="btn btn-style-site w-fit btn-tag pull-left mr-2" style="' . $style . '">' . $value['tagName'] . '</a>';
        //     }
        // }
        // return $tags;
    }

    public static function random_color_part()
    {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    public static function random_color()
    {
        $color = FrontendHelper::random_color_part() . FrontendHelper::random_color_part() . FrontendHelper::random_color_part();
        return $color;
    }

    public static function menuActive($site, $action)
    {
        if ($site == 'site') {
            if ($action == 'site/index') {
                return true;
            } else {
                return false;
            }
        }

        if ($site == 'story') {
            if ($action == 'site/story') {
                return true;
            } else {
                return false;
            }
        }

        if ($site == 'learning-center') {
            $str = strrpos($action, 'learning-center/');
            if ($str === false) {
                return false;
            } else {
                if ($action != 'learning-center/update') {
                    return true;
                } else {
                    return false;
                }
            }
        }
        if ($site == 'region') {
            // print_r(strrpos($action, "learning-center/central"));
            // exit();
            if ($action == $_SERVER['REQUEST_URI']) {
                return true;
            } else {
                return false;
            }
        }
        if ($site == 'news') {
            if ($action == 'news/index' || $action == 'news/view') {
                return true;
            } else {
                return false;
            }
        }
        if ($site == 'knowledge') {
            if ($action == 'knowledge/index' || $action == 'knowledge/view') {
                return true;
            } else {
                return false;
            }
        }
        if ($site == 'search') {
            if ($action == 'search/index' || $action == 'search/view') {
                return true;
            } else {
                return false;
            }
        }
    }  // function

    public static function menuActiveMain($site, $action)
    {
        if ($site == 'site') {
            if ($action == 'site/index') {
                return 'active';
            } else {
                return '';
            }
        }
        if ($site == 'news') {
            if ($action == 'news/index' || $action == 'news/view') {
                return 'active';
            } else {
                return '';
            }
        }
        if ($site == 'knowledge') {
            if ($action == 'knowledge/index' || $action == 'knowledge/view') {
                return 'active';
            } else {
                return '';
            }
        }
        if ($site == 'blog') {
            if ($action == 'blog/index' || $action == 'blog/view') {
                return 'active';
            } else {
                return '';
            }
        }
        if ($site == 'contact') {
            if ($action == 'site/contact') {
                return 'active';
            } else {
                return '';
            }
        }
    }

    public static function getPageView($id, $model)
    {
        if ($model == 'knowledge') {
            $model = KnowledgeStatistics::find(['pageview'])->where(['knowledge_root_id' => $id])->one();
            if (!empty($model)) {
                return number_format($model['pageview']);
            } else {
                return 0;
            }
        }
        if ($model == 'blog') {
            $model = BlogStatistics::find(['pageview'])->where(['blog_root_id' => $id])->one();
            if (!empty($model)) {
                return number_format($model['pageview']);
            } else {
                return 0;
            }
        }

        if ($model == 'news') {
            $model = NewsStatistics::find(['pageview'])->where(['news_root_id' => $id])->one();
            if (!empty($model)) {
                return number_format($model['pageview']);
            } else {
                return 0;
            }
        }

        if ($model == 'content') {
            $model = ContentStatistics::find(['pageview'])->where(['content_root_id' => $id])->one();
            if (!empty($model)) {
                return number_format($model['pageview']);
            } else {
                return 0;
            }
        }
    }

    public static function getStatisticsPageview()
    {
        $sum_knowledge = KnowledgeStatistics::find()->sum('pageview');
        $sum_blog = BlogStatistics::find()->sum('pageview');
        $sum_news = NewsStatistics::find()->sum('pageview');
        $sum_content = ContentStatistics::find()->sum('pageview');
        return number_format($sum_knowledge + $sum_blog + $sum_news + $sum_content);
    }

    public static function getStatisticsKnowleage()
    {
        $model = Knowledge::find()->select('id')->where(['active' => 1])->count();
        return number_format($model);
    }

    public static function getStatisticsBlog()
    {
        $model = Blog::find()->select('id')->where(['active' => 1])->count();
        return number_format($model);
    }

    public static function getStatisticsMemeber()
    {
        /*$model = Users::find()->select('user.id')->innerJoin('user_role', 'user.id = user_role.user_id')->where([
            'or',
            ['user_role.role_id' => 4],
            ['user_role.role_id' => 5],
        ])->count(); */

        $countUser = Yii::$app->db->createCommand('SELECT COUNT(user.id) FROM `user` INNER JOIN `user_role` ON user.id = user_role.user_id WHERE (`user_role`.`role_id`=4 OR `user_role`.`role_id`=5) AND ( `user`.`blocked_at` is null or `user`.`blocked_at` = 0)')->queryScalar();

        return number_format($countUser);
    }

    public static function getContentPicture($type, $file_name)
    {
        $picture = '';
        if ($type == 1) {
            $picture = '<img src="/files/content-plant/' . $file_name . '" class="w-100">';
        } else if ($type == 2) {
            $picture = '<img src="/files/content-animal/' . $file_name . '" class="w-100">';
        } else if ($type == 3) {
            $picture = '<img src="/files/content-fungi/' . $file_name . '" class="w-100">';
        } else if ($type == 4) {
            $picture = '<img src="/files/content-expert/' . $file_name . '" class="w-100">';
        } else if ($type == 5) {
            $picture = '<img src="/files/content-ecotourism/' . $file_name . '" class="w-100">';
        } else if ($type == 6) {
            $picture = '<img src="/files/content-product/' . $file_name . '" class="w-100">';
        }
        return $picture;
    }

    public static function getProfileImage($user_id)
    {
        $profileImg = Profile::find('picture')->where(['user_id' => $user_id])->asArray()->one();
        return $profileImg['picture'];
    }

    public static function getProfileName($user_id)
    {
        $profile = Profile::find('firstname', 'lastname')->where(['user_id' => $user_id])->asArray()->one();
        if (!empty($profile)) {
            return $profile['firstname'] . ' ' . $profile['lastname'];
        }
        return '';
    }

    public static function showLike($id, $site)
    {
        $user_id = Yii::$app->user->id;
        if ($site == 'blog') {
            $userLikeBlog = UserLikeBlog::find()->where(['user_id' => $user_id, 'blog_id' => $id])->asArray()->one();
            if (!empty($userLikeBlog)) {
                return 'active';
            }
        } else if ($site == 'content') {
            $userLikeContent = UserLikeContent::find()->where(['user_id' => $user_id, 'content_id' => $id])->asArray()->one();
            if (!empty($userLikeContent)) {
                return 'active';
            }
        }
    }

    public static function getNameProvince($id)
    {
        if (!empty($id)) {
            $data = Province::find()->where(['id' => $id])->one();
            if (!empty($data)) {
                return $data['name_th'];
            }
        }
        return '-';
    }

    public static function getNameDistrict($id)
    {
        if (!empty($id)) {
            $data = District::find()->where(['id' => $id])->one();
            if (!empty($data)) {
                return $data['name_th'];
            }
        }
        return '-';
    }

    public static function getNameSubDistrict($id)
    {
        if (!empty($id)) {
            $data = Subdistrict::find()->where(['id' => $id])->one();
            if (!empty($data)) {
                return $data['name_th'];
            }
        }
        return '-';
    }

    public static function getNameZipcode($id)
    {
        $model = Zipcode::findOne($id);
        if (!empty($model)) {
            return $model['zipcode'];
        } else {
            return '-';
        }
    }

    public static function getAddress($province_id, $district_id, $subdistrict_id, $zipcode_id)
    {
        $address = '';
        if (!empty($subdistrict_id) || !empty($district_id) || !empty($province_id) || !empty($zipcode_id)) {
            if (!empty($subdistrict_id)) {
                $address .= 'ต.' . FrontendHelper::getNameSubDistrict($subdistrict_id);
            }
            if (!empty($district_id)) {
                $address .= ' อ.' . FrontendHelper::getNameDistrict($district_id);
            }
            if (!empty($province_id)) {
                $address .= ' จ.' . FrontendHelper::getNameProvince($province_id);
            }
            if (!empty($zipcode_id)) {
                $address .= ' ' . FrontendHelper::getNameZipcode($zipcode_id);
            }
            return $address;
        }
        return '';
    }

    public static function getExpertCategoryName($id)
    {
        $expertCategory = ExpertCategory::find()->where(['id' => $id])->one();
        if (!empty($expertCategory)) {
            return $expertCategory['name'];
        } else {
            return '-';
        }
    }

    public static function getTaxonomyName($id)
    {
        $text = '';
        $count = 1;
        $nameTaxonomy = TaxonomyHelper::getTaxonomyListByContentName($id);
        if (!empty($nameTaxonomy)) {
            foreach ($nameTaxonomy as $key => $value) {
                if (count($nameTaxonomy) > 0) {
                    if (count($nameTaxonomy) == 1) {
                        $text = '<a href="/search?taxonomy=' . $value . '" >' . $value . '</a>';
                    } else {
                        if ($count == count($nameTaxonomy)) {
                            $text .= '<a href="/search?taxonomy=' . $value . '" >' . $value . '</a>';
                        } else {
                            $text .= '<a href="/search?taxonomy=' . $value . '" >' . $value . '</a>' . ', ';
                        }
                    }
                } else {
                    $text = '';
                }
                $count++;
            }
        } else {
            $text = '';
        }
        return $text;
    }

    public static function getCountContent($content)
    {
        $types = [
            'plants' => 1,
            'animal' => 2,
            'fungi' => 3,
            'expert' => 4,
            'ecotourism' => 5,
            'product' => 6,
        ];

        $count = 0;
        if (isset($types[$content])) {
            $typeId = $types[$content];
            $count = Yii::$app->cache->getOrSet("content_${content}_count", function () use ($typeId) {
                return (new \yii\db\Query())
                    ->select(['content.id'])
                    ->from('content')
                    ->where([
                        'type_id' => $typeId,
                        'status' => 'approved',
                        'active' => '1',
                        'is_hidden' => false,
                    ])
                    ->count();
            }, 60 * 5);
        }

        return number_format($count);
    }

    public static function getSchoolName($user_id)
    {
        if (!empty($user_id)) {
            $userSchool = UserSchool::find()
                ->select(['school.name AS school_name', 'user_school.user_id AS user_id', 'user_school.school_id AS school_id'])
                ->innerjoin('school', 'school.id=user_school.school_id')
                ->andWhere('user_school.user_id =' . $user_id)
                ->asArray()
                ->one();
            return $userSchool['school_name'];

            // $userSchool = UserSchool::find()->where(['user_id'])->asArray()->one();
        }
        return '-';
    }

    public static function getMetaImage($site, $path)
    {
        $frontendPath = \yii::getAlias('@frontend');
        $imagePath = str_replace('\\', '/', $frontendPath);
        $imageFilePath = $imagePath . '/web/' . 'files' . '/' . $site . '/' . $path;

        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        // $actual_link = "https://biogang.devfunction.com";
        if (!file_exists($imageFilePath)) {
            if ($site == 'content-plant') {
                $path = '/images/BIOG_default_plant.png';
            } else if ($site == 'content-animal') {
                $path = '/images/BIOG_default_animal.png';
            } else if ($site == 'content-ecotourism') {
                $path = '/images/BIOG_default_ecotourism.png';
            } else if ($site == 'content-expert') {
                $path = '/images/BIOG_default_expert.png';
            } else if ($site == 'content-fungi') {
                $path = '/images/BIOG_default_fungi.png';
            } else if ($site == 'content-product') {
                $path = '/images/BIOG_default_product.png';
            } else if ($site == 'knowledge') {
                $path = '/images/BIOG_default_knowledge.png';
            } else if ($site == 'news') {
                $path = '/images/BIOG_default_news.png';
            } else if ($site == 'blog') {
                $path = '/images/BIOG_default_fungi.png';
            } else {
                $path = '/images/default.png';
            }

            \Yii::$app->view->registerMetaTag([
                'property' => 'og:image',
                'content' => $actual_link . $path,
            ]);
        } else {
            \Yii::$app->view->registerMetaTag([
                'property' => 'og:image',
                'content' => $actual_link . '/files/' . $site . '/' . $path,
            ]);
        }

        \Yii::$app->view->registerMetaTag([
            'property' => 'og:type',
            'content' => 'website',
        ]);

        \Yii::$app->view->registerMetaTag([
            'property' => 'fb:app_id',
            'content' => '675823119701869',
        ]);
    }

    public static function getMetaTitle($title)
    {
        \Yii::$app->view->registerMetaTag([
            'property' => 'og:title',
            'content' => $title,
        ]);
    }

    public static function getDescription($dascription)
    {
        \Yii::$app->view->registerMetaTag([
            'property' => 'og:description',
            'content' => $dascription,
        ]);
    }

    public static function getUrl($url)
    {
        \Yii::$app->view->registerMetaTag([
            'property' => 'og:url',
            'content' => $url,
        ]);
    }

    public static function getContentTypeById($id)
    {
        switch ($id) {
            case '1':
                return 'plant';
                break;
            case '2':
                return 'animals';
                break;
            case '3':
                return 'fungi';
                break;
            case '4':
                return 'expert';
                break;
            case '5':
                return 'ecotourism';
                break;
            case '6':
                return 'product';
                break;
            default:
                return 'plant';
                break;
        }
    }

    public static function getFolderImageContent($content_id)
    {
        switch ($content_id) {
            case '1':
                return 'content-plant';
                break;
            case '2':
                return 'content-animal';
                break;
            case '3':
                return 'content-fungi';
                break;
            case '4':
                return 'content-expert';
                break;
            case '5':
                return 'content-ecotourism';
                break;
            case '6':
                return 'content-product';
                break;
            default:
                return 'content-plant';
                break;
        }
    }

    public static function checkCanViewContentForTeacher($studentId)
    {
        $student = StudentTeacher::find()->select(['student_id'])->where(
            [
                'teacher_id' => Yii::$app->user->identity->id,
                'student_id' => $studentId,
                'active' => 1
            ]
        )->asArray()->all();
        if (!empty($student)) {
            return true;
        }

        return false;
    }

    public static function getYoutubeEmbedUrl($url)
    {
        $shortUrlRegex = '/youtu.be\/([a-zA-Z0-9_-]+)\??/i';
        $longUrlRegex = '/youtube.com\/((?:embed)|(?:watch))((?:\?v\=)|(?:\/))([a-zA-Z0-9_-]+)/i';

        if (preg_match($longUrlRegex, $url, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
        }

        if (preg_match($shortUrlRegex, $url, $matches)) {
            $youtube_id = $matches[count($matches) - 1];
        }
        return 'https://www.youtube.com/embed/' . $youtube_id;
    }

    public static function getProfileUrl($filename)
    {
        $frontendPath = \yii::getAlias('@frontend');
        $imagePath = str_replace('\\', '/', $frontendPath);
        $imageFilePath = $imagePath . '/web/' . 'files' . '/profile/' . $filename;
        if (!empty($filename)) {
            if (file_exists($imageFilePath)) {
                return '/files/profile/' . $filename;
            } else {
                return '/images/default-user.png';
            }
        }

        return '/images/default-user.png';
    }

    public static function saveUserLog($table, $uid, $contentId, $actionName, $description)
    {
        $model = new UserLog();
        $model->type = $table;
        $model->user_id = $uid;
        $model->content_id = $contentId;
        $model->action_name = $actionName;
        $model->description = $description;
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();
    }

    public static function getSourceInformation($text)
    {
        if (strpos($text, 'http') !== false) {
            $textFetch = explode('http', $text);

            $link = '';
            if (!empty($textFetch)) {
                foreach ($textFetch as $value) {
                    $valueFetch = explode(' ', $value);
                    if (!empty($valueFetch)) {
                        foreach ($valueFetch as $valueSpace) {
                            if (!empty($valueSpace)) {
                                if (strpos($valueSpace, '://') !== false) {
                                    $url = 'http' . $valueSpace;
                                    $link = $link . '<a href="' . $url . '" target="_blank" >' . urldecode($url) . '</a>&nbsp;';
                                } else {
                                    $link = $link . $valueSpace . '&nbsp;';
                                }
                            }
                        }
                    } else {
                        if (!empty($value)) {
                            if (strpos($value, '://') !== false) {
                                $url = 'http' . $value;
                                $link = $link . '<a href="' . $url . '" target="_blank" >' . urldecode($url) . '</a>&nbsp;';
                            } else {
                                $link = $link . $value . '&nbsp;';
                            }
                        }
                    }
                }
            }

            return $link;
        } else {
            return $text;
        }
    }
}
