<?php

class FDX_ModuleControl
{
	var $id = 1;
	var $menuItem = array();

	var $allowModuleList = array();

	function __construct( $xmlFilePath, $arrayFilePath, $allowModuleList )
	{
		$this->xmlFilePath = $xmlFilePath;
		$this->arrayFilePath = $arrayFilePath;
		$this->allowModuleList = $allowModuleList;
	}

	function Load( $reload = 0 )
	{		
		if ( @filemtime( $this->xmlFilePath ) > @filemtime( $this->arrayFilePath ) || $reload )
		{
			$xml = @file_get_contents( $this->xmlFilePath );

			if ( $xml )
			{
				$this->_ParseDocument( $xml );

				$fp = @fopen( $this->arrayFilePath, "w+" );
				@flock( $fp, LOCK_EX );
				fwrite( $fp, "<?php\r\n\$moduleMenuArray = " . var_export( $this->xmlArray['module'], true ) . ";\r\n?>" );
				@flock( $fp, LOCK_UN );
				fclose( $fp );
			}
		}

		@include( $this->arrayFilePath );

		$this->moduleMenuArray = $moduleMenuArray;
	}

	function GetModule()
	{
		return $this->moduleMenuArray;
	}

	function GetMenuScript( $all = 0, $allow = 1 )
	{
		$this->_BuildMenu( $this->moduleMenuArray, 0, array(), $all, $allow, '' );

		return implode( "\r\n", $this->menuItem );
	}

	function GetPath( $path )
	{
		$path = explode( '.', $path );
		
		$subModule = $this->moduleMenuArray[$path[0]];
		$i = 1;
		while ( $subModule )
		{
			$result[] = array( 'title' => $subModule['title'] );

			$subModule = $subModule['sub'][$path[$i]];

			$i++;
		}

		return $result;
	}

	function _BuildMenu( $menus, $parentID, $parent, $all, $allow, $parentPath = '' )
	{
		foreach ( $menus as $key => $item )
		{
			if ( $key == 'id' )
			{
				break;
			}

			if ( !in_array( $item['id'], $this->allowModuleList ) && $allow )
			{
				//continue;
			}

			if ( $item['menu'] != "none" || $all )
			{
				unset( $subItem );
				$subParent = $parent;
				array_push( $subParent, $key );

				$url = $parent ? 'index.php?mod=' . implode( '.', $subParent ) : '';
				$title = $item['title'];
				$id = $item['id'];
				$icon = $item['icon'];

				if ( is_array( $item['sub'] ) )
					$this->menuItem[] = "tree.nodes['{$parentID}_{$id}'] = 'text:{$title};url:{$url};target:main';";
				else
					$this->menuItem[] = "tree.nodes['{$parentID}_{$id}'] = 'text:{$title};url:{$url};target:main';";

				if ( is_array( $item['sub'] ) )
				{
					$subItem = $this->_BuildMenu( $item['sub'], $id, $subParent, $all, $allow );
				}
			}
		}
	}

	function _ParseDocument( $xml )
	{
		$i = -1;

		$parser = xml_parser_create();
		xml_parser_set_option( $parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option( $parser, XML_OPTION_SKIP_WHITE  , 0 );
		xml_parse_into_struct( $parser, $xml, $vals ); 
		xml_parser_free( $parser );
		
		$this->xmlArray = $this->_GetChildren( $vals, $i );
	}

	function _BuildTag( $thisvals, $vals, &$i, $type, $p )
	{
		$tag = array();
		
		if ( isset($thisvals['attributes']) )
		{
			$attributes = $this->_DecodeAttribute($thisvals['attributes']);
			foreach ( $attributes as $key => $val )
			{
				$tag[$key] = $val;
			}
		}

		$p[] = $thisvals['tag'];

		if ( $type === 'complete' )
			$tag = $thisvals['value'];
		else
			$tag = array_merge( $tag, $this->_GetChildren($vals, $i, $p) );
		
		return $tag;
	}
	
	function _GetChildren( $vals, &$i, $p = array() )
	{
		$children = array();

		while( ++$i < count( $vals ) )
		{
			$type = $vals[$i]['type'];
			
			if ( $type === 'complete' || $type === 'open' )
			{
				$tag = $this->_BuildTag( $vals[$i], $vals, $i, $type, $p );

				if ( $this->index_numeric )
				{
					$tag['TAG'] = $vals[$i]['tag'];
					$children[] = $tag;
				}
				else
				{
					$key = $vals[$i]['tag'];
					$children[$key][] = $tag;
				}
			}
			elseif ($type === 'close')
			{
				break;
			}
		}

		foreach( $children as $key => $value )
		{
			if ( is_array( $value ) && ( count( $value ) == 1) )
			{
				$children[$key] = $value[0];
			}
		}

		$pp = array();
		$len = count( $p );


		for ( $n = 1; $n < $len; $n++ )
		{
			if ( $n % 2 != 0 )
				$pp[] = $p[$n];
		}

		$hash = md5( implode( '.', $pp ) );
		$children['id'] = substr( $hash, 0, 2 ) . substr( $hash, 8, 2 ) . substr( $hash, 16, 2 ) . substr( $hash, 30, 2 );

		return $children;
	}

	function _DecodeAttribute( $t )
	{
		$t = str_replace( "&amp;" , "&", $t );
		$t = str_replace( "&lt;"  , "<", $t );
		$t = str_replace( "&gt;"  , ">", $t );
		$t = str_replace( "&quot;", '"', $t );
		$t = str_replace( "&#039;", "'", $t );

		return $t;
	}
	
	function BuileFolder( $path )
	{
		$list = array();
		$this->ParseFolder( $path, $list );

		$xml  = "";
		$xml .= "<?xml version='1.0' encoding='utf-8'?>\r\n<module>\r\n";
		$xml .= $this->Array2Xml( '', $list, 0 );
		$xml .= "</module>";

		file_put_contents( $this->xmlFilePath, $xml );
		//echo $xml;
	}

	function ParseFolder( $path, &$array )
	{
		$res = @opendir( $path );

		while ( $fileName = @readdir( $res ) )
		{
			$tmpInfo = explode( '.', $fileName );
			$ext = strtolower( end( $tmpInfo ) );
			unset( $tmpInfo[count( $tmpInfo ) - 1] );
			$baseName = implode( '.', $tmpInfo );

			if ( $fileName != '.' && $fileName != '..' && $fileName != '.svn' )
			{
				$filePath = Helper_Misc::UnifySeparator( $path . "/" . $fileName );

				if ( is_dir( $filePath ) )
				{
					$array[$fileName] = array();
					$array[$fileName]['path'] = $filePath;
					$array[$fileName]['sub'] = array();
					$this->ParseFolder( $filePath, $array[$fileName]['sub'] );
				}
				elseif ( is_file( $filePath ) && $ext == 'php' )
				{
					$array[$baseName] = $filePath;
				}
			}
		}
	}

	function Array2Xml( $parentName = '', $list, $deep = 0 )
	{
		$space = str_repeat( "\t", $deep );
		$space2 = str_repeat( "\t", $deep + 1 );
		$space3 = str_repeat( "\t", $deep + 2 );
		$space4 = str_repeat( "\t", $deep + 3 );

		$xml = "";

		if ( $parentName )
			$xml .= "{$space}<{$parentName}>\r\n";

		if ( is_array( $list ) )
		{
			foreach ( $list as $key => $val )
			{
				if ( @array_key_exists( 'sub', $val ) && count( $val['sub'] ) > 0 )
				{
					$content = @file_get_contents( $val['path'] . '/' . 'acc.inf' );

					preg_match( '/@@acc_title="(.+?)"/is', $content, $res );
					$title = $res[1] ? $res[1] : $key;
					
					if ( strpos( $content, "@@acc_free" ) === false )
					{
						$xml .= "{$space2}<{$key}>\r\n";
						$xml .= "{$space3}<title>{$title}</title>\r\n";
						$xml .= "{$space3}<sub>\r\n";
						$xml .= $this->Array2Xml( '', $val['sub'], $deep + 2 );
						$xml .= "{$space3}</sub>\r\n";
						$xml .= "{$space2}</{$key}>\r\n";
					}
				}
				else
				{
					$content = @file_get_contents( $val );

					preg_match( '/@@acc_title="(.+?)"/is', $content, $res );
					$title = $res[1] ? $res[1] : $key;

					if ( strpos( $content, "@@acc_free" ) === false )
					{
						$xml .= "{$space2}<{$key}>\r\n";
						$xml .= "{$space3}<title>{$title}</title>\r\n";
						$xml .= "{$space2}</{$key}>\r\n";
					}
				}
			}
		}

		if ( $parentName )
			$xml .= "{$space}</{$parentName}>\r\n";

		return $xml;
	}

}

