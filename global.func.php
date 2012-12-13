<?php

function Alert( $msg, $url = '' )
{
	header( "Content-type: text/html; charset=utf-8" );
	echo "<script language=javascript>";
	echo "window.alert('$msg');";

	if ( $url )
		echo "window.location = '{$url}';";
	else
		echo "history.go(-1);";

	echo "</script>";
	exit;
}

function GetFileExt( $fileName )
{
	return strtolower( end( explode( '.', $fileName ) ) );
}

function DateFormat( $time, $format = 'Y-m-d H:i' )
{
	if ( $format == 'auto' )
	{
		if ( date( 'Ymd' ) == date( 'Ymd', $time ) )
			return date( "H:i", $time );
		else
			return date( "y-m-d H:i", $time );
	}
	else
	{
		return date( $format, $time );
	}
}

function ArrayIndex( $list, $key )
{
	if ( !is_array( $list ) )
		return array();

	$new = array();
	foreach ( $list as $val )
	{
		$new[$val[$key]] = $val;
	}

	return $new;
}

function ArrayKey( $list )
{
	if ( !is_array( $list ) )
		return array();

	$argList = func_get_args();

	$value = array();
	foreach ( $list as $val )
	{
		for ( $i = 1, $l = count( $argList ); $i < $l; $i++ )
		{
			$value[$val[$argList[$i]]] = 1;
		}
	}

	return $value ? @array_keys( $value ) : array();
}

function IsEmail( $email )
{
	if ( Nothing( $email ) )
		return false;

	if ( !preg_match( '/^([a-zA-Z0-9_\.\-\+])+@([-0-9A-Z]+\.)+([0-9A-Z]){2,4}$/i', $email ) )
		return false;
	else
		return true;
}

function Nothing( $str )
{
	if ( strlen( trim( $str ) ) > 0 )
		return false;
	else
		return true;
}

function NoHtml( $str )
{
	$str = str_replace( array( '&nbsp;' ), array( ' ' ), $str );
	$str = htmlspecialchars( $str );
	
	return trim( $str );
}

function Redirect( $url = '' )
{
	if ( !$url )
		$url = $_SERVER['HTTP_REFERER'];

	if ( !headers_sent() )
	{
		header( "Location:{$url}" );
	}
	else
	{
		echo "<script>";
		echo "window.location='{$url}';";
		echo "</script>";
	}

	exit();
}

function Redirect_301( $url='' )
{
	ob_end_clean();
	header('HTTP/1.0 301 Moved Permanently');
	header( "Location:{$url}" );
	exit();
}

function ArrayBlock( $list, $perNum = 4, $fill = false )
{
	$new = array();
	$len = count( $list );
	$num = ceil( $len / $perNum );

	for ( $i = 1; $i <= $num; $i++  )
	{
		$new[] = array_slice( $list, ( $i - 1 ) * $perNum, $perNum, true );
	}

	return $new;
}

function ArrayMaxAndMin( $list, $key )
{
	$max = 0;
	$min = 0;
	if ( is_array( $list ) )
	{
		foreach ( $list as $val )
		{
			$tmp[] = $val[$key];
		}

		$max = max( $tmp );
		$min = min( $tmp );
	}

	return array( $max, $min );
}

function ArrayOrder( $list, $sort )
{
	if ( !is_array( $list ) || !$list )
		return array();

	$sort = array_values( $sort );
	$sort = array_flip( $sort );

	$sortLength = count( $sort );

	if ( count( $list ) != $sortLength )
	{
		foreach ( $list as $key => $val )
		{
			if ( !$sort[$key] )
			{
				$sort[$key] = $sortLength + 1;
				$sortLength++;
			}
		}
	}

	foreach ( $sort as $key => $val )
	{
		$sort[$key] = $list[$key];
	}

	return $sort;
}

function ArrayGroup( $list, $num )
{
	$new = array();
	
	for ( $i = 0; $i < $num; $i++ )
	{
		if ( !$new[$i] )
			$new[$i] = array();
	}

	if ( !$list )
		return $new;

	$i = 0;
	foreach ( $list as $val )
	{
		$new[$i%$num][] = $val;
		$i++;
	}

	return $new;
}

function ArrayJoin( $list, $string, $key = '' )
{
	if ( !is_array( $list ) )
		return '';

	$new = array();

	if ( $key )
	{
		foreach ( $list as $val )
		{
			$new[] = $val[$key];
		}
	}
	else
	{
		$new = $list;
	}

	return implode( $new, $string );
}

function ArrayRandValue($arr, $num=1)
{
	if( 1==$num )
	{
		$key = array_rand($arr, 1);
		return $arr[$key];
	}
	$keys = array_rand($arr, $num);
	$info = array();
	foreach($keys as $k)
	{
		$info[] = $arr[$k];
	}
	return $info;
}

function ArraySort( $list, $key, $type = 'desc' )
{
	if ( !is_array( $list ) || !$list )
		return array();

	foreach ( $list as $k => $v )
	{
		$edition[$k] = $v[$key];
	}

	if ( $type == 'desc' )
		array_multisort( $edition, SORT_DESC, $list );
	else
		array_multisort( $edition, SORT_ASC, $list );

	return $list;
}

function dump( $s ) {
	Helper_Debug::dump($s);
}

function IfDump( $s ) {
	if ($_GET['dump'] == 1) {
		Helper_Debug::dump($s);
	}
}

/**
 * 转换 HTML 特殊字符，等同于 htmlspecialchars()
 * 
 * @param string $text 
 * @return string 
 */
function h( $text )
{
	return htmlspecialchars( $text );
}

function de_h( $text )
{
	return htmlspecialchars_decode( $text );
}

function isLogin()
{
	if ( isset( $_SESSION['user']['user_id'] ) )
	{
		return $_SESSION['user']['user_id'];
	}
	return false;
}

function currentUserID()
{
	if ( isset( $_SESSION['user']['user_id'] ) )
	{
		return $_SESSION['user']['user_id'];
	}
	return 0;
}

function GetUserIp()
{
	if ( $_SERVER['HTTP_CLIENT_IP'] )
		return$_SERVER['HTTP_CLIENT_IP'];
	elseif ( $_SERVER['HTTP_X_FORWARDED_FOR'] )
		return $_SERVER['HTTP_X_FORWARDED_FOR'];
	else
		return $_SERVER['REMOTE_ADDR'];
}

function CleanUrl( $string )
{
	return preg_replace( '/(http:\/\/|www.)[a-z0-9\.\/\\\\?=\-_#&]+/is', '', $string );
}

function AutoUrl( $string )
{
	return preg_replace( '/(http:\/\/[a-z0-9\.\/\\\\?=\-_#&;\*]+)/is', '<a href="\\1" target="_blank">\\1</a>', $string );
}
function NumCn($num=0,$isweek=false)
{	if($num>6&&$isweek)return false;
	$isweek?$zcn="日":$zcn="零";
	$dis	= array(0=>$zcn,1=>"一",2=>"二",3=>"三",4=>"四",5=>"五",6=>"六",7=>"七",8=>"八",9=>"九");
	if($isweek)return $dis[$num];
	$dnumArr	= array(1=>"十",2=>"百",3=>"千");
}
function LimitTime($time,$now=0)
{
	$now?"":$now	= time();
	$diffTime	= $time-$now;
	if($diffTime<=0)return false;
	$day	= intval($diffTime/(24*60*60));
	$hour	= intval($diffTime/(60*60));
	$minute	= intval($diffTime/60);
	$seconds= $diffTime;
	$t_hour	= $hour-$day*24;
	$t_minute=$minute-$t_hour*60-$day*24*60;
	$tseconds= $diffTime-$t_minute*60-$t_hour*60*60-$day*24*60*60;
	return array("day"=>$day,"hour"=>$hour,"minute"=>$minute,"seconds"=>$seconds,"t_hour"=>$t_hour,"t_minute"=>$t_minute,"tseconds"=>$tseconds);
}

function GetRedirect( $toUrl, $type )
{
	if ( !preg_match( '/^http(s*):\/\//is', $toUrl ) )
	{
		if ( $toUrl[0] != '/' )
			$toUrl = '/' . $toUrl;

		$toUrl = "http://" . Core::GetConfig( 'Site_Domain' ) . "{$toUrl}";
	}

	$argNum = func_num_args();

	$config['type'] = "type={$type}";

	if ( $argNum > 2 )
	{
		$argList = func_get_args();

		for ( $i = 2; $i < $argNum; $i++ )
		{
			if ( $i % 2 == 0 )
				$config[$argList[$i]] = null;
			else
				$config[$argList[$i-1]] = $argList[$i-1] . '=' . $argList[$i];
		}
	}

	$config = implode( ';', array_filter( $config ) );

	return "http://a.fandongxi.com/__redirect/{$config}/{$toUrl}";
}


function Style( $var )
{
	$var = str_replace( ';', ',', $var );
	Common::addStyle( $var );
}

function Script( $var )
{
	$var = str_replace( ';', ',', $var );
	Common::addScript( $var );
}

function FooterScript( $var )
{
	$var = str_replace( ';', ',', $var );
	Common::addFooterScript( $var );
}

function FormatMoney( $money)
{
	return sprintf( '%.2f', $money );
}

//获取客户端设备的类型
function getDeviceType(){
	$user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);
	$type = 'Other';
	
	if(strpos($user_agent, 'iphone')){
		$type = 'Iphone';
	} elseif( strpos($user_agent, 'ipad') ) {
		$type = 'Ipad';
	} elseif(strpos($user_agent, 'android')) {
		$type = 'AndroidPhone';
	}

	return $type;
}
 