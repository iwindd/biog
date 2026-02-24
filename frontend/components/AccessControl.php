<?php

namespace frontend\components;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\di\Instance;
use yii\web\ForbiddenHttpException;
use yii\helpers\ArrayHelper;
use frontend\models\UserRole;

class AccessControl 
{
    public static function BackendAccess($action)
    {

        if(!empty(Yii::$app->user->identity->id)){
            
            $roles = UserRole::find()->select(['role_id'])->where(['user_id' => Yii::$app->user->identity->id])->asArray()->all();
            if (!empty($roles)) {
                $dataRole = array();
                foreach ($roles as $key => $value) {
                    $dataRole[] = $value['role_id'];
                }

                $permission = (new \yii\db\Query())
                    ->select(['role_permission.*'])
                    ->from('role_permission')
                    ->leftJoin('permission', 'permission.id=role_permission.permission_id')
                    ->where(['in', 'role_id', $dataRole])
                    ->andWhere(['permission.permission_key' => $action])
                    ->all();

                
                if(!empty($permission)){
                    return true;
                }else{
                    return false;
                    //throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
                }

            }else{

                return true;
                //throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
            }
        }else{

            return true;
            //throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }

    }
}