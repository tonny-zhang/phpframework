<?php
class Core
{
	/**
	 * 加载配置文件
	 */
	static function loadConfigFile( $path, $recursive = false )
	{
		if ( isset( $GLOBALS['__IncludePath'][$path] ) )
			return;

		$config = include( $path );
		$GLOBALS['__IncludePath'][$path] = true;
		if ( isset ( $GLOBALS['__Config'] ) && is_array( $GLOBALS['__Config'] ) )
		{
			if ( $recursive )
				$GLOBALS['__Config'] = array_merge_recursive( $GLOBALS['__Config'], $config );
			else
				$GLOBALS['__Config'] = array_merge( $GLOBALS['__Config'], $config );
		}
		else
		{
			$GLOBALS['__Config'] = $config;
		}
	}

	/**
	 * 获取配置信息
	 */
	static function GetConfig()
	{
		$config = $GLOBALS['__Config'];

		$argList = func_get_args();

		if ( !$argList )
			return false;
		
		foreach ( $argList as $arg )
		{
			if ( $config[$arg] )
			{
				$config = $config[$arg];
			}
			else
			{
				$config = false;
				break;
			}
		}
		

		return $config;
	}

	/**
	 * 设置文件搜索路径，提供给getLoadFilePath使用
	 */
	static function setLoadDir( $dir )
	{
		if ( is_dir( $dir ) )
		{
			$loadDir = array( $dir );
			if ( isset ( $GLOBALS['__LoadDir'] ) && is_array( $GLOBALS['__LoadDir'] ) )
			{
				$GLOBALS['__LoadDir'] = array_merge( $GLOBALS['__LoadDir'], $loadDir );
			}
			else
			{
				$GLOBALS['__LoadDir'] = $loadDir;
			}
		}
	}

	/**
	 * 实现名字到实际路径的转换,依赖于setLoadDir的路径设置
	 */
	static function getLoadFilePath( $name )
	{
		if ( !isset ( $GLOBALS['__LoadDir'] ) )
			return;

		$fileName = str_replace( '_' , '/' , $name );
		$loadDir = $GLOBALS['__LoadDir'];
		foreach ( $loadDir as $dir )
		{
			$path = $dir . $fileName . '.class.php';
			if ( is_file( $path ) && is_readable( $path ) )
			{
				return $path;
			}
			$path = $dir . $fileName . '.php';
			if ( is_file( $path ) && is_readable( $path ) )
			{
				return $path;
			}
		}
		throw new FDX_Exception( "{$name} is not defined" );
	}

	/**
	 * 定义autoload
	 */
	static function autoload( $className )
	{ 
		// 先通过配置文件中的路径映射查找，找不到则通过名字转换获取路径
		$autoloadMap = self :: getConfig( 'AutoloadMap' );
		if ( isset( $autoloadMap[$className] ) )
		{
			require_once $autoloadMap[$className];
		}
		else
		{
			$file = self :: getLoadFilePath( $className );
			require_once $file;
		}
	}

	/**
	 * 注册autoload
	 */
	static function registerAutoload()
	{
		if ( !function_exists( 'spl_autoload_register' ) )
		{
			require_once CORE_PATH . '/Class/FDX_Exception.class.php';
			throw new FDX_Exception( 'spl_autoload does not exist in this PHP installation' );
		}
		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * 注册全局异常处理函数
	 */
	static function exception_handler( Exception $ex )
	{
		if(Common::$isDebug){
			FDX_Exception :: dump( $ex );die();
		}
		ob_end_clean();
		if( 299<$ex->getCode() ) {
			//数据库异常日志
			$fileName = date( 'Y-m-d-H-i-s_' ) . mt_rand( 0, 1000 );
		} else {
			//普通错误日志
			$fileName = date( 'Y-m-d' );

		}
		
		if( file_exists('/home') ) {
			$fileName = Core::GetConfig('errlogDir'). $fileName;
		} else {
			$dir = realpath( dirname(__FILE__).'/../' ) .'/log/www-mysql-error/';
			$fileName = $dir . $fileName;
		}
		
		$content  = 'Request:'.var_export($_REQUEST, TRUE);
		$content .= "\nURI: ".$_SERVER['REQUEST_URI'];
		$content .= "\n".$ex;
		@file_put_contents( $fileName, $content, FILE_APPEND );		
		
		header('HTTP/1.0 500 Internal Server Error');
		die( "<h3>Sorry but server is too busy, plz try again later~</h3>".$ex->getCode() );
	}

	/**
	 * 对字符串或数组进行格式化，返回格式化后的数组
	 */
	static function normalize( $input, $delimiter = ',' )
	{
		if ( !is_array( $input ) )
		{
			$input = explode( $delimiter, $input );
		}
		$input = array_map( 'trim', $input );
		return array_filter( $input, 'strlen' );
	}

	/**
	 * 应用程序初始化操作
	 */
	static function init()
	{
		ini_set( "magic_quotes_runtime", 0 );

		date_default_timezone_set( self :: getConfig( 'TimeZone' ) );

		self :: registerAutoload();

		if ( self :: getConfig( 'RunMode' ) == 'debug' )
		{
			set_exception_handler( array( __CLASS__, 'exception_handler' ) );
		} 
		// 初始化Session
		 ini_set('session.gc_maxlifetime', 86400); // 24 hours (in seconds)
		 ini_set('session.cache_expire', 1440); // 24 hours (in minutes)
		 ini_set('session.cookie_lifetime', 86400); // 24 小时(in seconds)
		 ini_set('session.cookie_lifetime', 0);//用户关闭浏览器，则用户就会登出。
		$config = Core :: getConfig( 'Session' );
		$cookie_domain = $config['cookie_domain'];
		ini_set( 'session.cookie_domain', $cookie_domain );
		//$config['mem_obj']	= FDX_Cache::getInstance()->getCache();
		//FDX_MemSession::init($config);
//		$session = new FDX_MemSession();
//		$session -> start();
		//自动登录
		//$objUser = self::ImportMl('User');
		//$objUser->autoLogin();
	}

	static function LoadLib( $fileName )
	{
		$path = Core :: getConfig( 'Lib_Path' ) . $fileName;
		if ( isset( $GLOBALS['__IncludePath'][$path] ) )
			return;
		if ( is_file( $path ) && is_readable( $path ) )
		{
			$GLOBALS['__IncludePath'][$path] = true;
			include( $path );
		}
	}

	
	static function ImportMl( $name )
	{
		$name = "Ml_{$name}";
		if ( isset( $GLOBALS['__MlCache'][$name] ) && !defined('USE_DB_FOR_SCRIPT') )
			return $GLOBALS['__MlCache'][$name];

		$Model = new $name();
		if(defined('USE_DB_FOR_SCRIPT')) return $Model;
		$GLOBALS['__MlCache'][$name] = $Model ;

		return $Model ;
	}	

	static function ImportFormat( $name )
	{
		$name = "Format_{$name}";
		if ( isset( $GLOBALS['__FormatCache'][$name] ) && !defined('USE_DB_FOR_SCRIPT') )
			return $GLOBALS['__FormatCache'][$name];
		$Format = new $name();
		$GLOBALS['__FormatCache'][$name] = $Format ;

		return $Format ;
	}

	static function ImportCache( $name )
	{
		$name = "Cache_{$name}";
		if ( isset( $GLOBALS['__CacheCache'][$name] ) && !defined('USE_DB_FOR_SCRIPT') )
			return $GLOBALS['__CacheCache'][$name];

		$Cache = new $name();
		$GLOBALS['__CacheCache'][$name] = $Cache;

		return $Cache;
	}

	static function ImportExtend( $name )
	{
		$name = "Extend_{$name}";
		if ( isset( $GLOBALS['__ExtendCache'][$name] ) && !defined('USE_DB_FOR_SCRIPT') )
			return $GLOBALS['__ExtendCache'][$name];

		$Extend = new $name();
		$GLOBALS['__ExtendCache'][$name] = $Extend;

		return $Extend;
	}

	static function ImportData( $name )
	{
		if ( isset( $GLOBALS['__DataCache'][$name] ) && !defined('USE_DB_FOR_SCRIPT') )
			return $GLOBALS['__DataCache'][$name];

		$GLOBALS['__DataCache'][$name] = include( APP_PATH . 'Data/' . $name . '.php' );

		return $GLOBALS['__DataCache'][$name];
	}
}
