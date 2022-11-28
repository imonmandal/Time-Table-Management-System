<?php

require_once("Database/DBController.php");
require_once("Database/time_table.php");

$db = new DBController();
$tt = new time_table($db);

$lec = '1';
$day = 'Monday';
$class = 'TE1';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  if (isset($_POST['lab'])) {

    $lec = $_POST['lec'];
    $day = $_POST['day'];
    $class = $_POST['class'];
    $L = (int)$lec;
    if ($L % 2 == 0) {
      $L = $L - 1;
    } else {
      $L = $L + 1;
    }

    $a = $tt->getData($class, 'Lecture_No', $L, $day);
    $a = explode('^', $a);
    if ($a[0] == 'lec') {
      $url = sprintf("Location: class.php?class-return=%s&clash-lec=%s&day=%s", $class, $L, $day);
      header($url);
      exit();
    }
  }

  if (isset($_POST['enter-data'])) {

    $lec = $_POST['lec'];
    $day = $_POST['day'];
    $class = $_POST['class'];
    $room = $_POST['room'];
    $teacher = $_POST['teacher'];
    $lab = $_POST['lab'];
    $L = (int)$lec;
    if ($L % 2 == 0) {
      $L = $L - 1;
    } else {
      $L = $L + 1;
    }

    $f1 = $tt->doesTableExists($teacher);
    $f2 = $tt->doesTableExists($room);

    if ($f1 == 0 && $f2 == 1) {
      $s = sprintf('<script type="text/javascript">alert("Table of teacher %s is not there in database.");</script>', $teacher);
      echo $s;
    } elseif ($f1 == 1 && $f2 == 0) {
      $s = sprintf('<script type="text/javascript">alert("Table of room %s is not there in database.");</script>', $room);
      echo $s;
    } elseif ($f1 == 0 && $f2 == 0) {
      $s = sprintf('<script type="text/javascript">alert("Table of teacher %s and room %s are not there in database.");</script>', $teacher, $room);
      echo $s;
    } else {

      $t = 0; // teacher don't have another lec
      $r = 0; // room is vacant

      if ((!$tt->isCellNull($teacher, 'Lecture_No', $lec, $day)) || (!$tt->isCellNull($teacher, 'Lecture_No', $L, $day))) {
        $t = 1;
      }
      if ((!$tt->isCellNull($room, 'Lecture_No', $lec, $day)) || (!$tt->isCellNull($room, 'Lecture_No', $L, $day))) {
        $r = 1;
      }

      if ($t == 1 && $r == 0) {
        $s = sprintf('<script type="text/javascript">alert("%s is having another lecture");</script>', $teacher);
        echo $s;
      } elseif ($t == 0 && $r == 1) {
        $s = sprintf('<script type="text/javascript">alert("Room %s is already occupied");</script>', $room);
        echo $s;
      } elseif ($t == 1 && $r == 1) {
        $s = sprintf('<script type="text/javascript">alert("%s is having another lecture and Room %s is already occupied");</script>', $teacher, $room);
        echo $s;
      } else {
        $tt->getData("teacher", "TeacherName", $teacher, "MaxNoOfLec") ? $mxl = $tt->getData("teacher", "TeacherName", $teacher, "MaxNoOfLec") : $mxl =  100000;
        if ($tt->noOfLec($teacher) >= $mxl - 1) {
          $s = sprintf('<script type="text/javascript">alert("No of lectures of teacher %s have reached the maximum limit");</script>', $teacher);
          echo $s;
        } else {
          $dataC = "";
          $backup_class_data = $tt->getData($class, 'Lecture_No', $lec, $day);

          $e1 = true;
          if ($tt->isCellNull($class, 'Lecture_No', $lec, $day)) {
            $e1 = $tt->updateTable($class, 'Lecture_No', $L, $day, "|Lab|");

            $dataC = "lab^" . $teacher . "#" . $room . "#" . $lab;
          } else {
            $d = $tt->getData($class, 'Lecture_No', $lec, $day);
            $dataC = $d . "^" . $teacher . "#" . $room . "#" . $lab;
          }

          $e2 = $tt->updateTable($teacher, 'Lecture_No', $L, $day, "|Lab|");
          $e3 = $tt->updateTable($room, 'Lecture_No', $L, $day, "|Lab|");

          $dataT = $class . "#" . $room . "#" . $lab;
          $dataR = $class . "#" . $teacher . "#" . $lab;

          $e4 = $tt->updateTable($class, 'Lecture_No', $lec, $day, $dataC);
          $e5 = $tt->updateTable($teacher, 'Lecture_No', $lec, $day, $dataT);
          $e6 = $tt->updateTable($room, 'Lecture_No', $lec, $day, $dataR);

          if (!($e1 && $e2 && $e3 && $e4 && $e5 && $e6)) {
            echo '<script type="text/javascript">alert("Could not enter data due to some issues with database");</script>';

            if ($backup_class_data) {
              $tt->updateTable($class, 'Lecture_No', $lec, $day, $backup_class_data);
            } else {
              $tt->updateTable($class, 'Lecture_No', $L, $day, "NULL");
              $tt->updateTable($class, 'Lecture_No', $lec, $day, "NULL");
            }
            $tt->updateTable($teacher, 'Lecture_No', $L, $day, "NULL");
            $tt->updateTable($room, 'Lecture_No', $L, $day, "NULL");
            $tt->updateTable($teacher, 'Lecture_No', $lec, $day, "NULL");
            $tt->updateTable($room, 'Lecture_No', $lec, $day, "NULL");
          }
        }
      }
    }
  }

  if (isset($_POST['delete-data'])) {

    $lec = $_POST['lec'];
    $day = $_POST['day'];
    $class = $_POST['class'];
    $room = $_POST['room'];
    $teacher = $_POST['teacher'];
    $index = $_POST['index'];
    $L = (int)$lec;
    if ($L % 2 == 0) {
      $L = $L - 1;
    } else {
      $L = $L + 1;
    }

    $tt->getData($class, 'Lecture_No', $L, $day) ? $backup_class_1 = $tt->getData($class, 'Lecture_No', $L, $day) : $backup_class_1 = "NULL";
    $tt->getData($class, 'Lecture_No', $lec, $day) ? $backup_class_2 = $tt->getData($class, 'Lecture_No', $lec, $day) : $backup_class_2 = "NULL";
    $tt->getData($teacher, 'Lecture_No', $L, $day) ? $backup_teacher_1 = $tt->getData($teacher, 'Lecture_No', $L, $day) : $backup_teacher_1 = "NULL";
    $tt->getData($teacher, 'Lecture_No', $lec, $day) ? $backup_teacher_2 = $tt->getData($teacher, 'Lecture_No', $lec, $day) : $backup_teacher_2 = "NULL";
    $tt->getData($room, 'Lecture_No', $L, $day) ? $backup_room_1 = $tt->getData($room, 'Lecture_No', $L, $day) : $backup_room_1 = "NULL";
    $tt->getData($room, 'Lecture_No', $lec, $day) ? $backup_room_2 = $tt->getData($room, 'Lecture_No', $lec, $day) : $backup_room_2 = "NULL";

    $cD = $tt->getData($class, 'Lecture_No', $lec, $day); // cell data
    $lbs = explode('^', $cD);

    $d1 = true;
    $d2 = true;
    if (count($lbs) == 2) {
      $d1 = $tt->updateTable($class, 'Lecture_No', $L, $day, "NULL");

      $d2 = $tt->updateTable($class, 'Lecture_No', $lec, $day, 'NULL');
    } else {
      array_splice($lbs, $index, 1); // array, index, how many values to delete
      $d = implode('^', $lbs);
      $d2 = $tt->updateTable($class, 'Lecture_No', $lec, $day, $d);
    }

    $d3 = $tt->updateTable($teacher, 'Lecture_No', $L, $day, "NULL");
    $d4 = $tt->updateTable($room, 'Lecture_No', $L, $day, "NULL");

    $d5 = $tt->updateTable($teacher, 'Lecture_No', $lec, $day, 'NULL');
    $d6 = $tt->updateTable($room, 'Lecture_No', $lec, $day, 'NULL');
    if (!($d1 && $d2 && $d3 && $d4 && $d5 && $d6)) {
      echo '<script type="text/javascript">alert("Could not delete data due to some issues with database");</script>';

      $tt->updateTable($class, 'Lecture_No', $L, $day, $backup_class_1);
      $tt->updateTable($class, 'Lecture_No', $lec, $day, $backup_class_2);
      $tt->updateTable($teacher, 'Lecture_No', $L, $day, $backup_teacher_1);
      $tt->updateTable($teacher, 'Lecture_No', $lec, $day, $backup_teacher_2);
      $tt->updateTable($room, 'Lecture_No', $L, $day, $backup_room_1);
      $tt->updateTable($room, 'Lecture_No', $lec, $day, $backup_room_2);
    }
  }
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Lab</title>
  <link rel="stylesheet" href="html/scss/style.css">
  <link rel="stylesheet" href="html/scss/lab.css">
</head>

<body>
  <h1>Lab</h1>

  <div class="container">
    <div class="con">
      <h2>Enter Data for Class <?php echo sprintf("%s %s Lab %s", $class, $day, $lec) ?></h2>
      <form action="class.php" method="post" id="form1-<?php echo $day . "-" . $i; ?>"></form>
      <div class="box">
        <?php
        $cD = $tt->getData($class, 'Lecture_No', $lec, $day); // cell data
        $labs = explode('^', $cD);
        for ($i = 1; $i <= 4; $i++) { // i->0to1  count(labs)->2to5 and 1 also for null
          if ($i + 1 <= count($labs) && !$tt->isCellNull($class, 'Lecture_No', $lec, $day)) {
            $arr = explode("#", $labs[$i]);
            $teacherName = $arr[0];
            $roomNo = $arr[1];
            $labName = $arr[2];
        ?>
            <div class="box2">
              <div class="cell">
                <div class="disp-dataCl">
                  Lab :- <?php echo $labName; ?>
                </div>
                <div class="disp-dataCl">
                  Teacher :- <?php echo $teacherName; ?>
                </div>
                <div class="disp-dataCl">
                  Room :- <?php echo $roomNo; ?>
                </div>
                <div class="disp-dataCl">
                  <form action="lab.php" method="post">
                    <input type="hidden" name='class' value='<?php echo $class; ?>'>
                    <input type="hidden" name='teacher' value='<?php echo $teacherName; ?>'>
                    <input type="hidden" name='room' value='<?php echo $roomNo; ?>'>
                    <input type="hidden" name='lec' value='<?php echo $lec; ?>'>
                    <input type="hidden" name='day' value='<?php echo $day; ?>'>
                    <input type="hidden" name='index' value='<?php echo $i; ?>'>
                    <button type="submit" name="delete-data" value="submit" class="btn2">Delete</button>
                  </form>
                </div>
              </div>
            </div>
          <?php
          } else {
          ?>
            <div class="box2">
              <div class="cell-form">
                <form action="lab.php" method="post">
                  <div class="form-ele">
                    <label>
                      <span class="mpx2">Select Lab</span> <input required list="subjects-<?php echo $day . "-" . $i; ?>" name="lab" class="inp">
                      <datalist id="subjects-<?php echo $day . "-" . $i; ?>">
                        <?php
                        $tableS = $tt->getTableData('subject');
                        foreach ($tableS as $rowS) :
                          if ($rowS['LabName']) {
                        ?>
                            <option value="<?php echo $rowS['LabName']; ?>">
                          <?php }
                        endforeach;
                          ?>
                      </datalist>
                    </label>
                  </div>
                  <div class="form-ele">
                    <label>
                      <span>Select Teacher</span> <input required list="teachers-<?php echo $day . "-" . $i; ?>" name="teacher" class="inp">
                      <datalist id="teachers-<?php echo $day . "-" . $i; ?>">
                        <?php
                        $tableT = $tt->getTableData('teacher');
                        foreach ($tableT as $rowT) :
                          if ($rowT['TeacherName']) {
                            $tt->getData("teacher", "TeacherName", $rowT['TeacherName'], "MaxNoOfLec") ? $mxl = $tt->getData("teacher", "TeacherName", $rowT['TeacherName'], "MaxNoOfLec") : $mxl =  100000;
                            if (($tt->isCellNull($rowT['TeacherName'], 'Lecture_No', $lec, $day)) && ($tt->isCellNull($rowT['TeacherName'], 'Lecture_No', $L, $day)) && $tt->noOfLec($rowT['TeacherName']) < $mxl - 1) {
                        ?>
                              <option value="<?php echo $rowT['TeacherName']; ?>">
                          <?php }
                          }
                        endforeach;
                          ?>
                      </datalist>
                    </label>
                  </div>
                  <div class="form-ele">
                    <label>
                      <span class="mpx">Select Room</span> <input required list="rooms-<?php echo $day . "-" . $i; ?>" name="room" class="inp">
                      <datalist id="rooms-<?php echo $day . "-" . $i; ?>">
                        <?php
                        $tableR = $tt->getTableData('room');
                        foreach ($tableR as $rowR) :
                          if ($rowR['RoomNo']) {
                            if (($tt->isCellNull($rowR['RoomNo'], 'Lecture_No', $lec, $day)) && ($tt->isCellNull($rowR['RoomNo'], 'Lecture_No', $L, $day))) {
                        ?>
                              <option value="<?php echo $rowR['RoomNo']; ?>">
                          <?php }
                          }
                        endforeach;
                          ?>
                      </datalist>
                    </label>
                  </div>
                  <div class="form-ele submit">
                    <!-- for form1 -->
                    <input type="hidden" name='class' value='<?php echo $class; ?>'>
                    <input type="hidden" name='lec' value='<?php echo $lec; ?>'>
                    <input type="hidden" name='day' value='<?php echo $day; ?>'>
                    <button type="submit" class="btn2" name="enter-data" value="submit">Submit</button>
                  </div>
                </form>
              </div>
            </div>
        <?php
          }
        }
        ?>

      </div>
      <div class="submit">
        <form action="class.php" method="get">
          <input type="hidden" name="class-data" value="<?php echo $class; ?>">
          <button class="btn1" name="class" value="submit">Back</button>
        </form>
      </div>
    </div>
  </div>
</body>

</html>