<?php
class Helper_Cdn
{

	function Flush( $url )
	{
		$cdnApi = "http://wscp.lxdns.com:8080/wsCP/servlet/contReceiver";
		$username = 'fandongxi';
		$password = '#hlz)oqL';
		
		$urlList = array();
		if ( is_array( $url ) )
		{
			foreach ( $url as $val )
			{
				$urlList[] = self::CutHttp( $val );
			}
		}
		else
		{
			$urlList[] = self::CutHttp( $url );
		}

		print_r( $urlList );

		$valid = md5( $username . $password . implode( '', $urlList ) );
		$url = $cdnApi . "?username=" . $username . "&passwd=" . $valid . "&url=" . implode( ';', $urlList );

		echo file_get_contents( $url );
	}

	function CutHttp( $url )
	{
		if ( substr( $url, 0, 7 ) == "http://" )
			return substr( $url, 7 );
		else
			return $url;
	}
}