<?php
// try to return 1 0 instead of true false becoz it returns nothing instead of false
require_once("dataImport.php");
require_once("dataExport.php");
require_once("Database/DBController.php");

class time_table
{
    private $db = null;

    public function __construct(DBController $db)
    {
        if (!isset($db->con)) return null;
        $this->db = $db;
    }

    public function getTableData($table)
    {
        if ($this->db->con != null) {
            // fetch each row
            // it returns mysqli_result Object
            $tableData = $this->db->con->query("SELECT * FROM `{$table}`;");
            $resultArray = array();

            // fetch data one by one from array
            while ($item = mysqli_fetch_array($tableData, MYSQLI_ASSOC)) {
                $resultArray[] = $item;
            }

            return $resultArray;
        }
    }

    // selcol -> select column
    // $getDataColumn -> from which column we want data
    public function getData($table, $selCol, $selData, $getDataColumn)
    {
        if ($this->db->con != null) {
            $data = $this->db->con->query("SELECT `{$getDataColumn}` FROM `{$table}` WHERE `{$selCol}` = '{$selData}';");

            // fetch data of first row from object $data
            if ($item = mysqli_fetch_array($data, MYSQLI_ASSOC)) {
                return $item[$getDataColumn];
            } else {
                return null;
            }
        }
    }

    public function isCellNull($table, $selCol, $selData, $checkDataColumn)
    {
        if ($this->db->con != null) {
            $data = $this->getData($table, $selCol, $selData, $checkDataColumn);
            return is_null($data);
        }
    }

    public function updateTable($table, $selCol, $selData, $upCol, $upData)
    {
        if ($this->db->con != null) {
            // SQL Query
            // Ensure that there is no ' or " in srting and it won't work for integers
            if ($upData == "NULL") { // to set NULL value not "NULL"
                $query_string = "UPDATE `{$table}` SET `{$upCol}` = {$upData} WHERE `{$selCol}` = '{$selData}';";
            } else {
                $query_string = "UPDATE `{$table}` SET `{$upCol}` = '{$upData}' WHERE `{$selCol}` = '{$selData}';";
            }

            // execute query
            $result = $this->db->con->query($query_string);
            return $result;
        }
    }

    // uid-> update integer data where seldata and updata both are integers
    public function uid($table, $selCol, $selData, $upCol, $upData)
    {
        if ($this->db->con != null) {
            $query_string = "UPDATE `{$table}` SET `{$upCol}` = {$upData} WHERE `{$selCol}` = {$selData};";
            $result = $this->db->con->query($query_string);
            return $result;
        }
    }

    public function noOfLec($table)
    {
        $tD = $this->getTableData($table);
        $week = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
        $c = 0;
        for ($row = 0; $row < count($tD); $row++) {
            for ($col = 0; $col < 6; $col++) {
                if ($tD[$row][$week[$col]]) {
                    $c++;
                }
            }
        }
        return $c;
    }

    public function doesTableExists($table)
    {
        if ($this->db->con != null) {
            $query_string = "SELECT * FROM `{$table}`;";
            $result = $this->db->con->query($query_string);
            if ($result) {
                return 1;
            } else {
                return 0;
            }
        }
    }

    public function helperCT($table, $sizOfCell, $n)
    {
        $query_string = "CREATE TABLE `{$table}` (`Lecture_No` varchar(5) PRIMARY KEY, `Monday` varchar($sizOfCell), `Tuesday` varchar($sizOfCell), `Wednesday` varchar($sizOfCell), `Thursday` varchar($sizOfCell), `Friday` varchar($sizOfCell), `Saturday` varchar($sizOfCell));";
        $this->db->con->query($query_string);

        $n = (int)$n;
        for ($i = 1; $i <= $n; $i++) {
            $query_string = "INSERT INTO `{$table}` (`Lecture_No`) VALUES ('{$i}');";
            $this->db->con->query($query_string);
        }
    }

    public function createTables($cA, $tA, $rA, $sA, $n)
    {
        $db = new DBController();
        $di = new dataImport($db);
        $di->impData($cA, 'class', array("ClassName"), 1);
        $di->impData($tA, 'teacher', array("TeacherName", "MaxNoOfLec"), 2);
        $di->impData($rA, 'room', array("RoomNo"), 1);
        $di->impData($sA, 'subject', array("SubjectName", "LabName"), 2);

        $cD = $this->getTableData('class');
        foreach ($cD as $row) :
            if ($row["ClassName"]) { // to avoid null values in excel sheet
                $this->helperCT($row["ClassName"], 500, $n);
            }
        endforeach;

        $tD = $this->getTableData('teacher');
        foreach ($tD as $row) :
            if ($row["TeacherName"]) {
                $this->helperCT($row["TeacherName"], 250, $n);
            }
        endforeach;

        $rD = $this->getTableData('room');
        foreach ($rD as $row) :
            if ($row["RoomNo"]) {
                $this->helperCT($row["RoomNo"], 250, $n);
            }
        endforeach;

        $this->uid('lec', 'sr_no', 1, 'lec', $n);
    }

    public function helperDT($table)
    {
        $query_string = "DROP TABLE IF EXISTS `{$table}`;";
        $this->db->con->query($query_string);
    }

    public function clear()
    {
        $cD = $this->getTableData('class');
        foreach ($cD as $row) :
            if ($row["ClassName"]) {
                $this->helperDT($row["ClassName"]);
            }
        endforeach;
        $query_string = "TRUNCATE TABLE `class`;";
        $this->db->con->query($query_string);

        $tD = $this->getTableData('teacher');
        foreach ($tD as $row) :
            if ($row["TeacherName"]) {
                $this->helperDT($row["TeacherName"]);
            }
        endforeach;
        $query_string = "TRUNCATE TABLE `teacher`;";
        $this->db->con->query($query_string);

        $rD = $this->getTableData('room');
        foreach ($rD as $row) :
            if ($row["RoomNo"]) {
                $this->helperDT($row["RoomNo"]);
            }
        endforeach;
        $query_string = "TRUNCATE TABLE `room`;";
        $this->db->con->query($query_string);

        $query_string = "TRUNCATE TABLE `subject`;";
        $this->db->con->query($query_string);

        $this->uid('lec', 'sr_no', 1, 'lec', 'NULL');
    }

    public function dropDatabase()
    {
        $query_string = "DROP DATABASE IF EXISTS `time_table`;";
        $this->db->con->query($query_string);
    }

    public function download($path)
    {
        $de = new dataExport();

        mkdir($path . "\\table_data_download");
        mkdir($path . "\\table_data_download\\class");
        mkdir($path . "\\table_data_download\\teacher");
        mkdir($path . "\\table_data_download\\room");

        $cD = $this->getTableData('class');
        foreach ($cD as $row) :
            $de->expData($row["ClassName"], $this->getTableData($row["ClassName"]), $path . "\\table_data_download\\class");
        endforeach;

        $tD = $this->getTableData('teacher');
        foreach ($tD as $row) :
            $de->expData($row["TeacherName"], $this->getTableData($row["TeacherName"]), $path . "\\table_data_download\\teacher");
        endforeach;

        $rD = $this->getTableData('room');
        foreach ($rD as $row) :
            $de->expData($row["RoomNo"], $this->getTableData($row["RoomNo"]), $path . "\\table_data_download\\room");
        endforeach;
    }
}
