<?php
class ContactBase
{
	public function GetUrl( $url, $method = "GET", $params = false, &$cookie, $xml = false, $ref = false )
	{
		$ch = curl_init();
		if ( $ref )curl_setopt( $ch, CURLOPT_REFERER, $ref );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_HEADER, 1 );
		curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
		if ( $xml )curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Accept-Language: zh-cn', 'Connection: Keep-Alive', 'Content-Type: application/xml; charset=UTF-8' ) );
		if ( $cookie )curl_setopt( $ch, CURLOPT_COOKIE, $cookie );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		if ( $method == "POST" )
		{
			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $params );
		}
		$rs = curl_exec( $ch ); 
		// $stat	= curl_getinfo($ch);
		// if($stat!=200)die("error!");
		$arr = explode( "\r\n\r\n", $rs, 2 );
		$flag = preg_match_all( '/Set-Cookie:(.+)$/m ', $arr[0], $regs ); ;
		if ( $flag )
		{ 
			// $cookie=$regs[1][0];
			$cookie = join( "\r\n", $regs[1] );
		}
		curl_close( $ch );
		return array( "header" => $arr[0], "content" => $arr[1] );
	}
}