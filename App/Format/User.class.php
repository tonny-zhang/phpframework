<?php

class Format_User
{
	function Format( $userInfo )
	{
		if ( !$userInfo )
			return $userInfo;
		
		//认证用户标识 start
		if(!isset($userInfo['is_auth'])){
			$userAuth = Core::ImportMl('User_Auth');
			$userInfo['is_auth'] = $userAuth->isAuthUser($userInfo['user_id']);
			$authUser = $userAuth->getAuthUserByUserId($userInfo['user_id']);
			$userInfo['description'] = $authUser['description'];
		}
		//认证用户标识 end

		$userInfo['link'] = '/user/' . $userInfo['user_id'];
		$userInfo['url'] = '/user/' . $userInfo['user_id'];
		$userInfo['icon_50'] = Helper_ImageUrl::GetUserIcon( $userInfo, 50 );
		$userInfo['icon_150'] = Helper_ImageUrl::GetUserIcon( $userInfo, 150 );
		$userInfo['icon_35'] = Format_Image::Format( $userInfo['icon_50'], '35', '35', 'c' );

		if ( $userInfo['account_type'] == 'shop' )
		{
			$objShop = Core::ImportMl( 'Shop' );
			$shopInfo = $objShop->getBaseShopInfoByUserId( $userInfo['user_id'] );

			$userInfo['icon_150'] = $shopInfo['image']['150'];
			$userInfo['icon_50'] = Format_Image::Format( $shopInfo['image']['150'], '50', '50', 'c' );
			$userInfo['icon_35'] = Format_Image::Format( $shopInfo['image']['150'], '35', '35', 'c' );
		}

		$objUserProfile = Core::ImportMl('User_Profile');
		$profileInfo = $objUserProfile->getProfileByUid( $userInfo['user_id'], false );
		if ( $profileInfo['city'] || $profileInfo['province'] ) {
			$objTool = Core::ImportMl('Tool');
			$userInfo['area']['city'] = $objTool->getCityName( $profileInfo['city'] );
			$userInfo['area']['province'] = $objTool->getProvinceName( $profileInfo['province'] );
		}

		if ( $profileInfo['sex'] == 1 )
		{
			$userInfo['sex_name'] = '他';
			$userInfo['sex_alias'] = '男生';
		}
		else
		{
			$userInfo['sex_name'] = '她';
			$userInfo['sex_alias'] = '女生';
		}

		$userInfo['t_blog'] = $profileInfo['t_blog'] ?: ( $userInfo['sina_uid'] ? 'http://weibo.com/'. $userInfo['sina_uid']: ( $userInfo['qq_uid'] ? 'http://t.qq.com/'.$userInfo['qq_uid']:'' ) );

		if ( $profileInfo['birthday'] )
		{
			$birthday = $profileInfo['birthday'];
			$birthday = date( 'Y-m-d', $birthday );

			list( $year, $month, $day ) = explode( '-', $birthday );

			if ( $year < 2000 )
				$userInfo['year'] = substr( $year, 2, 1 ) . "0";
			else
				$userInfo['year'] = "2000";

			$xzDict = array( '摩羯', '水瓶', '双鱼', '白羊', '金牛', '双子', '巨蟹', '狮子', '处女', '天秤', '天蝎', '射手' );
			$zone = array( 1222, 122, 222, 321, 421, 522, 622, 722, 822, 922, 1022, 1122, 1222 );
			$xzIndex = 0;
			if ( ( 100 * $month + $day ) >= $zone[0] || ( 100 * $month + $day ) < $zone[1] )
			{
				$xzIndex = 0;
			}
			else
			{
				for( $xzIndex = 1;$xzIndex < 12;$xzIndex++ )
				{
					if ( ( 100 * $month + $day ) >= $zone[$xzIndex] && ( 100 * $month + $day ) < $zone[$xzIndex + 1] ) break;
				}
			}

			$userInfo['xz'] = $xzDict[$xzIndex] . '座';
		}

		return $userInfo;
	}

	function FormatProfile( $profile )
	{
		if ( isset( $profile['city'] ) )
		{
			$objTool = Core::ImportMl('Tool');
			$profile['prov_name'] = $objTool->getProvinceName( $profile['province'] );
		}

		if ( $profile['sex'] )
		{
			$sex = $profile['sex'];
			if ( $sex == "1" )
				$profile['sex_name'] = "男生";
			if ( $sex == "2" )
				$profile['sex_name'] = "女生";
		}

		if ( isset( $profile['birthday'] ) )
		{
			$birthday = $profile['birthday'];
			$birthday = date( 'Y-m-d', $birthday );

			list( $year, $month, $day ) = explode( '-', $birthday );
			if ( $year < 2000 )
			{
				$profile['year'] = substr( $year, 2, 1 ) . "0";
			}
			else
			{
				$profile['year'] = "2000";
			}

			$xzDict = array( '摩羯', '水瓶', '双鱼', '白羊', '金牛', '双子', '巨蟹', '狮子', '处女', '天秤', '天蝎', '射手' );
			$zone = array( 1222, 122, 222, 321, 421, 522, 622, 722, 822, 922, 1022, 1122, 1222 );
			$xzIndex = 0;
			if ( ( 100 * $month + $day ) >= $zone[0] || ( 100 * $month + $day ) < $zone[1] )
			{
				$xzIndex = 0;
			}
			else
			{
				for( $xzIndex = 1;$xzIndex < 12;$xzIndex++ )
				{
					if ( ( 100 * $month + $day ) >= $zone[$xzIndex] && ( 100 * $month + $day ) < $zone[$xzIndex + 1] ) break;
				}
			}
			$profile['xz'] = $xzDict[$xzIndex] . '座';
		}
		return $profile;
	}
	
	static function FormatMobileByUserId( $userId )
	{
		$objUser = Core::ImportMl('User');
		$user = $objUser->getUserInfo( $userId );
		
		if( !$user )
		{
			return FALSE;
		}
		
		$info = array();
		$info['UserId'] = $userId;
		$info['Nick']   = $user['nickname'];
		$info['BigAvatar']  = $user['icon_150'];
		$info['MiniAvatar'] = $user['icon_35'];
		
		$objIphoneRemind = Core::ImportMl( "Iphone_Remind" );
		$info['LikeNumber']  = intval($objIphoneRemind->getValidRemindTotal($userId));
		
		return $info;
	}
	static function FormatMobileListByUserId( $uidArray )
	{
		$info = array();
		foreach( $uidArray as $uid )
		{
			$tmp = self::FormatMobileByUserId( $uid );
			
			if( $tmp )
			{
				$info[] = $tmp;
			}
		}
		return $info;
	}
}
