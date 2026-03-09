<?php
use backend\assets\AppAsset;
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */

dmstr\web\AdminLteAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="/admin/images/logo-favicon.png" type="image/vnd.microsoft.icon">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
    	.login-logo{
    		margin: 0;
    	}
    	.login-logo img{
    		margin: 0 auto;
    		width: 60%;
    	}
    	.panel-body{
    		font-size: 16px;
		    background: #22626A;
		    color: #fff;
    	}
    	.btn.btn-block{
    		background: #fbac18;
		    border-color: #fbac18;
		    border-radius: 0px;
		    color: #fff;
		    font-size: 16px;
    	}
    	.panel.panel-default{
    		margin-top: 10%;
    	}
    	.panel-default > .panel-heading{
    		background-color: #ffff;
		}
		.form-group.field-login-form-password.required label{
 			width: 80px;
     		overflow: hidden;
    		white-space: nowrap;
		}
		.f-right{
			float: right;
		}
    </style>
</head>
<body class="login-page">

<?php $this->beginBody() ?>

    <?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
