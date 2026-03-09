<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\components\BackendHelper;


$csrf = \yii::$app->request->csrfParam;
$token = \yii::$app->request->csrfToken;


use common\components\Upload;

//start from


/* @var $this yii\web\View */
/* @var $model backend\models\Users */

$this->title = "ข้อมูลผู้ใช้งาน";
$this->params['breadcrumbs'][] = ['label' => 'จัดการผู้ใช้งาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="users-view">

    <h1><?= Html::encode($this->title . ': ' . BackendHelper::getName($model->id)) ?></h1>

    <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    <?= Html::a('ลบข้อมูล', ['delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data' => [
            'confirm' => 'Are you sure you want to delete this item?',
            'method' => 'post',
        ],
    ]) ?>

    <br /><br>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="userstudent">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">ข้อมูลผู้ใช้</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [

                            'username',
                            'email:email',
                            // 'password_hash',
                            // 'auth_key',
                            //'confirmed_at',
                            [
                                'format' => 'raw',
                                'label' => 'บทบาท',
                                'value' => BackendHelper::getRoleName($model->id),
                            ],

                            [
                                'format' => 'html',
                                'label' => 'สถานะการเข้าใช้งาน',
                                'value' => $model->blocked_at == 1 ? "ปิดใช้งาน" : "เปิดใช้งาน",
                            ],

                            // [
                            //     'format' => 'html',
                            //     'label' => 'สถานะยืนยันตัวตน',
                            //     'value' => $model->confirmed_at == 1 ? "ยืนยัน" : "ไม่ได้ยืนยันตัวตน",
                            // ],
                            // 'unconfirmed_email:email',

                            // 'registration_ip',
                            [
                                'format' => 'html',
                                'label' => 'วันที่สร้าง',
                                'value' =>  BackendHelper::getDate($model->created_at),
                            ],

                            [
                                'format' => 'html',
                                'label' => 'วันที่แก้ไขล่าสุด',
                                'value' =>  BackendHelper::getDate($model->updated_at),
                            ],


                            [
                                'format' => 'html',
                                'label' => 'วันที่เข้าใช้งานล่าสุด',
                                'value' =>  BackendHelper::getDate($model->last_login_at),
                            ],

                            [
                                'format' => 'raw',
                                'label' => 'การเชื่อมต่อ ThaID',
                                'value' => function($model) {
                                    $userThaid = \common\models\UserThaid::findOne(['user_id' => $model->id]);
                                    if ($userThaid) {
                                        return '<span class="label label-success" style="font-size: 14px; padding: 5px 10px;">
                                                    <i class="glyphicon glyphicon-ok-sign"></i> เชื่อมต่อกับ ThaID แล้ว
                                                </span> ' . 
                                               Html::a('ยกเลิกการเชื่อมต่อ', ['/thaid/disconnect', 'id' => $model->id], [
                                                   'class' => 'btn btn-danger btn-xs',
                                                   'style' => 'margin-left: 10px;',
                                                   'data' => [
                                                       'confirm' => 'คุณต้องการยกเลิกการเชื่อมต่อบัญชีกับ ThaID ใช่หรือไม่?',
                                                       'method' => 'post',
                                                   ],
                                               ]);
                                    } else {
                                        if ($model->id == Yii::$app->user->id) {
                                            return Html::a(' เชื่อมต่อด้วย ThaID', ['/thaid/auth'], [
                                                'class' => 'btn btn-default',
                                                'style' => 'background-color: #004d99; color: white; border: none;'
                                            ]);
                                        }
                                        return '<span class="text-muted">ยังไม่ได้เชื่อมต่อ</span>';
                                    }
                                }
                            ],
                        ],
                    ]) ?>

                </div>
            </div>



        </div>

        <div role="tabpanel" class="tab-pane active" id="userstudent">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">ข้อมูลส่วนตัวผู้ใช้</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $profile,
                        'attributes' => [
                            [
                                'format' => 'html',
                                'label' => 'ชื่อ-นามสกุล',
                                'value' => $profile->firstname . " " . $profile->lastname
                            ],

                            // [
                            //     'format'=>'html',
                            //     'label' => 'วันที่เกิด',
                            //     'value' => $profile->birthday
                            // ],

                            // [
                            //     'format'=>'html',
                            //     'label' => 'ที่อยู่',
                            //     'value' => $profile->address
                            // ],

                            // [
                            //     'format'=>'html',
                            //     'label' => 'เพศ',
                            //     'value' => $profile->gender==1?  "ชาย" : "หญิง"
                            // ],

                            // [
                            //     'format'=>'html',
                            //     'label' => 'เบอร์โทรศัพท์',
                            //     'value' => ucfirst($profile->phone)
                            // ],

                            // [
                            //     'format'=>'html',
                            //     'label' => 'เลขบัตรประจำตัวประชาชน',
                            //     'value' => ucfirst($profile->idcard)
                            // ],





                        ],
                    ]) ?>

                </div>
            </div>



        </div>

        <?php /*
        <div role="tabpanel" class="tab-pane active" id="userstudent">
            <div class="panel panel-primary">
                <div class="panel-heading"><h3 class="panel-title">ชุมชนที่ดูแล</h3></div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $userCommu,
                        'attributes' => [
                            [
                                'format'=>'html',
                                'label' => 'ชื่อชุมชนที่ดูแล',
                                'value' => empty($userCommu->name_th)? "":$userCommu->name_th
                            ],

                        ],
                    ]) ?>

                </div>
            </div>

        </div> */ ?>

        <?php if(!empty($profile->file_key)){ ?>
        <div role="tabpanel" class="tab-pane active" id="userstudent">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">รูปภาพ</h3>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3">
                            <?php if (!empty($profile->file_key)) { ?>

                                <?php echo Upload::readfilePicture('profile', $profile->file_key); ?>

                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>



</div>