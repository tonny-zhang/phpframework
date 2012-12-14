<?php

/**
* NEATTemplateCompiler
* NEAT模板编译引擎
* reference : smarttemplate
* @name NEATTemplateCompiler
* @author : walker <walker[at]neatstudio[dot]com>
* @author : hihiyou <hihiyou[at]neatstudio[dot]com>
* @copyright neatstudio
* @version 1.0.0 : 2005-11-18 M : 2006-2-14
* @link http://nt.neatcn.com http://www.neatstudio.com http://bbs.chinahtml.com/f25
*/
class NEATTemplateCompiler
{
	/**
	* 内部版本号
	* @var string
	* @access public
	*/
	var $version = '1.0.0';

	/**
	* 是否把语言包编译进模板中的开关
	* @var boolean
	* @access public
	*/	
	var $staticLanguageString = true;
	
	/**
	* 编译文件路径
	* @var string
	* @access public
	*/
	var $compileFile = '';
	
	/**
	* 模板文件内容
	* @var string
	* @access public
	*/
	var $contents = '';

	/**
	* 编译后输出目标文件
	* @var string
	* @access public
	*/
	var $CompiledOutputFile = '';

	/**
	* 安全模式开关
	* 当本开关开启后,如果在模板中碰到 php 的起始和结束标记,编译将强行停止.
	* @var boolean
	* @access public
	*/
	var $safeMode = false;

	/**
	* 包含文件允许的后缀格式
	* @var array
	* @access public
	*/
	var $includeFileExt = array( 'html', 'htm', 'tpl', 'txt' );

	/**
	* 语言包容器
	* @var array
	* @access public
	*/
	var $languagePackage = array();	

	/**
	* 构造器
	* @return void
	*/
	function NEATTemplateCompiler( $plugin )
	{
		$this->plugin = $plugin;
	}

	/**
	* 编译模板
	* @param string $file 待编译目标文件
	* @access public
	* @return void
	*/
	function Compile( $file )
	{			
		$this->compileFile = $file;
		
		// 打开目标文件
		$this->contents = $this->_Open( $file );

		// 如果安全模式开启，检查PHP起始、结束标签
		if ( $this->safeMode == true )
			$this->_CheckPHPTag( $this->contents );
		
		// 处理模板中的标签
		// TODO : 让用户可以选择关闭/打开以下任何一个编译功能.
		$this->_ParseINCLUDE();
		$this->_ParseBEGIN();
		$this->_ParseBEGINEND();
		$this->_ParseVARS();
		$this->_ParseFunction();
		$this->_ParseIF();
		$this->_ParseELSE();
		$this->_ParseENDIF();

		// 其他处理
		$this->_CheckIncluded();
		$this->_HeaderNotes();

		if ( is_array( $this->plugin ) )
		{
			foreach ( $this->plugin as $plugin )
			{
				$this->contents = $plugin->Parse( $this->contents, $this );
			}
		}

		// 保存编译后的文件
		$this->_Save();
	}

	/**
	* 设置编译后输出目标文件
	* @param string $file 编译后输出的目标文件地址
	* @access public
	* @return void
	*/
	function SetCompiledOutputFile( $file )
	{
		$this->CompiledOutputFile = $file;
	}

	/**
	* 设置是否把语言包编译进模板中
	* @param boolean $boolean 真 或 假
	* @access public
	* @return void
	*/	
	function SetStaticLanguageString( $boolean = false )
	{
		$this->staticLanguageString = $boolean;
	}

	/**
	* 设置语言包文件名数组
	* @param array $package 语言包文件名数组
	* @access public
	* @return void
	*/
	function SetLanguagePackage( & $package )
	{
		$this->languagePackage = & $package;
	}


	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 一般处理
	//////////////////////////////////////////////////////////////////////////////////////////////////////////


	/**
	* 读取模板文件内容
	* @param string $file 模板文件路径
	* @access private
	* @return void
	*/
	function _Open( $file )
	{
		if ( function_exists( 'file_get_contents' ) )
		{
			$contents = @file_get_contents( $file );
			if ( !$contents )
				$contents  =  "模板文件不存在: " . $file;
		}
		else
		{
	        if ( $fp = @fopen($file, "r") )
	        {
		        $contents  =  fread( $fp, filesize( $file ) );
		        fclose( $fp );
		    }
		    else
		    	$contents  =  "模板文件不存在: " . $file;
		}

		if ( strlen( $contents ) < 1 )
			$this->_Halt( '模板文件为空, 文件:' . $file );

		return $contents;
	}
	/**
	* 当缓存文件不存在时创建
	* @access private
	* @return void
	*/
	function _mkdirs($dir){
		return is_dir($dir) or ($this->_mkdirs(dirname($dir)) and mkdir($dir,077)); 
	}
	/**
	* 保存编译后的文件
	* @access private
	* @return void
	*/
	function _Save()
	{
		$this->_mkdirs(pathinfo($this->CompiledOutputFile,PATHINFO_DIRNAME));
		if ( $fp = @fopen( $this->CompiledOutputFile, "w+" ) )
		{
			@flock( $fp, LOCK_EX );
			fwrite( $fp, $this->contents );
			@flock( $fp, LOCK_UN );
			fclose( $fp );
		}
		else
			$this->_Halt( '编译后的文件不能被写入.请检查权限、路径是否正确.' );
	}

	/**
	* 检查PHP起始、结束标签
	* 当 $safeMode 开始时，启用本方法检查PHP起始、结束标签是否存在于模板文件中,如果存在,编译强行中止.
	* @access private
	* @return void
	*/
	function _CheckPHPTag( $contents )
	{
		if ( strpos( $contents, '<?' ) )
			$this->_Halt( '编译引擎安全模式已经开启, 发现PHP起始标记,编译中止.' );

		if ( strpos( $contents, '?>' ) )
			$this->_Halt( '编译引擎安全模式已经开启, 发现PHP结束标记,编译中止.' );
	}

	/**
	* 抛出错误信息
	* @param string $message 要抛出的错误信息
	* @access private
	* @return void
	*/
	function _Halt( $message )
	{
		die( '<font color="blue">NEAT TEMPLATE COMPILER ENGINE Halt : </font><br><br><font color="red">错误信息:' . $message . '</font><br><br><font color="green">DATE:' . date( "Y-m-d H:i:s" ) . '</font>' );
	}

	//////////////////////////////////////////////////////////////////////////////////////////////////////////
	// 编译处理
	//////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	* 文件头注释
	* @access private
	* @return void
	*/
	function _HeaderNotes()
	{
		$phpCode = '<?php' . "\r\n"
						. '/**' . "\r\n"
						. '* Compiled by NEATTemplate ' . $this->version . "\r\n"
						. '* Created on : ' . date( "Y-m-d H:i:s" ) . "\r\n"
						. '*/'. "\r\n"
						. '?>' . "\r\n";

		$this->contents = $phpCode . $this->contents;
	}

	/**
	* 检查是否被模板引擎所包涵
	* @access private
	* @return void
	*/
	function _CheckIncluded()
	{
		$phpCode = '<?php if ( !defined( \'IN_NTP\' ) ) exit( \'Access Denied\' ); ?>' . "\r\n";
		$this->contents = $phpCode . $this->contents;
	}

	/**
	* 变量处理
	* 1. 根据{变量}生成PHP代码: echo $__NT['变量']
	* 2. 把{变量}替换成生成的PHP代码.
	* @access private
	* @return void
	*/
	function _ParseVARS()
	{
		// 提取{}内的变量名字 ( 范围:根据变量命名规则 )
		if ( preg_match_all('/{((?:[A-Za-z_]){1}[A-Za-z0-9_.:]*)}/', $this->contents, $rs ) )//zk update,加入静态变量过滤
		{		
		
			//print_r($rs);exit;
			/**
			对特殊处理格式数组进行保持键值不变逆向排序
			把有特殊格式的匹配结果提到没有特殊格式的匹配结果前,方便在循环中优先处理
			*/
			arsort( $rs[1] );
			
			// 循环处理
			foreach( $rs[1] as $key => $tag )
			{					
				$phpCode	= '';

				$ptag = $this->ProccessVARS( $tag );
				
				// 普通变量格式处理
				$phpCode = '<?php echo ' . $this->ProccessVARS( $tag ) . '; ?>';

				// 把原始内容替换成对应的PHP代码
				$this->contents = str_replace( $rs[0][$key], $phpCode,  $this->contents );
			}
		}
	}

	function _ParseFunction()
	{
		// 提取{}内的变量名字 ( 范围:根据变量命名规则 )
		if ( preg_match_all('/{#(.+?)}/', $this->contents, $rs ) )
		{
			// 循环处理
			foreach( $rs[1] as $key => $tag )
			{					
				$phpCode	= '';

				$ptag = $this->ProccessVARS( $tag );
				
				// 普通变量格式处理
				$phpCode = '<?php echo ' . $ptag . '; ?>';

				// 把原始内容替换成对应的PHP代码
				$this->contents = str_replace( $rs[0][$key], $phpCode,  $this->contents );
			}
		}

		if ( preg_match_all('/<!--(?:\s?)#(.+?)(?:\s?)AS(?:\s?)([a-zA-Z0-9_.]+)(?:\s?)-->/', $this->contents, $rs ) )
		{
			// 循环处理
			foreach( $rs[1] as $key => $tag )
			{					
				$phpCode	= '';

				$ptag = $this->ProccessVARS( $tag );
				$var = $this->ProccessVARS( $rs[2][$key] );
				
				// 普通变量格式处理
				$phpCode = '<?php ' . $var . ' = ' . $ptag . '; ?>';

				// 把原始内容替换成对应的PHP代码
				$this->contents = str_replace( $rs[0][$key], $phpCode,  $this->contents );
			}
		}

		if ( preg_match_all('/<!--(?:\s?)#(.+?)(?:\s?)-->/', $this->contents, $rs ) )
		{
			// 循环处理
			foreach( $rs[1] as $key => $tag )
			{					
				$phpCode	= '';

				$ptag = $this->ProccessVARS( $tag );
				$var = $this->ProccessVARS( $rs[2][$key] );
				
				// 普通变量格式处理
				$phpCode = '<?php ' . $ptag . '; ?>';

				// 把原始内容替换成对应的PHP代码
				$this->contents = str_replace( $rs[0][$key], $phpCode,  $this->contents );
			}
		}
	}

	/**
	* IF 和 ELSEIF 处理
	* @access private
	* @return void
	*/
	function _ParseIF()
	{	
		if ( preg_match_all( '/<!--(?:\s*)(ELSE)?IF(?:\s*)(.+?)(?:\s*)-->/', $this->contents, $rs ) )
		{			
			foreach( $rs[2] as $key => $tag )
			{
				$else = ( $rs[1][$key] == 'ELSE' ) ? '}' . "\r\n" . 'else' : null;

				$itemList = $this->_SplitIFItem( $tag );

				foreach ( $itemList as $val )
				{
					//list( $left, $right ) = split( '(!=|==|<|>)', trim( $val ), 2 );

					//$var = $this->ProccessVARS( $left );

					if ( preg_match('/([a-zA-Z0-9_.]+)(?:\s*)([!=<>%]+)(?:\s*)([^()\s]+)/', $val, $itemPart ) )
					{
						$var = $this->ProccessVARS( $itemPart[1] );
						$cmp = $itemPart[2];
						$value = $itemPart[3];

						// 改动开始
						// 这段被余峰改过
						// 用来适应比较的值是变量的情况
						// 比较的值有引号(单双)或者是数字,就是比较值
						// 没有就是比较变量
						if ( $value[0] == "'" || $value[0] == '"' || is_numeric( $value ) )
						{
							// 去掉首尾
							$value = str_replace( '"', '', $value );
							$value = str_replace( "'", '', $value );

							if ( !is_numeric( $value ) )
								$value = "'" . $value . "'";

							$phpCode = ' ' . $var . ' ' . $cmp . ' ' . $value . ' ';
						}
						else
						{
							$phpCode = ' ' . $var . ' ' . $cmp . ' ' . $this->ProccessVARS( $value ) . ' ';
						}

						
					}
					elseif ( preg_match('/!\s*([a-zA-Z0-9_.]+)/', $val, $itemPart ) )
					{
						$phpCode = ' !' . $this->ProccessVARS( $itemPart[1] ) . ' ';
					}
					else
					{
						$phpCode = ' ' . $this->ProccessVARS( trim( $val ) ) . ' ';
					}

					$tag = str_replace( $val, $phpCode, $tag );
				}

				$phpCode = '<?php' . "\r\n" 
							. $else . 'if (' . $tag . ')' . "\r\n"
							. '{' . "\r\n"
							. '?>';

				$this->contents = str_replace( $rs[0][$key], $phpCode, $this->contents );
			}
		}
	}

	function _SplitIFItem( $arg )
	{
		$list = array();

		$s = 0;
		$lock = false;
		for ( $i = 0; $i < strlen( $arg ); $i++ )
		{
			if ( ( $arg[$i] == "'" || $arg[$i] == '"' )  && !$lock )
			{
				$lock = true;
				continue;
			}

			if ( ( $arg[$i] == "'" || $arg[$i] == '"' ) && $lock )
			{
				$lock = false;
			}

			if ( $lock )
				continue;
			
			if ( ( ( ( $arg[$i+1] . $arg[$i+2] =="&&" ) || ( $arg[$i+1] . $arg[$i+2] =="||" ) ) && !$lock ) || ( $i + 1 ) == strlen( $arg ) )
			{
				$list[] =  substr( $arg, $s, $i - $s + 1 );

				$s = $i+3;
				$i = $i + 1;
			}
		}

		return $list;
	}

	/**
	* ELSE 处理
	* @access private
	* @return void
	*/
	function _ParseELSE()
	{
		$phpCode = '<?php' . "\r\n"
						. '}' . "\r\n"
						. 'else' . "\r\n"
						. '{' . "\r\n"
						. '?>';

		$this->contents = preg_replace("/<!--(?:\s*)ELSE(?:\s*)-->/",  $phpCode,  $this->contents );
	}

	/**
	* ENDIF 处理
	* @access private
	* @return void
	*/
	function _ParseENDIF()
	{
		$phpCode = '<?php' . "\r\n"
						. '}' . "\r\n"
						. '?>';

		$this->contents = preg_replace("/<!--(?:\s*)ENDIF(?:\s*)-->/",  $phpCode,  $this->contents );
	}

	/**
	* BEGIN 处理
	* @access private
	* @return void
	*/
	function _ParseBEGIN()
	{
		/**
		* 获取网页中的<!-- BEGIN tag -->部分,提取出其中tag的值.
		*/

		if ( preg_match_all('/<!--(?:\s*)BEGIN?(?:\s*)([a-zA-Z0-9_.]+)(?:\s+)AS(?:\s+)([a-zA-Z0-9_.]+)(?:\s*)-->/', $this->contents, $rs, PREG_SET_ORDER ) )
		{
			foreach ( $rs as $val )
			{
				$block = "<". "?php\r\nif ( " . $this->ProccessVARS( $val[1] ) . " )\r\n{\r\nforeach ( " . $this->ProccessVARS( $val[1] ) . " as " . $this->ProccessVARS( $val[2] ) . " )\r\n{\r\n?" . ">";

				$this->contents = str_replace( $val[0], $block, $this->contents );
			}
		}

		if ( preg_match_all('/<!--(?:\s*)BEGIN?(?:\s*)([a-zA-Z0-9_.]+)(?:\s+)AS(?:\s+)([a-zA-Z0-9_.]+)(?:\s+)=>(?:\s+)([a-zA-Z0-9_.]+)(?:\s*)-->/', $this->contents, $rs, PREG_SET_ORDER ) )
		{
			foreach ( $rs as $val )
			{
				$block = "<". "?php\r\nif ( " . $this->ProccessVARS( $val[1] ) .  " )\r\n{\r\nforeach ( " . $this->ProccessVARS( $val[1] ) .  " as " . $this->ProccessVARS( $val[2] ) .  " => " . $this->ProccessVARS( $val[3] ) .  " )\r\n{\r\n?" . ">";

				$this->contents = str_replace( $val[0], $block, $this->contents );
			}
		}

	}

	/**
	* END 处理
	* @access private
	* @return void
	*/
	function _ParseBEGINEND()
	{
		$block = "<" . "?php\r\n}\r\n}\r\n?" . ">";

		$this->contents = preg_replace("/<!-- END [a-zA-Z0-9_.]* -->/", $block,  $this->contents );
	}

	/**
	* 包含文件处理 ( 不编译PHP文件 )
	* @access private
	* @return void
	*/
	function _ParseINCLUDE()
	{	
		if ( preg_match_all( '/<!--(?:\s*)INC(?:LUDE)?(?:\s*)(?:"|\'*)(.+?)(?:"|\'*)(?:\s*)-->/',$this->contents, $rs ) )
		{
			foreach( $rs[1] as $key => $tag )
			{
				$contents = "<?p" . "hp include( '" . $tag . "' );?" . ">";
				
				$this->contents = str_replace( $rs[0][$key], $contents, $this->contents );
			}
		}
	}

	/**
	* 变量转换处理
	* 根据变量名字类型生成完整可用的变量名并返回
	* @access private
	* @return $code 处理后的变量
	*/
	function ProccessVARS( $string )
	{
		//zk add true|false关键词过滤
		if(preg_match('/true|false/is',$string)){
			return $string;
		}
		if ( $string[0] == "'" || $string[0] == '"' || is_numeric( $string ) )
			return $string;
		
		if ( preg_match( '/\(.*?\)/is', $string ) )//zk update,函数不一定都有参数
			return $this->_ParseVarInFunction( $string );

		if ( $string )
		{
			//zk add 加静态变量 {
			$list = explode( '::', $string );
			if(count($list) == 2){
				return implode('::$',$list);
			}
			//zk add 加静态变量 }
			$list = explode( '.', $string );

			$var = "$" . array_shift( $list );

			if ( is_array( $list ) )
			{
				foreach ( $list as $val )
				{
					$var .= "['{$val}']";
				}
			}

			return $var;
		}
		else
		{
			return false;
		}
	}

	function _ParseVarInFunction( $string )
	{
		$posList = $this->_GetFunctionVariable( $string );
		$source = $string;

		$offset = 0;
		foreach ( $posList as $pos => $varName )
		{
			$var = $this->ProccessVARS( $varName );

			$source =  substr( $source, 0, $pos + $offset ) . $var . substr( $source, $pos + $offset + strlen( $varName ) );

			$offset += strlen( $var ) - strlen( $varName );
		}

		return $source;
	}

	function _GetFunctionVariable( $string )
	{		
		$variableList = array();
		$offset = 0;
		while( preg_match( '/\((.+)\)/', $string, $result, PREG_OFFSET_CAPTURE ) )
		{
			$argList = explode( ',', $result[1][0] );

			$offset += $result[1][1];

			$pos = 0;
			foreach ( $argList as $k => $arg  )
			{
				if ( !preg_match( '/^\s*["|\'].*/is', $arg ) && !preg_match( '/^\s*([a-zA-Z0-9_]+\s*\().*/is', $arg ) )
				{
					if ( preg_match_all( '/([a-z0-9_\.]+)/is', $arg, $re, PREG_OFFSET_CAPTURE ) )
					{
						foreach ( $re[1] as $v )
						{
							if ( !is_numeric( trim($v[0] ) ) )
							{
								$p = $v[1] + $offset + $pos + $k;
								$variableList[$v[0]][$p] = 1;
							}
						}
					}
				}

				$pos += strlen( $arg );
			}

			$string = $result[1][0];
		}

		$list = array();
		foreach ( $variableList as $varName => $posList )
		{
			foreach ( $posList as $pos => $v )
			{
				$list[$pos] = $varName;
			}
		}

		ksort( $list );

		return $list;
	}

	/**
	* 语言变量转换处理
	* 根据语言变量名字类型生成完整可用的变量名并返回
	* @access private
	* @return $code 处理后的变量
	*/
	function PrccessLangVARS( $var )
	{
		$tmp		= explode( '.', $var );
		$counter = count( $tmp );
	

		$code = '$' . $tmp[0];

		for ( $i = 1; $i < $counter; $i++ )
			$code .=	"['" . $tmp[$i] . "']";
			
		return $code;
	}

	/**
	* 获取变量实际名字
	* 对应形如 a.b.c.d 的变量名字,返回最后一个点后面的名字,本例返回d
	* @access private
	* @return $var 处理后的变量
	*/
	function _GetRealVAR( $var )
	{
		if ( strpos( $var, '.' ) )
			$var = substr( $var, strrpos( $var, "." ) + 1, strlen( $var ) );

		return $var;
	}

	/**
	* 检查变量命名是否符合规定
	* @access private
	* @return boolean
	*/
	function _CheckVarNaming( $variable )
	{	
		if ( preg_match( '/^[a-zA-Z0-9_.]+$/', $variable ) )
			return true;
		else
			return false;	
	}

}

