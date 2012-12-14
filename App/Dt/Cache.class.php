<?php
class Dt_Cache extends Dt_Base {
	protected $type = null;
	
	/**
	 * 初始化
	 *
	 * @param unknown_type $type = (sphinx,)
	 */
	public function __construct($type) {
		//类的缓存定义
		$this->mcConfig = array(
			'sphinx' => array('key'=> CACHE_KEY_BASE.'_1_%s', 'lifetime'=> 43200, 'mc' => 'common'),//1天
		);
		
		$this->Cache = $this->getMc($type);
		$this->type = $type;
		if ($type == 'sphinx') {
			Core::LoadConfigFile(APP_PATH.'Config/CacheVersion.php');
			$cacheVersion = Core::GetConfig('cache_version');
			$this->mcConfig['sphinx']['key'] .=  "_" .$cacheVersion['sphinx'];
		}
	}

	public function getItem($hash) {
		$cacheInfo = $this->Cache->get(sprintf($this->mcConfig[$this->type]['key'], $hash));
		return $cacheInfo ? $cacheInfo : false;
	}

	public function setItem($hash, $info) {
		$cacheName = sprintf($this->mcConfig[$this->type]['key'], $hash);
		return $this->Cache->set($cacheName, $info, 0, $this->mcConfig[$this->type]['lifetime']);
	}
}
