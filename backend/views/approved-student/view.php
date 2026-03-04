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

$this->title = "ข้อมูลนักเรียน";
$this->params['breadcrumbs'][] = ['label' => 'อนุมัตินักเรียน', 'url' => ['index']];
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
                    <h3 class="panel-title">ข้อมูลนักเรียน</h3>
                </div>
                <div class="panel-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [

                            'username',
                            'email:email',
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


                        ],
                    ]) ?>

                </div>
            </div>



        </div>

        <div role="tabpanel" class="tab-pane active" id="userstudentprofile">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">ข้อมูลส่วนตัวนักเรียน</h3>
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

                        ],
                    ]) ?>

                </div>
            </div>



        </div>

        <?php if(!empty($profile->file_key)){ ?>
        <div role="tabpanel" class="tab-pane active" id="userstudentpic">
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
