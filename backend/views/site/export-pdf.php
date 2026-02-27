<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $contentList array */

// ฟอนต์ภาษาไทยสำหรับ mPDF (Garuda, Kinnari) จะถูกกำหนดในตั้งค่า kartik\mpdf\Pdf ใน Controller แล้ว

?>
<div class="pdf-export-content">
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

</div>
