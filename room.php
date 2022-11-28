<!DOCTYPE html>
<html lang="en">

<head>
  <?php

  require_once("Database/DBController.php");
  require_once("Database/time_table.php");

  $db = new DBController();
  $tt = new time_table($db);

  $rm = '301';

  if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (isset($_GET['room'])) {
      $rm  = $_GET['room-data'];
      $f = $tt->doesTableExists($rm);
      if ($f == 0) {
        $url = sprintf("Location: index.php?room-table-not-pres=%s", $rm);
        header($url);
        exit();
      }
    }
  }

  ?>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $rm; ?> Time Table</title>
  <link rel="stylesheet" href="html/scss/style.css" />
</head>

<body>
  <header><?php echo $rm; ?> Time Table</header>
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
              if (!$tt->isCellNull($rm, 'Lecture_No', $i, $row['days'])) {
                $cellData = $tt->getData($rm, 'Lecture_No', $i, $row['days']);
                $arr = explode("#", $cellData);
                if (count($arr) == 1) {
              ?>
                  <div class="disp-data">
                    <?php echo "Lab"; ?>
                  </div>
                <?php
                } else {
                  $className = $arr[0];
                  $teacherName = $arr[1];
                  $subjectName = $arr[2];
                ?>
                  <div class="disp-data">
                    Class :- <?php echo $className; ?>
                  </div>
                  <div class="disp-data">
                    Teacher :- <?php echo $teacherName; ?>
                  </div>
                  <div class="disp-data">
                    Subject :- <?php echo $subjectName; ?>
                  </div>
              <?php
                }
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