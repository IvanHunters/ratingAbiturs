<?php
namespace Volsu\Utility;
class Helper {
    public static function toArray($object){
        $arr = [];
        if(is_array($object))
            return $object;
        if(is_object($object)){
            $arr = [$object];
        }
        return $arr;
    }
	
	public static function groupArrayByElementField($sourceArray, $field)
	{
		$returnArray = [];
		
		if(!is_array($sourceArray)){
			throw new Exception('Bad parameter');
		}
		
		foreach($sourceArray as $key => $element)
		{
			if(!$element->{$field}){
				$element->{$field} = 'Отсутствует аттестация';
			}
			$returnArray[$element->{$field}][$key] = $element;
		}
		
		return $returnArray;
	}
	
	public static function formatMarks($marks)
	{

        usort($marks, function($a, $b){
            return strcmp($a->Предмет.$a->ВидКонтроля, $b->Предмет.$b->ВидКонтроля);
        });


        $formattedMarks = [];
        foreach($marks as $mark){
            $formattedMarks[$mark->ЗачетнаяКнига]['number'] = $mark->ЗачетнаяКнига;
            $formattedMarks[$mark->ЗачетнаяКнига]['group'] = $mark->Группа;
            $formattedMarks[$mark->ЗачетнаяКнига]['objects'][] = ['name' => $mark->Предмет, 'block' => $mark->ТипЗаписи, 'control' => $mark->ВидКонтроля, 'ball' => $mark->Балл];
        }


        $formattedMarks = self::addSumColumn($formattedMarks);

        usort($formattedMarks, function($a, $b){
            return $a['total'] < $b['total'];
        });

        return $formattedMarks;
    }

    private static function addSumColumn($marks)
    {
        foreach($marks as &$mark){
            $mark['total'] = array_sum(array_column($mark['objects'], 'ball'));
        }
        return $marks;
    }

    public static function getBody($html)
    {
        if(!class_exists('DOMDocument')){
            return false;
        }
        $doc = new \DOMDocument();
        //@ - Hide Warnings of loadHTML() method
        $doc->loadHTML($html);
        $body = $doc->getElementsByTagName('body')->item(0);
        return $doc->saveXML($body);
    }

    public static function formatTableReceptionHours($html)
    {
        $data       = explode("\n", $html);
        $rowspan     = count($data);
        if($rowspan>1){
            $out_html   = '<table><tr><td rowspan="'.$rowspan.'"><b>График приема:</b></td><td>'.$data[0].'</td></tr>';
            for($i=1; $i<=$rowspan-1; $i++){
                $out_html.= '<tr><td>'.$data[$i].'</td></tr>';
            }
            $out_html.='</table>';
            return $out_html;
        }else{
            return '<b>График приема: </b>'.$data[0];
        }
    }

    public static function makeEmail($email)
    {
		$email = trim($email);
        if(filter_var($email, FILTER_VALIDATE_EMAIL)){
            return $email;
        }else{
            return '-';
        }
    }

    public static function is_isset_value($string)
    {
        if((int)$string === 0)
            return 'значение не указано';
        return $string;
    }

    public static function pushObjectInArray($object){
        if(is_object($object)){
            $ar = array($object);
            return $ar;
        } else {
            return $object;
        }
    }

    public static function getFileNameFromObject($objectFile){
        if(!is_object($objectFile)){
            return false;
        }
        if($objectFile->Code && $objectFile->Version && $objectFile->Version){
            return trim($objectFile->Code).'-'.trim($objectFile->Version).'.'.trim($objectFile->Extension);
        }
        return false;
    }

    public static function convertToMB($byte){
        $digit = $byte / 1024;
        if($digit <= 100.0){
            return round($digit, 2).' Кб';
        }else{
            return round($digit / 1024, 2).' Мб';
        }
    }

}