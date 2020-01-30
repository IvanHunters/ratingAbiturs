 <?php
include "kernel.php";
require_once "volsu.php";
 ini_set('error_reporting', E_ALL);
 ini_set('display_errors', 0);
 ini_set('display_startup_errors', 0);

 $vstu = new vstu("http://welcome.vstu.ru/acceptance/reyting-abiturientov/forms.php");
 $vstu->check_abitur();

 $volsu = new volsu();

 $ranhigs = new ranhigs("https://vlgr.ranepa.ru/abitur/priem/");
 $ranhigs->check_abitur();

 $ped = new ped("https://prcom2019.vspu.ru/api");
 $ped->check_abitur();
 ?>
