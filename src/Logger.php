<?php

namespace Logger;

use Logger\Config;

use Logger\Store\StoreFile;
use Logger\Store\StoreTcp;
use Logger\Store\StoreUdp;
use Logger\Store\StoreHttp;



class Logger implements LoggerInterface{
    
    const DEBUG     = 1; // 调试日志
    const INFO      = 2; // 所有日志
    const NOTICE    = 3; //
    const WARN      = 4; // 警
    const ERROR     = 5; // ...
    const CRITICAL  = 6; // ...
    const ALERT     = 7; // ...
    const EMERGENCY = 8; // Least Verbose
    const OFF       = 9; // 关闭日志
    
    const LOG_OPEN    = 10; //打开日志
    const OPEN_FAILED = 20; //退出日志
    const LOG_CLOSED  = 30;
    
    private static $initialized = false;
    
    protected $_status;
    
    protected $_dateForm = 'Y/m/d|H:i:s';
    
    protected $_level;
    
    protected $_fileHandle;
    
    protected $_filepath;
    
    protected static $_uuid = '';
    
    protected static $_buffer = array();//缓冲区
    
    private $level;
    
    private $name;
    
    private static $_instance;
    
    private static $_config;
   
    public function __construct($config=null)
    {
        if($config==null && self::$_config){
            $config = self::$_config;
        }
        
        $this->config($config);
        
        $this->name = self::$_config["name"];
        
        $this->_level      = self::$_config["level"];
        
        list($usec, $sec) = explode(" ", microtime());
        $msec=round($usec*1000);
        
        $this->_dateForm = 'Y/m/d'.self::$_config["separtor"]."H:i:s {$msec}";
        
        if($this->_level == self::OFF)
        {
            return;
        }
        self::$_instance = $this;
    }
    
    public static function config($_config){
        
        self::$_config["name"] = isset($_config["name"])==true?$_config["name"]:Config::NAME;
        self::$_config["file_path"] =  isset($_config["file_path"])==true?$_config["file_path"]:Config::FILE_PATH;
        self::$_config["file_size"] =  isset($_config["file_size"])==true?$_config["file_size"]:Config::FILE_SIZE;
        self::$_config["port"] =  isset($_config["port"])==true?$_config["port"]:Config::PORT;
        self::$_config["host"] =  isset($_config["host"])==true?$_config["host"]:Config::HOST;
        self::$_config["uri"] =  isset($_config["uri"])==true?$_config["uri"]:Config::URI;
        self::$_config["logs_drive"] =  isset($_config["logs_drive"])==true?$_config["logs_drive"]:Config::LOGS_DRIVE;
        self::$_config["level"] =  isset($_config["level"])==true?$_config["level"]:Config::LEVEL;
        self::$_config["separtor"] =  isset($_config["separtor"])==true?$_config["separtor"]:Config::SEPARTOR;
        
    }
    
    public static function getInstance(){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    
    
    public function getName() {
        return $this->name;
    }
    
    public function emergency($message) {
        $this->log(self::EMERGENCY, $message);
    }
    
    public function alert($message) {
        $this->log(self::ALERT, $message);
    }
    
    
    public function debug($message) {
        $this->log(self::DEBUG, $message);
    }
    
    public function info($message) {
        $this->log(self::INFO, $message);
    }
    
    public function warn($message) {
        $this->log(self::WARN, $message);
    }
    
    public function error($message) {
        $this->log(self::ERROR, $message);
    }
    
    public function fatal($message) {
        $this->log(self::OPEN_FAILED, $message);
    }
    
    public function getLevel() {
        return $this->level;
    }
    
    public function setLevel($level = null) {
        $this->level = $level;
    }
    
    
    protected function guid()
    {
        if (function_exists('com_create_guid'))
        {
            return com_create_guid();
        }else{
    
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
    
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12);// "}"
            return $uuid;
        }
    }
    
    function __destruct()
    {
        //self::_buffer 
        $this->Send();
    }
    
    
    function Send()
    {
        switch (self::$_config["logs_drive"])
        {
            
            case "file":
                $store =  new StoreFile(self::$_config);
                break;
            case "tcp":
                $store =  new StoreTcp(self::$_config);
                break;
            case "udp":
                $store =  new StoreUdp(self::$_config);
                break;
            case "http":
                $store =  new StoreHttp(self::$_config);
                break;
            default:
                $store =  new StoreFile(self::$_config);
                break;
        }
        
        
        $content = '';
        if(count(self::$_buffer)<1){
            return;
        }
        
        foreach (self::$_buffer as $v){
            $content.=$v;
        }
        $store->write($content);
        self::$_buffer = array();
    }
    
    function log($level,$message)
    {
    
        if($this->_level <= $level)
        {
            if(!self::$_uuid){
                self::$_uuid = $this->guid();
            }
    
            $linePrefix = $this->getLinePrefix($level);
            
            $linePrefix = $linePrefix.$message;
            $this->writeLine($linePrefix."\n");
        }
    }
    
    private function writeLine($line)
    {
        if($this->_level == self::OFF)
        {
            return;
        }
    
        //写入buff
        array_push(self::$_buffer,$line);
        
        if(count(self::$_buffer)>Config::MAXLINE){
            $this->send();
        }
    }
   
    
    //取前缀
    private function getLinePrefix($level)
    {
        $time = date($this->_dateForm);
        
        $timePrefix = $time.self::$_config["separtor"].self::$_uuid;
        
        $levelstr = "";
        
        switch($level)
        {
            case self::DEBUG:
                $levelstr =  'DEBUG';
                break;
            case self::INFO:
                $levelstr = 'INFO';
                break;
            case self::WARN:
                $levelstr = 'WARN';
                break;
            case self::ERROR:
                $levelstr = 'ERROR';
                break;
            case self::CRITICAL:
                $levelstr = 'CRITICAL';//临界
                break;
            case self::ALERT:
                $levelstr =  'ALERT';//警觉
                break;
            case self::EMERGENCY:
                $levelstr = 'EMERGENCY';//急救
                break;
            default:
                $levelstr = 'LOG';
                break;
        }
        
        return $timePrefix.self::$_config["separtor"]."$levelstr".self::$_config["separtor"];
    }
}


interface LoggerInterface
{

    public function emergency($message);


    public function alert($message);
    
    public function debug($message);
    
    public function info($message);
    
    public function warn($message);
    
    public function error($message);

    public function fatal($message);

    public function log($level,$message);
}

