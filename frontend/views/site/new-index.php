<?php

/* @var $this yii\web\View */

$this->title = 'BIOGANG';
$this->registerCssFile('@web/jqcloud/jqcloud.css', ['depends' => 'frontend\assets\AppAsset']);
$this->registerJsFile('@web/jqcloud/jqcloud.js', ['depends' => 'frontend\assets\AppAsset']);
$this->registerJsFile('@web/jqcloud/jqcloudcustom.js', ['depends' => 'frontend\assets\AppAsset']);


?>
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/th_TH/sdk.js#xfbml=1&version=v8.0" nonce="IMO8ymNg"></script>
<div class="site-index" style="min-height: calc(100vh - 204px);">
    <div class="body-content">
        <div class="header header justify-content-center d-flex position-relative">
            <header class="section-banner">
                <img src="/images/banner/home-banner.png" alt="" class="banner">
            </header>
            <div class="container banner-content ">
                <div class="analysis">
                    <div class="row justify-content-center">
                        <div class="col-lg-5">
                            <p class="number">100,000</p>
                            <p class="banner-text">บัญชีสมาชิก</p>
                        </div>
                        <div class="col-lg-5">
                            <p class="number">100,000</p>
                            <p class="banner-text">บัญชีสมาชิก</p>
                        </div>
                    </div>

                </div>
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
                <div class="menu justify-content-center d-flex">
                    <div class="menu-item">
                        <div class="bg-icon"><img src="/images/icon/Home.svg"></div>
                        <p>Home</p>
                    </div>
                    <div class="menu-item">
                        <div class="bg-icon"><img src="/images/icon/News.svg"></div>
                        <p>News</p>
                    </div>
                    <div class="menu-item">
                        <div class="bg-icon"><img src="/images/icon/Knowledge.svg"></div>
                        <p>Knowledge</p>
                    </div>
                    <div class="menu-item">
                        <div class="bg-icon"><img src="/images/icon/Blog.svg"></div>
                        <p>Blog</p>
                    </div>
                    <div class="menu-item">
                        <div class="bg-icon"><img src="/images/icon/Contact.svg"></div>
                        <p>Contact</p>
                    </div>

                </div>
            </div>
        </div>
        <section class="content pt-4">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar col-lg-2">
                        <p class="menu"></p>
                        <?php echo $this->render('../layouts/sidebar');?>
                    </div>
                    <div class="section-center col-lg-7">
                        <p class="menu text-left">Wall boards</p>
                        <div class="row">
                            <div class="col-6">
                                <button class="btn btn-success border-radius w-100 text-left mb-2 text-center"><i class="fas fa-pencil-alt"></i> <span> เขียนบอร์ดใหม่</span></button>
                            </div>
                        </div>

                        <div class="wall-board-bg">
                            <div class="wall-board-content">
                                <div class="row">
                                    <div class="col-2">
                                        <img src="/images/user.png" alt="" class="img-rounded">
                                    </div>
                                    <div class="col-10">
                                        <p class="title-user">Admin</p>
                                        <p class="school">รร. อุดมศึกษา น้อมเกล้า</p>
                                        <p class="post text-info">โพสต์ 10:00<span class="post-date">วันที่ 20 มิถุนายน 2020</span></p>
                                    </div>
                                </div>

                                <p>ประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรม</p>
                                <p>สามารถสอบถามรายละเอียดเพิ่มเติมได้ที่</p>
                                <div class="album-wall-board d-none">
                                    <div class="row">
                                        <div class="col-7">
                                            <img src="/images/hummingbird-2139278_1920.jpg" alt="" class="w-100">
                                        </div>
                                        <div class="col-5">
                                            <div class="row">
                                                <div class="col-6">
                                                    <img src="/images/hummingbird-2139278_1920.jpg" alt="" class="w-100" style="
    width: 100px !important;
    height: 100px;
    border-radius: 1rem;">
                                                </div>
                                                <div class="col-6">
                                                    <img src="/images/hummingbird-2139278_1920.jpg" alt="" class="w-100" style="
    width: 100px !important;
    height: 100px;
    border-radius: 1rem;">
                                                </div>
                                                <div class="col-6">
                                                    <img src="/images/hummingbird-2139278_1920.jpg" alt="" class="w-100" style="
    width: 100px !important;
    height: 100px;
    border-radius: 1rem;">
                                                </div>
                                                <div class="col-6">
                                                    <img src="/images/hummingbird-2139278_1920.jpg" alt="" class="w-100" style="
    width: 100px !important;
    height: 100px;
    border-radius: 1rem;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </div>
                            <div class="wall-board-content">
                                <div class="row">
                                    <div class="col-2">
                                        <img src="/images/user.png" alt="" class="img-rounded">
                                    </div>
                                    <div class="col-10">
                                        <p class="title-user">Admin</p>
                                        <p class="school">รร. อุดมศึกษา น้อมเกล้า</p>
                                        <p class="post text-info">โพสต์ 10:00<span class="post-date">วันที่ 20 มิถุนายน 2020</span></p>
                                    </div>
                                </div>
                                <p>ประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรมประชาสัมพันธ์กิจกรรม</p>
                                <p>สามารถสอบถามรายละเอียดเพิ่มเติมได้ที่</p>
                            </div>
                        </div>
                    </div>
                    <div class="section-right col-lg-3">
                        <p class="menu text-left">ติดตาม</p>
                        <div class="fb-page" data-href="https://www.facebook.com/biogang" data-tabs="timeline" data-width="" data-height="" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true">
                            <blockquote cite="https://www.facebook.com/biogang" class="fb-xfbml-parse-ignore"><a href="https://www.facebook.com/biogang">BioGang.Net</a></blockquote>
                        </div>
                        <p class="menu text-left">ฐานข้อมูลสถิติ</p>
                        <button class="btn btn-success border-radius w-100 text-left mb-2">
                            <span class="text-left ml-2">สถิติผู้เข้าชม (คน)</span>
                            <span class="float-right">2,500</span>
                        </button>
                        <button class="btn btn-success border-radius w-100 text-left mb-2">
                            <span class="text-left ml-2">ข้อมูลความรู้วิชาการ (เรื่อง)</span>
                            <span class="float-right">1,256</span>
                        </button>
                        <button class="btn btn-success border-radius w-100 text-left mb-2">
                            <span class="text-left ml-2">ข้อมูลบทความ (บทความ)</span>
                            <span class="float-right">356</span>
                        </button>
                        <button class="btn btn-success border-radius w-100 text-left mb-2">
                            <span class="text-left ml-2">ข้อมูลจำนวนสมาชิก (คน)</span>
                            <span class="float-right">20,026</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
        <section class="section-word-cloud">
            <div class="container">
                
                <div class="row">
                    <div class="col-lg-8 d-block">
                        <p class="menu text-left text-white">World cloud</p>
                        <div id="world-cloud" style="width: 100%; max-height: 405px;
    height: 100%;background-color:#FFF"></div>
                    </div>
                    <div class="col-lg-4">
                        <div class="row">
                            <div class="col-6">
                                <div class="word-cloud">
                                    
                                        <i class="fas fa-user"></i>
                                   
                                    <p>Word cloud</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="keyword-map">
                                    
                                        <i class="fas fa-user"></i>
                                        <p>Keyword Map</p>
                                    
                                </div>
                            </div>
                           
                        </div>
                        <div class="row">
                                <div class="container">
                                    <table class="table table-bordered table-world-cloud mt-3">
                                        <thead>
                                            <tr>
                                                <td colspan=2>Description</td>
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Biology</td>
                                                <td>Research</td>
                                            </tr>
                                            <tr>
                                                <td>Bio</td>
                                                <td>Animals</td>
                                            </tr>
                                            <tr>
                                                <td>Science</td>
                                                <td>Ecosytem</td>
                                            </tr>
                                            <tr>
                                                <td>Analysis</td>
                                                <td>Fungi</td>
                                            </tr>
                                            <tr>
                                                <td>Cell</td>
                                                <td>Energy</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                            </div>
                    </div>
                </div>
        </section>
        <section class="section-content-knowledge">
            <div class="container">
                <p class="menu text-left">องค์ความรู้ออนไลน์</p>
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <img src="/images/45308224_2408323609182713_2610811353954254848_o.jpg" alt="" class="w-100">
                        <p class="title">
                            สายพันธ์ไผ่พื้นบ้านของไทย
                        </p>
                        <p class="short-desc">ได้แก่ ไผ่รวก ไผ่สีสุก</p>
                        <p class="post">โพสต์ <span class="datetime">09:00 น วันที่ 3 กรกฎาคม 2020</span> <span><i class="fas fa-share"></i> แชร์</span></p>
                        <p class="viewer"><i class="far fa-user"></i> ผู้เข้าชม 2,333 คน</p>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <img src="/images/31237166_2122255421122868_6260889349114560512_o.jpg" alt="" class="w-100">
                        <p class="title">
                            สายพันธ์ไผ่พื้นบ้านของไทย
                        </p>
                        <p class="short-desc">ได้แก่ ไผ่รวก ไผ่สีสุก</p>
                        <p class="post">โพสต์ <span class="datetime">09:00 น วันที่ 3 กรกฎาคม 2020</span> <span><i class="fas fa-share"></i> แชร์</span></p>
                        <p class="viewer"><i class="far fa-user"></i> ผู้เข้าชม 2,333 คน</p>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <img src="/images/45308224_2408323609182713_2610811353954254848_o.jpg" alt="" class="w-100">
                        <p class="title">
                            สายพันธ์ไผ่พื้นบ้านของไทย
                        </p>
                        <p class="short-desc">ได้แก่ ไผ่รวก ไผ่สีสุก</p>
                        <p class="post">โพสต์ <span class="datetime">09:00 น วันที่ 3 กรกฎาคม 2020</span> <span><i class="fas fa-share"></i> แชร์</span></p>
                        <p class="viewer"><i class="far fa-user"></i> ผู้เข้าชม 2,333 คน</p>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <img src="/images/45308224_2408323609182713_2610811353954254848_o.jpg" alt="" class="w-100">
                        <p class="title">
                            สายพันธ์ไผ่พื้นบ้านของไทย
                        </p>
                        <p class="short-desc">ได้แก่ ไผ่รวก ไผ่สีสุก</p>
                        <p class="post">โพสต์ <span class="datetime">09:00 น วันที่ 3 กรกฎาคม 2020</span> <span><i class="fas fa-share"></i> แชร์</span></p>
                        <p class="viewer"><i class="far fa-user"></i> ผู้เข้าชม 2,333 คน</p>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <img src="/images/45308224_2408323609182713_2610811353954254848_o.jpg" alt="" class="w-100">
                        <p class="title">
                            สายพันธ์ไผ่พื้นบ้านของไทย
                        </p>
                        <p class="short-desc">ได้แก่ ไผ่รวก ไผ่สีสุก</p>
                        <p class="post">โพสต์ <span class="datetime">09:00 น วันที่ 3 กรกฎาคม 2020</span> <span><i class="fas fa-share"></i> แชร์</span></p>
                        <p class="viewer"><i class="far fa-user"></i> ผู้เข้าชม 2,333 คน</p>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <img src="/images/45308224_2408323609182713_2610811353954254848_o.jpg" alt="" class="w-100">
                        <p class="title">
                            สายพันธ์ไผ่พื้นบ้านของไทย
                        </p>
                        <p class="short-desc">ได้แก่ ไผ่รวก ไผ่สีสุก</p>
                        <p class="post">โพสต์ <span class="datetime">09:00 น วันที่ 3 กรกฎาคม 2020</span> <span><i class="fas fa-share"></i> แชร์</span></p>
                        <p class="viewer"><i class="far fa-user"></i> ผู้เข้าชม 2,333 คน</p>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <img src="/images/45308224_2408323609182713_2610811353954254848_o.jpg" alt="" class="w-100">
                        <p class="title">
                            สายพันธ์ไผ่พื้นบ้านของไทย
                        </p>
                        <p class="short-desc">ได้แก่ ไผ่รวก ไผ่สีสุก</p>
                        <p class="post">โพสต์ <span class="datetime">09:00 น วันที่ 3 กรกฎาคม 2020</span> <span><i class="fas fa-share"></i> แชร์</span></p>
                        <p class="viewer"><i class="far fa-user"></i> ผู้เข้าชม 2,333 คน</p>
                    </div>
                    <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                        <img src="/images/45308224_2408323609182713_2610811353954254848_o.jpg" alt="" class="w-100">
                        <p class="title">
                            สายพันธ์ไผ่พื้นบ้านของไทย
                        </p>
                        <p class="short-desc">ได้แก่ ไผ่รวก ไผ่สีสุก</p>
                        <p class="post">โพสต์ <span class="datetime">09:00 น วันที่ 3 กรกฎาคม 2020</span> <span><i class="fas fa-share"></i> แชร์</span></p>
                        <p class="viewer"><i class="far fa-user"></i> ผู้เข้าชม 2,333 คน</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>