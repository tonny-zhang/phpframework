<?php
/**
 * 
 * @file class.sinaHttp.php
 * 获得sina邮箱通讯录列表
 * @author jvones<jvones@gmail.com> 
 * @date 2009-09-26
 */
include "ContactBase.php";
class sinaHttp extends ContactBase
{
	public $host = "";
	function checklogin( $user, $password, &$cookie )
	{
		if ( empty( $user ) || empty( $password ) )
		{
			return 0;
		}
		$arr = $this -> GetUrl( "http://mail.sina.com.cn/cgi-bin/login.cgi", "POST", "logintype=uid&u=" . urlencode( $user ) . "&psw=" . $password, $cookie, false, "http://mail.sina.com.cn/index.html" );

		if ( !preg_match( "/Location: (.*)\\/cgi\\/index\\.php\\?check_time=(.*)\n/", $arr['header'], $matches ) )
		{
			return 0;
		}
		$this -> host = $matches[1];
		return 1;
	}

	public function getAddressList( $user, $password )
	{
		if ( !$this -> checklogin( $user, $password, $cookie ) )
		{
			return 0;
		}
		$arr = $this -> GetUrl( $this -> host . "/classic/addr_member.php", "POST", "&act=list&sort_item=letter&sort_type=desc", $cookie );

		$content = $arr['content'];
		$res = $this -> _parsedata( $content );
		if ( !$res )
		{
			return 0; //没有联系人
		}
		return $res;
	}

	public function _parsedata( $content )
	{
		$ar = array();
		if ( !$content )
		{
			return 0;
		}
		$data = json_decode( $content );
		unset( $content );
		if ( !$data -> data )return 0;
		foreach ( $data -> data -> contact as $value )
		{
			if ( preg_match_all( "/[a-z0-9_\\.\\-]+@[a-z0-9\\-]+\\.[a-z]{2,6}/i", $value -> email, $matches ) )
			{
				$emails = array_unique( $matches[0] );
				unset( $matches );
				foreach ( $emails as $email )
				{
					$ar[$email] = $value -> name;
				}
			}
		}
		return $ar;
	}
}
