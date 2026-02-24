<?php

use miloschuman\highcharts\Highcharts;
use backend\components\BackendHelper;

use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use kartik\date\DatePicker;

use backend\models\Content;


/* @var $this yii\web\View */

$this->title = 'Dashboard';

?>
<div class="site-index">

    <!-- Main content -->
    <section class="content">
        <!-- Info boxes -->

        <div class="row">
            <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <div class="info-box-content">
                <span class="info-box-text">พืช</span>
                <span class="info-box-number text-b"><span>
                    <?php echo number_format(Content::find()->where(['active' => 1, 'type_id' => 1, 'status' => 'approved' ])->count()); ?>
                </span> &nbsp;&nbsp;<small> เรื่อง </small></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-4 col-sm-6 col-xs-12">
            <div class="info-box">
                <div class="info-box-content">
                <span class="info-box-text">สัตว์</span>
                <span class="info-box-number text-or"><span>
                    <?php echo number_format(Content::find()->where(['active' => 1, 'type_id' => 2, 'status' => 'approved'])->count()); ?>
                </span> &nbsp;&nbsp;<small> เรื่อง </small></span>
                </div>
                <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>
            <!-- /.col -->
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="info-box">
                    <div class="info-box-content">
                    <span class="info-box-text">จุลินทรีย์</span>
                    <span class="info-box-number text-gr"><span><?php echo number_format(Content::find()->where(['active' => 1, 'type_id' => 3, 'status' => 'approved'])->count()); ?></span> &nbsp;&nbsp;<small> เรื่อง </small></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>
            <!-- /.col -->
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="info-box">
                    <div class="info-box-content">
                    <span class="info-box-text">ภูมิปัญญา/ปราชญ์</span>
                    <span class="info-box-number text-gr"><span><?php echo number_format(Content::find()->where(['active' => 1, 'type_id' => 4, 'status' => 'approved'])->count()); ?></span> &nbsp;&nbsp;<small> เรื่อง </small></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <div class="clearfix visible-sm-block"></div>
            <!-- /.col -->
            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="info-box">
                    <div class="info-box-content">
                    <span class="info-box-text">ท่องเที่ยวเชิงนิเวศ</span>
                    <span class="info-box-number text-gr"><span><?php echo number_format(Content::find()->where(['active' => 1, 'type_id' => 5, 'status' => 'approved'])->count()); ?></span> &nbsp;&nbsp;<small> เรื่อง </small></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="info-box">
                    <div class="info-box-content">
                    <span class="info-box-text">ผลิตภัณฑ์ชุมชน</span>
                    <span class="info-box-number text-rd"><span><?php echo number_format(Content::find()->where(['active' => 1, 'type_id' => 6, 'status' => 'approved'])->count()); ?></span> &nbsp;&nbsp; <small> เรื่อง </small></span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                <h3 class="box-title">สถิติผู้ใช้งาน</h3>

                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                    
                    <div class="chart">
                        <!-- Sales Chart Canvas -->
                        <?php 
                        echo Highcharts::widget([
                            'options' => [
                                'title' => ['text' => 'จำนวนผู้ใช้งานตาม Role'],
                                'plotOptions' => [
                                    'pie' => [
                                        'cursor' => 'pointer',
                                    ],
                                ],
                                'series' => [
                                    [ // new opening bracket
                                        'type' => 'pie',
                                        'name' => 'จำนวน',
                                        'data' => $data
                                    ] // new closing bracket
                                ],
                            ],
                        ]);
                        ?>
                    </div>
                    <!-- /.chart-responsive -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
                </div>
                <!-- ./box-body -->
            
            </div>
            <!-- /.box -->
            </div>
            <!-- /.col -->
        </div> 


    </section>


</div>


<?php 

    $this->registerJs("

        $('td').click(function (e) {
            var id = $(this).closest('tr').data('id');
            if(id){
                if(e.target == this)
                    location.href = '" . Url::to(['/learningcenter/']) . "/' + id;
            }
        });

    ");
?>