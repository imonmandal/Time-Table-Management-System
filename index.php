<?php
function initDB()
{
  // Database connection properties
  $host = 'localhost';
  $user = 'root';
  $password = '0110';
  $database = 'time_table';

  $con = mysqli_connect($host, $user, $password);
  if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
  } else {
    $query_string = "create database `{$database}`;";
    $result = $con->query($query_string);

    if ($result) {
      mysqli_select_db($con, $database); // select database

      $query_string = "CREATE TABLE `class` (`ClassName` varchar(75));";
      mysqli_query($con, $query_string);

      $query_string = "CREATE TABLE `teacher` (`TeacherName` varchar(75), `MaxNoOfLec` varchar(5));";
      mysqli_query($con, $query_string);

      $query_string = "CREATE TABLE `room` (`RoomNo` varchar(75));";
      mysqli_query($con, $query_string);

      $query_string = "CREATE TABLE `subject` (`SubjectName` varchar(75), `LabName` varchar(75));";
      mysqli_query($con, $query_string);

      $query_string = "CREATE TABLE `week`(`days` varchar(25));";
      mysqli_query($con, $query_string);
      $query_string = "INSERT INTO `week` values ('Monday'), ('Tuesday'), ('Wednesday'), ('Thursday'), ('Friday'), ('Saturday');";
      mysqli_query($con, $query_string);

      $query_string = "CREATE TABLE `lec`(`sr_no` int, `lec` int);";
      mysqli_query($con, $query_string);
      $query_string = "INSERT INTO `lec` values(1, NULL);";
      mysqli_query($con, $query_string);
    }
    $con->close();
  }
}

initDB();

require_once("Database/DBController.php");
require_once("Database/time_table.php");

$db = new DBController();
$tt = new time_table($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  if (isset($_GET['class-table-not-pres'])) {
    echo sprintf('<script type="text/javascript">alert("Table of class %s is not there in database");</script>', $_GET['class-table-not-pres']);
  }
  if (isset($_GET['teacher-table-not-pres'])) {
    echo sprintf('<script type="text/javascript">alert("Table of teacher %s is not there in database");</script>', $_GET['teacher-table-not-pres']);
  }
  if (isset($_GET['room-table-not-pres'])) {
    echo sprintf('<script type="text/javascript">alert("Table of room %s is not there in database");</script>', $_GET['room-table-not-pres']);
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

  if (isset($_POST['clear'])) {
    $tt->dropDatabase();
    header("Location: index.php");
    exit();
  }

  if (isset($_POST['enter'])) {

    $tt->clear();

    $cA = $_FILES['cls'];
    $tA = $_FILES['techr'];
    $rA = $_FILES['rm'];
    $sA = $_FILES['sb'];
    $n = $_POST['l'];
    $tt->createTables($cA, $tA, $rA, $sA, $n);
    echo '<script type="text/javascript">alert("Data entered successfully");</script>';
  }

  if (isset($_POST['download'])) {
    // Generate Excelsheet from database
    $tt->downloadFromDatabase();

    // Create zip function
    function createZip($zip, $dir)
    {
      if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
          while (($file = readdir($dh)) !== false) {
            // If file
            if (is_file($dir . $file)) {
              if ($file != '' && $file != '.' && $file != '..') {

                $zip->addFile($dir . $file);
              }
            } else {
              // If directory
              if (is_dir($dir . $file)) {
                if ($file != '' && $file != '.' && $file != '..') {
                  // Add empty directory
                  $zip->addEmptyDir($dir . $file);

                  $folder = $dir . $file . '/';

                  // Read data of the folder
                  createZip($zip, $folder);
                }
              }
            }
          }
          closedir($dh);
        }
      }
    }

    // Create zip
    $zip = new ZipArchive();
    $filename = "./time_table.zip";
    if ($zip->open($filename, ZipArchive::CREATE) !== TRUE) {
      exit("cannot open <$filename>\n");
    }
    $dir = 'table_data_download/';
    createZip($zip, $dir);
    $zip->close();

    $filename = "time_table.zip";

    // Download Created zip file
    if (file_exists($filename)) {
      header('Content-Type: application/zip');
      header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
      header('Content-Length: ' . filesize($filename));

      flush();
      readfile($filename);
      // delete zip file
      unlink($filename);
    }
  }

  // delete table_data_download folder
  function delete_folder($folder)
  {
    if (is_dir($folder)) {
      $files = array_diff(scandir($folder), array('.', '..'));
      foreach ($files as $file) {
        if (is_dir("$folder/$file")) {
          delete_folder("$folder/$file");
        } else {
          unlink("$folder/$file");
        }
      }
      rmdir($folder);
    }
  }

  $folder = './table_data_download';
  delete_folder($folder);
}
?>

<!DOCTYPE html>
<html>

<head>
  <title>Time Table</title>
  <script src="html/js/index.js"></script>
  <link rel="stylesheet" href="html/scss/index.css">
</head>

<body>
  <h1>Time Table Management System</h1>
  <div class="container">
    <div class="con">
      <form action="index.php" method="post" id="form1" enctype="multipart/form-data"></form>
      <form action="index.php" method="post" id="form2"></form>
      <div class="fm-con1">
        <div class="fm-ele1">
          <label for="cls"><span>Class</span></label>
          <input form="form1" required type="file" placeholder="Upload Class Excelsheet" name="cls" id="cls" />
        </div>
        <div class="fm-ele1">
          <label for="techr"><span>Teacher</span></label>
          <input form="form1" required type="file" placeholder="Upload Teacher Excelsheet" name="techr" id="techr" />
        </div>
        <div class="fm-ele1">
          <label for="rm"><span>Room</span></label>
          <input form="form1" required type="file" placeholder="Upload Room Excelsheet" name="rm" id="rm" />
        </div>
        <div class="fm-ele1">
          <label for="sb"><span>Subject</span></label>
          <input form="form1" required type="file" placeholder="Upload Subject Excelsheet" name="sb" id="sb" />
        </div>
        <div class="fm-ele1">
          <label for="l"><span>No of Lec</span></label>
          <input form="form1" required type="text" placeholder="Enter Number of Lectures" name="l" id="l" />
        </div>
        <div class="fm-ele1">
          <button form="form1" type="submit" name="enter" value="submit"><span>Enter Data</span></button>
          <button form="form2" type="submit" name="clear" value="submit" Onclick="return ConfirmDelete();" value="1"><span>Clear Database</span></button>
        </div>
      </div>
    </div>

    <div class="con fm-con2">
      <h2>View Table</h2>
      <div class="fm-ele2">
        <form action="class.php" method="get">
          <label>
            Class <input list="classes" name="class-data" placeholder="Select Class" required>
            <datalist id="classes">
              <?php
              $tableC = $tt->getTableData('class');
              foreach ($tableC as $rowC) :
              ?>
                <option value="<?php echo $rowC['ClassName']; ?>">
                <?php endforeach;
                ?>
            </datalist>
          </label>
          <button type="submit" name="class" value="submit">Submit</button>
        </form>
      </div>
      <div class="fm-ele2">
        <form action="teacher.php" method="get">
          <label>
            Teacher <input list="teachers" name="teacher-data" placeholder="Select Teacher" required>
            <datalist id="teachers">
              <?php
              $tableT = $tt->getTableData('teacher');
              foreach ($tableT as $rowT) :
              ?>
                <option value="<?php echo $rowT['TeacherName']; ?>">
                <?php endforeach;
                ?>
            </datalist>
          </label>
          <button type="submit" name="teacher" value="submit">Submit</button>
        </form>
      </div>
      <div class="fm-ele2">
        <form action="room.php" method="get">
          <label>
            Room <input list="rooms" name="room-data" placeholder="Select Room" required>
            <datalist id="rooms">
              <?php
              $tableR = $tt->getTableData('room');
              foreach ($tableR as $rowR) :
              ?>
                <option value="<?php echo $rowR['RoomNo']; ?>">
                <?php endforeach;
                ?>
            </datalist>
          </label>
          <button type="submit" name="room" value="submit">Submit</button>
        </form>
      </div>
    </div>
  </div>

  <div class="btm">
    <form action="index.php" method="post">
      <button type="submit" name="download" value="getData">Download</button>
    </form>
  </div>
</body>

</html>