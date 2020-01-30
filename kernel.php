<?php
ini_set('error_reporting', 1);
ini_set('display_errors', 1);
class vk {
    private $token_user, $token_group, $user_id, $chat;

    function __construct($tokenUs, $tokenGr) {
        $this->token_user=$tokenUs;
        $this->token_group=$tokenGr;
    }

    function putChatUser($ch, $us){
        $this->user_id = $us;
        $this->chat = $ch;
    }

    function get_token($why){
        echo $this->$why;
    }

    function get_rating($fio, $flag = false, $vuz_param = false){
      global $db, $user_id, $vuz;
      if($vuz_param) $vuz_param = $vuz_param == "volsu" ? "AND vuz = '$vuz_param' ORDER BY priority ASC" : "AND vuz = '$vuz_param'";
      $q_ab = $db->execute_query("SELECT DISTINCT FIO FROM abiturs_rating WHERE FIO like '%$fio%'")['kolit_zapis'];
      if($q_ab>1){
        $this->messageFromGroup("Такие данные есть у $q_ab человек.\nВведите более полные данные");
        die('ok');
      }elseif ($q_ab == 0 ) {
        $this->messageFromGroup("Пользователя с тикими данными не существует");
        die('ok');
      }else{
        if($flag)      $db->execute_query("UPDATE users SET FIO = '".mb_strtoupper($fio)."' WHERE user_id = '$user_id'");
      }
      $ar_data = $db->execute_query("SELECT * FROM abiturs_rating WHERE FIO like '%$fio%' $vuz_param", true);
      while($row = mysqli_fetch_assoc($ar_data))  $r[$row['vuz']][]= $row;

      foreach($r as $vuz_r => $rat_info){
        $mes="ВУЗ: ".$vuz[$vuz_r]."\n"."Документ: ".$rat_info[0]['document'];
        foreach ($rat_info as $key => $rower) {
          $pr_2 = $rower['predmet_2']? $rower['predmet_2'].": ".$rower['ball_2']."\n" : "";
          $pr_3 = $rower['predmet_3']? $rower['predmet_3'].": ".$rower['ball_3']."\n" : "";

          $rower['position'] = $vuz_r == "volsu" ? $db->execute_query("SELECT nast_position, FIO FROM (SELECT *, @i:=@i+1 as nast_position FROM `abiturs_rating`, (SELECT @i:=0) d WHERE programm = '".$rower['programm']."' and vuz = '".$rower['vuz']."' and konk_group = '".$rower['konk_group']."' ORDER BY sum_ball DESC) dd WHERE FIO = '".$rower['FIO']."' LIMIT 1")['nast_position'] : $rower['position'];

          $dop = $vuz_r == "volsu"? "\nПриоритет: ".$rower['priority']."\nМест на направлении: ".$rower['mest'] : "";

          $q = $vuz_r == "volsu"? $db->execute_query("SELECT nast_position, FIO FROM (SELECT *, @i:=@i+1 as nast_position FROM `abiturs_rating`, (SELECT @i:=0) d WHERE programm = '".$rower['programm']."' and konk_group = '".$rower['konk_group']."' and osnovanie = '".$rower['osnovanie']."' and vuz = '".$rower['vuz']."' and priority = '1' and document = 'Оригинал' ORDER BY sum_ball DESC) dd WHERE FIO = '".$rower['FIO']."'")['nast_position'] != false? $db->execute_query("SELECT nast_position, FIO FROM (SELECT *, @i:=@i+1 as nast_position FROM `abiturs_rating`, (SELECT @i:=0) d WHERE programm = '".$rower['programm']."' and konk_group = '".$rower['konk_group']."' and osnovanie = '".$rower['osnovanie']."' and priority = '1' and document = 'Оригинал' ORDER BY sum_ball DESC) dd WHERE FIO = '".$rower['FIO']."'")['nast_position']: "Не конкурентен"  : "Неизвестно";

          $pred_position = ($vuz_r == "volsu" && ((int) $rower['priority'] != 1 || $rower['document'] == 'Копия')) ? $db->execute_query("SELECT nast_position + 1 as nast_position, FIO FROM (SELECT *, @i:=@i+1 as nast_position FROM `abiturs_rating`, (SELECT @i:=0) d WHERE programm = '".$rower['programm']."' and konk_group = '".$rower['konk_group']."' and osnovanie = '".$rower['osnovanie']."' and vuz = '".$rower['vuz']."' and priority = '1' and document = 'Оригинал' ORDER BY sum_ball DESC) dd WHERE sum_ball >= (SELECT sum_ball FROM `abiturs_rating` WHERE programm = '".$rower['programm']."' and konk_group = '".$rower['konk_group']."' and osnovanie = '".$rower['osnovanie']."' and vuz = '".$rower['vuz']."' AND FIO = '".$rower['FIO']."') ORDER BY sum_ball ASC LIMIT 1")['nast_position'] : "0";
          if($vuz_r == "volsu" && $pred_position == false) $pred_position = 1;
          if($pred_position != "0" && $vuz_r == "volsu" && ((int) $rower['priority'] > 1 || $rower['document'] == "Копия")) $pred_position_text = "\nПредполагаемая позиция если направление будет первым приоритетом и будет оригинал в ВУЗе: ".$pred_position;

          if($vuz_r == "volsu"){
            if( $pred_position >  $rower['mest'] && ($rower['document'] == "Копия" || (int) $rower['priority'] > 1) )
              $warn = "ВНИМАНИЕ!!!!!\nВ рейтинге вы ниже, чем мест на направлении.\nПОЖАЛУЙСТА, пересмотрите приоритеты!!!!\n\n";
            elseif($rower['priority'] == "1" && $q > $rower['mest'] && $rower['document'] != "Копия")
              $warn = "ВНИМАНИЕ!!!!!\nВ рейтинге вы ниже, чем мест на направлении.\nПОЖАЛУЙСТА, пересмотрите приоритеты!!!!\n\n";

            $nast_pos =
                        $dop.
                        "\nПозиция в рейтинге: ".$q.
                        $pred_position_text.
                        "\nФорма обучения: ".$rower['form'].
                        "\nВид подготовки: ".$rower['system_of_preparation'].
                        "\nОснование: ".$rower['osnovanie'];
          }
          if($flag) $FIO = $rower['FIO'];
          $mes.="\n".$warn."$FIO\nНаправление: ".$rower['programm'].
                "\nПозиция: ".$rower['position'].
                "\nСумма баллов: ".$rower['sum_ball'].
                $nast_pos.
                "\n______________________________\n".
                $rower['predmet_1'].": ".$rower['ball_1']."\n".
                $pr_2.$pr_3;
        unset($nast_pos, $warn, $pred_position_text, $FIO);
        }
        $this->messageFromGroup($mes);
      }
    }



    function currl($link, $param,$flag=false){
      usleep(334000);
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL, $link);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
    	if($flag)
    	curl_setopt($ch, CURLOPT_POST, true);
    	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); // отключить валидацию ssl
    	curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
    	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Expect:')); // это необходимо, чтобы cURL не высылал заголовок на ожидание
    	if($PROXY)curl_setopt($ch, CURLOPT_PROXY, $PROXY); // Прокси сервер если есть
    	curl_setopt($ch, CURLOPT_POSTFIELDS, $param); // Данные для отправки
    	//curl_setopt($ch, CURLOPT_HEADER, 0); // Не возвращать заголовки
    	$data = curl_exec($ch); // Выполняем запрос
    	curl_close($ch); // Закрываем соединение
    	return json_decode($data,true); // Парсим JSON и отдаем

    }

    function apiVkGroup($method,$param){
        $param['access_token']= $this->token_group;
        return $this->currl("https://api.vk.com/method/$method?v=5.85", $param);
    }

    function apiVkUser($method,$param){
        $param['access_token']= $this->token_user;
        return $this->currl("https://api.vk.com/method/$method?v=5.80", $param);
    }

    function messageFromUser($user,$message){
       return print_r($this->apiVkUser("messages.send",array('message'=>$message, 'user_id'=>$user)),true);
    }

    function messageFromGroup($message, $attachments,$flag=false){
      if(is_array($message))
    	$message = print_r($message,true);
    if($message==false) return;
      if($this->user_id != $this->chat)
        return print_r($this->apiVkGroup("messages.send",array('message'=>"[id".$this->user_id."|Ответ], $message", 'peer_id'=>$this->chat, 'keyboard'=>$this->keyboard, 'attachment'=>$attachments)),true);

       if($this->key_settings == '0'){
          $this->close_keyboard();
       }
       $mass = array('message'=>$message, 'user_id'=>$this->chat, 'attachment'=>$attachments, 'keyboard'=>$this->keyboard );
       if($flag) unset($mass['keyboard']);
      $response = $this->apiVkGroup("messages.send",$mass);
      //print_r($response);
       /* $mass = array('message'=>print_r($response,true), 'user_id'=>$this->chat, 'attachment'=>$attachments);
       $response = $this->apiVkGroup("messages.send",$mass);*/
    }

    function close_keyboard(){
       $this->keyboard = '{"buttons":[],"one_time":true}';
    }

    function keyboard_m($arr_keyboard,$arr_id_buttons = false, $new_button = false){
      global $grouper;
         $a = array();
        $a['buttons']=array();
    	if(!$arr_id_buttons){
    		foreach ($arr_keyboard as $i=>$value) {
    			if($value == "location")
    			$a['buttons'][$i][0]=array('action'=>array('type'=>'location','payload'=>'{"button": "$i"}'));
    			else $a['buttons'][$i][0]=array('action'=>array('type'=>'text','payload'=>'{"id_button": "'.$i.'"}','label'=>$value),'color'=>'default');
    		}
    		if(!$new_button){
    			$i++;
    			$a['buttons'][$i][0]=array('action'=>array('type'=>'vkpay','hash'=>"action=transfer-to-group&group_id=$grouper&aid=10"));
    		}
    	}else{
    		foreach ($arr_keyboard as $i=>$value) {
    			$a['buttons'][0][0]=array('action'=>array('type'=>'text','payload'=>'{"id_button": "'.$arr_id_buttons[$i].'"}','label'=>$value),'color'=>'default');
    		}
    	}

        $this->keyboard =json_encode($a, JSON_UNESCAPED_UNICODE);
    }

    function uploadFile($file, $upload_url){
      $aPost = array(
       'file' => new CURLFile($file)
    );
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $upload_url);
      curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $aPost);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $res = curl_exec ($ch);
      curl_close($ch);
      $res = json_decode($res,true)['file'];
      return $res;
    }

    function photosSave($id_album, $group_id, $server, $photos_list, $hash){
    return print_r(apiVkUser('photos.save', array('album_id'=>"$id_album", 'group_id'=>$group_id, 'server'=>$server, 'photos_list'=>$photos_list, 'hash'=>$hash)),true);
    }

    function giveFile($file, $upload_url, $id_album, $group_id){
         $a["file1"]= get_photo($file);
               $ar=  uploadFile($a, $upload_url);
               photosSave($id_album, $group_id, $ar['server'], $ar['photos_list'], $ar['hash']);
    }

    function get_audiomessage(){
      global $sigen;
      $upload_url = $this->apiVkGroup("docs.getMessagesUploadServer",array('type'=>'audio_message','peer_id'=>$sigen))['response']['upload_url'];
      $upload_file = $this->uploadFile("acy.mp3",$upload_url);
      $get_data = $this->apiVkGroup("docs.save",array('file'=>$upload_file,'title'=>'test'));
      $data = $get_data['response'][0];
      $attachment = "doc".$data['owner_id']."_".$data['id'];
      return $attachment;
    }

    function download($picture, $filename) {
    $pic = curl_init($picture);
    $file = fopen($filename, 'w+');
    curl_setopt($pic, CURLOPT_FILE, $file);
    curl_setopt($pic, CURLOPT_HEADER, 0);
    curl_exec($pic);
    curl_close($pic);
    fclose($file);

    }
}

   class get_plan{
     function __construct($url){
       $this->url = $url;
     }

     function currl($link, $param,$flag=false){
      usleep(334000);
     	$ch = curl_init();
     	curl_setopt($ch, CURLOPT_URL, $link);
     	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
     	curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
     	if($flag)
     	curl_setopt($ch, CURLOPT_POST, true);
     	curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0); // отключить валидацию ssl
     	curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.3) Gecko/2008092417 Firefox/3.0.3');
     	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Expect:')); // это необходимо, чтобы cURL не высылал заголовок на ожидание
     	curl_setopt($ch, CURLOPT_POSTFIELDS, $param); // Данные для отправки
     	//curl_setopt($ch, CURLOPT_HEADER, 0); // Не возвращать заголовки
     	$data = curl_exec($ch); // Выполняем запрос
     	curl_close($ch); // Закрываем соединение
     	return $data; // Парсим JSON и отдаем

     }

     function __destruct(){

     }

   }

   class vstu extends get_plan{
     public $array_abitur = array(), $count;

     function parsing_form($key){
       $db = new database();
        require_once  "simple_html_dom.php";
         $vuzs = array(1=>'vstu',2=>'volpi',3=>'kti',10=>'sfvstu', 4=>'arhitector');
         $db->execute_query("DELETE FROM naprav WHERE vuz = '".$vuzs[$key]."'");
             $arr_data = array("id_years" => '5', "id_highschool" => $key);
             $url_data = $this->currl($this->url, $arr_data, true);
             $html_f = str_get_html($url_data);
             foreach ($html_f->find('option') as $f_element) if ($f_element->value != false) $forms[$f_element->plaintext] = $f_element->value;
             $this->parsing_level($forms, $arr_data,$vuzs[$key]);
   }

   function parsing_level($forms,$arr_data,$vuz_name){
     $i=0;
     foreach($forms as $key=>$l_form){
      $arr_data['id_forms'] = $l_form;
      $url_l = $this->currl("http://welcome.vstu.ru/acceptance/reyting-abiturientov/levels.php",$arr_data,true);
      $html_l = str_get_html($url_l);
      foreach($html_l->find('input') as $l_element) $list_level[$l_form][$i][] = array('name_form'=>$key, 'level'=>$l_element->value);
      $i++;
    }
    unset($arr_data['id_forms']);
    $this->get_content($list_level,$arr_data,$vuz_name);
   }

   function get_content($list_level,$arr_data,$vuz_name){
     $db = new database();
     foreach ($list_level as $key_level => $level) {
       $arr_data['id_forms']=$key_level;
       foreach ($level as $key_arr => $arr_value) {
         foreach ($arr_value as $key => $value) {
           $arr_data['id_levels[]'] = $value['level'];
           $url = $this->currl("http://welcome.vstu.ru/acceptance/reyting-abiturientov/content.php",$arr_data,true);
           $html = str_get_html($url);
           $n = $html->find(".choice-form_result_table",0)->children(0)->innertext;
           //echo $url;
           foreach ($html->find(".choice-form_result_table tr") as $value_two) {
            if(preg_match_all("/(\d{2}\.\d{2}\.\d{2})\s&mdash;\s([а-я\s\-\(\)\\\,;:]+)<\/td>\s+<td>([а-я\s\-\(\)\\\,;:><a-z0-9]+)<\/td>\s+/imu",$value_two->outertext,$out)){
                $q_i = 0;
                $kod = $out[1][0];
                $name = $out[2][0];
                unset($out);
                $out['kod'] = $kod;
                $out['vuz'] =
                $out['name'] = $name;
                foreach ($value_two->find("td") as $el_link) {
                    if  ($q_i=='1') $out['plan'] = $el_link->innertext;
                    if  ($q_i=='2'){
                        if($el_link->find("a",0)->innertext) {
                            $out['budget'] = $el_link->find("a", 0)->innertext;
                            $out['link_budget'] = "http://welcome.vstu.ru".$el_link->find("a", 0)->href;
                        }else{
                            $out['budget'] = 0;
                            $out['link_budget'] = 0;
                        }
                    }
                    if  ($q_i=='3'){
                        if($el_link->find("a",0)->innertext) {
                            $out['dogovor'] = $el_link->find("a", 0)->innertext;
                            $out['link_dogovor'] = "http://welcome.vstu.ru".$el_link->find("a", 0)->href;
                        }else{
                            $out['dogovor'] = 0;
                            $out['link_dogovor'] = 0;
                        }
                    }
                    $q_i++;
                }
				echo $out['name']."\n";
                $db->execute_query ("INSERT INTO naprav SET updated = '".time()."', name='".$out['name']."',kod='".$out['kod']."',plan_nabor='".$out['plan']."',budget='".(int)$out['budget']."',dogovor='".$out['dogovor']."',link_dogovor='".$out['link_dogovor']."',link_budget='".$out['link_budget']."',v='".$n."',form='".$value['name_form']."',vuz='$vuz_name'",true);
                //$db->execute_query("UPDATE naprav SET updated = '".time()."', name='".$out['name']."',kod='".$out['kod']."',plan_nabor='".$out['plan']."',budget='".(int)$out['budget']."',dogovor='".$out['dogovor']."',link_dogovor='".$out['link_dogovor']."',link_budget='".$out['link_budget']."',v='".$n."',form='".$value['name_form']."' WHERE kod='".$out['kod']."'AND plan_nabor='".$out['plan']."'AND v='".$n."' AND vuz='$vuz_name'");
            }
           }
         }
       }
     }
   }
   function check_abitur(){
     system("clear");
       $db = new database();
       $db->execute_query("DELETE FROM abiturs_rating WHERE vuz != 'volsu' AND vuz != 'ranhigs' AND vuz != 'ped'",true);
       $get_links = $db->execute_query("SELECT * FROM naprav WHERE ((link_dogovor != '0' && link_dogovor!='') OR (link_budget != '0' && link_budget!='')) AND vuz != 'volsu' AND vuz != 'ranhigs' AND vuz != 'ped'",true);
       $arr_sql = array();
       while($row=mysqli_fetch_assoc($get_links)){ 
           if($row['link_budget']!=false){
               $this->parse_abitur($row['link_budget'],$row);
           }
           if($row['link_dogovor']!=false){
               $this->parse_abitur($row['link_dogovor'],$row);
           }
       }

       $db->multi_query ("INSERT INTO abiturs_rating (predmet_1, predmet_2, predmet_3, position, FIO, document, sum_ball, ball_1, ball_2, ball_3, individual_ach, consent, dop, vuz, form, programm, system_of_preparation, updated, form_vstupit)
       VALUES ".implode(",",$this->array_abitur),true);
       fwrite(fopen("req.sql", "w"), "INSERT INTO abiturs_rating (predmet_1, predmet_2, predmet_3, position, FIO, document, sum_ball, ball_1, ball_2, ball_3, individual_ach, consent, dop, , vuz, form, programm, system_of_preparation, updated)
       VALUES ".implode(",",$this->array_abitur));
       echo count($this->array_abitur);
   }

   function parse_abitur($link,$naprav_info){
       $predmets_list = array(
           '03.03.02'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '08.03.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '08.05.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '09.03.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '09.03.04'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '12.03.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '13.03.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '13.03.02'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '13.03.03'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '15.03.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '15.03.02'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '15.03.04'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '15.03.05'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '18.03.02'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '20.03.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '22.03.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '22.03.02'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '23.03.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '23.03.03'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '27.03.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '27.03.04'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '17.05.02'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '20.05.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '23.05.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '24.05.01'=>array('predmet_1'=>'физика', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '42.03.01'=>array('predmet_1'=>'обществознание', 'predmet_2'=>'история', 'predmet_3'=>'русский язык'),
           '38.03.01'=>array('predmet_1'=>'математика (проф.)', 'predmet_2'=>'обществознание', 'predmet_3'=>'русский язык'),
           '38.03.02'=>array('predmet_1'=>'математика (проф.)', 'predmet_2'=>'обществознание', 'predmet_3'=>'русский язык'),
           '38.03.05'=>array('predmet_1'=>'математика (проф.)', 'predmet_2'=>'обществознание', 'predmet_3'=>'русский язык'),
           '43.03.01'=>array('predmet_1'=>'математика (проф.)', 'predmet_2'=>'обществознание', 'predmet_3'=>'русский язык'),
           '09.03.02'=>array('predmet_1'=>'математика (проф.)', 'predmet_2'=>'информатика и ИКТ ', 'predmet_3'=>'русский язык'),
           '09.03.03'=>array('predmet_1'=>'математика (проф.)', 'predmet_2'=>'информатика и ИКТ ', 'predmet_3'=>'русский язык'),
           '29.03.02'=>array('predmet_1'=>'профильное ВИ', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '18.03.01'=>array('predmet_1'=>'химия', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '19.03.03'=>array('predmet_1'=>'химия', 'predmet_2'=>'математика (проф.)', 'predmet_3'=>'русский язык'),
           '19.03.03'=>array('predmet_1'=>'математика (проф.)', 'predmet_2'=>'биология', 'predmet_3'=>'русский язык'),
           '07.03.01'=>array('predmet_1'=>'архитектурный рисунок', 'predmet_2'=>'архитектурная композиция', 'predmet_3'=>'математика (проф.)'),
           '07.03.03'=>array('predmet_1'=>'архитектурный рисунок', 'predmet_2'=>'архитектурная композиция', 'predmet_3'=>'математика (проф.)'),
           '54.03.01'=>array('predmet_1'=>'рисунок детали', 'predmet_2'=>'композиция', 'predmet_3'=>'литература'),
           '54.05.01'=>array('predmet_1'=>'композиция', 'predmet_2'=>'рисунок обнаженной натуры', 'predmet_3'=>'литература'));

       require_once  "simple_html_dom.php";
       $db = new database();
       $html = file_get_html($link);
       $t['pod']=false;
	   foreach ($html->find(".wide") as $table_ones_data) {
       //echo $link."\r";
	   //if(!isset($table->find("tr"))) continue;
	   //var_dump($table->find("tr"));
       foreach ($table_ones_data->find("tr") as $table_data) {
           $q_i = 0;
              if(isset($predmets_list[$naprav_info['kod']]['predmet_1'])){
               $out['predmet_1'] = $predmets_list[$naprav_info['kod']]['predmet_1'];
               $out['predmet_2'] = $predmets_list[$naprav_info['kod']]['predmet_2'];
               $out['predmet_3'] = $predmets_list[$naprav_info['kod']]['predmet_3'];
             }else{
               $out['predmet_1']= '-';
               $out['predmet_2']= '-';
               $out['predmet_3']= '-';
             }
               foreach ($table_data->find("td") as $td_data) {
                       if ($q_i == 0) $out['position'] = $td_data->plaintext;
                       if ($q_i == 1) $out['FIO'] = mb_strtoupper ($td_data->plaintext);
                       if ($q_i == 2) $out['document'] = trim ($td_data->plaintext);
                       if ($q_i == 3) $out['sum_ball'] = $td_data->plaintext;
                       if ($q_i == 4) $out['ball_1'] = $td_data->plaintext;
                       if ($q_i == 5) $out['ball_2'] = $td_data->plaintext;
                       if ($q_i == 6) $out['ball_3'] = $td_data->plaintext;
                       if ($q_i == 8) $out['individual_ach'] = $td_data->plaintext;
                       if ($q_i == 10) $out['consent'] = $td_data->plaintext;
                       if($q_i==11) $out['dop'] = $td_data->plaintext;
                   $q_i++;
               }
			   
               if(!isset($out['position'])) continue;
               if ($naprav_info) {
                   $out['vuz'] = trim ($naprav_info['vuz']);
                   $out['form'] = trim ($naprav_info['form']);
                   $out['programm'] = trim ($naprav_info['name']);
				if(preg_match("/Машины и аппараты химических производств/imu",$out['programm'])){
						$out['predmet_1'] = 'физика';
						$out['predmet_2'] = 'математика (проф.)';
						$out['predmet_3'] = 'русский язык';  
				}
				if(preg_match("/Охрана окружающей среды и рациональное использование природных ресурсов/imu",$out['programm'])){
						$out['predmet_1'] = 'математика (проф.)';
						$out['predmet_2'] = 'биология';
						$out['predmet_3'] = 'русский язык';  
				}
				if(preg_match("/Технология молока и молочных продуктов/imu",$out['programm'])){
						$out['predmet_1'] = 'химия';
						$out['predmet_2'] = 'математика (проф.)';
						$out['predmet_3'] = 'русский язык';  
				}
				if(preg_match("/Технология мяса и мясных продуктов/imu",$out['programm'])){
						$out['predmet_1'] = 'математика (проф.)';
						$out['predmet_2'] = 'биология';
						$out['predmet_3'] = 'русский язык';  
				}
                   $out['form'] = trim ($naprav_info['form']);
                   $out['system_of_preparation'] = trim ($naprav_info['v']);
				   if($out['system_of_preparation'] == "магистратура"){
					$out['predmet_1'] = 'междисциплинарный';
					$out['predmet_2'] = '-';
					$out['predmet_3'] = '-';
				   }
                   $out['updated']= time();
						if(preg_match("/э|\*/imu",$out['ball_1'])||preg_match("/э|\*/imu",$out['ball_2'])||preg_match("/э|\*/imu",$out['ball_3'])) $out['form_vstupit'] = "Вступительные испытания";
						elseif(preg_match("/е/imu",$out['ball_1'])||preg_match("/е/imu",$out['ball_2'])||preg_match("/е/imu",$out['ball_3'])) $out['form_vstupit'] = "ЕГЭ";
						else $out['form_vstupit']="Нет данных";
               }
               $arr_sql = implode(', ', array_map(
                   function ($v, $k) {
                       return sprintf("'%s'", $v,  $k);
                   },
                   $out,
                   array_keys($out)
               ));
               $full_sql[]= "(".$arr_sql.")";
			   
              // echo "INSERT INTO abiturs_rating SET ".$arr_sql . ";<br>";
       }
	  }
       if(isset($full_sql)){
         $array_abitur = $this->array_abitur;
         $this->array_abitur = array_merge($array_abitur, $full_sql);
         echo "Абитуриентов: ".count($this->array_abitur)."\r";
       return $full_sql;
     }
   }
 }

 class ranhigs extends get_plan{
	public $q_ab = 0;
    function parsing_form(){
        require_once "simple_html_dom.php";
        $db = new database();
        $db->execute_query("DELETE FROM naprav WHERE vuz ='ranhigs'",true);
        $url = $this->url;
        $html = file_get_html($url);
        $q_i=0;
        foreach($html->find(".tab-pane") as $elem) {
            foreach ($elem->find(".table-bordered") as $row_table){
            $name_program=explode(",",preg_replace("/\s+/","",$row_table->find(".priem-summary-cap",0)->plaintext));
			//
            foreach ($row_table->find("tr") as $table_data) {
                if (preg_match("/(\d{2}\.\d{2}\.\d{2})\s/", $table_data->innertext)) {
                    $out['vuz']= 'ranhigs';
                    $out['budget']= '0';
                    $out['updated']= time();
                    $out['form'] = mb_strtolower($name_program[1]);
                    $out['v'] = mb_strtolower($name_program[0]);
$q_i=0;
                    foreach ($table_data->find("td") as $td_data) {
                        if ($q_i == 0) {
                            $out['kod'] = preg_replace("/\s(.*)/", '', $td_data->plaintext);
                            $out['name'] = trim(preg_replace("/^(\d{2}\.\d{2}\.\d{2})\s/", '', $td_data->plaintext));
                        }
                        if ($q_i == 7) $out['plan_nabor'] = $td_data->innertext;
						if ($q_i == 4) {
							if(isset($td_data->find("a", 0)->href)){
                            $out['budget'] = $td_data->find("a", 0)->innertext;
							
							echo $td_data->find("a", 0)->href."\n";
                            $out['link_budget'] = "https://vlgr.ranepa.ru" . $td_data->find("a", 0)->href;
							}else $out['link_budget'] = 0;
                        }
                        if ($q_i == 8) {
                            $out['dogovor'] = $td_data->find("a", 0)->innertext;
                            $out['link_dogovor'] = "https://vlgr.ranepa.ru" . $td_data->find("a", 0)->href;
                        }
$q_i++;
                    }
                    $arr_sql = implode(', ', array_map(
                        function ($v, $k) {
                            return sprintf("%s='%s'", $k, $v);
                        },
                        $out,
                        array_keys($out)
                    ));
                    //echo $i++ . "---" . "INSERT INTO naprav SET $arr_sql" . "<br>";
                    $db->execute_query ("INSERT INTO naprav SET $arr_sql",true);
                }
            }
                $q_i2++;
            }
        }
    }

     function check_abitur(){
         $db = new database();
         $db->execute_query("DELETE FROM abiturs_rating WHERE vuz ='ranhigs'",true);
         $get_links = $db->execute_query("SELECT * FROM naprav WHERE vuz = 'ranhigs'",true);
         while($row = $get_links->fetch_assoc()){
             if($row['link_dogovor']!=false){
                 $this->parse_abitur($row['link_dogovor'], $row);
                 usleep(360);
             }
			 if($row['link_budget']!="0"){
                 $this->parse_abitur($row['link_budget'], $row);
                 usleep(360);
             }
         }
     }
     function parse_abitur($link, $naprav_info=false){
         require_once  "simple_html_dom.php";
         $db = new database();
         $html = file_get_html($link);
         $table = $html->find(".table-bordered", 0);
         foreach ($table->find("tr") as $table_data) {
             $q_i = 0;
             if (preg_match("/№/", $table_data->innertext)) {
                 $out['predmet_1'] = trim($table_data->find("th", 3)->plaintext);
                 $out['predmet_2'] = trim($table_data->find("th", 4)->plaintext);
                 $out['predmet_3'] = trim($table_data->find("th", 5)->plaintext);
             }else{
                 if($out['predmet_3']=="Индивид. достижения") die("error #3!!!\n".$naprav_info['link_dogovor']);
                 foreach ($table_data->find("td") as $td_data) {
                   $out['individual_ach']=0;
                         if ($q_i == 0){ $td_data->find("a",0)->outertext='' ;  $out['position'] = (int)$td_data->plaintext;}
                         if ($q_i == 1){ $out['FIO'] = rtrim(ltrim(mb_strtoupper ($td_data->plaintext))); $this->q_ab++; var_dump($out['FIO']);}
                         if ($q_i == 2){if(isset($td_data->find("div",0)->outertext)){ $td_data->find("div",0)->outertext='' ; $out['form_vstupit']= ltrim($td_data->innertext)?rtrim(ltrim($td_data->innertext)):"Нет информации";}}
                         if(($out['predmet_2']=="Индивид. достижения"||$out['predmet_2']=='-'||$out['predmet_2']=="Индивид. достиж.") && $q_i=='4'){
                             $out['ball_2']=$out['ball_3']='-';
                             $out['predmet_2']=$out['predmet_3']='-';
                             $q_i =6;
                         }
                         if ($q_i == 4) $out['ball_2'] = $td_data->plaintext;
                         if ($q_i == 5) $out['ball_3'] = $td_data->plaintext;
                         if ($q_i == 6) $out['individual_ach'] = $td_data->plaintext;
                         if ($q_i == 7) $out['sum_ball'] = $td_data->plaintext;
                         if ($q_i == 8) $outs['teset'] = $td_data->plaintext;
                         if ($q_i == 9) $out['document'] = trim ($td_data->plaintext);
                         if ($q_i == 10) $out['consent'] = isset($td_data->outertext)?"Да":"";
                         if ($q_i == 10) $out['dop'] = 	   isset($td_data->outertext)?"Да":"";
                     $q_i++;
                 }
if($out['individual_ach']==false) $out['individual_ach']=0;
                 if(preg_match("/на этот конкурс еще нет заявлений/imu",$out['position'])) continue;
                 if ($naprav_info) {
                     $out['vuz'] = trim ($naprav_info['vuz']);
                     $out['form'] = trim ($naprav_info['form']);
                     $out['programm'] = trim ($naprav_info['name']);
                     $out['form'] = trim ($naprav_info['form']);
                     $out['system_of_preparation'] = trim ($naprav_info['v']);
                     $out['updated']= time();
                 }

                 $arr_sql = implode(', ', array_map(
                     function ($v, $k) {
                         return sprintf("%s='%s'", $k, $v);
                     },
                     $out,
                     array_keys($out)
                 ));
                 //echo "INSERT INTO abiturs_rating SET ".$arr_sql . ";\n\n";
				 //echo "INSERT INTO abiturs_rating SET ".$arr_sql."\r\r";
                 $db->execute_query ("INSERT INTO abiturs_rating SET ".$arr_sql, true)."\n";
				 echo "Абитуриентов: ".$this->q_ab."\r";
             }
         }
         //print_r($html)
			$this->q_ab = 0;
			 echo "\n";
     }
 }
 class ped extends get_plan{
       private $form = array('001'=>'очная','003'=>'очно-заочная','002'=>'заочная');
       private $level = array('001'=>'специалитет','002'=>'бакалавриат','003'=>'магистратура','005'=>'аспирантура');
       function get_source($form){
           $link = $this->url."/sources?form=$form";
           $data_from_cite = json_decode(file_get_contents($link),true);
           foreach($data_from_cite as $value) {
               $data[] = $this->url."/levels?form=".$form."&source=".$value['code'];
           }
           $this->get_levels($data);

       }

       function get_levels($data){
           foreach($data as $link) {
               $data_from_cite = json_decode(file_get_contents($link),true);
               $link_new = preg_replace("/levels/imu",'plans',$link);
               foreach($data_from_cite as $value) {
                   $data_new[] = $link_new."&level=".$value['code'];
                   //print_r($value);
               }
           }

           //print_r($data_new);
           $this->get_plans($data_new);
       }
       function get_plans($data){
           $db = new database();
           foreach($data as $link) {
               $data_from_cite = json_decode(file_get_contents($link),true);
               foreach($data_from_cite as $value) {
                   $link_predmets = preg_replace("/plans/",'specialties/view',$link)."&plan=".$value['code'];
                   $link_rating = preg_replace("/plans/",'rating',$link)."&specialty=".$value['code'];
                   $out=array('vuz'=>'ped','updated'=>time(),'name'=>$value['name'],'kod'=>$value['code'],'v'=>$this->level[$value['level']],'form'=>$this->form[$value['form']],'link_budget'=>$link_rating,'link_dogovor'=>$link_predmets);
                   $arr_sql = implode(', ', array_map(
                       function ($v, $k) {
                           return sprintf("%s='%s'", $k, $v);
                       },
                       $out,
                       array_keys($out)
                   ));
                   //echo "INSERT INTO abiturs_rating SET ".$arr_sql . ";\n\n";
                   $db->execute_query ("INSERT INTO naprav SET ".$arr_sql,true);
               }
           }
           //print_r($data_new);
       }
       function get_info($link){
           $data_from_cite = json_decode(file_get_contents($link),true);
           $info = array('updated'=>time(),'programm'=>$data_from_cite['name'],'system_of_preparation'=>$this->level[$data_from_cite['level']],'form'=>$this->form[$data_from_cite['form']]);
           $q_i=0;
           $info['predmet_2']=false;
           $info['predmet_3']=false;
           foreach ($data_from_cite['exams'] as $exam) {
               if($q_i==0) $info['predmet_1'] = $exam['subject'];
               if($q_i==1) $info['predmet_2'] = $exam['subject'];
               if($q_i==2) $info['predmet_3'] = $exam['subject'];
               $q_i++;
           }
           if($info['predmet_2']==false) $info['predmet_2']='-';
           if($info['predmet_3']==false) $info['predmet_3']='-';
           return $info;
       }

     function check_abitur(){
         $db = new database();
         //$db->execute_query("DELETE FROM abiturs_rating WHERE vuz ='ped'",true);
         $get_links = $db->execute_query("SELECT * FROM naprav WHERE vuz = 'ped'",true);
         while($row = $get_links->fetch_assoc()){
                 $this->parse_abitur($this->get_info($row['link_dogovor']), $row['link_budget']);
                 usleep(360);
         }
     }

     function parse_abitur($info,$link_rating){
         $db = new database();
         $data_from_cite = json_decode(file_get_contents($link_rating),true);
         foreach ($data_from_cite['data'] as $item) {
             foreach ($item['applications'] as $table_data) {
                 $q_i=0;
                 if ($table_data['application_status'] == 'активно') {
                     $info['FIO'] = mb_strtoupper($table_data['fio']);
                     $info['position'] = $table_data['position'];
                     $info['document'] = $table_data['edu_doc_st'];
                     $info['consent'] = $table_data['agreement_to_enrollment'];
                     $info['individual_ach'] = $table_data['achievements_mark'];
                     $info['sum_ball'] = $table_data['total'];
                     $info['vuz'] = 'ped';
                     foreach ($table_data['marks'] as $mark){
                         if($q_i==0) $info['ball_1']= $mark['type'];
                         if($q_i==1) $info['ball_2']= $mark['type'];
                         if($q_i==2) $info['ball_3']= $mark['type'];
                         $q_i++;
                     }
					 if(preg_match("/вуз/imu",$info['ball_1'])||preg_match("/вуз/imu",$info['ball_2'])||preg_match("/вуз/imu",$info['ball_3'])) $info['form_vstupit'] = "Вступительные испытания";
					 elseif(preg_match("/егэ/imu",$info['ball_1'])||preg_match("/егэ/imu",$info['ball_2'])||preg_match("/егэ/imu",$info['ball_3'])) $info['form_vstupit'] = "ЕГЭ";
						else $info['form_vstupit']="Нет данных";
						$info['ball_1']= (int) $info['ball_1'];
						$info['ball_2']= (int) $info['ball_2'];
						$info['ball_3']= (int) $info['ball_3'];
                 }
                 $arr_sql = implode(', ', array_map(
                     function ($v, $k) {
                         return sprintf("%s='%s'", $k, $v);
                     },
                     $info,
                     array_keys($info)
                 ));
                 //echo "INSERT INTO abiturs_rating SET ".$arr_sql . ";\n\n";
                 $db->execute_query ("INSERT INTO abiturs_rating SET ".$arr_sql,true);
             }
         }
     }
 }

 class database{
     function __construct($flag=false){
       $db_hostname = '127.0.0.1';
     	$db_database = 'viharev';
       $db_username = 'viharev';
       $db_password = '123123';
       $mysqli = mysqli_connect($db_hostname, $db_username, $db_password,$db_database);
       mysqli_query($mysqli,"SET character_set_client='utf8mb4'");
       mysqli_query($mysqli,"SET character_set_connection='utf8mb4'");
       mysqli_query($mysqli,"SET character_set_results='utf8mb4'");
       $this->mysqli = $mysqli;
     }

     function multi_query($query){
        $result = $this->mysqli->query($query);
        if(!$result)
        print_r(mysqli_error($this->mysqli));
     }

       function execute_query ($query, $flag=false){
   		global $num;
       $result = $this->mysqli->query($query);
	   if(!$result)
        print_r(mysqli_error($this->mysqli));
   	if($num)
   			return mysqli_num_rows($result);
       if($flag)
       return $result;
   $a = mysqli_fetch_assoc($result);
   $a['kolit_zapis'] = mysqli_num_rows($result);
        return $a;
       }

       function insert ($table, $place, $value){
         return  $this->execute_query("INSERT INTO `$table` $place VALUES $value",true);
       }

       function select ($place, $table, $where){
           return  $this->execute_query("SELECT `$place` FROM $table WHERE $where", true);
       }

       function update ($table, $set, $where){
           return  $this->execute_query("update $table Set $set where $where", true);
       }
 }

?>
