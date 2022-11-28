<!DOCTYPE html>
<html lang="en">

<head>
  <?php

  require_once("Database/DBController.php");
  require_once("Database/time_table.php");

  $db = new DBController();
  $tt = new time_table($db);

  $class = 'TE1';

  if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['class'])) {
      $class  = $_GET['class-data'];
      $f = $tt->doesTableExists($class);
      if ($f == 0) {
        $url = sprintf("Location: index.php?class-table-not-pres=%s", $class);
        header($url);
        exit();
      }
    }

    if (isset($_GET['class-return'])) {
      $class  = $_GET['class-return'];
      $L = $_GET['clash-lec'];
      $dy = $_GET['day'];
      $s = sprintf('<script type="text/javascript">alert("Cannot enter lab data because class %s is having lecture on %s in lecture number %s");</script>', $class, $dy, $L);
      echo $s;
    }
  }

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['enter-data'])) {

      $lec = $_POST['lec'];
      $day = $_POST['day'];
      $class = $_POST['class'];
      $room = $_POST['room'];
      $teacher = $_POST['teacher'];
      $subject = $_POST['subject'];

      $f1 = $tt->doesTableExists($teacher);
      $f2 = $tt->doesTableExists($room);

      if ($f1 == 0 && $f2 == 1) {
        $s = sprintf('<script type="text/javascript">alert("Table of teacher %s is not there in database");</script>', $teacher);
        echo $s;
      } elseif ($f1 == 1 && $f2 == 0) {
        $s = sprintf('<script type="text/javascript">alert("Table of room %s is not there in database");</script>', $room);
        echo $s;
      } elseif ($f1 == 0 && $f2 == 0) {
        $s = sprintf('<script type="text/javascript">alert("Table of teacher %s and room %s are not there in database");</script>', $teacher, $room);
        echo $s;
      } else {

        $t = 0; // teacher don't have another lec
        $r = 0; // room is vacant

        if (!$tt->isCellNull($teacher, 'Lecture_No', $lec, $day)) {
          $t = 1;
        }
        if (!$tt->isCellNull($room, 'Lecture_No', $lec, $day)) {
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
          if ($tt->noOfLec($teacher) >= $mxl) {
            $s = sprintf('<script type="text/javascript">alert("No of lectures of teacher %s have reached the maximum limit");</script>', $teacher);
            echo $s;
          } else {
            $dataC = "lec^" . $teacher . "#" . $room . "#" . $subject;
            $dataT = $class . "#" . $room . "#" . $subject;
            $dataR = $class . "#" . $teacher . "#" . $subject;

            // enter
            $e1 = $tt->updateTable($class, 'Lecture_No', $lec, $day, $dataC);
            $e2 = $tt->updateTable($teacher, 'Lecture_No', $lec, $day, $dataT);
            $e3 = $tt->updateTable($room, 'Lecture_No', $lec, $day, $dataR);

            if (!($e1 && $e2 && $e3)) {
              echo '<script type="text/javascript">alert("Could not enter data due to some issues with database");</script>';
              $tt->updateTable($class, 'Lecture_No', $lec, $day, "NULL");
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

      $tt->getData($class, 'Lecture_No', $lec, $day) ? $backup_class_data = $tt->getData($class, 'Lecture_No', $lec, $day) : $backup_class_data = "NULL";
      $tt->getData($teacher, 'Lecture_No', $lec, $day) ? $backup_teacher_data = $tt->getData($teacher, 'Lecture_No', $lec, $day) : $backup_teacher_data = "NULL";
      $tt->getData($room, 'Lecture_No', $lec, $day) ? $backup_room_data = $tt->getData($room, 'Lecture_No', $lec, $day) : $backup_room_data = "NULL";

      $d1 = $tt->updateTable($class, 'Lecture_No', $lec, $day, 'NULL');
      $d2 = $tt->updateTable($teacher, 'Lecture_No', $lec, $day, 'NULL');
      $d3 = $tt->updateTable($room, 'Lecture_No', $lec, $day, 'NULL');

      if (!($d1 && $d2 && $d3)) {
        echo '<script type="text/javascript">alert("Could not delete data due to some issues with database");</script>';

        $tt->updateTable($class, 'Lecture_No', $lec, $day, $backup_class_data);
        $tt->updateTable($teacher, 'Lecture_No', $lec, $day, $backup_teacher_data);
        $tt->updateTable($room, 'Lecture_No', $lec, $day, $backup_room_data);
      }
    }
  }
  ?>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $class; ?> Time Table</title>
  <link rel="stylesheet" href="html/scss/style.css" />
</head>

<body>
  <header><?php echo $class; ?> Time Table</header>
  <div class="tp">
    <a href="index.php">Home
    </a>
  </div>
  <section>
    <div class="time-table">
      <div class="thead">
        <div class="tcell">Lecture Number</div>
        <?php {
          $table = $tt->getTableData('week');
          foreach ($table as $row) :
        ?>
            <div class="tcell"><?php echo $row['days']; ?></div>
          <?php endforeach; ?>
      </div>
      <?php
          $noLec = $tt->getData('lec', 'sr_no', 1, 'lec');
          $table = $tt->getTableData('week');
          for ($i = 1; $i <= $noLec; $i++) {
      ?>
        <div class="row">
          <div class="cell num"><?php echo $i; ?></div>
          <?php
            foreach ($table as $row) :
          ?>
            <div class="cell">
              <?php
              // i -> lec
              // row[days] -> mon...
              if (!$tt->isCellNull($class, 'Lecture_No', $i, $row['days'])) {
                $cellData = $tt->getData($class, 'Lecture_No', $i, $row['days']);
                $identifier = explode("^", $cellData);
                if ($identifier[0] == "lec") {
                  $arr = explode("#", $identifier[1]);
                  $teacherName = $arr[0];
                  $roomNo = $arr[1];
                  $subjectName = $arr[2];
              ?>
                  <div class="disp-dataCl">
                    Subject :- <?php echo $subjectName; ?>
                  </div>
                  <div class="disp-dataCl">
                    Teacher :- <?php echo $teacherName; ?>
                  </div>
                  <div class="disp-dataCl">
                    Room :- <?php echo $roomNo; ?>
                  </div>
                  <div class="disp-dataCl">
                    <form action="class.php" method="post">
                      <input type="hidden" name='class' value='<?php echo $class; ?>'>
                      <input type="hidden" name='teacher' value='<?php echo $teacherName; ?>'>
                      <input type="hidden" name='room' value='<?php echo $roomNo; ?>'>
                      <input type="hidden" name='lec' value='<?php echo $i; ?>'>
                      <input type="hidden" name='day' value='<?php echo $row['days']; ?>'>
                      <button type="submit" name="delete-data" value="submit" class="btn">Delete</button>
                    </form>
                  </div>
                  <?php
                } elseif ($identifier[0] == "lab") {
                  $cD = $tt->getData($class, 'Lecture_No', $i, $row['days']); // cell data
                  $labs = explode('^', $cD);
                  array_splice($labs, 0, 1);
                  foreach ($labs as $ai) : // array items
                    $arr = explode("#", $ai);
                    $teacherName = $arr[0];
                    $roomNo = $arr[1];
                    $labName = $arr[2];
                    $d = $labName . "-" . $teacherName . "-" . $roomNo;
                  ?>
                    <div class="disp-dataCl2">
                      <?php echo $d; ?>
                    </div>
                  <?php
                  endforeach;
                  ?>
                  <div class="disp-dataCl2">
                    <form action="lab.php" method="post">
                      <input type="hidden" name='class' value='<?php echo $class; ?>'>
                      <input type="hidden" name='lec' value='<?php echo $i; ?>'>
                      <input type="hidden" name='day' value='<?php echo $row['days']; ?>'>
                      <button type="submit" name="lab" value="submit" class="btn">Lab</button>
                    </form>
                  </div>
                <?php
                } else {
                ?>
                  <div class="disp-dataCl2">
                    <?php echo "Lab"; ?>
                  </div>
                <?php
                }
              } else {
                ?>
                <!-- form1 for lec and form2 for lab -->
                <form action="class.php" method="post" id="form1-<?php echo $row['days'] . "-" . $i; ?>"></form>
                <form action="lab.php" method="post" id="form2-<?php echo $row['days'] . "-" . $i; ?>"></form>
                <div class="cell-form">
                  <div class="form-ele">
                    <label>
                      <span class="mpx2">Select Subject</span> <input required list="subjects-<?php echo $row['days'] . "-" . $i; ?>" name="subject" class="inp" form="form1-<?php echo $row['days'] . "-" . $i; ?>">
                      <datalist id="subjects-<?php echo $row['days'] . "-" . $i; ?>">
                        <?php
                        $tableS = $tt->getTableData('subject');
                        foreach ($tableS as $rowS) :
                          if ($rowS['SubjectName']) {
                        ?>
                            <option value="<?php echo $rowS['SubjectName']; ?>">
                          <?php }
                        endforeach;
                          ?>
                      </datalist>
                    </label>
                  </div>
                  <div class="form-ele">
                    <label>
                      Select Teacher <input required list="teachers-<?php echo $row['days'] . "-" . $i; ?>" name="teacher" class="inp" form="form1-<?php echo $row['days'] . "-" . $i; ?>">
                      <datalist id="teachers-<?php echo $row['days'] . "-" . $i; ?>">
                        <?php
                        $tableT = $tt->getTableData('teacher');
                        foreach ($tableT as $rowT) :
                          if ($rowT['TeacherName']) {
                            $tt->getData("teacher", "TeacherName", $rowT['TeacherName'], "MaxNoOfLec") ? $mxl = $tt->getData("teacher", "TeacherName", $rowT['TeacherName'], "MaxNoOfLec") : $mxl =  100000;
                            if ($tt->isCellNull($rowT['TeacherName'], 'Lecture_No', $i, $row['days']) && $tt->noOfLec($rowT['TeacherName']) < $mxl) {
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
                      <span class="mpx">Select Room</span> <input required list="rooms-<?php echo $row['days'] . "-" . $i; ?>" name="room" class="inp" form="form1-<?php echo $row['days'] . "-" . $i; ?>">
                      <datalist id="rooms-<?php echo $row['days'] . "-" . $i; ?>">
                        <?php
                        $tableR = $tt->getTableData('room');
                        foreach ($tableR as $rowR) :
                          if ($rowR['RoomNo']) {
                            if ($tt->isCellNull($rowR['RoomNo'], 'Lecture_No', $i, $row['days'])) {
                        ?>
                              <option value="<?php echo $rowR['RoomNo']; ?>">
                          <?php }
                          }
                        endforeach;
                          ?>
                      </datalist>
                    </label>
                  </div>
                  <div>
                    <!-- for form1 -->
                    <input type="hidden" name='class' value='<?php echo $class; ?>' form="form1-<?php echo $row['days'] . "-" . $i; ?>">
                    <input type="hidden" name='lec' value='<?php echo $i; ?>' form="form1-<?php echo $row['days'] . "-" . $i; ?>">
                    <input type="hidden" name='day' value='<?php echo $row['days']; ?>' form="form1-<?php echo $row['days'] . "-" . $i; ?>">

                    <!-- for form2 -->
                    <input type="hidden" name='class' value='<?php echo $class; ?>' form="form2-<?php echo $row['days'] . "-" . $i; ?>">
                    <input type="hidden" name='lec' value='<?php echo $i; ?>' form="form2-<?php echo $row['days'] . "-" . $i; ?>">
                    <input type="hidden" name='day' value='<?php echo $row['days']; ?>' form="form2-<?php echo $row['days'] . "-" . $i; ?>">
                  </div>
                  <div class="form-ele">
                    <button type="submit" name="lab" value="submit" class="btn" form="form2-<?php echo $row['days'] . "-" . $i; ?>">Lab</button>
                    <button type="submit" name="enter-data" class="btn" value="submit" form="form1-<?php echo $row['days'] . "-" . $i; ?>">Submit</button>
                  </div>
                </div>
              <?php
              }
              ?>
            </div>
          <?php
            endforeach;
          ?>
        </div>
    <?php
          }
        }
    ?>
    </div>
  </section>
  <footer></footer>
</body>

</html>