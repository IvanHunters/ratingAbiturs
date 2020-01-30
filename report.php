<?php
include "kernel.php";
$db = new database();
$q = 1;

$filename = "report.xls";


$query = "SELECT * FROM abiturs_rating WHERE vuz= 'vstu' ORDER BY FIO ASC, document DESC";
$get_info = $db->execute_query($query, true);
?>
<?php while($row = $get_info->fetch_assoc()):?>
    <?php
	$abiturs[$row['FIO']]['predmets'][$row['predmet_1']] = $row['ball_1']?$row['ball_1']:"-";
    $abiturs[$row['FIO']]['predmets'][$row['predmet_2']] = $row['ball_2']?$row['ball_2']:"-";
    $abiturs[$row['FIO']]['predmets'][$row['predmet_3']] = $row['ball_3']?$row['ball_3']:"-";
    $abiturs[$row['FIO']]['programm'][$row['programm']]=array( 'document'=> $row['document'], 'system_of_preparation'=>  $row['system_of_preparation'],'consent'=> $row['consent'] ? $row['consent'] : "-", 'form'=> $row['form'], 'sum_ball'=>$row['sum_ball'] );
    $abiturs[$row['FIO']]['individual_ach'] = $row['individual_ach'];

	$predmets[$row['predmet_1']]=0;
	$predmets[$row['predmet_2']]=0;
	$predmets[$row['predmet_3']]=0;
	
    ?>
    <?php endwhile;?>
	<?php $get_info = $db->execute_query($query, true); ?>
<table width="100%" height="441" border="1">
  <tr>
      <th height="31" >№</th>
      <th height="31" >ФИО</th>
      <th height="31" >Направление</th>
      <th height="31" >Форма обучения</th>
      <th height="31" >Система подготовки</th>
	  <?php foreach($predmets as $pr_a=>$undef):?>
		<?php if($pr_a == '-') continue;?>
        <th height="31" ><?=$pr_a?></th>
		<?php endforeach;?>
      <th height="31" >Вид экзаменов</th>
      <th height="31" >Cумма баллов</th>
      <th height="31" >Документ</th>
      <th height="31" >Согласие на зачисление</th>
      <th height="31" >Статус</th>
  </tr>
  <tbody>
  <tr>
  <?php  while($row = mysqli_fetch_assoc($get_info)):?>
  <?php 
	unset($tec_predmets, $vid, $lock);
	$tec_predmets[$row['predmet_1']]=$row['ball_1'];
	$tec_predmets[$row['predmet_2']]=$row['ball_2'];
	$tec_predmets[$row['predmet_3']]=$row['ball_3'];
  ?>
        <th><?=$q ?></th>
        <th  ><?=$row['FIO'] ?></th>
        <th  ><?=$row['programm'] ?></th>
        <th  ><?=$row['form'] ?></th>
        <th  ><?=$row['system_of_preparation'] ?></th>
		<?php foreach($predmets as $pr_a=>$undef){
			if($pr_a == '-') continue;					
					echo '<th>'.(int)$tec_predmets[$pr_a].'</th>';
		}
				
		?>
        <th  ><?= $row['form_vstupit'] ?></th>
        <th  ><?=$row['sum_ball'] ?></th>
        <th  ><?=$row['document']?></th>
        <th  ><?=$row['consent']?></th>
        <th  ><?=$row['dop']?></th>
    </tr>
    <?php $q++;?>
  <?php endwhile;?>
  </tbody>
</table>
