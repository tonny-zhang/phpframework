<?php

/**
 * 数据库操作MySQLi接口，自动支持Master/Slave 读/写 分离操作， 支持多Slave主机
 */
class FDX_Db_Mysqli
{

	/**
	 * 数据库配置信息
	 */
	private $wdbConf = array();
	private $rdbConf = array();
	/**
	 * Master数据库连接
	 */
	private $wdbConn = null;
	/**
	 * Slave数据库连接
	 */
	private $rdbConn = null;
	/**
	 * 当前操作的数据库链接
	 */
	private $currConn = null;
	/**
	 * 是否只有一台Master数据库服务器
	 */
	private $singleHost = true;
	/**
	 * 数据库结果
	 */
	private $dbResult;
	/**
	 * 数据库结果集提取方式
	 */
	private $fetchMode = MYSQLI_ASSOC;
	/**
	 * 是否记录SQL运行时间
	 */
	private $isRuntime = true;
	/**
	 * SQL执行时间
	 */
	private $runTime = 0;
	/**
	 * 初始化数据库连接
	 */
	private $isInitConn = true;

	/**
	 * 设置类属性
	 *
	 * @param str $key  需要设置的属性名
	 * @param str $value 需要设置的属性值
	 * @return void
	 */
	private function set($key, $value)
	{
		$this->$key = $value;
	}

	/**
	 * 读取类属性
	 *
	 * @param str $key  需要读取的属性名
	 * @return void
	 */
	private function get($key)
	{
		return $this->$key;
	}

	/**
	 * 构造函数
	 */
	public function __construct($config)
	{
		//构造数据库配置信息
		if (is_array($config['master']) && !empty($config['master']))
		{
			$this->wdbConf = $config['master'];
		}
		if (!is_array($config['slave']) || empty($config['slave']))
		{
			$this->rdbConf = $config['master'];
		}
		else
		{
			$this->rdbConf = $config['slave'];
		}
		$this->singleHost = $config['singlehost'];

		//初始化连接
		if ($this->isInitConn)
		{
			$this->currConn = $this->getDbWriteConn();
			if (!$this->singleHost)
			{
				$this->currConn = $this->getDbReadConn();
			}
		}
	}
	
	public function __destruct(){
		if($this->wdbConn) @mysqli_close($this->wdbConn);
		if($this->rdbConn) @mysqli_close($this->rdbConn);
		$this->wdbConn = $this->rdbConn = NULL;
	}

	/**
	 * 获取Master的写数据连接
	 */
	private function getDbWriteConn()
	{
		//判断是否已经连接
		if ($this->wdbConn && is_object($this->wdbConn))
		{
			return $this->wdbConn;
		}
		//没有连接则自行处理
		$db = $this->connect($this->wdbConf['host'], $this->wdbConf['user'], $this->wdbConf['pwd'], $this->wdbConf['db'], $this->wdbConf['charset']);
		if (!$db || !is_object($db))
		{
			return false;
		}
		$this->wdbConn = $db;
		return $this->wdbConn;
	}

	/**
	 * 获取Slave的读数据连接
	 */
	private function getDbReadConn()
	{
		//判断是否已经连接
		if ($this->rdbConn && is_object($this->rdbConn))
		{
			return $this->rdbConn;
		}

		//随机选择一台slave连接
		if (is_array($this->rdbConf) && !empty($this->rdbConf))
		{
			$key = array_rand($this->rdbConf);
			$db = $this->connect($this->rdbConf[$key]['host'], $this->rdbConf[$key]['user'], $this->rdbConf[$key]['pwd'], $this->rdbConf[$key]['db'], $this->rdbConf[$key]['charset']);
			if (!$db || !is_object($db))
			{
				return false;
			}
			$this->rdbConn = $db;
			return $this->rdbConn;
		}

		//如果没有可用的Slave连接，则继续使用Master连接
		return $this->getDbWriteConn();
	}

	/**
	 * 连接到MySQL数据库公共方法
	 */
	private function connect($dbHost, $dbUser, $dbPasswd, $dbDatabase, $dbCharset)
	{
		//连接数据库主机
		$db = @mysqli_connect($dbHost, $dbUser, $dbPasswd);
		if (!$db)
		{
			throw new FDX_Exception("Mysqli connect " . $dbHost . " failed, message: " . mysqli_connect_error(), 301);
			//return false 抛出异常后省略
		}

		//选定数据库
		if (!@mysqli_select_db($db, $dbDatabase))
		{
			throw new FDX_Exception("Select database $dbDatabase failed, message: " . mysqli_error($db), 302);
		}

		//设置字符集

		if (@mysqli_query($db, "SET NAMES '" . $dbCharset . "'") === false)
		{
			throw new FDX_Exception("Set db_host '$dbHost' charset=" . $dbCharset . " failed, message: " . mysqli_error($db), 302);
		}

		return $db;
	}

	/**
	 *
	 * @param string $sql 要执行查询的SQL语句
	 * @param bool $isMaster 是否在主服务器上执行查询
	 */
	public function query($sql, $isMaster=false)
	{
		if (trim($sql) == "")
		{
			throw new FDX_Exception("Sql query is empty.", 101);
		}
		//是否只有一台数据库机器
		if ($this->singleHost)
		{
			$isMaster = true;
		}

		//获取执行SQL的数据库连接
		if (!$isMaster)
		{
			$optType = trim(strtolower(substr(ltrim($sql), 0, 6)));
		}
		if ($isMaster || $optType != "select")
		{
			$dbConn = $this->getDbWriteConn();
		}
		else
		{
			$dbConn = $this->getDbReadConn();
		}
		if (!$dbConn || !is_object($dbConn))
		{
			throw new FDX_Exception("No available db connection.", 301);
		}

		//执行查询
		$this->currConn = $dbConn;
		$this->dbResult = null;
		if ($this->isRuntime)
		{
			$startTime = $this->getTime();
			$this->dbResult = @mysqli_query($dbConn, $sql);
			$this->runTime = $this->getTime() - $startTime;
		}
		else
		{
			$this->dbResult = @mysqli_query($dbConn, $sql);
		}
		if ($this->dbResult === false)
		{
			throw new FDX_Exception('MySQL errno:' . mysqli_errno($dbConn) . ', error:' . mysqli_error($dbConn)."\n SQL:".$sql, 302);
		}

		return true;
	}

	/**
	 * 返回查询结果集
	 */
	public function fetch()
	{
		return @mysqli_fetch_array($this->dbResult, $this->fetchMode);
	}

	/**
	 * 返回上一次查询所影响的行数
	 */
	public function getAffectedRows()
	{
		return @mysqli_affected_rows($this->currConn);
	}

	/**
	 * 返回当前连接的最后一次插入的自增字段值
	 */
	public function getInsertId()
	{
		$dbConn = $this->getDbWriteConn();
		return mysqli_insert_id($dbConn);
	}

	public function getTime()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float) $usec + (float) $sec);
	}

	/**
	 * 返回查询所消耗的时间
	 */
	public function getRunTime()
	{
		if ($this->isRuntime)
		{
			return sprintf("%.6f sec", $this->runTime);
		}
	}

	/**
	 * 转义SQL查询字符
	 */
	public function escapeString($str)
	{
		if (get_magic_quotes_gpc())
		{
			$str = stripslashes($str);
		}
		if(!$this->currConn || !is_object($this->currConn)) {
			if($this->singleHost) {
				$this->currConn = $this->getDbWriteConn();
			} else {
				$this->currConn = $this->getDbReadConn();
			}
			if (!$this->currConn || !is_object($this->currConn)) {
				throw new FDX_Exception("No available db connection.", 301);
			}
		}
		return mysqli_real_escape_string($this->currConn, $str);
	}

}