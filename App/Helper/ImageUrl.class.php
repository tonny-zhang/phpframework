<?php
/**
 * 获取各类图片地址
 */

class Helper_ImageUrl
{
	static function GetProductImageUrl( $product_id, $name, $type, $size )
	{
		$domain = Core::getConfig( "Domain" );
		$width = $height = "";
		if ( is_array( $size ) )
		{
			list( $width, $height ) = $size;
		}
		else
		{
			$width = $height = $size;
		}

		$sub_dir = "";
		if ( $type == "list" )
		{
			$sub_dir = intval( $product_id / 2000 );
		}
		if ( $type == "detail" )
		{
			$sub_dir = intval( $product_id / 300 );
		}

		return "http://img.{$domain}/product/{$type}/{$width}_{$height}/{$sub_dir}/{$name}.jpg";
	}

	static function DelProductImageFile( $product_id, $name, $type )
	{
		$img_dir = Core::getConfig( 'Img_Upload_Dir' );
		if ( $type == 'list' )
		{
			$list_sub_dir = intval( $product_id / 2000 );
			$list_160_160_dir = $img_dir . '/product/list/160_160/' . $list_sub_dir;
			$list_120_120_dir = $img_dir . '/product/list/120_120/' . $list_sub_dir;
			$list_80_80_dir = $img_dir . '/product/list/80_80/' . $list_sub_dir;
			$original_dir = $img_dir . '/product/list/original/' . $list_sub_dir;

			$original_image = $original_dir . "/" . $name . ".jpg";
			$list_160_160_image = $list_160_160_dir . "/" . $name . ".jpg";
			$list_120_120_image = $list_120_120_dir . "/" . $name . ".jpg";
			$list_80_80_image = $list_80_80_dir . "/" . $name . ".jpg";

			if ( file_exists( $original_image ) )
			{
				unlink( $original_image );
			}
			if ( file_exists( $list_160_160_image ) )
			{
				unlink( $list_160_160_image );
			}
			if ( file_exists( $list_120_120_image ) )
			{
				unlink( $list_120_120_image );
			}
			if ( file_exists( $list_80_80_image ) )
			{
				unlink( $list_80_80_image );
			}
		}

		if ( $type == 'detail' )
		{
			$detail_sub_dir = intval( $product_id / 300 );
			$detail_350_350_dir = $img_dir . '/product/detail/350_350/' . $detail_sub_dir;
			$detail_40_40_dir = $img_dir . '/product/detail/40_40/' . $detail_sub_dir;
			$original_dir = $img_dir . '/product/detail/original/' . $list_sub_dir;

			$original_image = $original_dir . "/" . $name . ".jpg";
			$detail_350_350_image = $detail_350_350_dir . "/" . $name . ".jpg";
			$detail_40_40_image = $detail_40_40_dir . "/" . $name . ".jpg";

			if ( file_exists( $original_image ) )
			{
				unlink( $original_image );
			}
			if ( file_exists( $detail_350_350_image ) )
			{
				unlink( $detail_350_350_image );
			}
			if ( file_exists( $detail_40_40_image ) )
			{
				unlink( $detail_40_40_image );
			}
		}
	}

	static function GetCMSImageUrl( $name, $add_time,$size=0 )
	{
		$domain = Core::getConfig( "Domain" );
		$sub_dir = date( 'ymd', $add_time );
		$sizeStr = '';
		if(is_array($size)){
			list($width,$height)	= $size;
			
			$sizeStr	= "-{$width}-{$height}";
		}
		return "http://img.{$domain}/cms/{$sub_dir}/{$name}{$sizeStr}.jpg";
	}

	static function DelCMSImageUrl( $name, $add_time )
	{
		$img_dir = Core::getConfig( 'Img_Upload_Dir' );
		$sub_dir = date( 'ymd', $add_time );
		$dest_dir = $img_dir . '/cms/' . $sub_dir;

		$destImage = $dest_dir . "/" . $name . ".jpg";
		if ( file_exists( $destImage ) )
		{
			unlink( $destImage );
		}
	}

	static function GetBrandIcon( $name, $size )
	{
		$domain = Core::getConfig( "Domain" );
		$width = $height = '';
		if ( is_array( $size ) )
		{
			list( $width, $height ) = $size;
		}
		else
		{
			$width = $height = $size;
		}
		return "http://img.{$domain}/icon/brand/{$width}_{$height}/{$name}";
	}

	static function GetShopIcon( $name, $size )
	{
		$domain = Core::getConfig( "Domain" );
		$width = $height = '';
		if ( is_array( $size ) )
		{
			list( $width, $height ) = $size;
		}
		else
		{
			$width = $height = $size;
		}
		return "http://img.{$domain}/icon/shop/{$width}_{$height}/{$name}";
	}

	static function GetUserIcon( $user, $size )
	{
		$domain = Core::getConfig( "Domain" );
		if ( is_array( $user ) )
		{
			$sub_dir = intval( $user['user_id'] / 2000 );

			if ( $user['icon'] )
			{
				$name = $user['icon'];
				return "http://img.{$domain}/icon/user/{$size}_{$size}/{$sub_dir}/{$name}.jpg";
			}
			else
			{
				return "http://misc.{$domain}/img/default/user{$size}.jpg";
			}
		}
		else
		{
			$sub_dir = intval( $user / 2000 );
			$usericon_id = "usericon_{$user}";
			$cache = FDX_Cache::getInstance()->getCache();
			$cacheName = $cache->get( $usericon_id );
			if ( $cacheName == "default" )
			{
				return "http://misc.{$domain}/img/default/user{$size}.jpg";
			}

			if ( $cacheName != '' )
			{
				return "http://img.{$domain}/icon/user/{$size}_{$size}/{$sub_dir}/{$cacheName}.jpg";
			}

			$db = FDX_Model::getInstance()->getDb();
			$sql = "SELECT icon FROM user WHERE user_id='{$user}'";
			$name = $db->getOne( $sql );
			if ( $name )
			{
				$cache->set( $usericon_id, $name );
				return "http://img.{$domain}/icon/user/{$size}_{$size}/{$sub_dir}/{$name}.jpg";
			}
			else
			{
				$cache->set( $usericon_id, "default" );
				return "http://misc.{$domain}/img/default/user{$size}.jpg";
			}
		}
	}

	static function GetCMSIcon( $name, $add_time )
	{
		$domain = Core::getConfig( "Domain" );
		$sub_dir = date( 'ymd', $add_time );
		return "http://img.{$domain}/icon/cms/{$sub_dir}/{$name}.jpg";
	}

	static function DelCMSIcon( $name, $add_time )
	{
		$img_dir = Core::getConfig( 'Img_Upload_Dir' );
		$sub_dir = date( 'ymd', $add_time );
		$dest_dir = $img_dir . '/icon/cms/' . $sub_dir;

		$dest_icon = $dest_dir . "/" . $name . ".jpg";
		if ( file_exists( $dest_icon ) )
		{
			unlink( $dest_icon );
		}
	}

	static function GetShowImageUrl( $name, $type, $time )
	{
		$domain = Core::getConfig( "Domain" );
		$sub_dir = date( 'ymd', $time );
		return "http://img.{$domain}/show/{$type}/{$sub_dir}/{$name}.jpg";
	}

	static function DelShowImageFile( $name, $time )
	{
		$img_dir = Core::getConfig( 'Img_Upload_Dir' );
		$sub_dir = date( 'ymd', $time );

		$dest_s1_dir = $img_dir . '/show/s1/' . $sub_dir;
		$dest_s2_dir = $img_dir . '/show/s2/' . $sub_dir;
		$dest_s3_dir = $img_dir . '/show/s3/' . $sub_dir;
		$dest_s4_dir = $img_dir . '/show/s4/' . $sub_dir;
		$dest_s5_dir = $img_dir . '/show/s5/' . $sub_dir;

		$dest_s1_image = $dest_s1_dir . "/" . $name . ".jpg";
		$dest_s2_image = $dest_s2_dir . "/" . $name . ".jpg";
		$dest_s3_image = $dest_s3_dir . "/" . $name . ".jpg";
		$dest_s4_image = $dest_s4_dir . "/" . $name . ".jpg";
		$dest_s5_image = $dest_s5_dir . "/" . $name . ".jpg";

		if ( file_exists( $dest_s1_image ) )
		{
			unlink( $dest_s1_image );
		}
		if ( file_exists( $dest_s2_image ) )
		{
			unlink( $dest_s2_image );
		}
		if ( file_exists( $dest_s3_image ) )
		{
			unlink( $dest_s3_image );
		}
		if ( file_exists( $dest_s4_image ) )
		{
			unlink( $dest_s4_image );
		}
		if ( file_exists( $dest_s5_image ) )
		{
			unlink( $dest_s5_image );
		}
	}
}
