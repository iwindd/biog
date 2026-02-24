<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use backend\components\BackendHelper;
/* @var $this yii\web\View */
/* @var $model backend\models\Knowledge */
use backend\models\KnowledgeFile;
use common\components\Upload;
$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'จัดการองค์ความรู้ออนไลน์', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="knowledge-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ลบข้อมูล', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'ต้องการลบองค์ความรู้ใช่หรือไม่',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="userstudent">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">ข้อมูลองค์ความรู้</h3>
                </div>
                <div class="panel-body">

                    <?= DetailView::widget([
                        'model' => $model,
                        'attributes' => [
                            'type',
                            'title',
                            [
                                'format'=>'raw',
                                'attribute'=>'picture_path',
                                'value'=>function($model){
                                    return Upload::readfilePictureNoPermission('knowledge',$model->picture_path);
                                }
                            ],
                            'path',
                            'description:html',
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
                            'created_at',
                            'updated_at',
                        ],
                    ]) ?>

                </div>
            </div>
        </div>
    </div>
 <?php

    $mediaModel = KnowledgeFile::find()->where(['knowledge_id' => $model->id, 'application_type' => 'image'])->all();
    $result = "";
    if (!empty($mediaModel)) {
    ?>

    <div role="tabpanel" class="tab-pane active" id="userstudent">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">รูปภาพทั้งหมด</h3>
            </div>
            <div class="panel-body">

                <div class="row">

                    <?php

                    foreach ($mediaModel as $value) {
                        if (!empty($value['path']) &&  $value['application_type'] == 'image') {
                    ?>
                            <div class="col-md-3 card-picture-item">

                                <?php
                                if ($value['application_type'] == 'image') {
                                    echo Upload::readfilePictureNoPermission('knowledge', $value['path']);
                                }
                                ?>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            <?php
        }
            ?>
            </div>
        </div>
    </div>

    <?php

    $mediaModel = KnowledgeFile::find()->where(['knowledge_id' => $model->id, 'application_type' => 'file'])->all();
    $result = "";
    if (!empty($mediaModel)) {
    ?>

    <div role="tabpanel" class="tab-pane active" id="userstudent">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <h3 class="panel-title">ไฟล์ทั้งหมด</h3>
            </div>
            <div class="panel-body">

                <div class="row">

                    <?php

                    foreach ($mediaModel as $value) {
                        if (!empty($value['path']) &&  $value['application_type'] == 'file') {

                            if ($value['application_type'] == 'file') {
                                $documentPath =  Upload::readFileDocumentNoPermission('knowledge', $value['path']);
                            }
                    ?>
                            <div class="col-md-12 document-list">
                                <a href="/admin/readfile/download-knowledge/<?php echo $value['id'] ?>" target="_blank"> <i class="fa fa-download" aria-hidden="true"></i> <?php echo $value['name'] ?></a>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            
            </div>
        </div>
    </div>

    <?php
        }
    ?>

</div>
