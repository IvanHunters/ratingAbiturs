<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of soap_config
 *
 * @author Владелец
 */
 ini_set("soap.wsdl_cache_enabled", "0");
 
class soap_config {
    //put your code here
    private $url;
    private $login;
    private $password;
    private $debug = false;


    public function __construct($url, $login, $password) {
        $this->url = $url;
        $this->login = $login;
        $this->password = $password;
    }
    
    public function getUrl()
    {
        return $this->url;
    }    

    public function getLogin()
    {
        return $this->login;   
    }
    
    public function getPassword()
    {
        return $this->password;
    }  
    
    public function setDebug($mode = true)
    {
        $this->debug = $mode;
    }
    
    public function getDebug()
    {
        return $this->debug;
    }
}