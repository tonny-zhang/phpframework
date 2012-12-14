<?php
//包含常量定义
//include_once('Define.php');

return array(
    'RunMode' => 'debug', //debug, deploy

    'TimeZone'	=> 'Asia/Shanghai',

    'AutoloadMap' => array(
        'FDX_Exception'        =>  PROJECT_BASE_PATH.'/Core/Class/FDX_Exception.class.php',
        'FDX_Model'            =>  PROJECT_BASE_PATH.'/Core/Class/FDX_Model.class.php',
//        'FDX_Request'          =>  PROJECT_BASE_PATH.'/Core/Class/FDX_Request.class.php',
//        'FDX_Response'         =>  PROJECT_BASE_PATH.'/Core/Class/FDX_Response.class.php',
        'FDX_Dispatcher'       =>  PROJECT_BASE_PATH.'/Core/Class/FDX_Dispatcher.class.php',
//        'FDX_InputFilter'      =>  PROJECT_BASE_PATH.'/Core/Class/FDX_InputFilter.class.php',
//        'FDX_Session'          =>  PROJECT_BASE_PATH.'/Core/Class/FDX_Session.class.php',
//        'FDX_ModuleControl'    =>  PROJECT_BASE_PATH.'/Core/Class/FDX_ModuleControl.class.php',
//        'FDX_AccessControl'    =>  PROJECT_BASE_PATH.'/Core/Class/FDX_AccessControl.class.php',
//        'FDX_Cache'            =>  PROJECT_BASE_PATH.'/Core/Class/FDX_Cache.class.php',
//        'FDX_MemSession'       =>  PROJECT_BASE_PATH.'/Core/Class/FDX_MemSession.class.php',
        //lib
        'FDX_Db'               =>  PROJECT_BASE_PATH.'/Core/Lib/Db/FDX_Db.class.php',
        //'FDX_Db_SqlBuilder'    =>  PROJECT_BASE_PATH.'/Core/Lib/Db/FDX_Db_SqlBuilder.class.php',
        'FDX_Db_Mysqli'        =>  PROJECT_BASE_PATH.'/Core/Lib/Db/Driver/FDX_Db_Mysqli.class.php',
        //'FDX_View_PHP'         =>  PROJECT_BASE_PATH.'/Core/Lib/View/FDX_View_PHP.class.php',
        'FDX_View_Neat'        =>  PROJECT_BASE_PATH.'/Core/Lib/View/FDX_View_Neat.class.php',
        //'FDX_Cache_Memcache'   =>  PROJECT_BASE_PATH.'/Core/Lib/Cache/FDX_Cache_Memcache.class.php',
        //'PHPMailer'            =>  PROJECT_BASE_PATH.'/Core/Lib/Mail/class.phpmailer.php',
		//'FDX_Mongo'            =>  PROJECT_BASE_PATH.'/Core/Lib/Db/FDX_Mongo.class.php',
		//'FDX_Db_Mongo'         =>  PROJECT_BASE_PATH.'/Core/Lib/Db/Driver/FDX_Db_Mongo.class.php',
    ),

    'DataBase' => array (
		'default' => array(
			'driver' => 'Mysqli',//数据库访问驱动

			'singlehost' => true,

			'master' => array(
				 'host'    => 'localhost'/*database*/,	//数据库主机地址
				 'user'    => 'root'/*mysql*/,		//数据库连接账户名
				 'pwd'	   => '',		//数据库连接密码
				 'db'	   => 'test',	//数据库名
				 'charset' => 'utf8'            //数据库字符集
			   ),

			'slave' => array(),
		),
    ),

    'Sphinx' => array(
        'host' => 'localhost'/*sphinx*/,
        'port' => 9312/*sphinx*/,
		'cache' => false/*sphinx*/,
    ),

    'gearman' => array(
        'host' => 'localhost'/*gearman*/,
        'port' => 4730,
    ),

    'redis' => array(
        'host' => "localhost"/*redis*/,
        'port' => 6379/*redis*/,
        'password' => 541300/*redis*/,
    ),

    'Session' => array(
		'cookie_domain' => '.fan.com',
		'tableName' => 'sys_sessions',
		'fieldId' => 'sess_id',
		'fieldData' => 'sess_data',
		'fieldActivity' => 'activity',
		'lifeTime' => 43200,
    ),
    'MemSession' => array(
		'cookie_domain' => '.fan.com',
		'lifeTime' => 43200,
	    'type'=>'Memcache',
        'store' => array(
            'servers' => array(array("host" => 'localhost'/*memsession*/, 'port' => '11222'/*memsession*/)),
            'compressed' => false,
            'persistent' => true,
        )
    ),
    'Cache' => array(
        'type' => 'Memcache',
        'Policy' => array(
            'servers' => array(array("host" => 'localhost'/*memcache*/, 'port' => '11211'/*memcache*/)),
            'compressed' => false,
            'life_time' => 86400,
            'persistent' => true,
        )
    ),

    'Domain' => "test.com",

    'mongodb' => array(
		'master' => array(
			'host' => "*.*.*.*"/*mongodb*/,
			'port' => 30000,
		),
		'user' => 'root',
		'pwd'  => '***',
		'db'   => 'db_name',
		'replica_set'     => FALSE, //or array(array('host'=>'host1','port'=>'port1'))
		'slave_ok'        => FALSE,
		'persist'         => '**',
		'connect'         => False,
		'queryTimeOut'    => 2500,
		'timeout'         => 60 //milliseconds
    ),
	
	'errlogDir' => '/data/log/www-mysql-error/',
);

