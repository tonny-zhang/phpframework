<?php
/**
 * 
 * @file class.163http.php
 * 获得yahoo邮箱通讯录列表
 * @author jvones<jvones@gmail.com> 
 * @date 2009-09-26
 */
include( "ContactBase.php" );
class yahooHttp extends ContactBase
{
	public function checklogin( $user, $password, &$cookie )
	{
		$arr = $this -> GetUrl( "http://mail.cn.yahoo.com", "GET", false, $cookie );

		$pattern = "/name=[\"|\\']*.challenge.[\"|\']*\\s+value=[\"|\'](.+?)[\"|\']/";
		if ( !preg_match_all( $pattern, $arr['content'], $result, PREG_PATTERN_ORDER ) )
		{
			return 0;
		}

		$challenge = trim( $result[1][0] );

		$request = "http://edit.bjs.yahoo.com/config/login";
		$postargs = ".intl=cn&.done=http%3A//cn.mail.yahoo.com/inset.html%3Frr%3D1052410730&.src=ym&.cnrid=ymhp_20000&.challenge=" . $challenge . "&login={$user}&passwd={$password}&.remember=y";
		$arr = $this -> GetUrl( $request, "POST", $postargs, $cookie );

		return 1;
	}

	public function getAddressList( $user, $password )
	{
		if ( !$this -> checklogin( $user, $password, $cookie ) )
		{
			return 0;
		}
		$url = "http://cn.address.mail.yahoo.com/";
		$tmpr = array();
		$tmpp = array();
		if ( !$this -> _getcontacts( $url, $tmpr, $tmpp, 1 , $cookie ) )
		{
			return 0;
		}
		$result = array();
		$result = $tmpr;
		while ( list( $k, $v ) = each( $tmpp ) )
		{
			if ( !( $v == 0 ) )
			{
				$tmpurl = $url . "?1&clp_c=0&clp_b=" . $v;
				$tmpr = $tempp = array();
				if ( $this -> _getcontacts( $tmpurl, $tmpr, $tempp , 0, $cookie ) )
				{
					$result = array_unique( array_merge( $result, $tmpr ) );
				}
			}
		}
		return $result;
	}

	public function _getcontacts( $url, &$result, &$presult, $gettotalpage = 0, &$cookie )
	{
		$arr = $this -> GetUrl( $url, "GET", false, $cookie );

		$contents = $arr['content'];
		$pattern = "/([\\w._-])+@([\\w])+([\\w.]+)/";
		$result = array();
		$tmpres = array();
		if ( !preg_match_all( $pattern, $contents, $tmpres, PREG_PATTERN_ORDER ) )
		{
			return 0;
		}
		$result = array_unique( $tmpres[0] );
		if ( $gettotalpage == 1 )
		{
			$presult = array();
			$tmpp = array();
			$pattern = "/&clp_b=(\\d)+/";
			preg_match_all( $pattern, $contents, $tmpp, PREG_PATTERN_ORDER );
			if ( !is_null( $tmpp[1] ) )
			{
				$presult = $tmpp[1];
				sort( array_unique( $presult ), SORT_NUMERIC );
			}
		}
		return 1;
	}
}

