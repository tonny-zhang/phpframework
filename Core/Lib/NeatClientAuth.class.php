<?php

class NeatClientAuth
{
	var $filedPrefix = '';
	var $authCode = '';
	var $fileds = array();
	var $cookieTime = 0;
	var $cookieDomain = '';

	function NeatClientAuth( $filedPrefix, $authCode, $fileds, $cookieTime = 0, $cookieDomain = '' )
	{
		$this->filedPrefix		= $filedPrefix;
		$this->authCode		= $authCode;
		$this->cookieTime		= $cookieTime;
		$this->cookieDomain		= $cookieDomain;
		$this->fileds = $fileds;
	}

	function SetAuthData( $data )
	{
		foreach ( $this->fileds as $filed )
		{
			$mixString .= $data[$filed];
		}

		$this->authData = $data;

		$this->curVerifyCodeClient = $this->GetVerifyCode( $mixString );
		$this->curDataEnCode = $this->EnCode( serialize( $data ) );

		$this->setcookie( 'data', $this->curDataEnCode );
		$this->setcookie( 'verifycode', $this->EnCode( $this->curVerifyCodeClient ) );
	}

	function GetVerifyCode( $value )
	{
		return md5( $this->authCode . $value . "ZZMM__!!$%" . $this->authCode );
	}

	function CheckAuth()
	{
		$dataEnCode = $this->GetCookie( 'data' );

		if ( !$dataEnCode )
			$dataEnCode = $this->curDataEnCode;

		$data = unserialize( $this->DeCode( $dataEnCode ) );

		foreach ( $this->fileds as $filed )
		{
			$mixString .= $data[$filed];
		}

		$verifyCode		= $this->GetVerifyCode( $mixString );
		$verifyCodeClient	= $this->DeCode( $this->GetCookie( 'verifycode' ) );

		if ( !$verifyCodeClient )
			$verifyCodeClient = $this->curVerifyCodeClient;

		if ( $verifyCodeClient && $verifyCode == $verifyCodeClient )
		{
			foreach ( $this->fileds as $filed )
			{
				$this->authData[$filed] = $data[$filed];
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	function CleanAuth()
	{
		setcookie( $this->filedPrefix . 'data', $value, $this->cookieTime, '/', $this->cookieDomain );
		setcookie( $this->filedPrefix . 'verifycode', $value, ( -86400 * 365 ), '/', $this->cookieDomain );
	}

	function GetAuthData( $name = '' )
	{
		if ( $name )
			return $this->authData[$name];
		else
			return $this->authData;
	}

	function SetCookie( $name, $value )
	{
		setcookie( $this->filedPrefix . $name, $value, $this->cookieTime, '/', $this->cookieDomain );
	}

	function GetCookie( $name )
	{
		return $_COOKIE[$this->filedPrefix . $name];
	}

	function EnCode( $string )
	{
		for( $i = 0;$i < strlen( $string ); $i++ )
		{
			$string[$i] = chr( ord( $string[$i] ) ^ $this->authCode );
		}

		return str_replace('=', '', base64_encode( $string ) );
	}

	function DeCode( $string )
	{
		$string = ( base64_decode( $string ) );

		for( $i = 0; $i < strlen( $string ); $i++ )
		{
			$string[$i] = chr( ord( $string[$i] ) ^ $this->authCode );
		}

		return $string;
	}
}
