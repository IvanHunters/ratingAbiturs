 <?php
include "kernel.php";
require_once "volsu.php";
 ini_set('error_reporting', E_ALL);
 ini_set('display_errors', 0);
 ini_set('display_startup_errors', 0);

 $volsu = new volsu();
 while(true){
 $volsu->get_abiturs_rating();
 sleep(300);
}
 ?>
