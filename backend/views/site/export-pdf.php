<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $contentList array */
/* @var $userRoleList array */
/* @var $schoolRegionList array */

// ฟอนต์ภาษาไทยสำหรับ mPDF (Garuda, Kinnari) จะถูกกำหนดในตั้งค่า kartik\mpdf\Pdf ใน Controller แล้ว

?>
<div class="pdf-export-content">
    <h2>รายงานสถิติ Dashboard</h2>
    <div class="date-info">วันที่ออกรายงาน: <?= date('d/m/Y H:i') ?></div>

    <h4>สถิติจำนวนเรื่องตามประเภทเนื้อหา</h4>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;" class="text-center">ลำดับ</th>
                <th style="width: 60%;">ประเภทเนื้อหา</th>
                <th style="width: 30%;" class="text-center">จำนวน (เรื่อง)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $total = 0;
            $index = 1;
            foreach ($contentList as $item): 
                $total += $item['count'];
            ?>
                <tr>
                    <td class="text-center"><?= $index++ ?></td>
                    <td><?= Html::encode($item['category']) ?></td>
                    <td class="text-center"><?= number_format($item['count']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right" style="font-weight: bold; background-color: #f5f5f5;">รวมทั้งหมด</td>
                <td class="text-center" style="font-weight: bold; background-color: #f5f5f5;"><?= number_format($total) ?></td>
            </tr>
        </tfoot>
    </table>

    <div style="page-break-before: always;"></div>
    <h4>สถิติผู้ใช้งานตาม Role</h4>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;" class="text-center">ลำดับ</th>
                <th style="width: 60%;">Role</th>
                <th style="width: 30%;" class="text-center">จำนวนผู้ใช้งาน</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalUsers = 0;
            $index = 1;
            foreach ($userRoleList as $item):
                $totalUsers += $item['count'];
            ?>
                <tr>
                    <td class="text-center"><?= $index++ ?></td>
                    <td><?= Html::encode($item['role']) ?></td>
                    <td class="text-center"><?= number_format($item['count']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right" style="font-weight: bold; background-color: #f5f5f5;">รวมทั้งหมด</td>
                <td class="text-center" style="font-weight: bold; background-color: #f5f5f5;"><?= number_format($totalUsers) ?></td>
            </tr>
        </tfoot>
    </table>

    <div style="page-break-before: always;"></div>
    <h4>จำนวนโรงเรียนแยกตามภาค</h4>
    <table>
        <thead>
            <tr>
                <th style="width: 10%;" class="text-center">ลำดับ</th>
                <th style="width: 60%;">ภาค</th>
                <th style="width: 30%;" class="text-center">จำนวนโรงเรียน</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $totalSchools = 0;
            $index = 1;
            foreach ($schoolRegionList as $item):
                $schoolCount = (int)$item['school_count'];
                $totalSchools += $schoolCount;
            ?>
                <tr>
                    <td class="text-center"><?= $index++ ?></td>
                    <td><?= Html::encode($item['region_name']) ?></td>
                    <td class="text-center"><?= number_format($schoolCount) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2" class="text-right" style="font-weight: bold; background-color: #f5f5f5;">รวมทั้งหมด</td>
                <td class="text-center" style="font-weight: bold; background-color: #f5f5f5;"><?= number_format($totalSchools) ?></td>
            </tr>
        </tfoot>
    </table>

</div>
