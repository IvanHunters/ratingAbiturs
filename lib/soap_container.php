<?php
require_once 'soap_config.php';
require_once 'Format.php';
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of soap_container
 *
 * @author Владелец
 */
class soap_container 
{
    //private fields
    private $request;
    private $response;


    public function __construct(soap_config $config, $method, array $parameters = null)
    {
        
        try
        {
            $this->request  = new SoapClient($config->getUrl(), array(
                'login'     => $config->getLogin(),
                'password'  => $config->getPassword(),
            ));

            $this->response = $this->request->$method($parameters);
            
        }
        catch (SoapFault $error)
        {
            if(!$config->getDebug())
            {
				header('Content-Type: text/html; charset=utf-8');
                echo '<div style="margin: 100px auto; border: red 3px solid;padding: 10px;"><h2>Произошел сбой работы программы. В настоящее время ведутся технические работы на сервере. Попробуйте позже. Если данное сообщение повторится - обратитесь за помощью по адресу: <a href="mailto:ovt@volsu.ru">ovt@volsu.ru</a></h2></div>';
				exit();
            }
            else
            {
                echo 'Soap request failed: '.$error->getMessage(); //uncomment for debug
            } 
        }
    }
    
    public function toArray($key = null)
    {
        
        return Format::SoapToArray($this->response, $key);
        
    }

    public function GroupByPost()
    {
        return Format::GroupByPost($this->response);
    }
    
    public function getResponse()
    {
        return $this->response;
        
    }
}