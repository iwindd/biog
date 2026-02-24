<?php

// รวมฟังก์ชั่น php ที่เป็นประโยชน์ เรียกใช้บ่อยๆ
// ถ้าจะใช้โปรดระวังการเปลี่ยนแปลง
// เรียกใช้งาน เช่น if(_::issetEmpty($value))
// muhammad

namespace common\components;

use Yii;
use DateTime;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\Response;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

class _
{
    /** =============== จัดการกับค่าต่าง ๆ =============== */

    public static function getValue($value)
    {
        if (isset($value) && !empty($value)) {
            return $value;
        }

        return null;
    }

    public static function issetNotEmpty($value)
    {
        if (isset($value) && !empty($value)) {
            return true;
        }

        return false;
    }

    public static function issetEmpty($value)
    {
        if (isset($value) && empty($value)) {
            return true;
        }

        return false;
    }

    public static function isNotSetEmpty($value)
    {
        if (!isset($value) && empty($value)) {
            return true;
        }

        return false;
    }

    public static function isNull($value)
    {
        if (is_null($value)) {
            return true;
        }

        return false;
    }

    public static function isNullEmpty($value)
    {
        if (is_null($value) && empty($value)) {
            return true;
        }

        return false;
    }

    // public static function 








    /** =============== debug ค่าต่าง ๆ =============== */

    public static function print($value)
    {
        highlight_string("<?php\n" . var_export($value, true));
        die();
    }











    /** =============== จัดการกับ Array =============== */

    public static function toObject(array $array)
    {
        $result = json_decode(json_encode($array), false);

        return is_object($result) ? $result : null;
    }

    public static function isArray($array)
    {
        if (is_array($array)) {
            return true;
        }

        return false;
    }

    public static function hasKey(array $array, $key)
    {
        if (array_key_exists($key, $array)) {
            return true;
        }

        foreach ($array as $element) {
            if (is_array($element)) {
                if (_::hasKey($element, $key)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function arrayGetValue(array $array, $key)
    {
        // สามารถดึงแบบ multidimensional ได้
        // Exaxmple $key = Content.taxonomy_list
        if (is_string($key) && _::issetNotEmpty($key)) {

            $keys = explode('.', $key);

            while (sizeof($keys) >= 1) {

                $k = array_shift($keys);

                if (!isset($array[$k])) {
                    return null;
                }

                if (sizeof($keys) === 0) {
                    return $array[$k];
                }

                $array = &$array[$k];
            }
        }
        return null;
    }







    /** =============== จัดการกับ Object =============== */

    public static function toArray($object)
    {
        if (is_object($object)) {
            return json_decode(json_encode($object), true);
        }

        return null;
    }

    public static function isObject($array)
    {
        if (is_object($array)) {
            return true;
        }

        return false;
    }

    public static function hasProperty($object, $key)
    {
        if (property_exists($object, $key)) {
            return true;
        }

        return false;
    }








    /** =============== จัดการกับ JSON =============== */

    public static function isJson($value)
    {
        json_decode($value);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function toJsonString($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }









    /** =============== จัดการกับเวลา =============== */

    public static function setThailandTimeZone()
    {
        date_default_timezone_set("Asia/Bangkok");
    }

    public static function getDateTimeYmdHis()
    {
        $dateTime = new DateTime();
        return $dateTime->format('Y-m-d H:i:s');
    }

    public static function formatDateYmdtodmY($date)
    {
        if (_::issetNotEmpty($date)) {
            $date = new DateTime($date);
            return $date->format('Y-m-d');
        }

        return null;
    }

    public static function formatDatedmYtoYmd($date)
    {
        if (_::issetNotEmpty($date)) {
            $date = new DateTime($date);
            return $date->format('d-m-Y');
        }

        return null;
    }









    /** =============== YII DEBUG Function =============== */

    // ไม่จำเป็นต้อง comment หรือ ลบทิ้ง 
    // ถ้าขึ้น production ฟังก์ชั่น debug จะปิดการทำงานอัตโนมัติ

    public static function debug($data)
    {
        Yii::debug($data);
    }

    public static function debugAttributes($model)
    {
        if ($model)
            Yii::debug(['ตาราง' => $model->tableName(), 'ค่าในฟิล' => $model->attributes]);
    }

    public static function debugValidateModel($model)
    {
        if ($model) {
            Yii::debug(['ตาราง' => $model->tableName(), 'ค่าในฟิล' => $model->attributes]);
            $model->validate();
            Yii::debug(['ตาราง' => $model->tableName(), 'ฟิลที่ error' => $model->getErrors()]);
        }
    }








    /** =============== YII Model Function =============== */
    // status code error
    // 1001 = model error
    const STATUS_CODE_MODEL_ERROR = 1001;

    public static function setupModel($model, array $data)
    {
        if (_::issetNotEmpty($data)) {
            foreach ($data as $key => $value) {
                $model->$key = _::getValue($value);
            }
        }

        return $model;
    }

    public static function saveModel($model)
    {
        if ($model->save() && $model->validate()) {
            return true;
        } else {
            _::debugValidateModel($model);
            _::throwErrorModel($model->getFirstErrors());
        }
    }

    public static function throwNotFoundIfNotFoundModel($model)
    {
        if (_::issetNotEmpty($model)) {
            return $model;
        } else {
            throw new NotFoundHttpException();
        }
    }

    public static function throwErrorModel(array $error)
    {
        throw new \Exception(json_encode($error), _::STATUS_CODE_MODEL_ERROR);
    }

    private static function renderHtmlList($textList)
    {
        $ulClass = 'list-group ml-3';
        $liClass = '';

        $htmlErrorList = '<ul class="' . $ulClass . '">';
        foreach ($textList as $text) {
            if (_::isArray($text)) {
                $htmlErrorList .= _::renderHtmlList($text);
            } else {
                $htmlErrorList .= '<li class="' . $liClass . '">' . $text . '</li>';
            }
        }
        $htmlErrorList .= "</ul>";

        return $htmlErrorList;
    }

    public static function getErrorListMessageModel($errors)
    {
        if ($errors->getCode() == _::STATUS_CODE_MODEL_ERROR) {

            $errors = json_decode(($errors->getMessage()), true);

            if (_::issetNotEmpty($errors)) {

                return _::renderHtmlList($errors);
            }
        }

        return null;
    }







    /** =============== YII Utility Function =============== */

    public static function isUserGoHome()
    {
        if (!Yii::$app->user->isGuest) {
            return Yii::$app->getResponse()->redirect(Url::home());
        }
    }

    public static function isGuestGoHome()
    {
        if (Yii::$app->user->isGuest) {
            return Yii::$app->getResponse()->redirect(Url::home());
        }
    }

    public static function currentUserId()
    {
        return Yii::$app->user->getId();
    }

    public static function isThaiLanguage()
    {
        return Yii::$app->language == 'th' ? true : false;
    }

    public static function currentLanguage()
    {
        return Yii::$app->language;
    }

    public static function post($key = null)
    {
        if (_::issetNotEmpty($key)) {
            return Yii::$app->request->post($key);
        }

        return Yii::$app->request->post();
    }

    public static function get($key = null)
    {
        if (_::issetNotEmpty($key)) {
            return Yii::$app->request->get($key);
        }

        return Yii::$app->request->get();
    }

    public static function setResponseAsJson()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
    }

    public static function setFlash($key, $options)
    {
        return Yii::$app->session->setFlash($key, $options);
    }

    public static function getFlash($key, $value)
    {
        return ArrayHelper::getValue(Yii::$app->session->getFlash($key), $value);
    }

    public static function hasFlash($key)
    {
        return Yii::$app->session->hasFlash($key);
    }

    public static function beginTransaction()
    {
        return Yii::$app->db->beginTransaction();
    }
}
