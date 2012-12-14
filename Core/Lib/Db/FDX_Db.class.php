<?php
class FDX_Db
{
	/**
	 * 
	 * @var 后端数据库对象
	 */
	private $backend = null;

	/**
	 * FDX_DB 构造函数
	 * 
	 * @param array $config 配置信息数组
	 */
	public function __construct( $config )
	{
		$driver_class = 'FDX_Db_' . $config['driver'];
		$this->backend = new $driver_class( $config );
	}

	public function getBackend()
	{
		return $this->backend;
	}

	/**
	 * Get
	 * 取得一条指定条件的数据
	 * $table
	 * $condition
	 * $select
	 * $conditionExt
	 */
	function Get( $array )
	{
		@extract( $array );

		$select = $select ? $select : '*'; 
		// $table = $this->table[$table] ? $this->table[$table] : $table;
		$where = $this->_parseCondition( $condition, $conditionExt );

		if ( !$sql )
		{
			$sql = "SELECT {$select} FROM {$table} ";
			$sql .= $where ? "WHERE {$where} " : " ";
			$sql .= $order ? "ORDER BY {$order} " : '';
			$sql .= "LIMIT 1";
		}
		/**
		 * $rs = $this->DB->Query( $sql );
		 * 
		 * $rs->NextRecord();
		 * 
		 * $info = $rs->GetArray();
		 * 
		 * if ( $key )
		 * return $info[$key];
		 * else
		 * return $info;
		 */

		$this->backend->query( $sql );
		$info = array();
		$info = $this->backend->fetch();

		if ( $key )
			return $info[$key];
		else
			return $info;
	}

	/**
	 * GetList
	 * 取得指定条件的数据列表
$table
	 * $condition
	 * $order
	 * $offset
	 * $limit
	 * $key
	 * $conditionExt
	 * $select
	 * $group
	 */
	function GetList( $array )
	{
		@extract( $array );

		$select = $select ? $select : '*'; 
		// $table = $this->table[$table] ? $this->table[$table] : $table;
		$where = $this->_parseCondition( $condition, $conditionExt );

		if ( !$sql )
		{
			$sql = "SELECT {$select} FROM {$table} ";
			$sql .= $where ? "WHERE {$where} " : '';
			$sql .= $group ? "GROUP BY {$group} " : '';
			$sql .= $order ? "ORDER BY {$order} " : '';
			$sql .= ( $offset || $limit ) ? "LIMIT {$offset}, {$limit} " : '';
		}

		$this->backend->query( $sql );
		$array = array();
		while ( $row = $this->backend->fetch() )
		{
			if ( $key )
				$array[$row[$key]] = $row;
			else
				$array[] = $row;
		}

		return $array;
	}

	/**
	 * Del
	 * 删除指定数据
	 * $table
	 * $condition
	 * $conditionExt
	 */
	function Del( $array )
	{
		@extract( $array ); 
		// $table = $this->table[$table] ? $this->table[$table] : $table;
		$where = $this->_parseCondition( $condition, $conditionExt );

		$sql = "DELETE FROM {$table} ";
		$sql .= $where ? "WHERE {$where} " : '';

		$this->backend->query( $sql );
		return $this->backend->getAffectedRows();
	}

	/**
	 * Update
	 * 更新指定数据
	 * $table
	 * $condition
	 * $conditionExt
	 * $data
	 * $dataExt
	 */
	function Update( $array )
	{
		@extract( $array ); 
		// $table = $this->table[$table] ? $this->table[$table] : $table;
		$set = $this->_parseData( $data, $dataExt );
		$where = $this->_parseCondition( $condition, $conditionExt );

		if ( !$sql )
		{
			$sql = "UPDATE {$table} ";
			$sql .= $set ? "SET {$set} " : '';
			$sql .= $where ? "WHERE {$where} " : '';
		}

		$this->backend->query( $sql );
		return $this->backend->getAffectedRows();
	}

	/**
	 * Add
	 * 插入数据
	 * $table
	 * $data
	 */
	function Add( $array )
	{
		@extract( $array );

		$sign = '';
		$fields = '';
		$values = '';
		foreach ( $data as $key => $val )
		{
			$fields .= $sign . $key;
			$values .= $sign . "'" . $this->backend->escapeString( $val ) . "'";
			$sign = ',';
		} 
		// $table = $this->table[$table] ? $this->table[$table] : $table;
		$sql = "INSERT INTO {$table} ";
		$sql .= "( {$fields} ) ";
		$sql .= "VALUES ( {$values} )";

		$this->backend->query( $sql );
		return $this->backend->getInsertId();
	}

	/**
	 * Replace
	 * 插入数据
	 * $table
	 * $data
	 */
	function Replace( $array )
	{
		@extract( $array );

		$sign = '';
		$fields = '';
		$values = '';
		foreach ( $data as $key => $val )
		{
			$fields .= $sign . $key;
			$values .= $sign . "'" . $this->backend->escapeString( $val ) . "'";
			$sign = ',';
		}

		$sql = "REPLACE INTO {$table} ";
		$sql .= "( {$fields} ) ";
		$sql .= "VALUES ( {$values} )";

		$this->backend->query( $sql );
		return $this->backend->getAffectedRows();
	}

	function RealReplace( $array, $uniqueKey = '' )
	{
		@extract( $array );

		$sign = '';
		$sign2 = '';
		$fields = '';
		$values = '';
		$set = '';
		foreach ( $data as $key => $val )
		{
			$fields .= $sign . $key;
			$values .= $sign . "'" . $this->backend->escapeString( $val ) . "'";

			if ( $uniqueKey != $key )
			{
				$set .= $sign2 . " {$key} = '" . $this->backend->escapeString( $val ) . "'";
				$sign2 = ',';
			}

			$sign = ',';
		}

		$sql = "INSERT INTO {$table} ";
		$sql .= "( {$fields} ) ";
		$sql .= "VALUES ( {$values} ) ";
		$sql .= "ON DUPLICATE KEY UPDATE {$set}";

		$this->backend->query( $sql );
		return $this->backend->getAffectedRows();
	}

	function ParseData( $data, $dataExt )
	{
		return $this->_parseData( $data, $conditionExt );
	}

	/**
	 * _parseCondition
	 * 构造条件 "WHERE"之后的语句 $condition 参数,构造条件字符串
	 */
	function _parseCondition( $condition, $conditionExt )
	{
		if ( !$condition && !$conditionExt )
			return '';

		if ( is_array( $conditionExt ) )
			$conditionExt = implode( " AND ", $conditionExt );

		if ( is_array( $condition ) )
		{
			foreach ( $condition as $k => $v )
			{
				$conditionList[] = "$k = '" . $this->backend->escapeString( $v ) . "'";
			}

			$sql = @implode( " AND ", $conditionList );
		}

		if ( $sql && $conditionExt )
			return $sql . " AND {$conditionExt} ";
		elseif ( !$sql && $conditionExt )
			return $conditionExt;
		else
			return $sql;
	}

	/**
	 * _parseData
	 * 构造 "SET" 之后的语句 $dataExt 参数,附加的SET参数
	 */
	function _parseData( $data, $dataExt )
	{
		if ( is_array( $data ) )
		{
			foreach ( $data as $key => $val )
			{
				$dataList[] = "{$key} = '" . $this->backend->escapeString( $val ) . "'";
			}

			$set = @implode( ',', $dataList );
		}

		if ( $set && $dataExt )
			return "{$set} , {$dataExt} ";
		elseif ( !$set && $dataExt )
			return $dataExt;
		else
			return $set;
	}

	function Error( $msg )
	{
		exit( $msg );
	} 
	// ------------------------------------------------------------------------------
	/**
	 * 获取单行记录(一维数组)
	 * 
	 * @param string $sql 需要执行查询的SQL语句
	 * @return 成功返回结果记录的一维数组, 数据空返回null
	 */
	public function getRow( $sql, $isMaster = false )
	{
		$this->backend->query( $sql, $isMaster );
		$record = array();
		$record = $this->backend->fetch();
		if ( !is_array( $record ) || empty( $record ) )
		{
			return null;
		}
		return $record;
	}

	/**
	 * 获取SQL执行的全部结果集(二维数组)
	 * 
	 * @param string $sql 需要执行查询的SQL语句
	 * @return 成功返回查询结果的二维数组, 数据空返回null
	 */
	public function getAll( $sql, $isMaster = false )
	{
		$this->backend->query( $sql, $isMaster );
		$record = array();
		while ( $row = $this->backend->fetch() )
		{
			$record[] = $row;
		}
		if ( !is_array( $record ) || empty( $record ) )
		{
			return null;
		}
		return $record;
	}

	/**
	 * 获取一列数据(一维数组)
	 * 
	 * @param string $sql 需要获取的字符串
	 * @param string $field 需要获取的列,如果不指定,默认是第一列
	 * @return 成功返回提取的结果记录的一维数组, 数据空返回null
	 */
	public function getCol( $sql, $field = '', $isMaster = false )
	{
		$this->backend->query( $sql, $isMaster );
		$record = array();
		while ( $row = $this->backend->fetch() )
		{
			if ( trim( $field ) == '' )
			{
				$record[] = current( $row );
			}
			else
			{
				$record[] = $row[$field];
			}
		}
		if ( !is_array( $record ) || empty( $record ) )
		{
			return null;
		}
		return $record;
	}

	/**
	 * 返回第一条记录的指定字段，如果未指定，则返回第一个
	 * 
	 * @param string $sql 需要执行查询的SQL
	 * @return 成功返回获取的一个数据, 数据空返回NULL
	 */
	public function getOne( $sql, $field = '', $isMaster = false )
	{
		$this->backend->query( $sql, $isMaster );
		$record = array();

		$row = $this->backend->fetch();
		if ( !is_array( $row ) || empty( $row ) )
		{
			return null;
		}
		if ( trim( $field ) != '' )
		{
			$record = $row[$field];
		}
		else
		{
			$record = current( $row );
		}
		return $record;
	}

	/**
	 * 执行执行非Select查询操作
	 * 
	 * @param string $sql 查询SQL语句
	 * @return bool 返回受影响的行
	 */
	public function execute( $sql )
	{
		$this->backend->query( $sql, true );
		return $this->backend->getAffectedRows();
	}

	/**
	 * 执行任意SQL操作
	 * 
	 * @param string $sql 需要执行的SQL
	 * @return mixed 返回受影响的行或查询结果集
	 */
	public function query( $sql )
	{
		$optType = trim( strtolower( substr( ltrim( $sql ), 0, 6 ) ) );
		if ( in_array( $optType, array( 'update', 'insert', 'delete' ) ) )
		{
			return $this->execute( $sql );
		}
		return $this->getAll( $sql );
	}

	public function getLastId()
	{
		if ( ( $lastId = $this->backend->getInsertId() ) > 0 )
		{
			return $lastId;
		}
		return $this->getOne( "SELECT LAST_INSERT_ID()", '', true );
	}

	/**
	 * 插入记录
	 */
	public function db_create( $table, $fields )
	{
		$sqlBuilder = new FDX_Db_SqlBuilder();
		$sql = $sqlBuilder->create( $table, $fields );
		$this->backend->query( $sql );
		return $this->getLastId();
	}

	/**
	 * 更新记录
	 */
	public function db_update( $table, $fields , $where = null )
	{
		$sqlBuilder = new FDX_Db_SqlBuilder();
		$sql = $sqlBuilder->update( $table, $fields, $where );
		return $this->execute( $sql );
	}

	/**
	 * 删除记录
	 */
	public function db_delete( $table, $where )
	{
		$sqlBuilder = new FDX_Db_SqlBuilder();
		$sql = $sqlBuilder->delete( $table, $where );
		return $this->execute( $sql );
	}

	public function count( $table, $where = null )
	{
		$sqlBuilder = new FDX_Db_SqlBuilder();
		$fields = 'COUNT(*) AS `count`';
		$sql = $sqlBuilder->find( $table , $where , null , null , $fields );
		$row = $this->getRow( $sql );
		return $row['count'];
	}

	public function getRunTime()
	{
		return $this->backend->getRunTime();
	}

	public function escapeString( $str )
	{
		return $this->backend->escapeString( $str );
	}
}
