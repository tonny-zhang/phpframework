<?php
class Common
{
	static function PageNotFound()
	{
		header( "HTTP/1.1 404 Not Found" );
		Common::PageOut( 'misc/404.html' );
		//$view = FDX_View :: getInstance() -> getView();
		//$view -> display( 'misc/404.php' );
		exit();
	}

	static function Error( $title = '', $content = '' )
	{
		$tpl['content'] = $content;
		$tpl['title'] = $title;

		$view = FDX_View :: getInstance() -> getView();
		$view -> display( 'misc/error.php', $tpl );
		exit();
	}

	static function Loading( $title = '', $url = '', $content = '', $delay = 15 , $html = 'misc/success.php')
	{
		$url = !$url ? $_SERVER['HTTP_REFERER'] : $url;

		$tpl['content'] = $content;
		$tpl['title'] = $title;
		$tpl['url'] = $url;

		header( "refresh:{$delay};url={$url}" );

		$view = FDX_View :: getInstance() -> getView();
		$view -> display( $html, $tpl );
		exit();
	}

	static function Success( $title, $url = '', $content = '', $linkList = array() )
	{
		$tpl['content'] = $content;
		$tpl['title'] = $title;
		$tpl['url'] = $url;
		$tpl['link_list'] = $linkList;

		$view = FDX_View :: getInstance() -> getView();
		$view -> display( 'misc/success.php', $tpl );
		exit();
	}

	static function GoToLogin()
	{
		if ( !currentUserID() )
		{
			Redirect( '/user/login.fan' );
			exit();
		}
	}

	static function IsLogin()
	{
		if ( !currentUserID() )
			return false;

		return true;
	}

	function PageArg( $onePage = 30, $page = false )
	{
		if ( $page === false )
			$page = ( int )$_GET['page'];

		if ( $page <= 0 )
			$page = 1;

		$offset = $onePage * ( $page - 1 );

		return array( $page, $offset, $onePage );
	}

	/**
	 * ******* 分页 *******
	 */
	function PageBar( $total, $onePage, $page, $base = '', $query = '', $offset = 5, $maxPageAllow = 0 )
	{
		if ( !$base )
		{
			if ( $_SERVER['HTTP_REQUEST_URI'] )
				list( $base, $query ) = explode( '?', $_SERVER['HTTP_REQUEST_URI'] );
			else
				list( $base, $query ) = explode( '?', $_SERVER['REQUEST_URI'] );
		}

		$totalPage = ceil( $total / $onePage );
		$linkArray = explode( "page=", $query );
		$linkArg = $linkArray[0];

		if ( $maxPageAllow && $totalPage >= $maxPageAllow )
			$totalPage = $maxPageAllow;

		if ( $linkArg == '' )
		{
			$url = $base . "?";
		}
		else
		{
			$linkArg = substr( $linkArg, -1 ) == "&" ? $linkArg : $linkArg . '&';
			$url = $base . '?' . $linkArg;
		}

		$url = preg_replace( '/&utm_medium=[0-9a-z_-]+/is', '', $url );
		$url = preg_replace( '/&utm_source=[0-9a-z_-]+/is', '', $url );
		$url = preg_replace( '/&utm_name=[0-9a-z_-]+/is', '', $url );

		$url = preg_replace( '/utm_medium=[0-9a-z_-]+/is', '', $url );
		$url = preg_replace( '/utm_source=[0-9a-z_-]+/is', '', $url );
		$url = preg_replace( '/utm_name=[0-9a-z_-]+/is', '', $url );

		$url = preg_replace( '/sto=[0-9]+/is', '', $url );
		$url = htmlspecialchars( $url );

		if ( !$totalPage )
			$totalPage = 1;

		if ( $totalPage == 1 )
			return '';

		if ( $page > $totalPage || !$page )
			$page = 1;

		$mid = floor( $offset / 2 );
		$last = $offset - 1;
		$minPage = ( $page - $mid ) < 1 ? 1 : $page - $mid;
		$maxPage = $minPage + $last;

		if ( $maxPage > $totalPage )
		{
			$maxPage = $totalPage;
			$minPage = $maxPage - $last;
			$minPage = $minPage < 1 ? 1 : $minPage;
		}

		// --- 上一页，下一页
		if ($page != 1) {
			$prePage = $page - 1;
			$prePageBar = "<a href='{$url}page={$prePage}' class='prev prev_active' title='上一页'></a>";
		} else {
			$prePageBar = "<a class='prev' title='上一页'></a>";
		}
		if ($page < $totalPage) {
			$nextPage = $page + 1;
			$nextPageBar = "<a href='{$url}page={$nextPage}' class='next next_active' title='下一页'></a>";
		} else {
			$nextPageBar = "<a class='next' title='下一页'></a>";		
		}
		// --- 第1页，最后1页
		if ($minPage != 1) {
			$firstPageBar = "<a href='{$url}page=1' class='first'><em>1</em></a>";
		} else {
			$firstPageBar = "";
		}
		if ($maxPage < $totalPage) {
			$lastPageBar = "<a href='{$url}page={$totalPage}' class='last'><em>尾页</em></a>";
		} else {
			$lastPageBar = "";	
		}
		// --- 跨度
		$numPageBar = '<span class="line"><span class="inner">';
		for ( $i = $minPage; $i <= $maxPage; $i++ ) {
			if ($i == $page) {
				$numPageBar .= "<span class='current'><strong>{$i}</strong></span>";
			} else {
				if ($page == 1) {
					$numPageBar .= "";
				}
				$numPageBar .= "<a href='{$url}page={$i}'><em>{$i}</em></a>";
				if ($page == $totalPage) {
					$numPageBar .= "";
				}
			}
		}
		$numPageBar .= '</span></span>';
		// --- 省略号
		$passFirstNumPageBar = $passLastNumPageBar = '';
		if ($minPage > 2) {
			$passFirstNumPageBar = "&nbsp;...&nbsp;";
		}
		if ($maxPage < $totalPage - 1) {
			$passLastNumPageBar = "&nbsp;...&nbsp;";
		}

		if($totalPage < 2){
			$tip = '';
		}
		else{
			if ( $page < $totalPage )
				$tip = '<a href="' . $url . 'page=' . $nextPage . '" class="tip"><img src="http://'. Core::GetConfig( 'Front_Resource_Site' ) .'/img2/bg_pager_tip.png" alt="下一页" /></a>';
			else
				$tip = '';
		}

		return '<div class="pager">' . $prePageBar . $firstPageBar . $passFirstNumPageBar . $numPageBar . $passLastNumPageBar . $lastPageBar . $nextPageBar . $tip . '</div>';
	}

	function Code2Html( $code )
	{
		$code = nl2br( $code );

		$pregTag = $pregValue = array();

		$pregTag['img'] = array( "/\[img\]([^\[]*)\[\/img\]/is" );
		$pregValue['img'] = array( "<p><img src=\"\\1\" border=\"0\" onload=\"attachimg(this, 'load');\"></p>" );

		foreach ( $pregTag as $key => $val )
		{
			$code = preg_replace( $val, $pregValue[$key], $code );
		}

		$code = preg_replace( "/(^(https?):\/\/)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+)/i", "<a href=\"\\1\\3\" target=\"_blank\">\\1\\3</a>", $code );
		$code = preg_replace( "/(?:[^\"'])((https?):\/\/)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+)/i", "<a href=\"\\1\\3\" target=\"_blank\">\\1\\3</a>", $code );

		return $code;
	}

	function ParseInnerUrl( $content )
	{
		$content = preg_replace( "/((https?):\/\/www\.fandongxi\.com)([a-z0-9\/\-_+=.~!%@?#%&;:$\\()|]+)/i", "<a href=\"\\1\\3\" target=\"_blank\">\\1\\3</a>", $content );
		return $content;
	}

	function ParseFace( $content )
	{
		$faceList = Core::ImportData( 'Face' );

		$find = array();
		$replace = array();

		foreach ( $faceList as $key => $val )
		{
			$find[] = $key;
			$replace[] = "<img src='http://misc.fandongxi.com/img/face/{$val}' class=\"face\"/>";
		}

		$content = str_replace( $find, $replace, $content );

		return $content;
	}

	function SetGlobal( $name, $value )
	{
		global $__GlobalVars;

		$__GlobalVars[$name] = $value;
	}

	function GetGlobal( $name )
	{
		global $__GlobalVars;
		return $__GlobalVars[$name];
	}
	

	/**是否处理调试状态*/
	static $isDebug = false;
	/**JS生产目录名*/
	static $jsBasePath = '/js/';
	/**JS开发目录名*/
	static $jsSourceBasePath = '/js/';
	/**CSS目录名*/
	static $cssBasePath = '/css/';
	/**
	 *	格式化JS文件路径
	 */

	function GetScriptBasePath()
	{
		return Common::GetGlobal('jsBasePath') ?: self::$isDebug?self::$jsSourceBasePath:self::$jsBasePath;
	}

	function GetStyleBasePath()
	{
		return Common::GetGlobal('styleBasePath') ?: self::$cssBasePath;
	}

	function formatScriptUrl($url,$basePath){
		$scriptName = ','.$url;
		$scriptName = str_replace(',',','.$basePath,$scriptName);
		$scriptName = substr($scriptName,1);
		return $scriptName;
	}
	/**得到CSS或JS文件URL*/
	static private function getURL($name){
		$domain = 'http://'.Core::GetConfig( 'Front_Resource_Site' );
		$version = Core::GetConfig( 'Front_Cache_Version' ).rand();
		return $domain.'/min/?f='.$name.'&'.$version;
		// if(self::$isDebug){
		// 	return $domain.'/'.$name.'?'.$version;
		// }else{
		// 	return $domain.'/min/?f='.$name.'&'.$version;
		// }
	}
	/**
	 * 添加js文件,文件以,隔开,相对路径为/js
	 */
	function addScript( $scriptName = '',$basePath = ''){
		$scriptName = str_replace( ';', ',', $scriptName );
		$scriptName = Common::uniqueAddFileName($scriptName);
		if($scriptName){
			$basePath = $basePath?$basePath:self::GetScriptBasePath();
			$jsPath = Common::GetGlobal('jsPath');
			$jsPath .= $jsPath?',':'';
			$jsPath .= Common::formatScriptUrl($scriptName,$basePath);
			Common::SetGlobal('jsPath', $jsPath);
		}
	}
	/**
	 *	得到ＪＳ全路径
	 */
	function getScript($scriptName = '',$basePath = ''){
		$scriptName = str_replace( ';', ',', $scriptName );
		$scriptName = Common::uniqueAddFileName($scriptName);
		if($scriptName){
			$basePath = $basePath?$basePath:self::GetScriptBasePath();
			$scriptName = Common::formatScriptUrl($scriptName,$basePath);
		}else{
			$scriptName = Common::GetGlobal('jsPath');
		}
		if(self::$isDebug){
			return $scriptName;
		}else{
			return self::getURL($scriptName);
		}
	}
	/**
	 *	得到完整的JS标签
	 */
	function getFullScript($scriptName = '',$basePath = ''){
		$scriptName = str_replace( ';', ',', $scriptName );
		$basePath = $basePath?$basePath:self::GetScriptBasePath();
		$scriptName = Common::getScript($scriptName,$basePath);
		if(self::$isDebug){
			$arr = explode(',',$scriptName);
			foreach($arr as $name){
				if($name){
					echo '<script src="'.self::getURL($name).'" type="text/javascript"></script>';
				}
			}
		}else if(preg_match('/min\/\?f=(.+)&\d+/',$scriptName)){
			echo '<script src="'.$scriptName.'" type="text/javascript"></script>';
		}
	}
	/**
	 *	格式化css文件路径
	 */
	function formatLinkUrl($name,$basePath){
		$cssName = ','.$name;
		$cssName = str_replace(',',','.$basePath,$cssName);
		$cssName = substr($cssName,1);
		return $cssName;
	}
	/**
	 * 添加css文件,文件以,隔开
	 */
	function addStyle( $name = '',$basePath = ''){
		$name = str_replace( ';', ',', $name );
		$name = Common::uniqueAddFileName($name);
		if($name){
			$basePath = $basePath?$basePath:self::GetStyleBasePath();
			$cssPath = Common::GetGlobal('extendStyle');
			$cssPath .= $cssPath?',':'';
			
			$cssPath .= Common::formatLinkUrl($name,$basePath);
			Common::SetGlobal('extendStyle', $cssPath);
		}
	}
	/**
	 *	得到css全路径
	 */
	function getStyle($name = '',$basePath = ''){		
		$name = str_replace( ';', ',', $name );
		$name = Common::uniqueAddFileName($name);
		if($name){
			$basePath = $basePath?$basePath:self::GetStyleBasePath();
			$linkUrl = Common::formatLinkUrl($name,$basePath);
		}else{
			$linkUrl = Common::GetGlobal('extendStyle');
		}
		if(self::$isDebug){
			return $linkUrl;
		}else{
			return self::getURL($linkUrl);
		}
	}
	/**
	 *	得到完整的link标签
	 */
	function getFullStyle($name = '',$basePath = ''){
		$name = str_replace( ';', ',', $name );
		$basePath = $basePath?$basePath:self::GetStyleBasePath();
		$linkUrl = Common::getStyle($name,$basePath);
		if(self::$isDebug){
			$arr = explode(',',$linkUrl);
			foreach($arr as $name){
				if($name){
					echo '<link rel="stylesheet" type="text/css" media="screen" href="'.self::getURL($name).'"/>';
				}
			}
		}else if(preg_match('/min\/\?f=(.+)&\d+/',$linkUrl)){
			echo '<link rel="stylesheet" type="text/css" media="screen" href="'.$linkUrl.'"/>';
		}
	}

	function addFooterScript( $scriptName = '',$basePath = ''){
		$scriptName = str_replace( ';', ',', $scriptName );
		$scriptName = Common::uniqueAddFileName($scriptName);
		if($scriptName){
			$basePath = $basePath?$basePath:self::GetScriptBasePath();
			$jsPath = Common::GetGlobal('jsFooterPath');
			$jsPath .= $jsPath?',':'';
			$jsPath .= Common::formatScriptUrl($scriptName,$basePath);
			Common::SetGlobal('jsFooterPath', $jsPath);
		}
	}

	function getFooterScript($scriptName = '',$basePath = ''){
		$scriptName = str_replace( ';', ',', $scriptName );
		$scriptName = Common::uniqueAddFileName($scriptName);
		if($scriptName){
			$basePath = $basePath?$basePath:self::GetScriptBasePath();
			$scriptName = Common::formatScriptUrl($scriptName,$basePath);
		}else{
			$scriptName = Common::GetGlobal('jsFooterPath');
		}
		if(self::$isDebug){
			return $scriptName;
		}else{
			return self::getURL($scriptName);
		}
	}
	function uniqueAddFileName($fileName,$char = ','){
		return implode($char,array_unique(explode($char,preg_replace('/'.$char.'+$/','',$fileName))));
	}
	function getFullFooterScript($scriptName = '',$basePath = ''){
		$scriptName = str_replace( ';', ',', $scriptName );
		$basePath = $basePath?$basePath:self::GetScriptBasePath();
		$scriptName = Common::getFooterScript($scriptName,$basePath);
		if(self::$isDebug){
			$arr = explode(',',$scriptName);
			foreach($arr as $name){
				if($name){
					echo '<script src="'.self::getURL($name).'" type="text/javascript"></script>';
				}
			}
		}else if(preg_match('/min\/f=(.+)&\d+/',$scriptName)){
			echo '<script src="'.$scriptName.'" type="text/javascript"></script>';
		}
	}

	function GetTemplate()
	{
		if ( isset( $GLOBALS['__Template'] ) )
			return $GLOBALS['__Template'];
		$config = Core::getConfig('View_Neat');
		$Template = new FDX_View_Neat( $config );

		if($config['plugins']){
			foreach($config['plugins'] as $name => $path){
				include $path;
				$Template->Template->Plugin( new $name );	
			}
		}

		$GLOBALS['__Template'] = $Template;

		return $Template;
	}
	//必须保证$template的$parent的顺序
	function PageOut_NoOrder($template, $vars = array(), $parent = 'main')
	{
		$Template = Common::GetTemplate();

		if ( $parent )
		{
			$vars['template'] = $template;
			
			$Template->display($parent . ".html", $vars);
		}
		else
		{
			$Template->display($template, $vars);
		}
	}
	
	function PageOut($template, $vars = array(), $parentVars = array(), $parent = 'main')
	{
		$Template = Common::GetTemplate();

		if ( $parent )
		{
			$result = $Template->result( $template, $vars );

			$parentVars['module'] = $result;
			$parentVars['front_domain'] = 'http://'.Core::GetConfig( 'Front_Resource_Site' );
			$Template->display($parent . ".html", $parentVars);
		}
		else
		{
			$Template->display($template, $vars);
		}
	}
}
//开启参数debug模式
Common::$isDebug = Common::$isDebug || preg_match('/debug/i',$_SERVER['HTTP_REQUEST_URI'] ? $_SERVER['HTTP_REQUEST_URI'] : $_SERVER['REQUEST_URI']);
