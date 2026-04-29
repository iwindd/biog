<?php

use backend\components\BackendHelper;
use backend\models\Content;
use kartik\date\DatePicker;
use miloschuman\highcharts\Highcharts;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Dashboard';

?>
<div class="site-index">

    <!-- Main content -->
    <section class="content">
        <!-- Actions -->
        <section style="margin: 0 0 15px 0; display:flex; justify-content: flex-end;">
            <a href="<?= Url::to(['/site/export-pdf']) ?>" target="_blank" class="btn btn-primary" title="Export to PDF"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
        </section>

        <!-- Info boxes -->

        <?php
        $css = <<<CSS
            .custom-stat-card {
                background: #ffffff;
                border-radius: 16px;
                padding: 24px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.03);
                margin-bottom: 25px;
                display: flex;
                align-items: center;
                position: relative;
                overflow: hidden;
                transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
                border: 1px solid rgba(0,0,0,0.05);
                text-decoration: none !important;
                color: inherit !important;
                cursor: pointer;
            }
            .custom-stat-card:hover, .custom-stat-card:focus {
                transform: translateY(-5px);
                box-shadow: 0 12px 25px rgba(0,0,0,0.08);
                text-decoration: none !important;
                color: inherit !important;
            }
            .custom-stat-card::before {
                content: '';
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 6px;
                background: var(--stat-color, #333);
                border-radius: 16px 0 0 16px;
            }
            .custom-stat-icon {
                width: 65px;
                height: 65px;
                border-radius: 16px;
                background: var(--stat-bg, #eee);
                color: var(--stat-color, #333);
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 30px;
                margin-right: 20px;
                transition: all 0.3s ease;
                box-shadow: 0 4px 10px var(--stat-shadow, rgba(0,0,0,0.1));
            }
            .custom-stat-card:hover .custom-stat-icon {
                transform: scale(1.1) rotate(5deg);
            }
            .custom-stat-content {
                flex: 1;
            }
            .custom-stat-title {
                font-size: 15px;
                color: #6c757d;
                font-weight: 700;
                margin-bottom: 5px;
                font-family: inherit;
            }
            .custom-stat-value {
                font-size: 32px;
                font-weight: 800;
                color: #2c3e50;
                margin: 0;
                line-height: 1;
                display: flex;
                align-items: baseline;
                gap: 8px;
            }
            .custom-stat-unit {
                font-size: 14px;
                font-weight: 600;
                color: #a0aab5;
            }
            /* สกินสี */
            .stat-plant { --stat-color: #2ecc71; --stat-bg: #eafaf1; --stat-shadow: rgba(46, 204, 113, 0.2); }
            .stat-animal { --stat-color: #f39c12; --stat-bg: #fef5e7; --stat-shadow: rgba(243, 156, 18, 0.2); }
            .stat-micro { --stat-color: #9b59b6; --stat-bg: #f5eef8; --stat-shadow: rgba(155, 89, 182, 0.2); }
            .stat-wisdom { --stat-color: #3498db; --stat-bg: #ebf5fb; --stat-shadow: rgba(52, 152, 219, 0.2); }
            .stat-eco { --stat-color: #00bcd4; --stat-bg: #e0f7fa; --stat-shadow: rgba(0, 188, 212, 0.2); }
            .stat-product { --stat-color: #e74c3c; --stat-bg: #fdedec; --stat-shadow: rgba(231, 76, 60, 0.2); }
CSS;
        $this->registerCss($css);
        ?>

        <div class="row">
            <!-- พืช -->
            <div class="col-md-4 col-sm-6 col-xs-12">
                <a href="<?= Url::to(['/content-plant']) ?>" class="custom-stat-card stat-plant">
                    <div class="custom-stat-icon">
                        <i class="fa fa-leaf"></i>
                    </div>
                    <div class="custom-stat-content">
                        <div class="custom-stat-title">พืช</div>
                        <div class="custom-stat-value">
                            <?php echo number_format(Content::find()->where(['active' => 1, 'type_id' => 1, 'status' => 'approved'])->count()); ?>
                            <span class="custom-stat-unit">เรื่อง</span>
                        </div>
                    </div>
                </a>
            </div>
            
            <!-- สัตว์ -->
            <div class="col-md-4 col-sm-6 col-xs-12">
                <a href="<?= Url::to(['/content-animal']) ?>" class="custom-stat-card stat-animal">
                    <div class="custom-stat-icon">
                        <i class="fa fa-paw"></i>
                    </div>
                    <div class="custom-stat-content">
                        <div class="custom-stat-title">สัตว์</div>
                        <div class="custom-stat-value">
                            <?php echo number_format(Content::find()->where(['active' => 1, 'type_id' => 2, 'status' => 'approved'])->count()); ?>
                            <span class="custom-stat-unit">เรื่อง</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- จุลินทรีย์ -->
            <div class="col-md-4 col-sm-6 col-xs-12">
                <a href="<?= Url::to(['/content-fungi']) ?>" class="custom-stat-card stat-micro">
                    <div class="custom-stat-icon">
                        <i class="fa fa-bug"></i>
                    </div>
                    <div class="custom-stat-content">
                        <div class="custom-stat-title">จุลินทรีย์</div>
                        <div class="custom-stat-value">
                            <?php echo number_format(Content::find()->where(['active' => 1, 'type_id' => 3, 'status' => 'approved'])->count()); ?>
                            <span class="custom-stat-unit">เรื่อง</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- ภูมิปัญญา/ปราชญ์ -->
            <div class="col-md-4 col-sm-6 col-xs-12">
                <a href="<?= Url::to(['/content-expert']) ?>" class="custom-stat-card stat-wisdom">
                    <div class="custom-stat-icon">
                        <i class="fa fa-graduation-cap"></i>
                    </div>
                    <div class="custom-stat-content">
                        <div class="custom-stat-title">ภูมิปัญญา/ปราชญ์</div>
                        <div class="custom-stat-value">
                            <?php echo number_format(Content::find()->where(['active' => 1, 'type_id' => 4, 'status' => 'approved'])->count()); ?>
                            <span class="custom-stat-unit">เรื่อง</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- ท่องเที่ยวเชิงนิเวศ -->
            <div class="col-md-4 col-sm-6 col-xs-12">
                <a href="<?= Url::to(['/content-ecotourism']) ?>" class="custom-stat-card stat-eco">
                    <div class="custom-stat-icon">
                        <i class="fa fa-map-signs"></i>
                    </div>
                    <div class="custom-stat-content">
                        <div class="custom-stat-title">ท่องเที่ยวเชิงนิเวศ</div>
                        <div class="custom-stat-value">
                            <?php echo number_format(Content::find()->where(['active' => 1, 'type_id' => 5, 'status' => 'approved'])->count()); ?>
                            <span class="custom-stat-unit">เรื่อง</span>
                        </div>
                    </div>
                </a>
            </div>

            <!-- ผลิตภัณฑ์ชุมชน -->
            <div class="col-md-4 col-sm-6 col-xs-12">
                <a href="<?= Url::to(['/content-product']) ?>" class="custom-stat-card stat-product">
                    <div class="custom-stat-icon">
                        <i class="fa fa-shopping-bag"></i>
                    </div>
                    <div class="custom-stat-content">
                        <div class="custom-stat-title">ผลิตภัณฑ์ชุมชน</div>
                        <div class="custom-stat-value">
                            <?php echo number_format(Content::find()->where(['active' => 1, 'type_id' => 6, 'status' => 'approved'])->count()); ?>
                            <span class="custom-stat-unit">เรื่อง</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>
    
    <!-- Chart content -->
    <section class="row" style="margin: 0  ">
        <!-- สถิติจำนวนเรื่องตามประเภทเนื้อหา -->
        <div class="col-md-12 col-lg-6">
            <div class="box">
                <div class="box-header with-border">
                <h3 class="box-title">สถิติจำนวนเรื่องตามประเภทเนื้อหา</h3>

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
                        <!-- Bar Chart Canvas -->
                        <?php
                        echo Highcharts::widget([
                            'options' => [
                                'chart' => [
                                    'type' => 'column'
                                ],
                                'title' => ['text' => 'จำนวนเรื่องทั้งหมดในแต่ละประเภทเนื้อหา'],
                                'xAxis' => [
                                    'categories' => $chartCategories,
                                    'crosshair' => true
                                ],
                                'yAxis' => [
                                    'min' => 0,
                                    'title' => [
                                        'text' => 'จำนวน (เรื่อง)'
                                    ]
                                ],
                                'plotOptions' => [
                                    'column' => [
                                        'pointPadding' => 0.2,
                                        'borderWidth' => 0,
                                        'colorByPoint' => true
                                    ]
                                ],
                                'series' => [
                                    [
                                        'name' => 'จำนวนเรื่อง',
                                        'data' => $chartSeriesData,
                                        'showInLegend' => false
                                    ]
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
        </div>
        <!-- สถิติผู้ใช้งาน -->
        <div class="col-md-6 col-lg-3">
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
                                    [  // new opening bracket
                                        'type' => 'pie',
                                        'name' => 'จำนวน',
                                        'data' => $data
                                    ]  // new closing bracket
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
        </div>
        <!-- สถิติโรงเรียนตามภาค -->
        <div class="col-md-6 col-lg-3">
            <div class="box">
                <div class="box-header with-border">
                <h3 class="box-title">สถิติโรงเรียน</h3>

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
                        <?php
                        echo Highcharts::widget([
                            'options' => [
                                'chart' => [
                                    'type' => 'bar'
                                ],
                                'title' => ['text' => 'จำนวนโรงเรียนแยกตามภาค'],
                                'xAxis' => [
                                    'categories' => $schoolRegionCategories,
                                    'title' => ['text' => null]
                                ],
                                'yAxis' => [
                                    'min' => 0,
                                    'allowDecimals' => false,
                                    'title' => [
                                        'text' => 'จำนวนโรงเรียน'
                                    ]
                                ],
                                'legend' => [
                                    'enabled' => false
                                ],
                                'series' => [
                                    [
                                        'name' => 'โรงเรียน',
                                        'data' => $schoolRegionSeriesData,
                                        'color' => '#3c8dbc'
                                    ]
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
        </div>
    </section>
</div>


<?php

$this->registerJs("

        \$('td').click(function (e) {
            var id = \$(this).closest('tr').data('id');
            if(id){
                if(e.target == this)
                    location.href = '" . Url::to(['/learningcenter/']) . "/' + id;
            }
        });

    ");
?>
