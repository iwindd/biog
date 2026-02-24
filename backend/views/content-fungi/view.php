<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\components\BackendHelper;
use frontend\components\FrontendHelper;
use yii\helpers\Url;
use frontend\components\GoogleMapHelper;
$this->registerJsFile(Url::base().'/js/map-view.js', ['depends' => \yii\web\JqueryAsset::className()]);
$this->registerJsFile(GoogleMapHelper::getGoogleMapApiUrl(), ['depends' => \yii\web\JqueryAsset::className(), 'async' => true, 'defer' => true]);

/* @var $this yii\web\View */
/* @var $model backend\models\Content */
use common\components\Upload;
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'จัดการข้อมูลจุลินทรีย์', 'url' => ['index']];
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
                'format'=>'raw',
                'attribute'=>'picture_path',
                'value'=>function($model){
                    return Upload::readfilePictureNoPermission('content-fungi',$model->picture_path);
                }
            ],
            'name',
            [
                'label'=>'ชื่ออื่น',
                'value'=>$modelContent->other_name,
            ],
            [
                'label'=>'ชื่อสามัญ',
                'value'=>$modelContent->common_name,
            ],
            [
                'label'=>'ชื่อวิทยาศาสตร์',
                'value'=>$modelContent->scientific_name,
            ],
            [
                'label'=>'ชื่อวงศ์',
                'value'=>$modelContent->family_name,
            ],
            [
                'format'=>'html',
                'label'=>'ลักษณะ/คุณสมบัติ',
                'value'=>$modelContent->features,
            ],
            [
                'format'=>'html',
                'label'=>'ประโยชน์',
                'value'=>$modelContent->benefit,
            ],
            [
                'format'=>'html',
                'label'=>'ศักยภาพการใช้งานในชุมชน',
                'value'=>$modelContent->ability,
            ],

            [
                'format'=>'html',
                'label'=>'ฤดูกาลที่ใช้ประโยชน์ได้',
                'value'=>$modelContent->season,
            ],
            // [
            //     'format'=>'html',
            //     'attribute'=>'description',
            //     'value'=>$model->description,
            // ],
            [
                'format'=>'html',
                'attribute'=>'other_information',
                'value'=>$modelContent->other_information,
            ],
            [
                'label'=>'แหล่งที่พบ',
                'value'=>$modelContent->found_source,
            ],
            'latitude',
            'longitude',
            [
                'label'=>'แผนที่',
                'format' => 'raw',
                'value' => function(){
                    return '<div class="col-12 map-block">
                                <div id="content-google-map" class="content-google-map" style="min-height:400px;"></div>
                            </div>';
                }

            ],
            [
                'attribute'=>'region_id',
                'value'=>function($model){
                    return BackendHelper::getNameRegion($model->region_id);
                }
            ],
             [
                'attribute'=>'province_id',
                'value'=>function($model){
                    return BackendHelper::getNameProvince($model->province_id);
                }
            ],
             [
                'attribute'=>'subdistrict_id',
                'value'=>function($model){
                    return BackendHelper::getNameSubdistrict($model->subdistrict_id);
                }
            ],
             [
                'attribute'=>'district_id',
                'value'=>function($model){
                    return BackendHelper::getNameDistrict($model->district_id);
                }
            ],
            [
                'attribute'=>'zipcode_id',
                'value'=>function($model){
                    return BackendHelper::getNameZipcode($model->zipcode_id);
                }
            ],

            [
                'format'=>'raw',
                'attribute'=>'photo_credit',
                'value'=> function($model){
                    return FrontendHelper::getSourceInformation($model["photo_credit"]);
                }
            ],
            [
                'format'=>'raw',
                'attribute'=>'source_information',
                'label'=>'แหล่งที่มาของข้อมูล',
                'value'=> function($model){
                    return FrontendHelper::getSourceInformation($model["source_information"]);
                }
            ],

            [
                'attribute'=>'approved_by_user_id',
                'value'=>function($model){
                    return BackendHelper::getName($model->approved_by_user_id);
                }
            ],
            [
                'attribute'=>'created_by_user_id',
                'value'=>function($model){
                    return BackendHelper::getName($model->created_by_user_id);
                }
            ],
            [
                'attribute'=>'updated_by_user_id',
                'value'=>function($model){
                    return BackendHelper::getName($model->updated_by_user_id);
                }
            ],
            'note',
            [
                'attribute'=>'status',
                'value'=>function($model){
                    return ucfirst($model->status);
                }
            ],
            //'active',
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>
