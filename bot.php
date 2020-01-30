
<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

  include "kernel.php";
	$data = json_decode(file_get_contents('php://input'));
	$group = $data->group_id;
	$confirmation_token = '2582a13f'; //Строка для подтверждения адреса сервера из настроек Callback API
	$token_Group = '1e04fea4baa6008e4f0b64ef8e4085fdfe5a394e341632e958d2b92d7a580099fa9c2135e4bea9796d5f1';
	$vk = new vk("", $token_Group);
	$db = new database();
	switch ($data->type)
	{
	case 'confirmation':
	exit($confirmation_token);

	case 'message_new':
  $vuz = array('volsu'=> "ВолГУ", 'vstu'=> "Политех", 'ped'=> "Педагогический", 'ranhigs'=> "Ранхигс");
	$key_settings = 1;
	$chat=$data->object->peer_id;
	$user_id = $data->object->from_id;
	$grouper = $data->group_id;
	$id_mes= $data->object->id;
  $tester = $spam_m = $vk->chat_text = $data->object->text;
  $publish_date = $data->object->date;
	$vk->putChatUser($data->object->peer_id, $data->object->from_id);
  $vk->keyboard_m(array("Начать"), true);
  $users_data = $db->execute_query("SELECT * FROM users WHERE user_id =  '$user_id'");
  if($users_data['FIO'] == false && $users_data['user_id'] == false){
    $db->execute_query("INSERT INTO users SET user_id = '$user_id'", true);
    $vk->messageFromGroup("Приветствую. Для начала работы, напиши свое ФИО");
    die('ok');
  }elseif($users_data['FIO'] == false && $users_data['user_id'] != false){
    $vk->keyboard_m(array("Рейтинг"), true);
    $db->execute_query("UPDATE users SET FIO = '".mb_strtoupper($tester)."' WHERE user_id = '$user_id'", true);
    $vk->messageFromGroup("Все готово.\nДля начала работы напиши: рейтинг");
    die('ok');
  }else  $vk->keyboard_m(array("Рейтинг"), true);
  $tester = preg_replace("/\[(.*)\]\s/","",mb_strtolower($tester));
  $tester = preg_replace("/ё/","е",$tester);
  switch($tester){
    case 'начать':
    if($users_data['FIO'] == false && $users_data['user_id'] == false){
      $db->execute_query("INSERT INTO users SET user_id = '$user_id'", true);
      $vk->messageFromGroup("Приветствую. Для начала работы, напиши свое ФИО");
      die('ok');
    }

    break;

    case 'рейтинг':
    $q_v_ab = $db->execute_query("SELECT DISTINCT vuz FROM abiturs_rating WHERE FIO like '%".$users_data['FIO']."%'")['kolit_zapis'];
    if( $q_v_ab > 1 ){
      $arr_vuz = $db->execute_query("SELECT DISTINCT vuz FROM abiturs_rating WHERE FIO like '%".$users_data['FIO']."%'", true);
      while ($value = mysqli_fetch_assoc($arr_vuz)) $keyboard_vuz[] = $vuz[$value['vuz']];
      $vk->keyboard_m($keyboard_vuz);
      $vk->messageFromGroup("Документы находятся в разных вузах. Пожалуйста, выберите нужный");
      exit('ok');
    }else    $vk->get_rating($users_data['FIO'], true);
    break;

    default:
    if(in_array($spam_m, $vuz)){
      $arr_vuz = $db->execute_query("SELECT DISTINCT vuz FROM abiturs_rating WHERE FIO like '%".$users_data['FIO']."%'", true);
      while ($value = mysqli_fetch_assoc($arr_vuz)) $keyboard_vuz[] = $vuz[$value['vuz']];
      $vk->keyboard_m($keyboard_vuz);
      $vk->get_rating($users_data['FIO'], false, array_search($spam_m, $vuz));
    }else{
      $q_ab = $db->execute_query("SELECT DISTINCT LTRIM(FIO) FROM abiturs_rating WHERE FIO like '%$tester%'")['kolit_zapis'];
      if($q_ab > 1){
        $vk->messageFromGroup("Существает $q_ab c такими данными. Напиши его подробнее");
        die('ok');
      }
      $q_v_ab = $db->execute_query("SELECT DISTINCT vuz FROM abiturs_rating WHERE FIO like '%$tester%'")['kolit_zapis'];
      if( $q_v_ab > 1 ){
        $arr_vuz = $db->execute_query("SELECT DISTINCT vuz FROM abiturs_rating WHERE FIO like '%$tester%'", true);
        while ($value = mysqli_fetch_assoc($arr_vuz)) $keyboard_vuz[] = $vuz[$value['vuz']];
        $db->execute_query("UPDATE users SET FIO = '".mb_strtoupper($tester)."' WHERE user_id = '$user_id'");
        $vk->keyboard_m($keyboard_vuz);
        $vk->messageFromGroup("Документы находятся в разных вузах. Пожалуйста, выберите нужный");
        exit('ok');
      }else   $vk->get_rating($tester, true);
    }
    break;
  }
	echo ('ok');
	break;
	}

?>
