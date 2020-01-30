<?php namespace Volsu\Format;
/**
 * Description of formatter
 *
 * @author
 */
class Format 
{
        /*
         * Format of soap response:
         *  response
         *      -> return
         *          ->response key
         *              -> array of stdClass objects
         * This static method transform response to only array of stdClass objects
         */
    public static function SoapToArray($response, $key = null)
    {
        
        $response = (array)$response->return;
        if(isset($key))
        {
            $key = array_keys($response);
            return $response[$key[0]];
        }
        else
        {
            return $response;
        }
        
    }

     /**
     * Reorder input array from format A in format B
     * 
     * A: 
     * 
     * B:
     * 
     * @param type array
     * @return array
     * @autor vuylov AT gmail DOT com
     */
    
    public static function GroupByPost($array)
    {
        //income raw array
        $rawArray   = self::SoapToArray($array, 1);
        $postKeys   = array();
        $i = 0;

        //extract value of POST for future keys array
        foreach ($rawArray as $emp)
        {
            $postKeys[$i] = $emp->Post->Type;
            $i++;
        }
        //remove the same keys
        $postKeys = array_unique($postKeys);
        
        //sort array
        sort($postKeys, SORT_STRING);
        
        //fill array with data
        $i = 0;
        $outArray = array();
        foreach ($postKeys as $post)
        {
            $outArray[$i]["post"] = $post;
            
            $j = 0;
            foreach ($rawArray as $element)
            {
                if($element->Post->Type === $post)
                {
                    $outArray[$i]["el"][$j] = array(
                        "Order"=>$j, 
                        "Code" => $element->Code, 
                        "Name" => $element->Name, 
                        "Place" => $element->Post->Name,
						"ExternalPost"	=> $element->ExternalPost->Type,
						"ExternalOrganisation" => $element->ExternalOrganisation
						);
                }   
                $j++;
            }
            $i++;
        }
        return $outArray; //$postKeys; 
        
    }
    
    public static function getBody($html)
    {
        if(!class_exists('DOMDocument'))
        {
            return 'class DOMDocument not available';
        }
        $doc = new DOMDocument();
        //@ - Hide Warnings of loadHTML() method
        @$doc->loadHTML($html);
        $body = $doc->getElementsByTagName('body')->item(0);
        return $doc->saveXML($body);
    }
    
    public static function table_format_reception_hours($html)
    {
        $data       = explode("\n", $html);
        $rowspan     = count($data);
        if($rowspan>1)
        {
            $out_html   = '<table><tr><td rowspan="'.$rowspan.'"><b>График приема:</b></td><td>'.$data[0].'</td></tr>';
            for($i=1; $i<=$rowspan-1; $i++)
            {
                $out_html.= '<tr><td>'.$data[$i].'</td></tr>';
            }
            $out_html.='</table>';
            return $out_html;
        }
        else
        {
            return '<b>График приема: </b>'.$data[0];
        }
    }
    
    public static function make_email($email)
    {
        if(filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                return $email;
            }
            else
            {
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
    
}
?>
