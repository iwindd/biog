<?php

namespace backend\components;

use Yii;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

/**
 * BaseExportController provides centralized export functionality using Box\Spout
 * This replaces the old PHPExcel library to reduce memory usage and improve performance
 */
class BaseExportController
{
    /**
     * Create a new PhpSpreadsheet instance with basic properties
     */
    public static function createSpreadsheet()
    {
        $spreadsheet = new Spreadsheet();
        
        // Set basic properties
        $spreadsheet->getProperties()
            ->setCreator("Biogang Database")
            ->setLastModifiedBy("Biogang Database")
            ->setTitle("Export Data")
            ->setSubject("Export Data")
            ->setDescription("Exported data from Biogang Database");
            
        return $spreadsheet;
    }
    
    /**
     * Get standard style arrays for Excel formatting
     * @return array Array of style definitions
     */
    public static function getStyles()
    {
        return [
            'header' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'name' => 'Angsana New'
                ]
            ],
            'content' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
                'font' => [
                    'bold' => false,
                    'size' => 12,
                    'name' => 'Angsana New'
                ]
            ],
            'content_center' => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
                'font' => [
                    'bold' => false,
                    'size' => 12,
                    'name' => 'Angsana New'
                ]
            ],
            'borders' => [
                'borders' => [
                    'allborders' => [
                        'borderStyle' => Border::BORDER_THIN
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Setup column widths for the spreadsheet
     * @param Spreadsheet $spreadsheet
     * @param array $columnWidths Array of column letters and their widths
     */
    public static function setupColumnWidths($spreadsheet, $columnWidths = [])
    {
        $sheet = $spreadsheet->getActiveSheet();
        
        // Default column widths if not specified
        $defaultWidths = [
            'A' => 40, 'B' => 30, 'C' => 30, 'D' => 40, 'E' => 40,
            'F' => 20, 'G' => 20, 'H' => 20, 'I' => 20, 'J' => 20,
            'K' => 20, 'L' => 20, 'M' => 20, 'N' => 20, 'O' => 20,
            'P' => 25, 'Q' => 25, 'R' => 25, 'S' => 25
        ];
        
        $widths = array_merge($defaultWidths, $columnWidths);
        
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }
    }
    
    /**
     * Export data to Excel file and send to browser
     * @param Spreadsheet $spreadsheet
     * @param string $filename
     * @param string $sheetName
     */
    public static function exportToBrowser($spreadsheet, $filename, $sheetName = 'Sheet1')
    {
        // Set active sheet and name
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);
        
        // Remove default sheet if it exists
        try {
            $worksheet = $spreadsheet->getSheetByName('Worksheet');
            if ($worksheet !== null) {
                $spreadsheet->removeSheetByIndex(
                    $spreadsheet->getIndex($worksheet)
                );
            }
        } catch (\Exception $e) {
            // Sheet doesn't exist or can't be removed, continue
        }
        
        // Generate filename with timestamp
        $time = date("H:i:s");
        $date = date("Y-m-d");
        list($h, $i, $s) = explode(":", $time);
        $timestamp = $date . "_" . $h . "_" . $i . "_" . $s . "s";
        $fullFilename = $filename . "_" . $timestamp . ".xlsx";
        
        // Clean output buffers
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Set headers for download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fullFilename . '"');
        header('Cache-Control: max-age=0');
        header('Expires: 0');
        header('Pragma: public');
        
        // Create writer and output
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        
        exit;
    }

    public static function saveToFile($spreadsheet, $filePath, $sheetName = 'Sheet1')
    {
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheetName);

        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($filePath);
    }

    public static function cleanupSpreadsheet($spreadsheet)
    {
        if ($spreadsheet instanceof Spreadsheet) {
            $spreadsheet->disconnectWorksheets();
        }

        unset($spreadsheet);
        gc_collect_cycles();
    }

    public static function saveRowsToStreamingXlsx($filePath, array $headers, array $rows, $sheetName = 'Sheet1', $title = '')
    {
        $factoryClass = '\\Box\\Spout\\Writer\\Common\\Creator\\WriterEntityFactory';
        $styleBuilderClass = '\\Box\\Spout\\Writer\\Common\\Creator\\Style\\StyleBuilder';

        if (!class_exists($factoryClass) || !class_exists($styleBuilderClass)) {
            throw new \RuntimeException('เกิดข้อผิดพลาดกรุณาลองใหม่อีกครั้งในภายหลัง');
        }

        $writer = $factoryClass::createXLSXWriter();
        $writer->openToFile($filePath);

        $headerStyle = (new $styleBuilderClass())
            ->setFontBold()
            ->build();

        if (!empty($sheetName) && method_exists($writer, 'getCurrentSheet')) {
            $currentSheet = $writer->getCurrentSheet();
            if ($currentSheet && method_exists($currentSheet, 'setName')) {
                $currentSheet->setName($sheetName);
            }
        }

        if ($title !== '') {
            $writer->addRow($factoryClass::createRowFromArray([$title], $headerStyle));
        }

        $writer->addRow($factoryClass::createRowFromArray($headers, $headerStyle));

        foreach ($rows as $row) {
            $writer->addRow($factoryClass::createRowFromArray($row));
        }

        $writer->close();
        gc_collect_cycles();
    }
    
    /**
     * Process data in chunks to reduce memory usage
     * @param yii\db\Query $query
     * @param callable $processor Function to process each chunk
     * @param int $chunkSize Size of each chunk
     */
    public static function processInChunks($query, $processor, $chunkSize = 1000)
    {
        $offset = 0;
        
        do {
            $chunk = $query->limit($chunkSize)->offset($offset)->asArray()->all();
            
            if (empty($chunk)) {
                break;
            }
            
            $processor($chunk, $offset);
            $offset += $chunkSize;
            
            // Clear memory periodically
            if ($offset % ($chunkSize * 10) == 0) {
                gc_collect_cycles();
            }
            
        } while (count($chunk) === $chunkSize);
    }
    
    /**
     * Clean text for Excel output
     * @param string $text
     * @return string Cleaned text
     */
    public static function cleanText($text)
    {
        if (empty($text)) {
            return '';
        }
        
        // Remove HTML tags and non-breaking spaces
        $text = strip_tags($text);
        $text = str_replace('&nbsp;', ' ', $text);
        $text = trim($text);
        
        return $text;
    }
}
