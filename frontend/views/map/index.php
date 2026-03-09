<?php

/* @var $this yii\web\View */

use yii\widgets\Breadcrumbs;
use frontend\components\FrontendHelper;
use yii\helpers\ArrayHelper;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;
use frontend\models\Region;
use frontend\models\District;
use frontend\models\Subdistrict;
use frontend\models\Zipcode;
use frontend\models\Province;

$region = ArrayHelper::map(Region::find()->all(), 'id', 'name_th');
$province = array();//ArrayHelper::map(Province::find()->all(), 'id', 'name_th');
$district =  array();//ArrayHelper::map(District::find()->all(), 'id', 'name_th');
$subdistrict =  array();//ArrayHelper::map(Subdistrict::find()->all(), 'id', 'name_th');
$zipcode =  array();//ArrayHelper::map(Zipcode::find()->all(), 'id', 'name_th');

use yii\helpers\Url;

$this->registerJsFile(Url::base().'/js/jquery.preloaders.js', ['depends' => [\frontend\assets\AppAsset::className()]]);
$this->registerCssFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.css');
$this->registerJsFile('https://unpkg.com/leaflet@1.9.4/dist/leaflet.js');
$this->registerJsFile('https://unpkg.com/topojson-client@3');
$this->registerJsFile(Url::base().'/js/leaflet-map.js?v='.time(), ['depends' => [\frontend\assets\AppAsset::className()]]);

$this->title = 'Interactive Map';
$this->registerCss("nav {background-image: url('/images/banner/News_Banner.png'); }");

$this->registerCss("
.legend-map {
    line-height: 18px;
    color: #333;
    background: white;
    padding: 12px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #ddd;
    font-family: inherit;
    font-size: 13px;
}
.legend-map i {
    width: 18px;
    height: 18px;
    float: left;
    margin-right: 8px;
    opacity: 0.8;
    border: 1px solid #ccc;
    border-radius: 2px;
}
.area-label {
    background: none !important;
    border: none !important;
    box-shadow: none !important;
    color: #333;
    font-weight: bold;
    text-shadow: 0 0 3px white, 0 0 5px white;
    font-size: 11px;
    pointer-events: none;
}
.area-label-hidden {
    display: none !important;
}
");

$this->registerCssFile("@web/css/map.css?v=".time(), ['depends' => 'frontend\assets\AppAsset']);


use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'Interactive Map'])->one();

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
<div class="site-news" style="min-height: calc(100vh - 204px);">
    <div class="body-content">
        <section class="content pt-4">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar">
                        <p class="menu"></p>
                        <?php echo $this->render('../layouts/sidebar'); ?>
                    </div>
                    <div class="section-main">

                        <div class="d-flex">
                            <div class="col-6 pl-0">
                                <p class="menu text-left">Interactive Map</p>
                            </div>
                            <!-- <div class="col-6 pr-0">
                                <p class="menu text-right view-all">ดูทั้งหมด</p>
                            </div> -->
                        </div>
                        <div class="map-title mb-3">
                            <div class="d-flex">
                                <p class="menu mb-4 mr-auto" id="main-title-map">ขอบเขต</p>
                                <div>
                                <button id="btn-back-map" class="btn btn-map mr-2" style="display: none;" type="button">⬅ กลับไปหน้าประเทศ</button>
                                <button class="btn btn-map" type="button" onclick="getLocation()"><i class="fas fa-map-marker-alt"></i> My location</button>
                                <button class="btn btn-map d-none"type="button" onclick="getLocation()"><i class="fas fa-sync"></i> Reset</button>
                                <button class="btn btn-map d-none"type="button" onclick="getLocation()"><i class="fas fa-expand"></i> Full Screen</button>
                            </div>
                            </div>
                            
                            
                        </div>
                        <div class="news-content" style="position: relative;">
                            <div id="loading" class="loading-map" style="display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10000; background: rgba(255,255,255,0.9); padding: 10px 20px; border-radius: 8px;">Loading Map...</div>
                            <div id="map" class="map" style="height: 600px; background-color: transparent !important; border-radius: 8px; border: 1px solid #eaeaea;"></div>
                        </div>

                        <div class="block-filter-search-map">
                        <?php $form = ActiveForm::begin([
                            'id' => 'map-form',
                            'method'=>'GET',
                            'options'=>[
                                'class'=>'map-form',
                            ],
                            ]); ?>   
                            <div class="row">

                                <div class="container container-search">
                                    <div class="form-search-map  pr-0">
                                        <div class="form--">
                                            <label>ภาค</label>
                                        </div>
                                        <div class="input-region">
                                            <?= $form->field($mapModel, 'region_id')->dropDownList($region, ['prompt' => 'กรุณาเลือกภาค'])->label(false); ?>
                                        </div>
                                    </div>
                                    <div class="form-search-map  pr-0"> 
                                        <div class="form--">
                                            <label>จังหวัด</label>   
                                        </div>      
                                        <div class="input-province">                  
                                            <?= $form->field($mapModel, 'province_id')->dropDownList($province, ['prompt' => 'กรุณาเลือกจังหวัด'])->label(false); ?>
                                        </div>
                                    </div>
                                    <div class="form-search-map  pr-0">
                                        <div class="form--">
                                            <label>อำเภอ</label>  
                                        </div>
                                        <div class="input-district">
                                            <?= $form->field($mapModel, 'district_id')->dropDownList($district, ['prompt' => 'กรุณาเลือกอำเภอ'])->label(false); ?>
                                        </div>
                                    </div>
                                    <div class="form-search-map pr-0">
                                        <div class="form--">
                                            <label>ตำบล</label>  
                                        </div>
                                        <div class="input-sub-district">
                                            <?= $form->field($mapModel, 'subdistrict_id')->dropDownList($subdistrict, ['prompt' => 'กรุณาเลือกตำบล'])->label(false); ?>
                                        </div>
                                    </div>
                                    <div class="form-search-map pr-0 form-search-text">
                                        <div class="form--" style="display: inline-block;">
                                            <label>ชื่อ</label> 
                                        </div> 
                                        <div class="input-group-btn">
                                            <div class="form-group input-search">
                                                <input type="text" class="form-control" id="keyword-text" placeholder="ระบุคำที่ต้องการค้นหา">
                                            </div>
                                            <button type="button" class="btn btn-search">ค้นหา</button>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php ActiveForm::end(); ?>
                        <div class="row check-content-type">
                            <div class="col-sm-12 col-md-2 pull-left">
                                <label class="container">พืช (<span class="number" id="span-type-1">0</span>)
                                    <input type="checkbox" name="content-type[]" checked="checked" value="1" onChange="checkboxType()">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="col-sm-12 col-md-2 pull-left">
                                <label class="container">สัตว์ (<span class="number" id="span-type-2">0</span>)
                                    <input type="checkbox" name="content-type[]" checked="checked" value="2" onChange="checkboxType()">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="col-sm-12 col-md-2 pull-left">
                                <label class="container">จุลินทรีย์ (<span class="number" id="span-type-3">0</span>)
                                    <input type="checkbox" name="content-type[]" checked="checked" value="3" onChange="checkboxType()">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="col-sm-12 col-md-2 pull-left">
                                <label class="container">ภูมิปัญญา / ปราชญ์ <br>Expert (<span class="number" id="span-type-4">0</span>)
                                    <input type="checkbox" name="content-type[]" checked="checked" value="4" onChange="checkboxType()">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="col-sm-12 col-md-2 pull-left">
                                <label class="container">การท่องเที่ยวเชิงนิเวศ (<span class="number" id="span-type-5">0</span>)
                                    <input type="checkbox" name="content-type[]" checked="checked" value="5" onChange="checkboxType()">
                                    <span class="checkmark"></span>
                                </label>
                            </div><div class="col-sm-12 col-md-2 pull-left">
                                <label class="container">ผลิตภัณฑ์ชุมชน (<span class="number" id="span-type-6">0</span>)
                                    <input type="checkbox" name="content-type[]" checked="checked" value="6" onChange="checkboxType()">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="content pt-4">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar">

                    </div>
                        <div class="section-main">
                        <div class="search-breadcrumb"></div>
                        <div id="show-list-item">
                            <a href="">
                            <div class="row list-item">
                                <div class="col-md-4 col-sm-12 image">
                                    <img src="/files/content-fungi/biodiversity-224803-1.jpg">
                                </div>
                                <div class="col-md-8 col-sm-12 detail">
                                    <p class="item-name">จุลินทรีย์ Em</p>
                                    <p class="item-address">ชลบุรี เมืองชลบุรี    หนองรี</p>
                                    <div class="item-detail">
                                            ผลการค้นหา ตัวอย่างข้อมูลแนะนำจากเว็บ น้ำหมักชีวภาพ หรือ น้ำสกัดชีวภาพ หรือ น้ำจุลินทรีย์ เป็นของเหลว สีดำออกน้ำตาล กลิ่นอมเปรี้ยวอมหวาน ไม่เป็นอันตรายต่อสิ่งมีชีวิตทุกชนิด เช่น พืช สัตว์ทุกประเภท สามารถช่วยปรับความสมดุลของสิ่งแวดล้อมและสิ่งมีชีวิตได้ บางครั้งยังสามารถนำน้ำหมักชีวภาพไปชำระล้างห้องน้ำได้ ซึ่งจะช่วยกำจัดกลิ่นเหม็นได้
                                    </div> 
                                </div>
                            </div>
                            <hr/>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-12 text-center">
                        <button class="btn btn-read-more" onClick="readMore(this)"><i class="fas fa-spinner"></i> โหลดข้อมูลเพิ่มเติม</button>
                    </div>
                </div>
            </div>
        </section>




    </div>
</div>