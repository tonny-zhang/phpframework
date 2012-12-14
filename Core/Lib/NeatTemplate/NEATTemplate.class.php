<?php

/**
* NEATTemplate
* NEAT模板解析引擎
* reference : smarttemplate
* @name NEATTemplate
* @author walker <walker[at]neatstudio[dot]com>
* @copyright neatstudio
* @version 1.0.0 2005-11-18 M : 2006-2-14
* @link http://nt.neatcn.com http://www.neatstudio.com http://bbs.chinahtml.com/f25
*/
class NEATTemplate
{

	/**
	* 自动编译
	* @var boolean
	* @access public
	*/
	var $autoCompile = true;

	/**
	* 把语言包编译进模板中
	* @var boolean
	* @access public
	*/
	var $staticLanguageString = true;

	/**
	* php错误报告等级
	* @var integer
	* @access public
	*/
	var $errorReportingLevel = 7;

	/**
	* 解析结果缓存文件生存时间
	* 如果为0不生成解析结果缓存文件
	* @var integer
	* @access public
	*/
	var $aliveTimes = 0;

	/**
	* 模板编译文件存储目录
	* @var string
	* @access public
	*/
	var $templateCacheDir = './caches/templates/';

	/**
	* 模板文件
	* @var string $templateFile
	* @access public
	*/
	var $templateFile = '';

	/**
	* 模板变量
	* @var array
	* @access private
	*/
	var $vars = array();

	/**
	* 模板引擎运行次数
	* @var integer
	* @access public
	*/
	var $loadTemplateTimes = 0;

	/**
	* 编译模板文件命名掩码
	* @var string
	* @access public
	*/
	var $compiledFileNamedMask = 'NEATTemplate';
	
	/**
	* 编译模板文件命名编码
	* @var boolean
	* @access public
	*/
	var $compiledFileNamedEncode = false;

	/**
	* 语言包容器
	* @var array
	* @access public
	*/
	var $languagePackage = array();

	/**
	* 构造函数
	* @param  string $file 模板文件路径
	* @access public
	* @return void
	*/
	function NEATTemplate( $file = '' )
	{
		if ( $file != '' )
			$this->SetTemplate( $file );
	}


	///////////////////////////////////////////////////////////////////////////////////////////////
	// 快捷操作 ( 方法别名 )
	///////////////////////////////////////////////////////////////////////////////////////////////

	/**
	* 设置模板文件 ( 别名 )
	* @param  string $file 模板文件路径
	* @see SetTemplate()
	* @access public
	* @return void
	*/
	function ST( $file )
	{
		$this->SetTemplate( $file );
	}

	/**
	* 设置模板变量 ( 别名 )
	* @param mixed $key 变量名字 或者 变量数组
	* @param  string $value 变量值
	* @see SetValue()
	* @access public
	* @return void
	*/
	function SV( $key, $value = '' )
	{
		$this->SetValue( $key, $value );
	}

	/**
	* 获取模板解析结果 ( 别名 )
	* @see Result()
	* @access public
	* @return $result 解析后的模板结果
	*/
	function &RS()
	{
		return $this->Result();
	}

	/**
	* 输出模板 ( 别名 )
	* 把模板解析结果输出到浏览器
	* @see OutPut()
	* @access public
	* @return void
	*/
	function OP()
	{
		$this->OutPut();
	}

	/**
	* 把执行后的模板结果输出到指定文件 ( 别名 )
	* @param string $file 要输出的文件的路径
	* @see Save()
	* @access public
	* @return void
	*/
	function SA( $file )
	{
		$this->Save( $file );
	}

	///////////////////////////////////////////////////////////////////////////////////////////////
	// 一般操作
	///////////////////////////////////////////////////////////////////////////////////////////////

	/**
	* 设置模板文件
	* @param string $file 模板文件路径
	* @access public
	* @return void
	*/
	function SetTemplate( $file )
	{
		if ( $file == '' )
			$this->_Halt( '请输入一个模板文件' );

		$this->templateFile = $file;
		$this->loadTemplateTimes++;
	}

	/**
	* 设置编译后的模板文件存放地址
	* @param  string $path cache文件夹路径
	* @access public
	* @return void
	*/
	function SetCachePath( $path )
	{
		if ( $path == '' )
			$this->_Halt( '请输入一个有效的cache目录' );

		$this->templateCacheDir = $path;
	}

	/**
	* 设置模板是否进行自动编译
	* @param boolean $auto 真 或 假
	* @access public
	* @return void
	*/
	function SetAutoCompile( $auto )
	{
		$this->autoCompile = $auto;
	}

	/**
	* 装载模板外部配置参数
	* @param array $config 模板配置参数一维数组
	* @access public
	* @return void
	*/
	function SetConfig( $config )
	{
		if ( is_array( $config ) )
		{
			foreach( $config as $k => $v )
			{
				if ( $this->$k  && $v != '' )
					$this->$k = $v;
			}
		}
	}

	/**
	* 设置语言包文件
	* @param mixed $package 语言包文件名或语言包文件名数组
	* @access public
	* @return void
	*/
	function SetLanguagePackage( $package )
	{
		if ( !empty( $package ) || $package != '' )
		{
			if ( is_array( $package ) )
			{
				$this->languagePackage += $package;
			}
			else
				$this->languagePackage[] = $package;
		}
	}

	/**
	* 设置是否把语言包编译进模板中
	* @param boolean $static 真 或 假
	* @access public
	* @return void
	*/	
	function SetStaticLanguageString( $static )
	{
		$this->staticLanguageString = $static;
	}

	/**
	* 获取模板装载次数
	* @access public
	* @return integer 模板装载次数
	*/
	function GetLoadTemplateTimes()
	{
		return $this->loadTemplateTimes;
	}

	/**
	* 设置模板结果缓存时间
	* @param boolean $times 缓存时间 单位:秒
	* @access public
	* @return void
	*/
	function SetAliveTimes( $times )
	{
		$this->aliveTimes = intval( $times );
	}

	/**
	* 设置模板变量
	* 例一:
	* SetValue( 'url', 'www.neatstudio.com' );
	* 例二:
	* $array = ( 'name' => 'walker', 'skill' => 'PHP' );
	* SetValue( $array );
	* @param mixed $key 变量名字 或者 变量数组
	* @param  string $value 变量值
	* @access public
	* @return void
	*/
	function SetValue( $key, $value = '' )
	{
		if ( is_array( $key ) )
		{
			foreach ( $key as $k => $v )
				$this->vars[$k] = $v;
		}
		else
		{
			$this->vars[$key] = $value;
		}
	}

	function Plugin( $obj )
	{
		$this->plugin[] = $obj;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////
	// 输出处理
	///////////////////////////////////////////////////////////////////////////////////////////////

	function Compile()
	{
		// 根据编译文件命名编码开关生成对应的目标编译文件名字
		if ( $this->compiledFileNamedEncode == true )
			$compiledFile = strtoupper( md5( $this->templateFile . $this->compiledFileNamedMask ) ) . '.php';
		else
			$compiledFile = preg_replace( '/[:\/.\\\\]/', '-', $this->templateFile ) . '.php';

		$compiledFile = $this->templateCacheDir . $compiledFile;

		// 获取编译后的模版文件创建时间
		$createdTime = $this->_GetFileCreatedTime( $compiledFile );
		
		/**
		* 1. 如果模板编译文件不存在,则返回0,那模板创建时间一定大于0.
		* 2. 如果模板文件创建时间大于编译后的文件,说明模板文件有改动,再次编译.
		*/
		if ( $this->_GetFileCreatedTime( $this->templateFile ) > $createdTime )
		{
			// 包含模板编译引擎
			include_once( 'NEATTemplateCompiler.class.php' );

			// 编译模板文件
			$complier = new NEATTemplateCompiler( $this->plugin );
			$complier->SetCompiledOutputFile( $compiledFile );
			$complier->SetStaticLanguageString( $this->staticLanguageString );
			$complier->SetLanguagePackage( $this->languagePackage );
			$complier->Compile( $this->templateFile );
		}

		return $compiledFile;
	}

	/**
	* 输出模板
	* 把模板解析结果输出到浏览器
	* @access public
	* @return void
	*/
	function Output()
	{
		// 设置PHP错误报告等级
		error_reporting( $this->errorReportingLevel );

		$compiledFile = $this->Compile();

		// 把收集到的模板变量赋值給编译后的模板文件中的变量
		extract( $this->vars );

		if ( !defined( 'IN_NTP' ) )
			define( 'IN_NTP', true );
		
		// 包含编译后的模板文件
		if ( !file_exists( $compiledFile ) )
			$this->_Halt( "没找到编译过的模板文件[{$compiledFile}]." );

		include( $compiledFile );		

		// 清空模板变量
		//unset( $this->vars );
	}

	/**
	* 获取模板解析结果
	* @access public
	* @return $result 解析后的模板结果
	*/
	function &Result()
	{
		// 打开缓冲区,捕捉Output()方法输出的解析后的模板内容,然后把缓冲区内的内容返回.
		ob_start();
		$this->Output();
		$result = ob_get_contents(); 
		ob_end_clean();

		return $result;
	}

	/**
	* 把执行后的模板结果输出到指定文件
	* @param string $file 要输出的文件的路径
	* @access public
	* @return void
	*/
	function Save( $file )
	{
		if ( $file == '' )
			$this->_Halt( '请输入一个有效的目标文件' );
		
		if ( $fp = fopen( $file, "w+" ) )
		{
			// 注意:文件锁在FAT16,32磁盘格式下不起作用。
			@flock( $fp, LOCK_EX );
			fwrite( $fp, $this->Result() );
			@flock( $fp, LOCK_UN );
			fclose( $fp );
		}
		else
			$this->_Halt( '解析后的结果不能被写入.请检查权限、路径、目标文件是否正确或正确.' );
	}

	/**
	* 清空指定模板缓存目录中的缓存文件
	* @access public
	* @return void
	*/
	function ClearCache()
	{
		if ( is_dir( $this->templateCacheDir ) )
		{
			if ( $dir = @opendir( $this->templateCacheDir ) )
			{
				while ( $file = @readdir( $dir ) )
				{
					$check = is_dir( $file );
					if ( !$check )
						@unlink( $this->templateCacheDir . $file );
				}
				@closedir( $dir );
				return true;
			}
			else
			{
				$this->_Halt( '不能打开模板缓存目录,请检查其权限是否正确.' );
				return false;
			}
		}
		else
		{
			$this->_Halt( '模板缓存目录设置错误.' );
			return false;
		}
	}

	/**
	* 判断是否有可用缓存
	* @param boolean $exit 输出缓存内容后是否终止程序
	* @access public
	* @return boolean 真 或 假
	*/
	function IsAlive( $exit = false )
	{
		// 文件名或者路径不为空
		
		if ( $this->templateFile != '' && $this->templateCacheDir != '' )
		{
			$compiledFile = $this->templateCacheDir . md5( $_SERVER ['HTTP_HOST'] . $_SERVER['PHP_SELF'] . $_SERVER["REQUEST_URI"] );
			$cacheFileCreatedTimes = $this->_GetFileCreatedTime( $compiledFile );

			// 在生存时间内
			if ( ( $cacheFileCreatedTimes + $this->aliveTimes ) > time() )
			{
				if ( @readfile( $compiledFile ) )
				{
					if ( $exit == true )
						exit;
					else
						return true;
				}
				else
					return false;
			}
			// 在生存时间外
			else
			{
				$this->refreshTempFile = true;
				return false;
			}
		}
		// 文件名或者路径为空
		else
			return false;
	}

	///////////////////////////////////////////////////////////////////////////////////////////////
	// 内部调用
	///////////////////////////////////////////////////////////////////////////////////////////////

	/**
	* 获取文件创建时间
	* @param string $file 要获取创建时间的目标文件路径
	* @access private
	* @return datetime 文件创建时间
	*/
	function _GetFileCreatedTime( $file )
	{
		if ( @is_file( $file ) )
			return filemtime( $file );
		else
			return 0;
	}

	/**
	* 抛出错误信息
	* @param string $message 要抛出的错误信息
	* @access private
	*/
	function _Halt( $message )
	{
		die( '<font color="blue" face="verdana">NEATTemplate halt : </font><br><br><font color="red" face="verdana">错误信息:' . $message . '</font><br><br><font color="green" face="verdana">DATE:' . date( "Y-m-d H:i:s" ) . '</font>' );
	}
}

