<?php

use frontend\components\FrontendHelper;
?>
<div class="menu-block menu-desktop">
    <div class="menu-side-item">
    <a href="/interactive-map" class="bg-icon tooltip-sidebar-interactive" title="Interactive Map"><img src="/images/icon/S_Interactive_Map.svg"></a>
        
    </div>
    <div class="menu-side-item ">
        <a href="http://bedolib.bedo.or.th/" class="bg-icon d-block tooltip-sidebar-bedo" title="BEDO Libary" target="_blank"><img src="/images/icon/S_Bedolib.svg"></a>
    </div>
    <div class="menu-side-item biodiversity">
    <a href="https://www.thaibiodiversity.org/bedo/index.php" class="bg-icon d-block tooltip-sidebar tooltip-sidebar-1" target="_blank" title="Thai Bio"><img src="/images/icon/S_Biodiversity.png"></a>
    </div>
</div>
<div class="menu-block menu-desktop" style="overflow-x: hidden;">
    <div class="content-group">
        <?php if (FrontendHelper::isContentTypeVisible(1)): ?>
        <div class="menu-side-item">
            <a class="bg-icon d-block tooltip-sidebar-plant" title="พืช | Plants (<?= FrontendHelper::getCountContent('plants') ?>)" href="/content-plant"><img src="/images/icon/S_Plant.svg"></a>
        </div>
        <?php endif; ?>
        <?php if (FrontendHelper::isContentTypeVisible(2)): ?>
        <div class="menu-side-item">
            <a class="bg-icon d-block tooltip-sidebar-animals" title="สัตว์ | Animal (<?= FrontendHelper::getCountContent('animal') ?>)" href="/content-animals"><img src="/images/icon/S_Animals.svg"></a>
        </div>
        <?php endif; ?>
        <?php if (FrontendHelper::isContentTypeVisible(3)): ?>
        <div class="menu-side-item">
            <a class="bg-icon d-block tooltip-sidebar-fungi" title="จุลินทรีย์ | Fungi (<?= FrontendHelper::getCountContent('fungi') ?>)" href="/content-fungi">
                <img src="/images/icon/S_Funji.svg" style="padding-bottom: 1px;">
            </a>
        </div>
        <?php endif; ?>
        <?php if (FrontendHelper::isContentTypeVisible(4)): ?>
        <div class="menu-side-item">
            <a class="bg-icon pt-0 d-block tooltip-sidebar-expert" title="ภูมิปัญญา | TK (<?= FrontendHelper::getCountContent('expert') ?>)" href="/content-expert">
                <img src="/images/icon/S_Expert.svg" style="padding-bottom: 1px;">
            </a>
        </div>
        <?php endif; ?>
        <?php if (FrontendHelper::isContentTypeVisible(5)): ?>
        <div class="menu-side-item">
            <a class="bg-icon d-block tooltip-sidebar-ecotourism" title="สถานที่ท่องเที่ยวเชิงนิเวศ | Ecotourism (<?= FrontendHelper::getCountContent('ecotourism') ?>)" href="/content-ecotourism"><img src="/images/icon/S_Ecotourism.svg"></a>
        </div>
        <?php endif; ?>
        <?php if (FrontendHelper::isContentTypeVisible(6)): ?>
        <div class="menu-side-item product">
            <a class="bg-icon pt-0 d-block tooltip-sidebar-product" title="ผลิตภัณฑ์ชุมชน | Product (<?= FrontendHelper::getCountContent('product') ?>)" href="/content-product"><img src="/images/icon/S_Product.svg"></a>
        </div>
        <?php endif; ?>
    </div>
</div>
<div class="menu-block menu-desktop">
    <div class="menu-side-item facebook">
        <a href="https://www.facebook.com/biogang" class="bg-icon pt-0 d-block tooltip-sidebar-facebook" target="_blank" title="Facebook">
            <img src="/images/icon/Facebook_Logo.png">
        </a>
    </div>
    <div class="menu-side-item line">
        <a href="https://page.line.me/czs3996y" class="bg-icon pt-0 d-block tooltip-sidebar-line" target="_blank" title="Line">
            <img src="/images/icon/Line_Logo.png">
        </a>
    </div>
    <div class="menu-side-item youtube">
        <a href="https://www.youtube.com/channel/UCc1uIdvMvnnuGgVGh4DOudQ" class="bg-icon pt-0 d-block tooltip-sidebar-youtube" target="_blank" title="Youtube" >
            <img src="/images/icon/Youtube_Logo.png" style="border-radius: 50%";>
        </a>
    </div>
</div>


<div class="menu-block menu-mobile">

    <div class="col-12 mobile-layout">

        <div class="menu-side-item" id="show-menu-mobile">
            <a href="javascript:void(0)" class="bg-icon" ><i class="fas fa-arrow-right"></i></a>
            
        </div>

        <div class="menu-side-item">
            <a href="/interactive-map" class="bg-icon" title="Interactive Map"><img src="/images/icon/S_Interactive_Map.svg"></a>
            
        </div>
        <div class="menu-side-item ">
            <a href="http://bedolib.bedo.or.th/" class="bg-icon d-block " title="BEDO Libary" target="_blank"><img src="/images/icon/S_Bedolib.svg"></a>
        </div>
        <div class="menu-side-item biodiversity">
            <a href="https://www.thaibiodiversity.org/bedo/index.php" class="bg-icon d-block " target="_blank" title="Thai Bio"><img src="/images/icon/S_Biodiversity.png"></a>
        </div>

        <?php if (FrontendHelper::isContentTypeVisible(1)): ?>
        <div class="menu-side-item">
            <a class="bg-icon d-block " title="พืช | Plants (<?= FrontendHelper::getCountContent('plants') ?>)" href="/content-plant"><img src="/images/icon/S_Plant.svg"></a>
        </div>
        <?php endif; ?>
        <?php if (FrontendHelper::isContentTypeVisible(2)): ?>
        <div class="menu-side-item">
            <a class="bg-icon d-block " title="สัตว์ | Animal (<?= FrontendHelper::getCountContent('animal') ?>)" href="/content-animals"><img src="/images/icon/S_Animals.svg"></a>
        </div>
        <?php endif; ?>
        <?php if (FrontendHelper::isContentTypeVisible(3)): ?>
        <div class="menu-side-item">
            <a class="bg-icon d-block " title="จุลินทรีย์ | Fungi (<?= FrontendHelper::getCountContent('fungi') ?>)" href="/content-fungi">
                <img src="/images/icon/S_Funji.svg" style="padding-bottom: 1px;">
            </a>
        </div>
        <?php endif; ?>
        <?php if (FrontendHelper::isContentTypeVisible(4)): ?>
        <div class="menu-side-item">
            <a class="bg-icon pt-0 d-block " title="ภูมิปัญญา | TK (<?= FrontendHelper::getCountContent('expert') ?>)" href="/content-expert">
                <img src="/images/icon/S_Expert.svg" style="padding-bottom: 1px;">
            </a>
        </div>
        <?php endif; ?>
        <?php if (FrontendHelper::isContentTypeVisible(5)): ?>
        <div class="menu-side-item">
            <a class="bg-icon d-block " title="สถานที่ท่องเที่ยวเชิงนิเวศ | Ecotourism (<?= FrontendHelper::getCountContent('ecotourism') ?>)" href="/content-ecotourism"><img src="/images/icon/S_Ecotourism.svg"></a>
        </div>
        <?php endif; ?>
        <?php if (FrontendHelper::isContentTypeVisible(6)): ?>
        <div class="menu-side-item product">
            <a class="bg-icon pt-0 d-block " title="ผลิตภัณฑ์ชุมชน | Product (<?= FrontendHelper::getCountContent('product') ?>)" href="/content-product"><img src="/images/icon/S_Product.svg"></a>
        </div>
        <?php endif; ?>

        <div class="menu-side-item facebook">
            <a href="https://www.facebook.com/biogang" class="bg-icon pt-0 d-block " target="_blank" title="Facebook">
                <img src="/images/icon/Facebook_Logo.png">
            </a>
        </div>
        <div class="menu-side-item line">
            <a href="https://page.line.me/czs3996y" class="bg-icon pt-0 d-block " target="_blank" title="Line">
                <img src="/images/icon/Line_Logo.png">
            </a>
        </div>
        <div class="menu-side-item youtube">
            <a href="https://www.youtube.com/channel/UCc1uIdvMvnnuGgVGh4DOudQ" class="bg-icon pt-0 d-block " target="_blank" title="Youtube" >
                <img src="/images/icon/Youtube_Logo.png" style="border-radius: 50%";>
            </a>
        </div>
    </div>

</div>