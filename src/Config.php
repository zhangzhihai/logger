<?php
namespace Logger;

final class Config
{
    const SDK_VER = '1.0.0';

    const FILE_PATH = "D:\\websoft\\phptmp\\appliction.log"; //4*1024*1024 分块上传块大小，该参数为接口规格，暂不支持修改
	
	const NAME = "application";
    
    const FILE_SIZE = 1073741824;//1G
    const PORT = 9000; 
    const HOST = "127.0.0.1";
    
    const LOGS_DRIVE = "file";//file,tcp,udp,http
    const LEVEL = 2;
	const URI = "";
	const TIMEOUT = 1;
	const MAXLINE = 100;
    
    const SEPARTOR = "|";
    
}
