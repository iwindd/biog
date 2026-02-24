<?php
namespace common\components;
//use common\models\User;
use dektrium\user\models\User;
use yii;


class AccessRule extends \yii\filters\AccessRule{



    protected function matchRole($user){
        

        // print_r(Yii::$app->user->identity->role);

        if(empty($this->roles)){

            return true;

        }

        foreach ($this->roles as $role){



            if($role == '?' && $user->getIsGuest()){

                    return true;

            }

            elseif(!$user->getIsGuest() && $role == $user->identity->role){ 

                return true;

            }

        }

        return false;

    }

}