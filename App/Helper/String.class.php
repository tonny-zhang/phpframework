<?php
class Helper_String
{
	/**
	 * 截字符
	 * 
	 * @return string 
	 */
	static function cut( $str, $len, $suffix = '...' )
	{
		$str = preg_replace( "/<(.*?)>/", "", $str );
		if ( strlen( $str ) <= $len ) return $str;
		$n = 0;
		$tempstr = '';
		for ( $i = 0; $i < $len; $i++ )
		{
			if ( ord( substr( $str, $n, 1 ) ) > 224 )
			{
				$tempstr .= substr( $str, $n, 3 );
				$n += 3;
				$i++; //把一个中文按两个英文的长度计算
			}elseif ( ord( substr( $str, $n, 1 ) ) > 192 )
			{
				$tempstr .= substr( $str, $n, 2 );
				$n += 2;
				$i++; //把一个中文按两个英文的长度计算
			}
			else
			{
				$tempstr .= substr( $str, $n, 1 );
				$n ++;
			}
		}
		return $tempstr . $suffix;
	}

	static function CleanHtml( $str )
	{
		$str = htmlspecialchars_decode( $str );
		$str = strip_tags( $str );
		$list = array( '&nbsp;', '>', '<' );
		foreach ( $list as $one )
		{
			$str = str_replace( $one, '', $str );
		}
		return $str;
	}

	static function CleanBanWord( $string )
	{
		$banWord = Core :: ImportData( 'BanWord' );

		return str_replace( $banWord, '***', $string );
	}
	/**
	 * 把一个数字转换成为数组。带小数点
例如：
558.00
	 * 转换以后为
array(
	 *   '0' =>  5,
	 *   '1' =>  5,
	 *   '2' =>  8,
	 *   '3' =>  '.',
	 *   '4' =>  0,
	 *   '5' =>  0,
	 * );
	 */
	function NumberToArray( $number, $point = true )
	{
		if ( is_string( $number ) )
		{
			$out = array();
			for( $i = 0; $i < strlen( $number ); $i++ )
			{
				if ( $number[$i] == '.' )
					$out[] = 'point';
				else
					$out[] = $number[$i];
			}

			return $out;
		}

		// 先把小数整数分开
		$num = intval( $number );
		$small = $number - $num;

		do
		{
			$na = self :: cutNumber( $num );
			$array[] = $na['0'];
			$num = $na['1'];
		}
		while ( $num != 0 );
		krsort( $array );

		if ( $point )
		{
			do
			{
				$na = self :: cutNumberSmall( $num );
				$arraysmall[] = $na['0'];
				$num = $na['1'];
			}
			while ( $num != 0 );
			if ( count( $arraysmall ) == 1 )
			{
				$arraysmall[] = 0;
			}
		}

		foreach ( $array as $one )
		{
			$out[] = $one;
		}

		if ( $point )
		{
			$out[] = 'point';

			foreach ( $arraysmall as $one )
			{
				$out[] = $one;
			}
		}

		return $out;
	}

	function cutNumber ( $num )
	{
		$newnum = intval( $num / 10 );
		return array( $num - $newnum * 10,
			$newnum, 
			);
	}

	function cutNumberSmall( $num )
	{
		$newnum = intval( $num * 10 );
		return array( $newnum,
			( $num - $newnum / 10 ) * 10, 
			);
	}
}
