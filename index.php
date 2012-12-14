<?php
error_reporting(E_ALL ^ E_NOTICE);
header('P3P:CP="IDC DSP COR ADM DEVi TAIi PSA PSD IVAi IVDi CONi HIS OUR IND CNT"');

define("PROJECT_BASE_PATH", realpath(dirname(__FILE__) . "/"));
define("ROOT_PATH",PROJECT_BASE_PATH.'/site/');
define("CORE_PATH",PROJECT_BASE_PATH.'/Core/');
define("APP_PATH",PROJECT_BASE_PATH.'/App/');

//加载核心类
require_once CORE_PATH.'Core.class.php';
require_once CORE_PATH.'global.func.php';
require_once APP_PATH.'Common/Site/Common.class.php';

//加载配置文件
Core::loadConfigFile(APP_PATH.'Config/Global.php');
Core::loadConfigFile(APP_PATH.'Config/Site.php');

//设置autoload查找路径
Core::setLoadDir(APP_PATH);

//初始化程序
Core::init();

//路由请求
$dispatcher = new Dispatcher_Site(Core::getConfig('Module_Path'));
$dispatcher->Run();