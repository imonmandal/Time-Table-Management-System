<?php

require_once('vendor/autoload.php');
require_once('DBController.php');

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class dataImport
{
    private $db = null;

    public function __construct(DBController $db)
    {
        if (!isset($db->con)) return null;
        $this->db = $db;
    }

    public function replace_error_chars($arr, $str)
    {
        foreach ($arr as $c) {
            $str = str_replace($c, "", $str);
        }
        return $str;
    }

    public function impData($file, $table, $column, $cols) // col->array of col name of table in db
    {   // cols->number of cols to take from xlsheet
        $arr_file = explode('.', $file['name']);
        $extension = end($arr_file);
        $reader = null;

        if ('csv' == $extension) {
            $reader = new Csv();
        } else {
            $reader = new Xlsx();
        }

        $spreadsheet = $reader->load($file['tmp_name']);

        // Note that sheets are indexed from 0
        $sheetData = $spreadsheet->getSheet(0)->toArray();
        // $sheetData = $spreadsheet->getActiveSheet()->toArray();

        if (!empty($sheetData)) {
            $error_chars = array("`", "'", '"', "#", "^");
            for ($row = 1; $row < count($sheetData); $row++) { // fetch row (indexing starts from 0)
                $data = "";
                // for ($col = 0; $col < count($sheetData[$row]) - 1; $col++) {
                for ($col = 0; $col < $cols - 1; $col++) {
                    $d = $sheetData[$row][$col];
                    $d = $this->replace_error_chars($error_chars, $d);
                    if (strlen($d) == 0) {
                        $data = $data . "NULL" . ", ";
                    } else {
                        $data = $data . "'" . $d . "'" . ", ";
                    }
                }
                // $d2 = $sheetData[$row][count($sheetData[$row]) - 1];
                $d2 = $sheetData[$row][$cols - 1];
                $d2 = $this->replace_error_chars($error_chars, $d2);
                if (strlen($d2) == 0) {
                    $data = $data . "NULL";
                } else {
                    $data = $data . "'" . $d2 . "'";
                }
                $columns = implode('`, `', array_values($column));
                $columns = "`" . $columns . "`";

                $query_string = "INSERT INTO `{$table}` ({$columns}) VALUES ({$data});";
                $this->db->con->query($query_string);
            }
        }
    }
}
