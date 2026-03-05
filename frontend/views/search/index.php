<?php
use yii\helpers\Url;
/* @var $this yii\web\View */
use frontend\components\FrontendHelper;
use common\components\FileLibrary;
use frontend\models\Knowledge;

$this->title = 'ค้นหาข้อมูล';
$this->registerCss("nav {background-image: url('/images/banner/News_Banner.png'); }");
$linkMain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']; //
$_SESSION['currentUrl'] = $linkMain . $_SERVER['REQUEST_URI'];
$this->registerJsFile(Url::base().'/js/location.js', ['depends' => [\frontend\assets\AppAsset::className()]]);
$keyword = '';
$region_id = '';
$province_id = '';
$district_id = '';
$subdistrict_id = '';
$taxonomy = '';
if(!empty($_GET['keyword'])){
    $keyword = $_GET['keyword'];
}

if(!empty($_GET['region_id'])){
    $region_id = $_GET['region_id'];
}

if(!empty($_GET['province_id'])){
    $province_id = $_GET['province_id'];
}

if(!empty($_GET['district_id'])){
    $district_id = $_GET['district_id'];
}

if(!empty($_GET['subdistrict_id'])){
    $subdistrict_id = $_GET['subdistrict_id'];
}

if(!empty($_GET['taxonomy'])){
    $taxonomy = $_GET['taxonomy'];
}

use frontend\models\Banner;
$banner = Banner::find()->where(['slug_url' => 'Search'])->one();

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
            <li class="breadcrumb-item home"><a href="/">Home</a></li>
            <li class="breadcrumb-item"><a href="/ecotourism"><?=$this->title?></a></li>
        </ol>
    </div>
</div>


<div class="site-fungi" style="min-height: calc(100vh - 204px);">
    <div class="body-content">
        <section class="content pt-4">
            <div class="container">
                <div class="row">
                    <div class="menu-sidebar">
                        <p class="menu"></p>
                        <?php echo $this->render('../layouts/sidebar');?>
                    </div>
                    <div class="section-main">
                        <div class="d-bock">

                            <p class="menu text-left">ค้นหาข้อมูล</p>

                            <form method="get" id="search-form">

                                <div class="row">
                                    <div class="col">
                                        <div class="form-group field-search-keyword">
                                            <label for="search-keyword">ระบุคำค้นหาชื่อเรื่อง</label>
                                            <input type="text" id="search-keyword" class="form-control" name="keyword"
                                                placeholder="ระบุคำค้นหา" value="<?php echo $keyword; ?>">

                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <div class="form-group field-taxonomy">
                                            <label for="taxonomy">ระบุคำช่วยค้นหา</label>
                                            <input type="text" id="taxonomy" class="form-control" name="taxonomy"
                                                placeholder="ระบุคำช่วยค้นหา" value="<?php echo $taxonomy; ?>">

                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group my-2 pl-1">

                                    <div role="radiogroup" aria-required="true" aria-invalid="false">


                                        <?php if (FrontendHelper::isContentTypeVisible(1)): ?>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="checkbox" class="custom-control-input is-valid "
                                                name="content_plant" value="1"
                                                <?php echo !empty($_GET['content_plant'])? 'checked':'' ?>
                                                id="search-plant">
                                            <label class="custom-control-label" for="search-plant">พืช</label>
                                        </div>
                                        <?php endif; ?>



                                        <?php if (FrontendHelper::isContentTypeVisible(2)): ?>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="checkbox" class="custom-control-input is-valid "
                                                name="content_animal" value="1"
                                                <?php echo !empty($_GET['content_animal'])? 'checked':'' ?>
                                                id="search-animal">
                                            <label class="custom-control-label" for="search-animal">สัตว์</label>
                                        </div>
                                        <?php endif; ?>



                                        <?php if (FrontendHelper::isContentTypeVisible(3)): ?>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="checkbox" class="custom-control-input is-valid "
                                                name="content_fungi" value="1"
                                                <?php echo !empty($_GET['content_fungi'])? 'checked':'' ?>
                                                id="search-fungi">
                                            <label class="custom-control-label" for="search-fungi">จุลินทรีย์</label>
                                        </div>
                                        <?php endif; ?>



                                        <?php if (FrontendHelper::isContentTypeVisible(4)): ?>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="checkbox" class="custom-control-input is-valid "
                                                name="content_expert" value="1"
                                                <?php echo !empty($_GET['content_expert'])? 'checked':'' ?>
                                                id="search-expert">
                                            <label class="custom-control-label" for="search-expert">ภูมิปัญญา /
                                                ปราชญ์</label>
                                        </div>
                                        <?php endif; ?>



                                        <?php if (FrontendHelper::isContentTypeVisible(5)): ?>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="checkbox" class="custom-control-input is-valid "
                                                name="content_ecotourism" value="1"
                                                <?php echo !empty($_GET['content_ecotourism'])? 'checked':'' ?>
                                                id="search-ecotourism">
                                            <label class="custom-control-label"
                                                for="search-ecotourism">การท่องเที่ยวเชิงนิเวศ</label>
                                        </div>
                                        <?php endif; ?>



                                        <?php if (FrontendHelper::isContentTypeVisible(6)): ?>
                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="checkbox" class="custom-control-input is-valid "
                                                name="content_product" value="1"
                                                <?php echo !empty($_GET['content_product'])? 'checked':'' ?>
                                                id="search-product">
                                            <label class="custom-control-label"
                                                for="search-product">ผลิตภัณฑ์ชุมชน</label>
                                        </div>
                                        <?php endif; ?>

                                        <div class="custom-control custom-radio custom-control-inline">
                                            <input type="checkbox" class="custom-control-input is-valid "
                                                name="content_knowledge" value="1"
                                                <?php echo !empty($_GET['content_knowledge'])? 'checked':'' ?>
                                                id="search-knowledge">
                                            <label class="custom-control-label"
                                                for="search-knowledge">องค์ความรู้ออนไลน์</label>
                                        </div>

                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col">
                                        <div class="form-group field-search-region_id validating">
                                            <label for="search-region_id">ภาค</label>
                                            <select id="search-region_id" class="form-control " name="region_id"
                                                aria-invalid="false">
                                                <option value="">กรุณาเลือกภาค</option>
                                                <option value="1">ภาคกลาง</option>
                                                <option value="2">ภาคเหนือ</option>
                                                <option value="3">ภาคตะวันออกเฉียงเหนือ</option>
                                                <option value="4">ภาคตะวันออก</option>
                                                <option value="5">ภาคตะวันตก </option>
                                                <option value="6">ภาคใต้</option>

                                            </select>

                                            <input type="hidden" id="region_init" value="<?php echo $region_id; ?>">

                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group field-search-province_id validating">
                                            <label for="search-province_id">จังหวัด</label>
                                            <select id="search-province_id" class="form-control " name="province_id"
                                                aria-invalid="false">
                                                <option value="">กรุณาเลือกจังหวัด</option>
                                            </select>

                                            <input type="hidden" id="province_init" value="<?php echo $province_id; ?>">

                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group field-search-district_id validating">
                                            <label for="search-district_id">อำเภอ</label>
                                            <select id="search-district_id" class="form-control " name="district_id"
                                                aria-invalid="false">
                                                <option value="">กรุณาเลือกอำเภอ</option>
                                            </select>

                                            <input type="hidden" id="district_init" value="<?php echo $district_id; ?>">

                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col">
                                        <div class="form-group field-search-subdistrict_id validating">
                                            <label for="search-subdistrict_id">ตำบล</label>
                                            <select id="search-subdistrict_id" class="form-control"
                                                name="subdistrict_id" aria-invalid="false">
                                                <option value="">กรุณาเลือกตำบล</option>
                                            </select>


                                            <input type="hidden" id="subdistrict_init"
                                                value="<?php echo $subdistrict_id; ?>">

                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group field-search-zipcode_id">
                                            <label for="search-zipcode_id">รหัสไปรษณีย์</label>
                                            <input type="text" id="search-zipcode_id" class="form-control"
                                                name="zipcode_id" placeholder="รหัสไปรษณีย์">

                                            <div class="invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>



                                <div class="row">
                                    <div class="col align-self-center">
                                        <button type="submit" class="btn btn-primary btn-edit mt-3">ค้นหา</button>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="container">
                                        <div class="d-flex">
                                            <div class="mr-auto">

                                                <h4 class="keyword-result">คำที่ค้นหา <?php 

                                                    if(!empty($taxonomy) && !empty($keyword)){
                                                        echo '"'.$keyword.'" และ "'.$taxonomy.'"'; 
                                                    }else if(empty($taxonomy) && !empty($keyword)){
                                                        echo '"'.$keyword.'"';
                                                    }else if(!empty($taxonomy) && empty($keyword)){
                                                        echo '"'.$taxonomy.'"';
                                                    }
                                                
                                                ?></h4>
                                            </div>
                                            <div class="">
                                                <p class="keyword-result">จำนวนที่พบ
                                                    <?php echo number_format($totalCount); ?> รายการ</p>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </form>
                        </div>
                        <div class="blog-content">
                            <div class="row content-list">
                                <?php if(empty($search)) { ?>
                                <div class="container empty-data">
                                    <p class="text-center">ไม่พบข้อมูล</p>
                                </div>
                                <?php }  foreach ($search as $key => $value) { 
                                    $thisUrl = '/content-'.FrontendHelper::getContentTypeById($value["type_id"]).'/' . $value["id"];
                                    ?>
                                <a href="/content-<?php echo FrontendHelper::getContentTypeById($value["type_id"]); ?>/<?=$value["id"]?>"
                                    class="col-lg-4 mb-3">
                                    <object>
                                        <div class="content-img">
                                            <?php echo FileLibrary::getImageFrontend(FrontendHelper::getFolderImageContent($value["type_id"]), $value["picture_path"]); ?>
                                        </div>
                                        <h1 class="title"><?php echo $value["name"]; ?></h1>
                                        <p class="short-desc"><?php echo strip_tags($value["description"]); ?></p>

                                        <p class="creator-post">
                                            <?php echo FileLibrary::getImageFrontend('profile', FrontendHelper::getProfileImage($value["created_by_user_id"]), '', false, '', 'img-rounded-small'); ?>
                                            <?php echo FrontendHelper::getProfileName($value["created_by_user_id"]); ?>
                                        </p>

                                        <div class="post">โพสต์
                                            <?php echo FrontendHelper::getTime($value["created_at"]); ?>
                                            <span class="post-date">วันที่
                                                <?php echo FrontendHelper::getDate($value["created_at"]) ?> </span>
                                            <span class="dropdown share">
                                                <span class="share-label"><i class="fas fa-share"></i> แชร์</span>
                                                <div class="dropdown-content share-dropdown-item">
                                                    <a class="fb-share" data-href="<?php echo $linkMain . $thisUrl; ?>"
                                                        target="_blank"
                                                        href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $linkMain . $thisUrl; ?>&amp;src=sdkpreparse&display=popup"><i
                                                            class="fab fa-facebook-square"></i> Facebook
                                                    </a>
                                                    <a class="line-share"
                                                        data-href="<?php echo $linkMain . $thisUrl; ?>"
                                                        href="https://lineit.line.me/share/ui?url=<?php echo $linkMain . $thisUrl; ?>"
                                                        target="_blank"
                                                        data-url="<?php echo $linkMain . $thisUrl; ?>"><i
                                                            class="fab fa-line"></i> Line</a>
                                                </div>
                                            </span>
                                            <span class="viewer"><i class="far fa-user"></i> ผู้เข้าชม
                                                <?= FrontendHelper::getPageView($value["id"], "content") ?> คน</span>
                                        </div>
                                    </object>
                                </a>
                                <?php } ?>
                            </div>
                            <div class="pagin justify-content-center mt-5">
                                <?php echo \yii\widgets\LinkPager::widget([
                                    'pagination' => $pagination,
                                    'maxButtonCount' => 4,
                                ]); ?>
                            </div>
                        </div>

                        <div class="blog-content">
                            <?php if(!empty($knowledge)) {  ?>
                            <div class="row mt-3">
                                <div class="container">
                                    <div class="d-flex">
                                        <div class="mr-auto">

                                            <h5 class="keyword-result">องค์ความรู้อออนไลน์</h5>
                                            
                                        </div>
                                       
                                    </div>

                                </div>

                            </div>

                            <?php } ?>

                            <div class="row content-list">
                                <?php if(!empty($knowledge)) { 
                                     foreach ($knowledge as $key => $value) {
                                    $thisUrl = '/knowledge/' . $value["id"];
                                ?>
                                <a href="/knowledge/<?= $value["id"] ?>" class="col-lg-4 mb-4">
                                    <object>
                                        <div class="knowledge-img">
                                            <?php if(!empty($value['path']) && strpos($value['path'], 'youtube') !== false): ?>
                                            <iframe height="250"
                                                src="<?php echo FrontendHelper::getYoutubeEmbedUrl($value['path']); ?>"></iframe>
                                            <?php else: ?>
                                            <?php echo FileLibrary::getImageFrontend(Knowledge::UPLOAD_FOLDER_KNOWLEDGE, $value["picture_path"]); ?>
                                            <?php endif; ?>
                                        </div>
                                        <h1 class="title"><?php echo $value["title"] ?></h1>
                                        <p class="short-desc">
                                            <?php echo strip_tags($value["description"]); ?>
                                        </p>
                                        <div class="post">โพสต์
                                            <?php echo FrontendHelper::getTime($value["created_at"]); ?>
                                            <span class="post-date">วันที่
                                                <?php echo FrontendHelper::getDate($value["created_at"]) ?> </span>
                                            <span class="dropdown share">
                                                <span class="share-label"><i class="fas fa-share"></i> แชร์</span>
                                                <div class="dropdown-content share-dropdown-item">
                                                    <a class="fb-share" data-href="<?php echo $linkMain . $thisUrl; ?>"
                                                        target="_blank"
                                                        href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $linkMain . $thisUrl; ?>&amp;src=sdkpreparse&display=popup"><i
                                                            class="fab fa-facebook-square"></i> Facebook
                                                    </a>
                                                    <a class="line-share"
                                                        data-href="<?php echo $linkMain . $thisUrl; ?>"
                                                        href="https://lineit.line.me/share/ui?url=<?php echo $linkMain . $thisUrl; ?>"
                                                        target="_blank"
                                                        data-url="<?php echo $linkMain . $thisUrl; ?>"><i
                                                            class="fab fa-line"></i> Line</a>
                                                </div>
                                            </span>
                                            <span class="viewer"><i class="far fa-user"></i> ผู้เข้าชม
                                                <?= FrontendHelper::getPageView($value["id"], "knowledge") ?> คน</span>
                                        </div>
                                    </object>
                                </a>
                                <?php }} ?>
                            </div>
                            <div class="pagin justify-content-center mt-5">
                                <?php echo \yii\widgets\LinkPager::widget([
                                    'pagination' => $paginationKnowledge,
                                    'maxButtonCount' => 4,
                                ]); ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
</div>