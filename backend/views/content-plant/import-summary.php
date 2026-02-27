<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $data array */

$this->title = 'ตรวจสอบข้อมูลที่นำเข้า';
$this->params['breadcrumbs'][] = ['label' => 'จัดการข้อมูลพืช', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'นำเข้าข้อมูล', 'url' => ['import']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="import-summary">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="alert alert-info">
        พบข้อมูลทั้งหมด <?= count($data) ?> รายการ กรุณาตรวจสอบความถูกต้องก่อนกดบันทึก
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ชื่อเรื่อง</th>
                    <th>พิกัด</th>
                    <th>ที่อยู่</th>
                    <th>สถานะ</th>
                    <th>ปัญหา</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $index => $item): ?>
                    <?php
                        $errors = [];
                        if (empty($item['name'])) {
                            $errors[] = "ไม่มีชื่อเรื่อง";
                        }
                        if (empty($item['latitude']) || empty($item['longitude'])) {
                            $errors[] = "พิกัดไม่ถูกต้อง";
                        }
                        if (empty($item['province_id'])) {
                            $errors[] = "ไม่พบรหัสจังหวัด";
                        }
                    ?>
                    <tr class="<?= !empty($errors) ? 'danger' : '' ?>">
                        <td><?= $index + 1 ?></td>
                        <td><?= Html::encode($item['name']) ?></td>
                        <td><?= Html::encode($item['coord']) ?></td>
                        <td>
                            <?= Html::encode($item['subdistrict'] ?: '-') ?>, 
                            <?= Html::encode($item['district'] ?: '-') ?>, 
                            <?= Html::encode($item['province'] ?: '-') ?> 
                            <?= Html::encode($item['zipcode'] ?: '') ?>
                        </td>
                        <td><?= Html::encode($item['status']) ?></td>
                        <td>
                            <?php if (!empty($errors)): ?>
                                <ul class="text-danger">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?= $error ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <span class="text-success">ปกติ</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="form-group">
        <?php if (count($data) > 0): ?>
            <?= Html::a('ยืนยันบันทึกเป็นฉบับร่าง', ['import-confirm'], [
                'class' => 'btn btn-success',
                'data' => [
                    'confirm' => 'คุณต้องการบันทึกข้อมูลทั้งหมด ' . count($data) . ' รายการเป็นฉบับร่างใช่หรือไม่?',
                    'method' => 'post',
                ],
            ]) ?>
        <?php endif; ?>
        <?= Html::a('ยกเลิกและทำใหม่', ['import'], ['class' => 'btn btn-default']) ?>
    </div>

</div>
