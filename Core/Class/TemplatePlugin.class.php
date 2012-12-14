<?php

class TemplatePlugin
{	
	function TemplatePlugin()
	{
		
	}

	function Parse( $content, $compiler )
	{
		$this->compiler = $compiler;

		$content = $this->ProcessPattern( $content );

		return $content;
	}

	function ProcessPattern( $content )
	{
		$patternList = array(
			array(
				'find' => '/<!--(?:\s+)IMPORT(?:\s*)(.+?)(?:\s+)-->/',
				'replace' => 'ImportTemplate',
				'function' => 'include',
			)
		);

		foreach ( $patternList as $pattern )
		{
			if ( preg_match_all( $pattern['find'], $content, $rs ) )
			{
				foreach ( $rs[1] as $key => $val )
				{
					$argList = explode( ',', $val );

					foreach ( $argList as $k => $arg )
					{
						$arg = trim( $arg );
						if ( $arg[0] == "'" || $arg[0] == '"' || is_numeric( $arg ) )
						{
							$argList[$k] = $arg;
						}
						else
						{
							$argList[$k] = $this->compiler->ProccessVARS( $arg );
						}
					}

					$var = $this->compiler->ProccessVARS( $rs[2][$key] );

					if ( $pattern['function'] )
						$phpCode = "<?p" . "hp {$var} include( TemplatePlugin::" . $pattern['replace'] . "( ". implode( $argList, ',' ) ." ) );?" . ">";
					else
						$phpCode = "<?p" . "hp {$var} TemplatePlugin::" . $pattern['replace'] . "( ". implode( $argList, ',' ) ." );?" . ">";

					$content = str_replace( $rs[0][$key], $phpCode, $content );
				}
			}
		}

		return $content;
	}

	function ImportTemplate( $path )
	{
		$Template = Common::GetTemplate();
		return $Template->compile( $path );
	}
}
