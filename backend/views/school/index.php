<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SchoolSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'จัดการโรงเรียน';
$this->params['breadcrumbs'][] = $this->title;


$url = "";
if(!empty($_GET['SchoolSearch'])){
    if(!empty($_GET['SchoolSearch']['name'])){
        $url = "?name=".$_GET['SchoolSearch']['name'];
    }

    if(!empty($_GET['SchoolSearch']['phone'])){
        if (!empty($url)) {
            $url = $url."&phone=".$_GET['SchoolSearch']['phone'];
        }else{
            $url = "?phone=".$_GET['SchoolSearch']['phone'];
        }
    }

    if(!empty($_GET['SchoolSearch']['email'])){
        if (!empty($url)) {
            $url = $url."&email=".$_GET['SchoolSearch']['email'];
        }else{
            $url = "?email=".$_GET['SchoolSearch']['email'];
        }
    }



    if(!empty($_GET['SchoolSearch']['address'])){
        if (!empty($url)) {
            $url = $url."&address=".$_GET['SchoolSearch']['address'];
        }else{
            $url = "?address=".$_GET['SchoolSearch']['address'];
        }
    }

    if(!empty($_GET['SchoolSearch']['updated_at'])){
        if (!empty($url)) {
            $url = $url."&updated_at=".$_GET['SchoolSearch']['updated_at'];
        }else{
            $url = "?updated_at=".$_GET['SchoolSearch']['updated_at'];
        }
    }
    
}

?>
<div class="school-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('เพิ่มโรงเรียนใหม่', ['create'], ['class' => 'btn btn-success']) ?>

        <?= Html::a(  Html::img('/images/csv.png', ['class' => 'csv-export']).'Export CSV', ['export'.$url], ['class' => 'btn btn-info export-bakcground f-right ','title' => 'Export CSV']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>
    <div class="table-responsive">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'rowOptions'   => function ($model, $key, $index, $grid) {
            return ['data-id' => $model->id];
        },
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'name',
            'phone',
            'email:email',
            'address',
            //'province_id',
            //'subdistrict_id',
            //'district_id',
            //'created_at',
            [
                'label' => 'วันที่แก้ไขล่าสุด',
                'attribute' => 'updated_at',
                'filter' => DateRangePicker::widget([
                    'model' => $searchModel,
                    'attribute' => 'updated_at',
                    'convertFormat'=>true,
                    //'useWithAddon'=>true,
                    'pluginOptions'=>[
                        'locale'=>[
                            'format'=>'Y-m-d',
                            'separator'=>' to ',
                        ],
                        'opens'=>'left'
                    ]
                ]),
                'value' => function ($model, $key, $index, $column) {
                    return $model->updated_at;
                }
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    </div>


</div>


<?php 

    $this->registerJs("

        $('td').click(function (e) {
            var id = $(this).closest('tr').data('id');
            if(id){
                if(e.target == this)
                    location.href = '" . Url::to(['/school/']) . "/' + id;
            }
        });

    ");
?>
