<?php
/**
 * 
 * @file class.tomHttp.php
 * 获得tom邮箱通讯录列表
 * @author jvones<jvones@gmail.com> 
 * @date 2009-09-26
 */
include "ContactBase.php";
class tomHttp extends ContactBase
{
	public function checklogin( $user, $password, &$cookie )
	{
		$fileds = "user=" . $user . "&pass={$password}";
		$fileds .= "&style=0&verifycookie";
		$fileds .= "&type=0&url=http://bjweb.mail.tom.com/cgi/login2";
		$arr = $this -> GetUrl( "http://login.mail.tom.com/cgi/login", "POST", $fileds, $cookie );

		/**
		 * if (preg_match("/warning|", $result))
		 * {
		 * return 0;
		 * }
		 */
		return 1;
	}

	public function getAddressList( $user, $password )
	{
		if ( !$this -> checklogin( $user, $password, $cookie ) )
		{
			return 0;
		}
		$this -> _readcookies( $cookie, $res );

		if ( $res['Coremail'] == "" )
		{
			return 0;
		}
		$sid = substr( trim( $res['Coremail'] ), -16 );
		$url = "http://bjapp2.mail.tom.com/cgi/ldvcapp";
		$url .= "?funcid=address&sid=" . $sid . "&showlist=all&listnum=0";

		$arr = $this -> GetUrl( $url, "GET", false, $cookie ); 
		// file_put_contents('./res.txt',$res);
		// print_r($arr);die();
		// $pattern = "/([\\w_-])+@([\\w])+([\\w.]+)/";
		$p = '/<a href="(.+?)address\/add.htm">(.+?)<\/a><\/td><td class="Addr_Td_Address"><a href="(.+?)address\/add.htm">(.+?)<\/a>/';
		if ( preg_match_all( $p, $arr['content'], $tmpres ) )
		{
			$result[] = array( "nickname" => $tmpres[2], "email" => $tmpres[4] ); 
		}
		return $result;
	}

	public function _readcookies( $cookie, &$result )
	{
		if ( !$cookie )return false;
		$tmp = split( ";", $cookie );
		foreach( $tmp as $k => $a )
		{
			$arr = explode( "=", $a );
			$result[trim( $arr[0] )] = trim( $arr[1] );
		}
		return 1;
	}
}

