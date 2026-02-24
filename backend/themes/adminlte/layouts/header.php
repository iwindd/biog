<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
?>

<header class="main-header">

    <?= Html::a('<span class="logo-mini"></span><span class="logo-lg"> <img src="/images/logo.jpg" class="user-image" alt="User Image"/> </span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Menu</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">

                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="/images/admin_icon.png" class="user-image" alt="User Image"/>
                        <span class="hidden-xs">
                            <?php echo Yii::$app->user->identity->profile->firstname; ?> 
                            <?php echo Yii::$app->user->identity->profile->lastname; ?>                                
                        </span> 
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="/images/admin_icon.png" class="img-circle"
                                 alt="User Image"/>

                            <p>
                                <?php //echo Yii::$app->user->identity->profile->first_name; ?> 
                                <?php //echo Yii::$app->user->identity->profile->last_name; ?>
                            </p>
                        </li>
                       
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="/admin/users/<?php echo  Yii::$app->user->identity->id  ?>" class="btn btn-default btn-flat">
                                    <i class="fa fa-user" aria-hidden="true"></i> Profile
                                </a>
                            </div>
                            <div class="pull-right">
                                <?= Html::a(
                                    Html::tag('i', '', ['class' => 'fa fa-sign-out']).' Logout',
                                    ['/site/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>

            </ul>
        </div>
    </nav>
</header>
