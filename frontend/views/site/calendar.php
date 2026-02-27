
<?php

/* @var $this yii\web\View */

use yii\widgets\Breadcrumbs;
use frontend\components\FrontendHelper;
use common\components\FileLibrary;
use frontend\models\News;

use yii\helpers\Html;

$this->title = 'ปฎิทินกิจกรรม';
$this->params['breadcrumbs'][] = $this->title;
$this->registerCss("nav {background-image: url('/images/banner/News_Banner.png'); }");

use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'News'])->one();

$linkMain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']; //
$_SESSION['currentUrl'] = $linkMain . $_SERVER['REQUEST_URI'];

?>
<?php $this->beginBlock('banner') ?>
<div class="section-banner">
    <?php if(!empty($banner->picture_path)): ?>
        <img src="/files/banner/<?php echo $banner->picture_path; ?>" class="banner ">
    <?php else: ?>
        <img src="/images/banner/News_Banner.png" class="banner ">
    <?php endif; ?>
</div>
<?php $this->endBlock() ?>
<div class="main-breadcrumb" aria-label="breadcrumb">
    <div class="container">
        <ol class="breadcrumb pl-0">
            <li class="breadcrumb-item home"><a href="#">Home</a></li>
            <li class="breadcrumb-item"><a href="#"><?= $this->title ?></a></li>
        </ol>
    </div>
</div>
<div class="site-calendar container" >
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="embed-responsive embed-responsive-16by9 mt-4 mb-5" style="height: 600px;">
        <iframe
            title="Google Calendar"
            src="<?= isset(Yii::$app->params['google_calendar_embed_url']) ? Yii::$app->params['google_calendar_embed_url'] : '' ?>" 
            style="border: 0"
            width="100%"
            height="600"
        >
        </iframe>
    </div>
</div>
