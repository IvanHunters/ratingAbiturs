<?php
include "../kernel.php";

$db = new database();
$FIO = $_GET['fio'];
get_rating_json($FIO, $_GET['json']);

function get_rating_json($fio, $json){
  global $db;
  if(!isset($fio)){
    $result['error'] = "1";
    $result['code_error'] = "Введите ФИО!";
    return_json($result);
  }
  $q_ab = $db->execute_query("SELECT DISTINCT LTRIM(FIO) FROM abiturs_rating WHERE FIO like '%$fio%'")['kolit_zapis'];
  if($q_ab>1){
    $result['error'] = "2";
    $result['code_error'] = "Такие данные есть у $q_ab человек.\nВведите более полные данные";
    return_json($result);
  }elseif ($q_ab == 0 ) {
    $result['error'] = "3";
    $result['code_error'] = "Пользователя с тикими данными не существует";
    return_json($result);
  }

  $ar_data = $db->execute_query("SELECT * FROM abiturs_rating WHERE LTRIM(FIO) like '%$fio%' $vuz_param", true);
  while($row = mysqli_fetch_assoc($ar_data))  $r[$row['vuz']][]= $row;

  foreach($r as $vuz_r => $rat_info){
    $result[$vuz_r]['document']=$rat_info[0]['document'];
    $result[$vuz_r]['updated']=date("d-m-Y H:i",$rat_info[0]['updated']);

    foreach ($rat_info as $key => $rower) {
      $result[$vuz_r][$rower['programm']]['predmet_1'] = $rower['predmet_1'];
      $result[$vuz_r][$rower['programm']]['ball_1'] = $rower['ball_1'];
      if($rower['predmet_2']){
      $result[$vuz_r][$rower['programm']]['predmet_2'] = $rower['predmet_2'];
      $result[$vuz_r][$rower['programm']]['ball_2'] = $rower['ball_2'];
      }

      if($rower['predmet_3']){
      $result[$vuz_r][$rower['programm']]['predmet_3'] = $rower['predmet_3'];
      $result[$vuz_r][$rower['programm']]['ball_3'] = $rower['ball_3'];
      }

      $result[$vuz_r][$rower['programm']]['predpolog_position'] = '<b>-</b>';
      $result[$vuz_r][$rower['programm']]['priority'] = '<b>-</b>';
      $result[$vuz_r][$rower['programm']]['mest'] = '<b>-</b>';
      $result[$vuz_r][$rower['programm']]['osnovanie'] = '<b>-</b>';
      $result[$vuz_r][$rower['programm']]['sum_ball'] = $rower['sum_ball'];
      $result[$vuz_r][$rower['programm']]['form'] = $rower['form'];
      $result[$vuz_r][$rower['programm']]['system_of_preparation'] = $rower['system_of_preparation'];

      $result[$vuz_r][$rower['programm']]['position'] = $vuz_r == "volsu" ? $db->execute_query("SELECT nast_position, FIO FROM (SELECT *, @i:=@i+1 as nast_position FROM `abiturs_rating`, (SELECT @i:=0) d WHERE programm = '".$rower['programm']."' and vuz = '".$rower['vuz']."' and konk_group = '".$rower['konk_group']."' ORDER BY sum_ball DESC) dd WHERE FIO = '".$rower['FIO']."' LIMIT 1")['nast_position'] : $rower['position'];
      if($vuz_r == "volsu"){

              $result[$vuz_r][$rower['programm']]['priority'] = $vuz_r == "volsu"? $rower['priority'] : "";
              $result[$vuz_r][$rower['programm']]['mest'] = $vuz_r == "volsu"? $rower['mest'] : "";

              /*$result[$vuz_r][$rower['programm']]['position'] = $vuz_r == "volsu"? $db->execute_query("SELECT nast_position, FIO FROM (SELECT *, @i:=@i+1 as nast_position FROM `abiturs_rating`, (SELECT @i:=0) d WHERE programm = '".$rower['programm']."' and konk_group = '".$rower['konk_group']."' and osnovanie = '".$rower['osnovanie']."' and vuz = '".$rower['vuz']."' and priority = '1' and document = 'Оригинал' ORDER BY sum_ball DESC) dd WHERE FIO = '".$rower['FIO']."'")['nast_position'] != false? $db->execute_query("SELECT nast_position, FIO FROM (SELECT *, @i:=@i+1 as nast_position FROM `abiturs_rating`, (SELECT @i:=0) d WHERE programm = '".$rower['programm']."' and konk_group = '".$rower['konk_group']."' and osnovanie = '".$rower['osnovanie']."' and priority = '1' and document = 'Оригинал' ORDER BY sum_ball DESC) dd WHERE FIO = '".$rower['FIO']."'")['nast_position']: "Не конкурентен"  : "Неизвестно";
              */
              $result[$vuz_r][$rower['programm']]['position'] = ($vuz_r == "volsu" && ((int) $rower['priority'] != 1 || $rower['document'] == 'Копия')) ? $db->execute_query("SELECT nast_position + 1 as nast_position, FIO FROM (SELECT *, @i:=@i+1 as nast_position FROM `abiturs_rating`, (SELECT @i:=0) d WHERE programm = '".$rower['programm']."' and konk_group = '".$rower['konk_group']."' and osnovanie = '".$rower['osnovanie']."' and vuz = '".$rower['vuz']."' and priority = '1' and document = 'Оригинал' ORDER BY sum_ball DESC) dd WHERE sum_ball >= (SELECT sum_ball FROM `abiturs_rating` WHERE programm = '".$rower['programm']."' and konk_group = '".$rower['konk_group']."' and osnovanie = '".$rower['osnovanie']."' and vuz = '".$rower['vuz']."' AND FIO = '".$rower['FIO']."') ORDER BY sum_ball ASC LIMIT 1")['nast_position'] : "0";
              if($vuz_r == "volsu" && $result[$vuz_r][$rower['programm']]['position'] == false) $result[$vuz_r][$rower['programm']]['predpolog_position'] = 1;
              if($pred_position != "0" && $vuz_r == "volsu" && ((int) $rower['priority'] > 1 || $rower['document'] == "Копия")) $pred_position_text = "\nПредполагаемая позиция если направление будет первым приоритетом и будет оригинал в ВУЗе: ".$pred_position;
              $result[$vuz_r][$rower['programm']]['osnovanie'] = $rower['osnovanie'];
              if($result[$vuz_r][$rower['programm']]['position'] == "0") $result[$vuz_r][$rower['programm']]['position'] = 1;

      }
    unset($nast_pos, $warn, $pred_position_text, $FIO);
    }
  }

    if(!is_null($json))
    return_json($result);
    else
    return_html($result);
}
function return_json($array){
  die (json_encode($array, JSON_UNESCAPED_UNICODE));
}
function return_html($array){
  $vuz_arr = array('volsu'=> "ВолГУ", 'vstu'=> "ВолГТУ", 'ped'=> "ВГСПУ", 'ranhigs'=> "Ранхигс");
  if(isset($_GET['app'])) echo '
  <!-- подключение css-файла -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css"
  integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">


  <!-- подключение нужной версии jQuery -->
  <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
  integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
  </script>

  <!-- подключение popper.js, необходимого для корректной работы некоторых плагинов Bootstrap 4 -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
  integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous">
  </script>

  <!-- подключение js-файла -->
   <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"
  integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T" crossorigin="anonymous">
  </script>
  ';
  ?>
  <br><br><br>
  <?php  foreach ($array as $vuz => $data):?>
    <h3 align = "center"><?= $vuz_arr[$vuz] ?> обновлено <?=$data['updated']?></h3>
  <table class="table table-striped" style="width:50% !important;  margin: 0 auto;">
  <thead class="thead-striped">
    <tr>
      <th scope="col">Положение</th>

        <th scope="col">Направление</th>
        <?php  if(true): ?>
        <th scope="col">Количество бюджетных мест</th>
        <th scope="col">Основание</th>
        <?php  endif;?>
        <th scope="col">Сумма баллов</th>
        <th scope="col">Вид документа</th>
        <th scope="col">Форма обучения</th>
        <th scope="col">Система подготовки</th>
    </tr>
  </thead>
  <tbody>
<?php foreach ($data as $name_program => $data_program):?>
  <?php if($name_program == "document" || $name_program == "updated") continue; ?>
<tr>
  <td><b><?=$data_program['position'];?><b></th>

    <td><b><?=$name_program;?></b></td>
    <?php  if(true): ?>
    <td><?=$data_program['mest'];?></td>
    <td><?=$data_program['osnovanie'];?></td>
    <?php  endif;?>
    <td><b><?=$data_program['sum_ball'];?></b></td>
    <td><b><?=strtolower($data['document']);?></b></td>
    <td><b><?=strtolower($data_program['form']);?></b></td>
    <td><b><?=$data_program['system_of_preparation'];?></b></td>
</tr>
<?php  endforeach;?>
</tbody>
</table>
<br><br>
<?php  endforeach;?>
<?php }?>
