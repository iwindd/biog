<?php
//include ("Classes/PHPExcel.php");
use backend\components\BackendHelper;
use frontend\components\FrontendHelper;

use yii\db\Query;




// สร้าง object ของ Class  PHPExcel  ขึ้นมาใหม่
$objPHPExcel = new PHPExcel();

// กำหนดค่าต่างๆ
$objPHPExcel->getProperties()->setCreator("Company Co., Ltd.");
$objPHPExcel->getProperties()->setLastModifiedBy("Company Co., Ltd.");
$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX ReportQuery Document");
$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX ReportQuery Document");
$objPHPExcel->getProperties()->setDescription("ReportQuery from Company Co., Ltd.");

$protocol = stripos($_SERVER['SERVER_PROTOCOL'], 'https') === true ? 'https://' : 'http://';
$hostname = $_SERVER['HTTP_HOST'];
if (empty($hostname)) {
    $hostname = "localhost:8080";
}

$sheet = $objPHPExcel->getActiveSheet();
$pageMargins = $sheet->getPageMargins();

$columnCharacter = array('A','B','C','D','E','F','G','H','I','J','K','L','M');

// margin is set in inches (0.5cm)
$margin = 0.5 / 2.54;

$pageMargins->setTop($margin);
$pageMargins->setBottom($margin);
$pageMargins->setLeft($margin);
$pageMargins->setRight(0);

$styleContentHeader = array(

        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
        ),
		'font'  => array(
            'bold'  => true,
            'size'  => 12,
            'name'  => 'Angsana New'
        ));
$styleHeaderLeft = array(

    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    ),
    'font'  => array(
        'bold'  => false,
        'size'  => 12,
        'name'  => 'Angsana New'
    ));

$styleContent = array(

    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    ),
    'font'  => array(
        'bold'  => false,
        'size'  => 12,
        'name'  => 'Angsana New'
    ));

$styleContentCenter = array(

    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    ),
    'font'  => array(
        'bold'  => false,
        'size'  => 12,
        'name'  => 'Angsana New'
    ));
$styleContentCheckmarkCenter  = array(

    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    ),
    'font'  => array(
        'bold'  => false,
        'size'  => 12,
        'name'  => 'Angsana New'
    ));

$styleRight = array(

    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
    ),
    'font'  => array(
        'bold'  => false,
        'size'  => 12,
        'name'  => 'Angsana New'
    ));

$styleLeft = array(

    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    ),
    'font'  => array(
        'bold'  => false,
        'size'  => 12,
        'name'  => 'Angsana New'
    ));

$styleHeadRight = array(

    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
    ),
    'font'  => array(
        'bold'  => true,
        'size'  => 12,
        'name'  => 'Angsana New'
    ));

$styleHeadLeft = array(

    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
    ),
    'font'  => array(
        'bold'  => true,
        'size'  => 12,
        'name'  => 'Angsana New'
    ));

$styleHeader = array(

    'alignment' => array(
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    ),
    'font'  => array(
        'bold'  => true,
        'size'  => 12,
        'name'  => 'Angsana New'
    ));

$styleArrayBorder = array(
    'borders' => array(
        'allborders' => array(
        'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    )
    );
$styleArrayBorderBottomDouble = array(
    'borders' => array(
        'bottom' => array(
        'style' => PHPExcel_Style_Border::BORDER_DOUBLE
        )
    )
    );
$styleArrayBorderBottomSingle = array(
    'borders' => array(
        'bottom' => array(
        'style' => PHPExcel_Style_Border::BORDER_THIN 
        )
    )
    );





$count = 0;
for($tab = 0; $tab < 1; $tab++ ){

    $objPHPExcel->createSheet();
    $sheet = $objPHPExcel->setActiveSheetIndex($tab);

    $sheet->getColumnDimension('A')->setWidth(40);
    $sheet->getColumnDimension('B')->setWidth(20);
    $sheet->getColumnDimension('C')->setWidth(30);
    $sheet->getColumnDimension('D')->setWidth(40);
    $sheet->getColumnDimension('E')->setWidth(40);
    $sheet->getColumnDimension('F')->setWidth(20);
    $sheet->getColumnDimension('G')->setWidth(20);
    $sheet->getColumnDimension('H')->setWidth(20);
    $sheet->getColumnDimension('I')->setWidth(20);
    $sheet->getColumnDimension('J')->setWidth(20);
    $sheet->getColumnDimension('K')->setWidth(20);
    $sheet->getColumnDimension('L')->setWidth(20);
    $sheet->getColumnDimension('M')->setWidth(20);
    $sheet->getColumnDimension('N')->setWidth(20);
    $sheet->getColumnDimension('O')->setWidth(20);
    $sheet->getColumnDimension('P')->setWidth(25);
    $sheet->getColumnDimension('Q')->setWidth(25);
    $sheet->getColumnDimension('R')->setWidth(25);
    $sheet->getColumnDimension('S')->setWidth(25);

    $sheet->setCellValue('A1', 'ข้อมูลผลิตภัณฑ์ชุมชนทั้งหมด');

    $sheet->getStyle('A1:S1')->applyFromArray($styleContentHeader);
    $sheet->mergeCells('A1:S1');

    $sheet->setCellValue('A2', 'ชื่อเรื่อง');
    $sheet->getStyle('A2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('B2', 'หมวดหมู่ผลิตภัณฑ์');
    $sheet->getStyle('B2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('C2', 'ลิงก์');
    $sheet->getStyle('C2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('D2', 'จุดเด่น/ประโยชน์');
    $sheet->getStyle('D2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('E2', 'ราคาขาย');
    $sheet->getStyle('E2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('F2', 'สถานที่ผลิต/จำหน่าย');
    $sheet->getStyle('F2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('G2', 'เบอร์โทรศัพท์ติดต่อ');
    $sheet->getStyle('G2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('H2', 'วัตถุดิบหลัก');
    $sheet->getStyle('H2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('I2', 'แหล่งวัตถุดิบ');
    $sheet->getStyle('I2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('J2', 'ภาค');
    $sheet->getStyle('J2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('K2', 'จังหวัด');
    $sheet->getStyle('K2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('L2', 'อำเภอ');
    $sheet->getStyle('L2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('M2', 'ตำบล');
    $sheet->getStyle('M2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('N2', 'รหัสไปรษณีย์');
    $sheet->getStyle('N2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('O2', 'ชื่อผู้นำเข้าข้อมูล');
    $sheet->getStyle('O2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('P2', 'ชื่อผู้อนุมัติข้อมูล');
    $sheet->getStyle('P2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('Q2', 'สถานะ');
    $sheet->getStyle('Q2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('R2', 'หมายเหตุ');
    $sheet->getStyle('R2')->applyFromArray($styleContentHeader);

    $sheet->setCellValue('S2', 'วันที่นำเข้าข้อมูล');
    $sheet->getStyle('S2')->applyFromArray($styleContentHeader);

    $i = 3;
    $projectNo = 1;

    if(!empty($model)){
        //styleLeft
        //plan

        
        foreach ($model as $keyPlan => $value){

            $sheet->setCellValue('A'.$i, $value['name']);
            $sheet->getStyle('A'.$i)->applyFromArray($styleLeft)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('B'.$i, BackendHelper::getNameCategoryProduct($value['product_category_id']));
            $sheet->getStyle('B'.$i)->applyFromArray($styleLeft)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('C'.$i, $protocol.$hostname."/content-".FrontendHelper::getContentTypeById($value['type_id']).'/'.$value['content_id']);
            $sheet->getStyle('C'.$i)->applyFromArray($styleLeft);

            $sheet->setCellValue('D'.$i,  str_replace('&nbsp;', '', strip_tags($value['product_features'])));
            $sheet->getStyle('D'.$i)->applyFromArray($styleLeft)->getAlignment()->setWrapText(true);;

            $sheet->setCellValue('E'.$i,  str_replace('&nbsp;', '', strip_tags($value['product_price'])));
            $sheet->getStyle('E'.$i)->applyFromArray($styleLeft)->getAlignment()->setWrapText(true);;
              
            $sheet->setCellValue('F'.$i, $value['product_distribution_location']);
            $sheet->getStyle('F'.$i)->applyFromArray($styleLeft)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('G'.$i, $value['product_phone']);
            $sheet->getStyle('G'.$i)->applyFromArray($styleLeft)->getAlignment()->setWrapText(true);
               
            $sheet->setCellValue('H'.$i, $value['product_main_material'] );
            $sheet->getStyle('H'.$i)->applyFromArray($styleLeft)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('I'.$i, $value['product_sources_material']  );
            $sheet->getStyle('I'.$i)->applyFromArray($styleLeft)->getAlignment()->setWrapText(true);

            $sheet->setCellValue('J'.$i, BackendHelper::getNameRegion($value['region_id'])  );
            $sheet->getStyle('J'.$i)->applyFromArray($styleLeft); 
              
            $sheet->setCellValue('K'.$i, BackendHelper::getNameProvince($value['province_id'])   );
            $sheet->getStyle('K'.$i)->applyFromArray($styleLeft); 

            $sheet->setCellValue('L'.$i, BackendHelper::getNameDistrict($value['district_id']));
            $sheet->getStyle('L'.$i)->applyFromArray($styleLeft); 
              
            $sheet->setCellValue('M'.$i, BackendHelper::getNameSubdistrict($value['subdistrict_id'])   );
            $sheet->getStyle('M'.$i)->applyFromArray($styleLeft); 

            $sheet->setCellValue('N'.$i, BackendHelper::getNameZipcode($value['zipcode_id'])    );
            $sheet->getStyle('N'.$i)->applyFromArray($styleLeft); 
              
            $sheet->setCellValue('O'.$i, BackendHelper::getName($value['created_by_user_id']));
            $sheet->getStyle('O'.$i)->applyFromArray($styleLeft);   

            $sheet->setCellValue('P'.$i,  BackendHelper::getName($value['approved_by_user_id']) );
            $sheet->getStyle('P'.$i)->applyFromArray($styleLeft); 

            $sheet->setCellValue('Q'.$i, ucfirst($value['status'])    );
            $sheet->getStyle('Q'.$i)->applyFromArray($styleLeft); 

            $sheet->setCellValue('R'.$i, $value['note']    );
            $sheet->getStyle('R'.$i)->applyFromArray($styleLeft); 

            $sheet->setCellValue('S'.$i, $value['created_at']    );
            $sheet->getStyle('S'.$i)->applyFromArray($styleLeft); 
            
            $projectNo++;
            $i++;
     
        }

    }

    $sheet->setTitle("รายงานข้อมูลผลิตภัณฑ์ชุมชน");


}  


// // Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);
try {
    // if (COUNT($result) == 2) {
    //     $objPHPExcel->removeSheetByIndex(
    //         $objPHPExcel->getIndex(
    //             $objPHPExcel->getSheetByName('Worksheet')
    //         )
    //     );
    // }else{

        $objPHPExcel->removeSheetByIndex(
            $objPHPExcel->getIndex(
                $objPHPExcel->getSheetByName('Worksheet 1')
            )
        );

    //}
}catch(\yii\db\Exception $exception){

}


//ตั้งชื่อไฟล์
$time	= date("H:i:s");
$date	= date("Y-m-d");
list($h,$i,$s) = explode(":",$time);
$file_name = "รายงานข้อมูลผลิตภัณฑ์ชุมชน_".$date."_".$h."_".$i."_".$s."s";
//

// Save Excel 2007 file
#echo date('H:i:s') . " Write to Excel2007 format\n";
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
ob_end_clean();
// We'll be outputting an excel file
header('Content-type: application/vnd.ms-excel');
// It will be called file.xls
header('Content-Disposition: attachment;filename="'.$file_name.'.xlsx"');

ob_implicit_flush(1);
ob_clean();

$objWriter->save('php://output');	
exit();
?>