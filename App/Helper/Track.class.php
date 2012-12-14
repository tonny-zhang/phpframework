<?php
class Helper_Track
{
    public static function run() {
        $time = time();
        $domain = Core::getConfig("Domain");
        if(!$_COOKIE['fan_v'] ) {
            $fan_v = md5(uniqid(rand())); //唯一浏览器标识
            setcookie("fan_v", $fan_v, $time+86400*365*30, "/", ".{$domain}");
        }
		
		if( !$_COOKIE['fan_fvt'] ) {
			//first visit time
			setcookie("fan_fvt", $time, $time+86400*365*30, "/", ".{$domain}");
		}

        $user_id = currentUserID();
        if($user_id) {
			setcookie("fan_u", $user_id, $time+86400*365*30, "/", ".{$domain}");

            if(isset($_SESSION['user']['username'])) {
                $fan_e = $_SESSION['user']['username'];
                if(!isset($_COOKIE['fan_e'])) {
                    setcookie("fan_e", $fan_e, $time+86400*365*30, "/", ".{$domain}");
                } else {
                    if($_COOKIE['fan_e'] != $fan_e) {//更换用户登录后的情况
						setcookie("fan_e", $fan_e, $time+86400*365*30, "/", ".{$domain}");
                    }
                }
            }

            if(!isset($_COOKIE['fan_t'])) { //登录状态
                setcookie("fan_t", "1", $time+86400, "/", ".{$domain}");
            }
        } else {
			setcookie('fan_u','');
		}

		if ( $_COOKIE['fan_cpc'] ) {
			setcookie("fan_cpc2", $_COOKIE['fan_cpc'], $time+86400, "/", ".{$domain}");
		}
    }
	
	public static function trackImage() {
		$domain = Core::getConfig("Domain");
		$cur = trim($_SERVER["REQUEST_URI"]);
		$prev = trim($_SERVER["HTTP_REFERER"]);
		$fan_f = isset($_COOKIE['fan_f']) ? $_COOKIE['fan_f'] : '';
		
		$fan_lvt = isset($_COOKIE['fan_lvt']) ? $_COOKIE['fan_lvt'] : ''; //上次visit时间
		$fan_lpt = isset($_COOKIE['fan_lpt']) ? $_COOKIE['fan_lpt'] : 0; //上次pageview时间
		$fan_fvt = isset($_COOKIE['fan_fvt']) ? $_COOKIE['fan_fvt'] : 0; //首次pageview时间
		$fan_n = ''; //是否是新visit
		$fan_cpt = time(); //当前pageview时间
		if($fan_cpt-$fan_lpt>1800) {
			$fan_n = '1';
			setcookie("fan_lvt", $fan_cpt, $fan_cpt+86400*365*2, "/", ".{$domain}"); //更新上次visit时间
		}
		setcookie("fan_lpt", $fan_cpt, $fan_cpt+86400*365*2, "/", ".{$domain}"); //更新上次pageview时间
		
		$uid = currentUserID()?:0;
		if( $uid ) {
			setcookie("fan_u", $uid, $fan_cpt+86400*365*30, "/", ".{$domain}");
			
			$fan_lvl = $_COOKIE['fan_lvl'] ? : '';
			if( !$fan_lvl ) {
				$objHonour = Core::ImportMl('Honour');
				$rt = $objHonour->valid($uid);
				$fan_lvl = ArrayKey( $rt, 'honour_id' );
				$fan_lvl = implode('', $fan_lvl);
				setcookie("fan_lvl", $fan_lvl, $fan_cpt+86400, "/", ".{$domain}");
			}
		} else {
			setcookie('fan_u','');
			setcookie('fan_lvl','');
		}
		$fan_sid = @session_id();
		if( !$fan_sid ) {
			if( $_COOKIE['fan_s'] ) {
				$fan_sid = $_COOKIE['fan_s'];
			} else {
				$fan_sid = md5(uniqid(rand()));
				setcookie("fan_s", $fan_sid, 0, "/", ".{$domain}");
			}
		}
		
		$ext=self::$extra===NULL?'':('&fan_ext='.urlencode( self::$extra ));
		
		$node = '<img src="http://a.fandongxi.com/__fan.gif?v=' . md5(uniqid(rand()))
			.'&cur='. urlencode($cur)
			.'&prev='. urlencode($prev)
			.'&fan_f='. urlencode($fan_f)
			.'&fan_cpt='.urlencode($fan_cpt)
			.'&fan_lvt='.urlencode($fan_lvt)
			.'&fan_n='.urlencode($fan_n)
			.($fan_sid?'&fan_sid='.$fan_sid:'')
			.($fan_lvl?'&fan_lvl='.urlencode($fan_lvl):'')
			.($fan_fvt?'&fan_fvt='.urlencode($fan_fvt):'')
			.($fan_lpt?'&fan_lpt='.urlencode($fan_lpt):'')
			.$ext
			.'" style="display:none;" />';
		return $node;
	}
	
	private static $extra = NULL;
	
	/**
	 * 设置额外的统计信息，由module调用
	 * 当用户搜索时，返回搜索是否有结果。
	 * 当用户在访问哇塞、分享宝贝时，返回谁的哇塞、谁的分享宝贝。或其他。
	 **/
	public static function setExtra( $info ) {
		self::$extra = $info;
	}
}
