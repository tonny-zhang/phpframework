<?php
/**
 * 
 * @file class.163http.php
 * 163邮箱登陆获取类
 * @author jvones<jvones@gmail.com> 
 * @date 2009-09-26
 */

include "ContactBase.php";
class http163 extends ContactBase
{
	/**
	 * 
	 * @desc : login in the 163 mail box
	 * @param string $username 
	 * @param string $password 
	 * @return int //the login status
	 */
	public function login( $username, $password, &$cookie )
	{
		$contents = $this -> GetUrl( "http://reg.163.com/logins.jsp", "POST", "username=" . $username . "&password=" . $password . "&type=1", $cookie );

		if ( strpos( $contents['content'], "安全退出" ) !== false )
		{
			return 0;
		}

		return 1;
	}

	/**
	 * 
	 * @desc : get address list from mail box
	 * @param string $username 
	 * @param string $password 
	 * @return array //the address list
	 */
	public function getAddressList( $username, $password )
	{
		if ( !$this -> login( $username, $password, $cookie ) )
		{
			return 0;
		}

		$header = $this -> _getheader( $username, $cookie );

		if ( !$header['sid'] )
		{
			return 0;
		}
		$str = "<?xml version=\"1.0\"?><object><array name=\"items\"><object><string name=\"func\">pab:searchContacts</string>" . "<object name=\"var\"><array name=\"order\"><object><string name=\"field\">FN</string><boolean name=\"ignoreCase\">true</boolean></object>" . "</array></object></object><object><string name=\"func\">user:getSignatures</string></object>" . "<object><string name=\"func\">pab:getAllGroups</string></object></array></object>";
		$arr = $this -> GetUrl( "http://" . $header['host'] . "/a/s?sid=" . $header['sid'] . "&func=global:sequential", "POST", $str, $cookie, true );
		preg_match_all( "/<string\s*name=\"EMAIL;PREF\">(.*)<\/string>/Umsi", $arr['content'], $mails );
		preg_match_all( "/<string\s*name=\"FN\">(.*)<\/string>/Umsi", $arr['content'], $names );
		$users = array();
		foreach( $names[1] as $k => $user )
		{ 
			// $user = iconv($user,'utf-8','gb2312');
			$users[] = array( "email" => $mails[1][$k], "nickname" => $user );
		}

		return $users;
	}

	/**
	 * get cookie
	 */
	public function _getheader( $username, &$cookie )
	{
		$rs = $this -> GetUrl( "http://entry.mail.163.com/coremail/fcg/ntesdoor2?lightweight=1&verifycookie=1&language=-1&style=-1&username=" . $username, "GET", false, $cookie );
		preg_match_all( '/Location:\s*(.*?)\r\n/i', $rs['header'], $regs );
		$refer = $regs[1][0];
		preg_match_all( '/http\:\/\/(.*?)\//i', $refer, $regs );
		$host = $regs[1][0];
		preg_match_all( "/sid=(.*)/i", $refer, $regs );
		$sid = $regs[1][0];
		return array( 'sid' => $sid, 'refer' => $refer, 'host' => $host );
	}
}

