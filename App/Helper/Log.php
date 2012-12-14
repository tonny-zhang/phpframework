<?php

/**
 * Usage:
 * $log = Helper_Log::getInstance( 'file' ); //currently only file log supported
 * $log->log( 'string', array('a','b'), $obj... );
 * 
 */
abstract class Helper_Log
{
	public static function getInstance( $type='file' )
	{
		if( !self::$instance )
		{
			$file = dirname(__FILE__).'/../../test';
			$file = realpath( $file ).'/fan.log';
			return ( self::$instance = new FileLog( $file ) );
		}
		return self::$instance;
	}
	
	public abstract function log($args);
	
	public static $instance=FALSE;
}

class FileLog extends Helper_Log
{
	private $filePath = NULL;
	private $fileHandle = NULL;
	
	public function __construct( $filePath )
	{
		$this->filePath   = $filePath;
		$this->fileHandle = @fopen($filePath, 'a');
	}
	
	public function log($args)
	{
		$args = func_get_args();
		foreach($args as $a)
		{
			$this->writeLog( $a );
		}
	}
	
	private function writeLog($msg)
	{
		if( $msg )
		{
			$msg = var_export($msg, TRUE) . "\n";
			@fwrite($this->fileHandle, $msg);
		}
	}
	
	function __destruct()
	{
		@fclose( $this->fileHandle );
	}
}