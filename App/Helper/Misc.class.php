<?php
class Helper_Misc
{
	static function fan_encrypt( $txt )
	{
		$key = "nbscke476vasgda72a5suxv3kvez9adcgavekc59888";
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
		$ikey = "-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
		$nh1 = rand( 0, 64 );
		$nh2 = rand( 0, 64 );
		$nh3 = rand( 0, 64 );
		$ch1 = $chars{$nh1};
		$ch2 = $chars{$nh2};
		$ch3 = $chars{$nh3};
		$nhnum = $nh1 + $nh2 + $nh3;
		$knum = 0;
		$i = 0;
		while ( isset( $key{$i} ) ) $knum += ord( $key{$i++} );
		$mdKey = substr( md5( md5( md5( $key . $ch1 ) . $ch2 . $ikey ) . $ch3 ), $nhnum % 8, $knum % 8 + 16 );
		$txt = base64_encode( $txt );
		$txt = str_replace( array( '+', '/', '=' ), array( '-', '_', '.' ), $txt );
		$tmp = '';
		$j = 0;
		$k = 0;
		$tlen = strlen( $txt );
		$klen = strlen( $mdKey );
		for ( $i = 0; $i < $tlen; $i++ )
		{
			$k = $k == $klen ? 0 : $k;
			$j = ( $nhnum + strpos( $chars, $txt{$i} ) + ord( $mdKey{$k++} ) ) % 64;
			$tmp .= $chars{$j};
		}
		$tmplen = strlen( $tmp );
		$tmp = substr_replace( $tmp, $ch3, $nh2 % ++$tmplen, 0 );
		$tmp = substr_replace( $tmp, $ch2, $nh1 % ++$tmplen, 0 );
		$tmp = substr_replace( $tmp, $ch1, $knum % ++$tmplen, 0 );
		return $tmp;
	}

	static function fan_decrypt( $txt )
	{
		$key = "nbscke476vasgda72a5suxv3kvez9adcgavekc59888";
		$chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.";
		$ikey = "-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm";
		$knum = 0;
		$i = 0;
		$tlen = strlen( $txt );
		while ( isset( $key{$i} ) ) $knum += ord( $key{$i++} );
		$ch1 = $txt{$knum % $tlen};
		$nh1 = strpos( $chars, $ch1 );
		$txt = substr_replace( $txt, '', $knum % $tlen--, 1 );
		$ch2 = $txt{$nh1 % $tlen};
		$nh2 = strpos( $chars, $ch2 );
		$txt = substr_replace( $txt, '', $nh1 % $tlen--, 1 );
		$ch3 = $txt{$nh2 % $tlen};
		$nh3 = strpos( $chars, $ch3 );
		$txt = substr_replace( $txt, '', $nh2 % $tlen--, 1 );
		$nhnum = $nh1 + $nh2 + $nh3;
		$mdKey = substr( md5( md5( md5( $key . $ch1 ) . $ch2 . $ikey ) . $ch3 ), $nhnum % 8, $knum % 8 + 16 );
		$tmp = '';
		$j = 0;
		$k = 0;
		$tlen = strlen( $txt );
		$klen = strlen( $mdKey );
		for ( $i = 0; $i < $tlen; $i++ )
		{
			$k = $k == $klen ? 0 : $k;
			$j = strpos( $chars, $txt{$i} ) - $nhnum - ord( $mdKey{$k++} );
			while ( $j < 0 ) $j += 64;
			$tmp .= $chars{$j};
		}
		$tmp = str_replace( array( '-', '_', '.' ), array( '+', '/', '=' ), $tmp );
		return trim( base64_decode( $tmp ) );
	}

	static function fanViewUrl( $module, $array )
	{
		$array = self::arrayRemoveEmpty( $array );
		$u = array();
		foreach( $array as $key => $value )
		{
			$u[] = $key . '=' . $value;
		}
		if ( count( $u ) > 0 )
		{
			$url = implode( '&', $u );
			return $module . '?' . $url;
		}
		return $module;
	}

	static function Alert( $msg, $url = '' )
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
	static function AllDateFormat( $array, $format = 'Y-m-d H:i' )
	{
		if ( is_Array( $array ) )
		{
			foreach ( $array as $key => $value )
			{
				$array[$key]['time'] = self :: DateFormat( $value['created'], $format );
			}
			return $array;
		}
	}
	static function DateFormat( $time, $format = 'Y-m-d H:i' )
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
		return htmlspecialchars( trim( $str ) );
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

	function UnifySeparator( $path )
	{
		preg_match( '/((?:https?:\/\/|ftp:\/\/|mms:\/\/|))(.+?)$/', $path, $result );

		$pathPart = $result[2];
		$pathPart = str_replace( '\\', '/', $pathPart );
		$pathPart = preg_replace( '/(\/+)/', '/', $pathPart );

		return $result[1] . $pathPart;
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

	function getOutTime( $time )
	{
		$now = time();
		$timecount = $now - $time ;

		if ( $timecount < 60 )
			$time = "刚刚更新";
		elseif ( $timecount < 3600 )
			$time = floor( $timecount / 60 ) . "分钟前";
		elseif ( $timecount < 86400 )
			$time = floor( $timecount / 3600 ) . "小时前";
		elseif ( $timecount < 259200 )
			$time = floor( $timecount / 86400 ) . "天前";
		else
			$time = date( 'm月d日', $time );

		return $time;
	}
	
	/**
	 * 根据指定的键对数组排序
	 *
	 * 用法：
	 * @code php
	 * $rows = array(
	 *            array('id' => 1, 'value' => '1-1', 'parent' => 1),
	 *            array('id' => 2, 'value' => '2-1', 'parent' => 1),
	 *            array('id' => 3, 'value' => '3-1', 'parent' => 1),
	 *            array('id' => 4, 'value' => '4-1', 'parent' => 2),
	 *            array('id' => 5, 'value' => '5-1', 'parent' => 2),
	 *            array('id' => 6, 'value' => '6-1', 'parent' => 3),
	 * );
	 *
	 * $rows = Helper_Misc::arraySortByCol($rows, 'id', SORT_DESC);
	 * dump($rows);
	 * // 输出结果为：
	 * // array(
	 * //          array('id' => 6, 'value' => '6-1', 'parent' => 3),
	 * //          array('id' => 5, 'value' => '5-1', 'parent' => 2),
	 * //          array('id' => 4, 'value' => '4-1', 'parent' => 2),
	 * //          array('id' => 3, 'value' => '3-1', 'parent' => 1),
	 * //          array('id' => 2, 'value' => '2-1', 'parent' => 1),
	 * //          array('id' => 1, 'value' => '1-1', 'parent' => 1),
	 * // )
	 * @endcode
	 *
	 * @param array $array 要排序的数组
	 * @param string $keyname 排序的键
	 * @param int $dir 排序方向
	 *
	 * @return array 排序后的数组
	 */
	static function arraySortByCol($array, $keyname, $dir = SORT_ASC) {
		return self::arraySortByMultiCols($array, array($keyname => $dir));
	}

	/**
	 * 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
	 *
	 * 用法：
	 * @code php
	 * $rows = Helper_Misc::arraySortByMultiCols($rows, array(
	 *            'parent' => SORT_ASC,
	 *            'name' => SORT_DESC,
	 * ));
	 * @endcode
	 *
	 * @param array $rowset 要排序的数组
	 * @param array $args 排序的键
	 *
	 * @return array 排序后的数组
	 */
	static function arraySortByMultiCols($rowset, $args) {
		$sortArray = array();
		$sortRule = '';
		foreach ($args as $sortField => $sortDir) {
			foreach ($rowset as $offset => $row) {
				$sortArray[$sortField][$offset] = $row[$sortField];
			}
			$sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
		}
		if (empty($sortArray) || empty($sortRule)) {
			return $rowset;
		}
		eval('array_multisort(' . $sortRule . '$rowset);');
		return $rowset;
	}

	/**
	 * 从数组中删除空白的元素（包括只有空白字符的元素）
	 *
	 * 用法：
	 * @code php
	 * $arr = array('', 'test', '   ');
	 * Helper_Misc::arrayRemoveEmpty($arr);
	 *
	 * dump($arr);
	 *   // 输出结果中将只有 'test'
	 * @endcode
	 *
	 * @param array $arr 要处理的数组
	 * @param boolean $trim 是否对数组元素调用 trim 函数
	 */
	static function arrayRemoveEmpty(&$arr, $trim = true) {
		foreach ($arr as $key => $value) {
			if (is_array($value)) {
				self::arrayRemoveEmpty($arr[$key]);
			} else {
				$value = trim($value);
				if ($value == '') {
					unset($arr[$key]);
				} elseif ($trim) {
					$arr[$key] = $value;
				}
			}
		}
		return $arr;
	}

	/**
	 * 从2维数组中按照某个key分组
	 *
	 * 用法：
	 * @code php
	 * $array = array(
	 *
	 *     '0' =>  array(
	 *                 'key'   =>  1,
	 *                 'value' =>  1,
	 *             ),
	 *     '1' =>  array(
	 *                 'key'   =>  2,
	 *                 'value' =>  1,
	 *             ),
	 *     '2' =>  array(
	 *                 'key'   =>  1,
	 *                 'value' =>  1,
	 *             ),
	 *
	 * );
	 * Helper_Misc::arrayMakeGroup($array,'key');
	 *
	 * dump($arr);
	 *
	 *
	 * @endcode
	 *
	 * @param array   $array 要处理的数组
	 * @param string  $key  是否对数组分组调用 KEY
	 */
	static function arrayMakeGroup($array, $key) {
		$newarray = array();
		foreach ($array as $value) {
			$newarray[strtoupper($value[$key])][] = $value;
		}
		return $newarray;
	}

	
	/*
	* 获取Location: 重定向后url
	*/
	function getLocationUrl($url, $referer='', $timeout = 10) {
		$redirect_url = false;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Accept: */*',
		'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)',
		'Connection: Keep-Alive'));
		if ($referer) {
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		}

		$content = curl_exec($ch);
		if(!curl_errno($ch)) {
			$redirect_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		}

		return $redirect_url;
	}
}
