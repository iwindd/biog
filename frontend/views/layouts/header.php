<?php

use frontend\components\FrontendHelper;
use yii\widgets\Block;
use frontend\models\Content;
use frontend\models\Users;
// $countUser  = Yii::$app->db->createCommand('SELECT COUNT(user.id) FROM `user` INNER JOIN `user_role` ON user.id = user_role.user_id WHERE (`user_role`.`role_id`=4) OR (`user_role`.`role_id`=5)')->queryScalar();
$countCount  = Yii::$app->db->createCommand('SELECT COUNT(content.id) as count_content FROM `content` WHERE (`active`=1) AND (`status`="approved")')->queryScalar();
// print "<pre>";
// print_r($countCount);
// print "</pre>";
// exit();
?>
<header class="header header justify-content-center d-flex position-relative">
    <?php echo $this->blocks['banner']; ?>

    <div class="container banner-content ">
        <?php if ($this->context->route == "site/index") { ?>
            <div class="analysis">
                <div class="row justify-content-center">
                    <div class="col-5 mobile-analysis">
                        <p class="number"><?php echo FrontendHelper::getStatisticsMemeber(); ?></p>
                        <p class="banner-text">บัญชีสมาชิก</p>
                    </div>
                    <div class="col-5 mobile-analysis">
                        <p class="number"><?php echo number_format($countCount); ?></p>
                        <p class="banner-text">ข้อมูลในระบบ</p>
                    </div>
                </div>

            </div>
        <?php } ?>
        <div class="search-box">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <form action="/search" method="GET" id="search-form">
                        <div class="w-100">
                            <div class="input-group">
                                <input class="form-control input-lg search-input mt-1 kanit" type="text" placeholder="ใส่คำค้นหาได้ที่นี่" name="keyword">
                                <div class="input-group-append">
                                    <button type="submit" class="btn-search input-group-text amber lighten-3 mt-1 pr-3 pl-3"><i class="fas fa-search text-grey" aria-hidden="true"></i> <span>ค้นหา</span></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="main-menu menu table-responsive">
            <div class="header-menu">
                <a href="/" class="menu-item">
                    <div class="bg-icon <?php echo FrontendHelper::menuActiveMain("site", $this->context->route); ?>">
                        <img src="/images/icon/Home.svg" class="injectable">
                    </div>
                    <p>Home</p>
                </a>
                <a href="/news" class="menu-item">
                    <div class="bg-icon <?php echo FrontendHelper::menuActiveMain("news", $this->context->route); ?>">
                        <img src="/images/icon/News.svg" class="injectable">
                    </div>
                    <p>News</p>
                </a>
                <a href="/knowledge" class="menu-item">
                    <div class="bg-icon <?php echo FrontendHelper::menuActiveMain("knowledge", $this->context->route); ?>">
                        <img src="/images/icon/Knowledge.svg" class="injectable">
                    </div>
                    <p>Knowledge</p>
                </a>
                <a href="/blog" class="menu-item">
                    <div class="bg-icon <?php echo FrontendHelper::menuActiveMain("blog", $this->context->route); ?>">
                        <img src="/images/icon/Blog.svg" class="injectable">
                    </div>
                    <p>Blog</p>
                </a>
                <a href="/contact" class="menu-item">
                    <div class="bg-icon <?php echo FrontendHelper::menuActiveMain("contact", $this->context->route); ?>">
                        <img src="/images/icon/Contact.svg" class="injectable">
                    </div>
                    <p>Contact</p>
                </a>
                <a href="/site/calendar" class="menu-item">
                    <div class="bg-icon <?php echo FrontendHelper::menuActiveMain("calendar", $this->context->route); ?>">
                        <img src="/images/icon/calendar.svg" class="injectable">
                    </div>
                    <p>ปฎิทินกิจกรรม</p>
                </a>
            </div>
        </div>
    </div>
</header>