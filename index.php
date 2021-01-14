<?php

if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

header("Content-Type:text/html; charset=utf-8");    

define('APP_DEBUG', true);

define('BIND_MODULE','Home');
  
define ('APP_PATH', './Modules/' );


define('ROOT_PATH',str_replace('\\','/',dirname(__FILE__)) . '/'); 
define('ATTACHMENT_ROOT', ROOT_PATH.'Uploads/image/'); 



define ('RUNTIME_PATH','./Runtime/');

require './ThinkPHP/ThinkPHP.php';

?>