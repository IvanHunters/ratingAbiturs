<?php
include "kernel.php";
$db = new database();
$q = 1;

$filename = "report.xls";
header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
header ( "Cache-Control: no-cache, must-revalidate" );
header ( "Pragma: no-cache" );
header ( "Content-type: application/vnd.ms-excel" );
header ( "Content-Disposition: attachment; filename=".$filename );

$query = "SELECT * FROM (SELECT FIO, predmet_1, predmet_2, predmet_3, predmet_4, ball_1, ball_2, ball_3, ball_4 FROM `fizics_abiturs` UNION SELECT FIO, predmet_1, predmet_2, predmet_3, predmet_4, ball_1, ball_2, ball_3, ball_4 FROM `inf_abiturs`) a ORDER BY a.FIO ASC";
$abiturs = $db->execute_query($query, true);
?>
<table width="100%" height="441" border="1">
  <tr>
      <th height="31" scope="col">№</th>
      <th height="31" scope="col">ФИО</th>
      <th height="31" scope="col">Физика</th>
      <th height="31" scope="col">Информатика</th>
      <th height="31" scope="col">Математика</th>
      <th height="31" scope="col">Русский</th>
  </tr>
  <?php  while($row = mysqli_fetch_assoc($abiturs)):?>
    <tr>
        <th height="100" scope="col"><?=$q ?></th>
        <th height="100" scope="col"><?=$row['FIO'] ?></th>
        <th height="100" scope="col"><?=$row['ball_1'] ?></th>
        <th height="100" scope="col"><?php if($row['predmet_2'] == "информатика и ИКТ") echo $row['ball_2']; else echo $row['ball_3']; ?></th>
        <th height="100" scope="col"><?php if($row['predmet_3'] == "информатика и ИКТ") echo $row['ball_2']; else echo $row['ball_3']; ?></th>
        <th height="100" scope="col"><?=$row['ball_4'] ?></th>
    </tr>
    <?php $q++;?>
  <?php endwhile;?>
</table>
