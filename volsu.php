<?php
  require_once "./lib/SoapConfig.php";
  require_once "./lib/SoapClient.php";
  require_once "./lib/Uri.php";
  require_once "./lib/Util.php";
  require_once "./lib/Validator.php";

  use Volsu\Soap\SoapConfig;
  use Volsu\Soap\SoapClient;
  use Volsu\Utility\Helper;
  use Volsu\Url\Uri;
  use Volsu\Validation\Validator;
class volsu {
  function __construct(){
    $this->config = require_once "./lib/Config.php";
  }

  function get_abiturs($code){

    system("clear");

    system("tput bold");
    system("setterm -background blue");
    system("setterm -foreground white");
    echo "Получаю данные для: \033[0;32m$code\033[0m\n";
    $db = new database();
    $full_napr = $this->get_plann($code);
    $config = $this->config;
    $soapConfig = new SoapConfig($config['eduprogs']['url'], $config['eduprogs']['login'], $config['eduprogs']['password']);

    $client = new SoapClient($soapConfig, 'Рейтинг', [
      'ИмяВарианта' => "СписокВтораяВолнаДляСайта",
      'НомерПриемнойКампании' => '0000000'.$code,
      'СтрокаС'               => 1,
      'СтрокаПо'              => 12000
    ]);

        echo "Получение данных завершено.\nОбрабатываю...\r";
    $data = json_decode(json_encode(Helper::toArray($client->getResponse())), true)[0];
      if($code==21) $type = "бакалавриат";
      else if($code==22) $type = "магистратура";
      else die("error_code: line 37");
      $num_naprav = '';
      echo "Заполняю данные для запроса...".$data['КоличествоЗаявлений']."\n";
      for($i=0; $i < count($data['РейтингСтрока']); $i++){
		  
		if(!isset($data['РейтингСтрока'][$i]['ФизическоеЛицо'])) die();
        $FIO = ltrim($data['РейтингСтрока'][$i]['ФизическоеЛицо']['Наименование']);
        $full_info_naprav = $full_napr[$data['РейтингСтрока'][$i]['КонкурснаяГруппаКод']];
        $q[$data['РейтингСтрока'][$i]['КонкурснаяГруппаКод']][]=1;
        $sql_param['position'] = '-';
        $sql_param['vuz'] = 'volsu';
        $sql_param['FIO'] = ltrim(mb_strtoupper($FIO));
        $sql_param['system_of_preparation'] = ltrim(mb_strtolower($full_info_naprav[5]));
        $sql_param['updated'] = time()+3600;
        $sql_param['programm'] = $full_info_naprav[4];
        $sql_param['form'] = mb_strtolower($full_info_naprav[3]);
        $sql_param['predmet_1'] = $full_info_naprav[0];
        $sql_param['predmet_2'] = $full_info_naprav[1];
        $sql_param['predmet_3'] = $full_info_naprav[2];
        $sql_param['ball_1'] = (int)$data['РейтингСтрока'][$i]['Предмет1'];
        $sql_param['ball_2'] = (int)$data['РейтингСтрока'][$i]['Предмет2'];
        $sql_param['ball_3'] = (int)$data['РейтингСтрока'][$i]['Предмет3'];
        $sql_param['document'] = trim($data['РейтингСтрока'][$i]['ВидДокумента']);
        $sql_param['sum_ball'] = (int) $data['РейтингСтрока'][$i]['СуммаБаллов'] + $data['РейтингСтрока'][$i]['СуммаБалловПоИДДляКонкурса'];
        $sql_param['consent'] = trim($data['РейтингСтрока'][$i]['СогласиеНаЗачисление']);
        $sql_param['priority'] = (int)$data['РейтингСтрока'][$i]['Приоритет'];
        $sql_param['konk_group'] = $data['РейтингСтрока'][$i]['КонкурснаяГруппаКод'];
        $sql_param['osnovanie'] = $full_info_naprav[6];
        $sql_param['mest'] = (int)$full_info_naprav[7];
        $sql_q[] = "(".$this->construct_sql($sql_param).")";
        $proc = round(($i/$data['КоличествоЗаявлений'])*100,1);
        echo "Прогресс: ".$proc."% \r";
      }
      $this->delete_abiturs($type);
      $db->multi_query("INSERT INTO abiturs_rating
        (position,vuz,FIO,system_of_preparation,updated,programm,form,predmet_1,predmet_2,predmet_3,ball_1,ball_2,ball_3,document,sum_ball,consent,priority,konk_group,osnovanie,mest)
        VALUES ".implode(",",$sql_q),true);
        unset($sql_q);
        echo "\nГотово";
      //$this->insert_database($abitur, $type);
  }

  function get_plann($code){
    $db = new database();
    $config = $this->config;
    $soapConfig = new SoapConfig($config['eduprogs']['url'], $config['eduprogs']['login'], $config['eduprogs']['password']);
    $client = new SoapClient($soapConfig, 'ПланПриема', [
      'НомерПриемнойКампании' => '0000000'.$code
    ]);

    $data = json_decode(json_encode(Helper::toArray($client->getResponse())), true)[0]['ПланПриемаСтрока'];
    foreach ($data as $key => $value) {
      $arr[$value['КонкурснаяГруппа']['Код']]= [$value['Предмет1'], $value['Предмет2'], $value['Предмет3'], $value['КонкурснаяГруппа']['ФормаОбучения'], $value['КонкурснаяГруппа']['Специальность']['Наименование'],$value['КонкурснаяГруппа']['УровеньПодготовки'], $value['КонкурснаяГруппа']['ОснованиеПоступления'], $value['КоличествоМест']];
    }
    return $arr;
    //$this->insert_database($abitur, $type);
  }

  function insert_database($abit_list, $type_program){
    $db = new database();
    foreach($abit_list as $abitur=>$key){
      $db->execute_query("INSERT INTO abiturs_rating SET vuz='volsu', system_of_preparation='$type_program', FIO = '".mb_strtoupper($abitur)."'",true);
    }
  }

  function delete_abiturs($type){
    if($type == "бакалавриат") $type = "AND (system_of_preparation = 'бакалавриат' OR system_of_preparation = 'специалитет')";
    else  $type = "AND system_of_preparation = '$type'";
    $db = new database();
    echo "\nУдаление .........\r";
    $db->execute_query("DELETE FROM abiturs_rating WHERE vuz='volsu' $type",true);
    //die("DELETE FROM abiturs_rating WHERE vuz='volsu' $type");
    echo "Удаление завершено\n";
  }

  function construct_sql($array){
    $arr_sql = implode(', ', array_map(
        function ($v, $k) {
            return sprintf("'%s'", $v, $k);
        },
        $array,
        array_keys($array)
    ));
    return $arr_sql;
  }

  function get_abiturs_rating(){
    $this->get_abiturs(21);
    $this->get_abiturs(22);
  }
}

//for($i=1;$i!=9;$i++){
//$argv[2] = $i;
