<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\components;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;
use yii\di\Instance;
use yii\web\ForbiddenHttpException;
use yii\web\User;


class AccessControl extends  \yii\filters\AccessControl
{
    
    protected function denyAccess($user)
    {
      
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        
    }
}
