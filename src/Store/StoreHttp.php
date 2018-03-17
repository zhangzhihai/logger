<?php
namespace Logger\Store;

use Logger\Store\Store;
use Logger\Http\Client;
use Logger\Http\Error;

class StoreHttp implements Store{

    public $url;
    public $headers;
    public $body;
    public $method;

    function __construct($_config)
    {
        $this->_config = $_config;
    }
    
    
    function write($body)
    {
        $ret = Client::post($this->_config["uri"], $body);
        
        if (!$ret->ok()) {
            return array(null, new Error($this->_config["uri"], $ret));
        }
        
       return ;
    }
    

}