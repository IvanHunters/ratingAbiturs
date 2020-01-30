<?php
include "kernel.php";
$db = new database();
$vuz = $_POST['vuz'];
$filename =date("d-m-Y")." $vuz (".$_POST['system_of_preparation'].").xls";
 header ( "Expires: Mon, 1 Apr 1974 05:00:00 GMT" );
 header ( "Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT" );
 header ( "Cache-Control: no-cache, must-revalidate" );
 header ( "Pragma: no-cache" );
 header ( "Content-type: application/vnd.ms-excel" );
 header ( "Content-Disposition: attachment; filename=$filename" );
?>
<table width="100%" height="441" border="1">
<?php

if($_POST['system_of_preparation']=='bs')
$_POST['system_of_preparation'] = "AND (`system_of_preparation` = 'специалитет' OR `system_of_preparation`='бакалавриат')";
else
$_POST['system_of_preparation'] = "AND system_of_preparation = '".$_POST['system_of_preparation']."'";

if($vuz == 'vstu_fil'){
$vuz= "volpi' OR vuz='kti' OR vuz='sfvstu";
}

$get_info = $db->execute_query("SELECT * FROM abiturs_rating WHERE vuz = '$vuz' ".$_POST['system_of_preparation']." ORDER BY sum_ball ASC",true);
?>

    <?php while($row = $get_info->fetch_assoc()):?>
    <?php

    if	( ( $row['predmet_3']   ==  "Математика"    &&  (int)   $row['ball_3']  <   33    &&  (int)   $row['ball_3']!=0)||
		( $row['predmet_1']   ==  "Математика"    &&  (int)   $row['ball_1']  <   33    &&  (int)   $row['ball_1']!=0)
		||  $row['predmet_1']   ==  'химия' || $row['predmet_2']   ==  'химия' || $row['predmet_3']   ==  'химия'
		||  ($row['predmet_1'] == "Биология" &&  (int)   $row['ball_1']  <   38    &&  (int)   $row['ball_1']!=0)
		|| ($row['predmet_2'] == "биология" &&  (int)   $row['ball_2']  <   38    &&  (int)   $row['ball_2']!=0)
	)   continue;
	$predmets[$row['predmet_1']]=0;
	$predmets[$row['predmet_2']]=0;
	$predmets[$row['predmet_3']]=0;

    $abiturs[$row['FIO']]['predmets'][$row['predmet_1']] = $row['ball_1']?$row['ball_1']:"-";
    $abiturs[$row['FIO']]['predmets'][$row['predmet_2']] = $row['ball_2']?$row['ball_2']:"-";
    $abiturs[$row['FIO']]['predmets'][$row['predmet_3']] = $row['ball_3']?$row['ball_3']:"-";
    $abiturs[$row['FIO']]['programm'][$row['programm']]=array( 'document'=> $row['document'], 'system_of_preparation'=>  $row['system_of_preparation'],'consent'=> $row['consent'] ? $row['consent'] : "-", 'form'=> $row['form'], 'sum_ball'=>$row['sum_ball'] );
    $abiturs[$row['FIO']]['individual_ach'] = $row['individual_ach'];

    ?>
    <?php endwhile;?>
    <?php $q = 1;?>
    <tr>
        <th height="31" scope="col">№</th>
        <th height="31" scope="col">ФИО</th>
        <th height="31" scope="col">Направление</th>
		<?php foreach($predmets as $pr_a=>$undef):?>
		<?php if($pr_a == '-') continue;?>
        <th height="31" scope="col"><?=$pr_a?></th>
		<?php endforeach;?>
        <th height="31" scope="col">Форма</th>
        <th height="31" scope="col">Квалификация</th>
        <th height="31" scope="col">Сумма баллов</th>
        <th height="31" scope="col">Инд. достижения</th>
        <th height="31" scope="col">Документ</th>
        <th height="31" scope="col">Согласие</th>
    </tr>
    <?php foreach($abiturs as $FIO=>$abitur):?>
        <tr>
            <th height="100" scope="col"><?=$q ?></th>
            <th height="100" scope="col"><?=$FIO ?></th>
            <?php
unset($pr,$frm,$sop,$sum,$doc,$cons, $pr_p, $resp);
            foreach($abitur['programm'] as $program=>$detail_program){
                $pr[] = $program;
                $frm[] = $detail_program['form'];
                $sop[] = $detail_program['system_of_preparation'];
                $sum[]  = $detail_program['sum_ball'];
                $doc[] = $detail_program['document'];
                $cons[] = $detail_program['consent'];
            }
			
				foreach($abitur['predmets'] as $pred=>$ball){
					$pr_p[]= $pred;
				}
            foreach($predmets as $pr_a=>$undef){
				if($pr_a == '-') continue;
					if(!in_array($pr_a, $pr_p)){
						$resp.= '<th height="100" scope="col">0</th>';
					}else{
						$resp.= '<th height="100" scope="col">'.(int) $abitur['predmets'][$pr_a].'</th>';
					}
				}
            ?>
            <th height="100" scope="col"><?= implode("||",$pr) ?></th>
            <?= $resp ?>
            <th height="100" scope="col"><?= implode("||",$frm) ?></th>
            <th height="100" scope="col"><?= implode("||",$sop) ?></th>
            <th height="100" scope="col"><?= $sum[0] ?></th>
            <th height="100" scope="col"><?= $abitur['individual_ach'] ?></th>
            <th height="100" scope="col"><?= implode("||",$doc) ?></th>
            <th height="100" scope="col"><?= implode("||",$cons) ?></th>
        </tr>
    <?php $q++?>
    <?php endforeach ?>
</table>
