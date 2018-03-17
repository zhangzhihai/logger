<?php
namespace Logger\Store;

use Logger\Store\Store;
use Logger\Store\TcpError;

class StoreTcp implements Store
{

    protected $socket;
    protected $_fileHandle;
    
    function __construct($_config)
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if($this->socket===false){
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);
            throw new \Exception("tcp socket create");
        }
        socket_set_option($this->socket,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>1, "usec"=>0 ));
        
        $result = socket_connect($this->socket,$_config["host"],$_config["port"]);
        if($result == false){
            throw new \Exception("tcp socket connect");
        }
        
        $this->_fileHandle = true;
    }

    public function write($body)
    {
        try{
           @socket_write($this->socket,$body,strlen($body));
        }catch(Exception $e){
            socket_close($this->socket);
        }
    }

    function __destruct()
    {
        if ($this->_fileHandle == true) {
            socket_shutdown($this->socket);
            socket_close($this->socket);
        }
    }
}