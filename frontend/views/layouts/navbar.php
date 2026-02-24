    
<?php

use yii\helpers\Html;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\bootstrap4\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;
use frontend\components\FrontendHelper;
use frontend\components\PermissionAccess;

$IsTeacherOrStudent = false;
if(PermissionAccess::FrontendAccess("menu_teacher_approved_content", "sdsd")) {
    $IsTeacherOrStudent = true;
    $url = '/content/views/teacher';
    $lebel_menu = "อนุมัติข้อมูลของนักเรียน";
}
else if(PermissionAccess::FrontendAccess("menu_student_add_content", "sdsd")) {
    $IsTeacherOrStudent = true;
    $url = '/content/views/student';
    $lebel_menu = "การนำเข้าข้อมูลของฉัน";
}
else {
    $IsTeacherOrStudent = false;
}

if (!Yii::$app->user->isGuest) {
    $userProfile = FrontendHelper::getProfile(Yii::$app->user->identity->id);
    $userRole = "";
    if (empty($userProfile['picture'])) {
        $profileDisplay = '<img src="/images/icon/Login.png" alt="" class="icon-login "> <span class="display-name">' . $userProfile['display_name']."</span>";
    } else {
        //'<img src="/files/profile/'.$userProfile['picture'].'" alt="" class="icon-login "> '.
        $profileDisplay = $userProfile['display_name'];
    }
}

NavBar::begin([
    'brandLabel' => 
        '<object><div class="brand-group d-flex">' .
            '<a href="https://www.bedo.or.th/bedo" target="_blank"><div class="d-inline bedo-logo" >' .
                Html::img('/images/logo/BEDO_Logo_Circle.png', ['alt' => 'Home', 'id' => 'link-logo', 'class' => 'logo-biogang img-fluid']) .
            '</div></a>'.
            '<div class="vertical-line-navbar"></div>' . 
            Html::img('/images/logo/biogang_logo.png', ['alt' => 'Home', 'class' => 'logo-biogang img-fluid']) .
        '</div></object>',
    'brandUrl' => Yii::$app->homeUrl,
    'options' => [
        'class' => 'fixed-top navbar-expand-lg navbar-light bg-theme-style menu-header p-0',
        'id' => 'navbar'
    ],
]);

$menuItems = [
    // [
    //     'label' => 'ก',
    //     'items' => [
    //         [
    //             'label' => 'ก',

    //             // 'url' => ['/community/' . $community_url . '/search-type'],

    //         ],

    //     ],
    //     'options' => [
    //         'class' => [
    //             'option-text mr-5'
    //         ]
    //     ]
    // ],
    [
        'label' => 'ก-',
        
        'options' => [
            'class' => [
                'yellow'
            ]
        ]
    ],
    [
        'label' => 'ก',
        
        'options' => [
            'class' => [
                'black'
            ]
        ]
    ],
    [
        'label' => 'ก+',
        
        'options' => [
            'class' => [
                'white'
            ]
        ]
    ]
];

if (Yii::$app->user->isGuest) {
    $menuItemsUser[] = [
        'label' => '<img src="/images/icon/Login.png" alt="" class="icon-login "> เข้าสู่ระบบ ',
        'encode' => false,
        'items' => [
            // Html::a(Html::img('/images/Login.png', 
            // [
            // //     'class' => 'img-register'
            // // ]).' <span>สมัครสมาชิก<span>', false, 
            // // [
            // //     'data-lang' => 'en',
            // //     'class' => 'lang dropdown-item',

            //  ]),
        ],
        'options' => ['class' => 'nav-login'],
        'url' => ['/user/login'],

    ];
} else {
    $menuItemsUser[] = "<li class='profile nav-item'>".
        "<a class='nav-link' href='/profile'>".
        FrontendHelper::profileImage($userProfile['picture']).
        "</a></li>";
    if($IsTeacherOrStudent) {
        $menuItemsUser[] = 
        [
            'label' => empty($profileDisplay) ? "" : $profileDisplay,
            'encode' => false,
            'items' => [
                ['label' =>  'ข้อมูลส่วนตัว', 'url' => ['/profile'], 'active' => FrontendHelper::menuActive("profile", $this->context->route)],
                ['label' =>  'บล็อกของฉัน', 'url' => ['/blog/list'], 'active' => FrontendHelper::menuActive("blog", $this->context->route)],
                ['label' =>  $lebel_menu, 'url' => [$url], 'active' => FrontendHelper::menuActive("content", $this->context->route)],
                ['label' =>  'ออกจากระบบ', 'url' => ['/logout'], 'options' => ['class' => 'nonDisplay--'], 'linkOptions' => ['data-method' => 'post']],
            ],
            'options' => ['class' => 'nav-profile'],
        ];
    }
    else {
        $menuItemsUser[] = 
    [
        'label' => empty($profileDisplay) ? "" : $profileDisplay,
        'encode' => false,
        'items' => [
            ['label' =>  'ข้อมูลส่วนตัว', 'url' => ['/profile'], 'active' => FrontendHelper::menuActive("profile", $this->context->route)],
            ['label' =>  'บล็อกของฉัน', 'url' => ['/blog/list'], 'active' => FrontendHelper::menuActive("blog", $this->context->route)],
            ['label' =>  'ออกจากระบบ', 'url' => ['/logout'], 'options' => ['class' => 'nonDisplay--'], 'linkOptions' => ['data-method' => 'post']],
        ],
        'options' => ['class' => 'nav-profile'],
    ];
    }
    
}
echo Nav::widget([
    'options' => ['class' => 'header-menu ml-auto theme-mode'],
    'items' => $menuItems,
]);
echo Nav::widget([
    'options' => ['class' => 'header-menu login'],
    'items' => $menuItemsUser,
    'encodeLabels' => false,
]);
NavBar::end();
?>