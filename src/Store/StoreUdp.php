<?php

namespace Logger\Store;

use Logger\Store\Store;
use Logger\Store\TcpError;

class StoreUdp implements Store{


    protected $socket;
    protected $_fileHandle;
    protected $_config;
    
    function __construct($_config)
    {
        $this->_config = $_config;
        
        $this->socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if($this->socket===false){
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new \Exception("udp socket create");
        }
        
        $this->_fileHandle = true;
    }

    public function write($body)
    {
        try{

           socket_sendto($this->socket,$body,strlen($body),0,$this->_config["host"],$this->_config["port"]);
           
        }catch(Exception $e){
            socket_close($this->socket);
        }
    }

    function __destruct()
    {
        
        if ($this->_fileHandle == true) {
            socket_close($this->socket);
        }
    }
}
