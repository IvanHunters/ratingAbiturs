<?php

include "kernel.php";
 ini_set('error_reporting', E_ALL);
 ini_set('display_errors', 1);
 ini_set('display_startup_errors', 1);
/*
 $vstu = new vstu("http://welcome.vstu.ru/acceptance/reyting-abiturientov/forms.php");
 $vstu->check_abitur();
*/
 $ranhigs = new ranhigs("https://vlgr.ranepa.ru/abitur/priem/");
 $ranhigs->check_abitur();


?>
