<?php

class FDX_Cache {

    /**
     * @var object 对象单例
     */
    private static $_instance = NULL;

    /**
     * @var 配置文件数组
     */
    protected $config = array();

    /**
     * @var 视图对象
     */
    protected $cache = NULL;


    /**
     * Cache构造函数
     *
     * @param array $config 配置数据数组
     */
    private function __construct($config) {
        if (empty($config)) {
            throw new FDX_Exception("配置文件内容为空");
        }

        $this->config = $config;
    }

    /**
     * 保证对象不被clone
     */
    private function __clone() {

    }

    /**
     * 获取对象唯一实例
     *
     * @param void
     * @return FDX_Cache 返回本对象实例
     */
    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            $config = Core::getConfig('Cache');
            self::$_instance = new self($config);
        }
        return self::$_instance;
    }

    /**
     * 获取Cache访问对象
     *
     * @param void
     * @return FDX_Cache_Memcache
     */
    public function getCache($cfg = array(), $key = "Cache") {
        try
        {
            if (is_object($this->cache[$key])) {
                return $this->cache[$key];
            }

            if($key == 'Cache') {
	            $Type = $this->config['type']=='' ? "Memcache" : $this->config['type'];
	            $class  = "FDX_Cache_". $Type;
	
	            $this->cache[$key] = new $class($this->config['Policy']);
            } else {
            	$Type = $cfg['type']=='' ? "Memcache" : $cfg['type'];
	            $class  = "FDX_Cache_". $Type;
	
	            $this->cache[$key] = new $class($cfg['Policy']);
            }

            return $this->cache[$key];
        }
        catch(FDX_Exception $e)
        {
            throw $e;
        }
    }

}