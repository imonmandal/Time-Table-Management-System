<?php

require_once('vendor/autoload.php');
require_once('DBController.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class dataExport
{
    public function expData($tableName, $tableArray, $path = null)
    {
        // CREATE A NEW SPREADSHEET + WORKSHEET
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($tableName);

        $tableTitle = array("Lecture_No", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
        for ($i = 0; $i < count($tableTitle); $i++) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue($col . 1, $tableTitle[$i]);
        }

        for ($i = 0; $i < count($tableArray); $i++) {
            for ($j = 0; $j < count($tableArray[$i]); $j++) {
                $d = $tableArray[$i][$tableTitle[$j]];
                $row = $i + 2; // becoz of table headings
                $col = Coordinate::stringFromColumnIndex($j + 1);
                if (strlen($d) == 0) {
                    $sheet->setCellValue($col . $row, "-");
                } else {
                    $d = str_replace('#', '-', $d);
                    $d = str_replace('^', ' ', $d);
                    $sheet->setCellValue($col . $row, $d);
                }
            }
        }

        // SAVE FILE
        $writer = new Xlsx($spreadsheet);
        $p = $path . "\\" . $tableName . ".xlsx";
        $writer->save($p);
    }
}
