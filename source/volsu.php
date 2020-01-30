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
    $db = new database();
    $config = $this->config;
    $soapConfig = new SoapConfig($config['eduprogs']['url'], $config['eduprogs']['login'], $config['eduprogs']['password']);
    $client = new SoapClient($soapConfig, 'Рейтинг', [
      'ИмяВарианта' => "СписокВтораяВолнаДляСайта",
      'НомерПриемнойКампании' => '0000000'.$code,
      'СтрокаС'               => 1,
      'СтрокаПо'              => 10000
    ]);

    $data = json_decode(json_encode(Helper::toArray($client->getResponse())), true)[0];
      if($code==21) $type = "бакалавриат";
      else if($code==22) $type = "магистратура";
      else die("error_code: line 37");

      for($i=0;$i<$data['КоличествоЗаявлений'];$i++){
        $FIO = $data['РейтингСтрока'][$i]['ФизическоеЛицо']['Наименование'];
        //print_r($data['РейтингСтрока'][$i]);
        $db->execute_query("INSERT INTO abiturs_rating SET vuz='volsu', system_of_preparation='$type',
           FIO = '".mb_strtoupper($FIO)."', ball_1='".$data['РейтингСтрока'][$i]['Предмет1']."',
           ball_2='".$data['РейтингСтрока'][$i]['Предмет2']."', ball_3='".$data['РейтингСтрока'][$i]['Предмет3']."',
            document='".$data['РейтингСтрока'][$i]['ВидДокумента']."', sum_ball='".$data['РейтингСтрока'][$i]['СуммаБаллов']."',
             consent='".$data['РейтингСтрока'][$i]['СогласиеНаЗачисление']."'",true);
      }
      //$this->insert_database($abitur, $type);
  }

  function insert_database($abit_list, $type_program){
    $db = new database();
    foreach($abit_list as $abitur=>$key){
      $db->execute_query("INSERT INTO abiturs_rating SET vuz='volsu', system_of_preparation='$type_program', FIO = '".mb_strtoupper($abitur)."'",true);
    }
  }

  function delete_abiturs(){
    $db = new database();
    $db->execute_query("DELETE FROM abiturs_rating WHERE vuz='volsu'",true);
  }

  function __destruct(){
    $this->delete_abiturs();
    $this->get_abiturs(21);
    $this->get_abiturs(22);
  }
}

//for($i=1;$i!=9;$i++){
//$argv[2] = $i;
