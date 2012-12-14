<?php
/**
 * 使用memcached来存储session
 */
class FDX_MemSession
{
	protected $_version = '1';
	protected $_memcache;

	public function __construct() 
	{
		$config = Core::getConfig('MemSession');
        $this->lifeTime = intval($config['lifeTime']);
		$this->store = $config['store'];
		session_set_save_handler(
			array($this, 'sessionOpen'),
			array($this, 'sessionClose'),
			array($this, 'sessionRead'),
			array($this, 'sessionWrite'),
			array($this, 'sessionDestroy'),
			array($this, 'sessionGc')
		);
	}

	// --- 启动session
	public	function start()
	{
		session_name('fan_s');
		session_start();
	}
   /**
	 * 打开session，类似于constructor，session_star()时执行一次
	 *
	 * @param string $savePath
	 * @param string $sessionName
	 *
	 * @return boolean
	 */
	public function sessionOpen($savePath, $sessionName)
	{
		$this->_memcache = new FDX_Cache_Memcache($this->store);
		return true;
	}

	/**
	 * 关闭 session, 类似于destructor
	 *
	 * @return boolean
	 */
	public function sessionClose()
	{
		return true;
	}

	/**
	 * 读取指定 id 的 session 数据
	 *
	 * @param string $sessid
	 *
	 * @return string
	 */
	public function sessionRead($sessid)
	{
		$cacheKey = $this->_getCacheName($sessid);
		$data = $this->_memcache->get($cacheKey);
		if (empty($data)) {
			return false;
		}
		// --- 更新cache存活时间
		$this->_memcache->set($cacheKey, $data, null, $this->lifeTime);
		return $data;
	}

	/**
	 * 写入指定 id 的 session 数据，只执行一次
	 * As of PHP 5.0.5 the write and close handlers 在所有对象被删除之后被调用，所以无法使用外部的对象和抛出异常
	 *
	 * @param string $sessid
	 * @param string $data
	 *
	 * @return boolean
	 */
	public function sessionWrite($sessid, $data)
	{
		$cacheKey = $this->_getCacheName($sessid);
		return $this->_memcache->set($cacheKey, $data, null, $this->lifeTime);
	}

	/**
	 * 销毁指定 id 的 session
	 *
	 * @param string $sessid
	 *
	 * @return boolean
	 */
	public function sessionDestroy($sessid)
	{
		$cacheKey = $this->_getCacheName($sessid);
		return $this->_memcache->remove($cacheKey);
	}

	/**
	 * 清理过期的 session 数据
	 *
	 * @param int $maxlifetime
	 *
	 * @return boolean
	 */
	public function sessionGc($maxlifetime)
	{
		return true;
	}
	/**
	 *
	 * @param string $key
	 * @return string 
	 */
	public function _getCacheName($sessid)
	{
		return "session_{$this->_version}_{$sessid}";
	}
}