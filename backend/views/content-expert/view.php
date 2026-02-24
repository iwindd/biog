<?php

use backend\components\BackendHelper;
use frontend\components\FrontendHelper;
use frontend\components\GoogleMapHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;

$this->registerJsFile(Url::base() . '/js/map-view.js', ['depends' => \yii\web\JqueryAsset::className()]);
$this->registerJsFile(GoogleMapHelper::getGoogleMapApiUrl(), ['depends' => \yii\web\JqueryAsset::className(), 'async' => true, 'defer' => true]);

/* @var $this yii\web\View */

/* @var $model backend\models\Content */
use common\components\Upload;

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'จัดการข้อมูลภูมิปัญญา/ปราชญ์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="content-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ลบออก', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'ต้องการลบเนื้อหานี้ใช่หรือไม่',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <input type="hidden" id="content-latitude" value="<?php echo $model['latitude']; ?>">
    <input type="hidden" id="content-longitude" value="<?php echo $model['longitude']; ?>">

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'หมวดหมู่ภูมิปัญญา/ปราชญ์',
                'value' => BackendHelper::getNameCategoryExpert($modelContent->expert_category_id),
            ],
            [
                'format' => 'raw',
                'attribute' => 'picture_path',
                'value' => function ($model) {
                    return Upload::readfilePictureNoPermission('content-expert', $model->picture_path);
                }
            ],
            'name',
            [
                'label' => 'ชื่อ ผู้รู้/ปราชญ์ - นามสกุล ผู้รู้/ปราชญ์',
                'value' => $modelContent->expert_firstname . ' ' . $modelContent->expert_lastname,
            ],
            [
                'label' => 'วัน/เดือน/ปีเกิด ของผู้รู้/ปราชญ์',
                'value' => $modelContent->expert_birthdate,
            ],
            [
                'format' => 'html',
                'label' => 'ภูมิปัญญาที่เชี่ยวชาญ',
                'value' => $modelContent->expert_expertise,
            ],
            [
                'format' => 'html',
                'label' => 'รายละเอียด',
                'value' => $model->description,
            ],
            [
                'format' => 'html',
                'label' => 'ข้อมูลอื่น ๆ ที่ฉันรู้',
                'value' => $model->other_information,
            ],
            [
                'format' => 'html',
                'label' => 'อาชีพ ผู้รู้/ปราชญ์',
                'value' => $modelContent->expert_occupation,
            ],
            [
                'format' => 'html',
                'label' => 'เลขบัตรประชาชน ผู้รู้/ปราชญ์',
                'value' => $modelContent->expert_card_id,
            ],
            [
                'attribute' => 'โทรศัพท์ ผู้รู้/ปราชญ์',
                'value' => $modelContent->phone,
            ],
            [
                'label' => 'ที่อยู่',
                'value' => $modelContent->address,
            ],
            'latitude',
            'longitude',
            [
                'label' => 'แผนที่',
                'format' => 'raw',
                'value' => function () {
                    return '<div class="col-12 map-block">
                                <div id="content-google-map" class="content-google-map" style="min-height:400px;"></div>
                            </div>';
                }
            ],
            [
                'attribute' => 'region_id',
                'value' => function ($model) {
                    return BackendHelper::getNameRegion($model->region_id);
                }
            ],
            [
                'attribute' => 'province_id',
                'value' => function ($model) {
                    return BackendHelper::getNameProvince($model->province_id);
                }
            ],
            [
                'attribute' => 'subdistrict_id',
                'value' => function ($model) {
                    return BackendHelper::getNameSubdistrict($model->subdistrict_id);
                }
            ],
            [
                'attribute' => 'district_id',
                'value' => function ($model) {
                    return BackendHelper::getNameDistrict($model->district_id);
                }
            ],
            [
                'attribute' => 'zipcode_id',
                'value' => function ($model) {
                    return BackendHelper::getNameZipcode($model->zipcode_id);
                }
            ],
            [
                'format' => 'raw',
                'attribute' => 'photo_credit',
                'value' => function ($model) {
                    return FrontendHelper::getSourceInformation($model['photo_credit']);
                }
            ],
            [
                'format' => 'raw',
                'attribute' => 'source_information',
                'label' => 'แหล่งที่มาของข้อมูล',
                'value' => function ($model) {
                    return FrontendHelper::getSourceInformation($model['source_information']);
                }
            ],
            [
                'attribute' => 'approved_by_user_id',
                'value' => function ($model) {
                    return BackendHelper::getName($model->approved_by_user_id);
                }
            ],
            [
                'attribute' => 'created_by_user_id',
                'value' => function ($model) {
                    return BackendHelper::getName($model->created_by_user_id);
                }
            ],
            [
                'attribute' => 'updated_by_user_id',
                'value' => function ($model) {
                    return BackendHelper::getName($model->updated_by_user_id);
                }
            ],
            'note',
            [
                'format' => 'html',
                'attribute' => 'status',
                'value' => function ($model) {
                    if ($model->status == 'pending') {
                        return "<span class='label label-warning'>Pending</span>";
                    } elseif ($model->status == 'approved') {
                        return "<span class='label label-success'>Approved</span>";
                    } elseif ($model->status == 'rejected') {
                        return "<span class='label label-danger'>Rejected</span>";
                    }
                }
            ],
            [
                'format' => 'html',
                'attribute' => 'is_hidden',
                'value' => function ($model) {
                    if ($model->is_hidden == '0') {
                        return "<span class='label label-success'>แสดงผล</span>";
                    } elseif ($model->is_hidden == '1') {
                        return "<span class='label label-warning'>ซ่อน</span>";
                    }
                }
            ],
            [
                'attribute' => 'license_id',
                'value' => function ($model) {
                    return $model->license ? $model->license->name : '-';
                }
            ],
            // 'active',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
