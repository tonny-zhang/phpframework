<?php
/********************************
 *
 *  描述: Fandongxi 核心模型类
 *
 *  初始化Model
 *
 ********************************/


class FDX_Model {
    /**
     * @var object 对象单例
     */
    private static $_instance = NULL;

    /**
     * @var 配置文件数组
     */
    protected $config = array();

    /**
     * @var 数据库对象
     */
    protected $db = array();


    /**
     * Model构造函数
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
     * @return FDX_Model 返回本对象实例
     */
    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            $config = Core::getConfig('DataBase');
            self::$_instance = new self($config);
        }
        return self::$_instance;
    }

    /**
     * 获取数据库访问对象
     *
     * @param void
     * @return FDX_Db
     */
    public function getDb($type='default') {
        try
        {
            if ( isset($this->db[$type]) && !defined('USE_DB_FOR_SCRIPT') ) {
                return $this->db[$type];
            }

            //初始化数据库访问
            $this->db[$type] = new FDX_Db($this->config[$type]);
            return $this->db[$type];
        }
        catch(FDX_Exception $e)
        {
            throw $e;
        }
    }

    /**
     * 获取FDX_Mongo的实例
     * @return  FDX_Mongo
     */
    public function getMongo(){
	if( !is_object($this->mongo) ){
	    $this->mongo = FDX_Mongo::getInstance();
	}
	return $this->mongo;
    }

}