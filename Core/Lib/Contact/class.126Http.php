<?php
/**
 * 
 * @file class.126http.php
 * 获得126邮箱通讯录列表
 * @author jvones<jvones@gmail.com> http://www.jvones.com/blog 
 * @date 2009-09-26
 */

include "ContactBase.php";
class http126 extends ContactBase
{
	public function __construct()
	{
	}
	private function login( $username, $password, &$cookie )
	{ 
		// 第一步：初步登陆
		$arr = $this -> GetUrl( "https://reg.163.com/logins.jsp?type=1&product=mail126&url=http://entry.mail.126.com/cgi/ntesdoor?hid%3D10010102%26lightweight%3D1%26verifycookie%3D1%26language%3D0%26style%3D-1", "POST", "username=" . $username . "&password=" . $password, $cookie ); 
		// 获取redirect_url跳转地址，可以从126result.txt中查看，通过正则在$str返回流中匹配该地址
		preg_match( "/replace\(\"(.*?)\"\)\;/", $arr['content'], $mtitle );
		$_url1 = $mtitle[1]; 
		// file_put_contents('./126resulturl.txt', $redirect_url);
		// 第二步：再次跳转到到上面$_url1
		$arr = $this -> GetUrl( $_url1, "GET", false, $cookie );

		if ( strpos( $arr['content'], "安全退出" ) !== false )
		{
			return 0;
		}
		return 1;
	}

	/**
	 * 获取邮箱通讯录-地址
	 * 
	 * @param  $user 
	 * @param  $password 
	 * @param  $result 
	 * @return array 
	 */
	public function getAddressList( $username, $password )
	{
		if ( !$this -> login( $username . '@126.com', $password, $cookie ) )
		{
			return 0;
		}

		$header = $this -> _getheader( $username . '@126.com', $cookie );

		if ( !$header['sid'] )
		{
			return 0;
		} 
		// 开始进入模拟抓取
		$str = "<?xml version=\"1.0\"?><object><array name=\"items\"><object><string name=\"func\">pab:searchContacts</string><object name=\"var\"><array name=\"order\"><object><string name=\"field\">FN</string><boolean name=\"ignoreCase\">true</boolean></object></array></object></object><object><string name=\"func\">user:getSignatures</string></object><object><string name=\"func\">pab:getAllGroups</string></object></array></object>";
		$arr = $this -> GetUrl( "http://" . $header['host'] . "/a/s?sid=" . $header['sid'] . "&func=global:sequential", "POST", $str, $cookie, true ); 
		// get mail list from the page information username && emailaddress
		preg_match_all( "/<string\s*name=\"EMAIL;PREF\">(.*)<\/string>/Umsi", $arr['content'], $mails );
		preg_match_all( "/<string\s*name=\"FN\">(.*)<\/string>/Umsi", $arr['content'], $names );
		$users = array();
		foreach( $names[1] as $k => $user )
		{ 
			// $user = iconv($user,'utf-8','gb2312');
			$users[] = array( "nickname" => $names[1][$k], "email" => $mails[1][$k] );
		}

		return $users;
	}

	/**
	 * Get Header info
	 */
	private function _getheader( $username, &$cookie )
	{
		$arr = $this -> GetUrl( "http://entry.mail.126.com/cgi/ntesdoor?hid=10010102&lightweight=1&verifycookie=1&language=0&style=-1&username=" . $username, "GET", false, $cookie );
		preg_match_all( '/Location:\s*(.*?)\r\n/i', $arr['header'], $regs );
		$refer = $regs[1][0];
		preg_match_all( '/http\:\/\/(.*?)\//i', $refer, $regs );
		$host = $regs[1][0];
		preg_match_all( "/sid=(.*)/i", $refer, $regs );
		$sid = $regs[1][0];
		return array( 'sid' => $sid, 'refer' => $refer, 'host' => $host );
	}
}