<?php
use backend\components\BackendHelper;
use backend\components\PermissionAccess;
?>

<aside class="main-sidebar">

    <section class="sidebar"> 




        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget' => 'tree'],
                'items' => [
                    [
                        'label' => 'เนื้อหา',
                        'icon' => 'list-alt',
                        'url' => '#',
                        'items' => [
                            ['label' => 'พืช', 'icon' => 'leaf', 'url' => ['/content-plant'], 'active' => BackendHelper::menuActive('content-plant', $this->context->route)],
                            ['label' => 'สัตว์', 'icon' => 'bug', 'url' => ['/content-animal'], 'active' => BackendHelper::menuActive('content-animal', $this->context->route)],
                            ['label' => 'จุลินทรีย์', 'icon' => 'asterisk', 'url' => ['/content-fungi'], 'active' => BackendHelper::menuActive('content-fungi', $this->context->route)],
                            ['label' => 'ภูมิปัญญา/ปราชญ์', 'icon' => 'graduation-cap', 'url' => ['/content-expert'], 'active' => BackendHelper::menuActive('content-expert', $this->context->route)],
                            ['label' => 'ท่องเที่ยวเชิงนิเวศ', 'icon' => 'area-chart', 'url' => ['/content-ecotourism'], 'active' => BackendHelper::menuActive('content-ecotourism', $this->context->route)],
                            ['label' => 'ผลิตภัณฑ์ชุมชน', 'icon' => 'archive', 'url' => ['/content-product'], 'active' => BackendHelper::menuActive('content-product', $this->context->route)],
                        ],
                    ],
                    ['label' => 'องค์ความรู้ออนไลน์', 'icon' => 'book', 'url' => ['/knowledge'], 'active' => BackendHelper::menuActive('knowledge', $this->context->route)],
                    ['label' => 'ข่าวสาร', 'icon' => 'newspaper-o', 'url' => ['/news'], 'active' => BackendHelper::menuActive('news', $this->context->route)],
                    ['label' => 'บล็อก', 'icon' => 'th-large', 'url' => ['/blog'], 'active' => BackendHelper::menuActive('blog', $this->context->route)],
                    PermissionAccess::BackendAccess('school_list', 'funtion') == true
                        ? ['label' => 'โรงเรียน', 'icon' => 'university', 'url' => ['/school'], 'active' => BackendHelper::menuActive('school', $this->context->route)]
                        : array(),
                    PermissionAccess::BackendAccess('user_list', 'funtion') == true
                        ? ['label' => 'อนุมัติอาจารย์', 'icon' => 'user-plus', 'url' => ['/approved-teacher'], 'active' => BackendHelper::menuActive('teacher', $this->context->route)]
                        : array(),
                    PermissionAccess::BackendAccess('user_list', 'funtion') == true
                        ? ['label' => 'จัดการผู้ใช้งาน', 'icon' => 'user-o', 'url' => ['/users'], 'active' => BackendHelper::menuActive('users', $this->context->route)]
                        : array(),
                    ['label' => 'Wallboard', 'icon' => 'commenting', 'url' => ['/wallboard'], 'active' => BackendHelper::menuActive('wallboard', $this->context->route)],
                    // ['label' => 'จัดการสิทธิ์','icon' => 'lock','url' => ['/permission'] , 'active' => BackendHelper::menuActive("permission", $this->context->route)],
                    ['label' => 'จัดการแบนเนอร์', 'icon' => 'picture-o', 'url' => ['/banner'], 'active' => BackendHelper::menuActive('banner', $this->context->route)],
                    // ['label' => 'รายงาน','icon' => 'book ','url' => ['/report'] , 'active' => BackendHelper::menuActive("report", $this->context->route)],
                    // ['label' => 'ตั้งค่าทั่วไป','icon' => 'cog','url' => ['/setting'] , 'active' => BackendHelper::menuActive("setting", $this->context->route)],
                    // // ['label' => 'ติดต่อเรา','icon' => 'fax','url' => ['/contact-us'], 'active' => BackendHelper::menuActive("contact-us", $this->context->route)],
                    [
                        'label' => 'ตั้งค่า',
                        'icon' => 'cog',
                        'url' => '#',
                        'items' => [
                            ['label' => 'จัดการการแสดงผล Content', 'icon' => 'minus', 'url' => ['/content-type'], 'active' => BackendHelper::menuActive('content-type', $this->context->route)],
                            ['label' => 'หมวดหมู่ภูมิปัญญา', 'icon' => 'minus', 'url' => ['/expert-category'], 'active' => BackendHelper::menuActive('expert-category', $this->context->route)],
                            ['label' => 'หมวดหมู่ผลิตภัณฑ์', 'icon' => 'minus', 'url' => ['/product-category'], 'active' => BackendHelper::menuActive('product-category', $this->context->route)],
                            ['label' => 'ตั้งค่าภูมิปัญญา', 'icon' => 'minus', 'url' => ['/setting/expert']],
                            ['label' => 'ตั้งค่า Data Protection', 'icon' => 'minus', 'url' => ['/setting/data-protection']],
                            ['label' => 'ตั้งค่าทั่วไป', 'icon' => 'minus', 'url' => ['/setting']],
                        ],
                    ],
                ],
            ]
        ) ?>


    </section>

</aside>
