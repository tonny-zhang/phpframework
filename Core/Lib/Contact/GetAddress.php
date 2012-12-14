<?php

class GetAddress
{
	public $username;
	public $userpass;
	public $host;
	public $host_arr = array( "163" => array( "file" => "class.163Http.php", "class" => "http163" ),
		"gmail" => array( "file" => "class.gmail.php", "class" => "Gmail" ),
		"126" => array( "file" => "class.126Http.php", "class" => "http126" ),
		"sina" => array( "file" => "class.sinaHttp.php", "class" => "sinaHttp" ),
		"yahoo" => array( "file" => "class.yahooHttp.php", "class" => "yahooHttp" ),
		"tom" => array( "file" => "class.tomHttp.php", "class" => "tomHttp" ),
		"msn" => array( "file" => "class.msn.php", "class" => "msnHttp" ) 
	);
	public function __construct( $username, $userpass, $host )
	{
		if ( !$username || !$userpass )
			return false;
		$this->username = $username;
		$this->userpass = $userpass;
		$this->host = $host;
	}
	public function GetList()
	{
		$obj = $this->GetInstance();

		if ( !$obj )return false;
		$rs = $obj->getAddressList( $this->username, $this->userpass );
		if ( !is_array( $rs ) || count( $rs ) < 1 )return false;
		return $rs;
	}
	public function GetInstance()
	{
		$classinfo = $this->host_arr[$this->host];
		if ( !$classinfo )return false;
		include_once( $classinfo['file'] );
		if ( class_exists( $classinfo['class'] ) )
		{
			$class = $classinfo['class'];
			$obj = new $class;
			return $obj;
		}
		return false;
	}
	public static function Instance( $username, $userpass, $host )
	{
		$obj = new GetAddress( $username, $userpass, $host );
		return $obj;
	}
}
