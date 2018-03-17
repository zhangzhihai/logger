<?php
namespace Logger\Store;

use Logger\Store\Store;


//支持多种日志类型
class StoreFile implements Store{
    
    //
    protected $_fileHandle;
    const OPEN_FAILED = 20; //退出日志
    const LOG_CLOSED  = 30;
	const LOG_OPEN    = 10; //打开日志
    protected $_config = array();
    
    function __construct($_config){
        $this->_config = $_config;
        
        
        try{
            if(!file_exists($this->_config["file_path"]))
            {
                @touch($this->_config["file_path"]);
            }
            
            $this->_fileHandle = @fopen($this->_config["file_path"], 'a');
            @flock($this->_fileHandle,LOCK_EX);
            
        }catch(Exception $e){
            throw new Exception($e);
        }
        
        if($this->_fileHandle == true)
        {
            $this->_status = self::LOG_OPEN;
        }else{
			
            $this->_status = self::OPEN_FAILED;
            $msg           = "The file could not be opened. Check permissions.";
            throw new Exception($msg);
        }
    }
    
    
    public function write($body)
    {
        
        if(fwrite($this->_fileHandle, $body) === false)
        {
            $msg = 'The log file could not be written to. '
                . 'Please check that appropriate permissions have been set.';
            throw new Exception($msg);
        }
        
        @fclose($this->_fileHandle);
        $this->_fileHandle = false;
    }
    
    
    
    function __destruct(){
        
        if($this->_fileHandle == true)
        {
            @fclose($this->_fileHandle);
            $this->_status = self::LOG_CLOSED;
        }
    }
    
}