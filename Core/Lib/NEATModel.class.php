<?php

/**
* 定义 NEATModel 类
* Model 类封装了一系列数据库操作
* @copyright Copyright (c) 2004 NeatStudio.com
* @author hihiyou
*/

class NEATModel
{
	/**
	* 数据类
	* @var object
	*/
	var $DB;
	
	/**
	* 数据表名称
	* @var array
	*/
	var $table = array();

	/**
	* Model
	* 构造函数
	*/
	function NEATModel( $DB )
	{
		$this->DB = $DB;
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
		$table = $this->table[$table] ? $this->table[$table] : $table;
		$where = $this->_parseCondition( $condition, $conditionExt );

		if ( !$sql )
		{
			$sql  = "SELECT {$select} FROM {$table} ";
			$sql .= $where ? "WHERE {$where} " : " ";
			$sql .= $order ? "ORDER BY {$order} " : '';
			$sql .= "LIMIT 1";
		}

		/*
		$rs = $this->DB->Query( $sql );

		$rs->NextRecord();

		$info = $rs->GetArray();

		if ( $key )
			return $info[$key];
		else
			return $info;
		*/

		$this->DB->query($sql);
		$info = array();
		$info = $this->DB->fetch();

		if ( $key )
			return $info[$key];
		else
			return $info;
	}

	/**
	* GetList
	* 取得指定条件的数据列表
	* $table
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
		$table = $this->table[$table] ? $this->table[$table] : $table;
		$where = $this->_parseCondition( $condition, $conditionExt );

		if ( !$sql )
		{
			$sql  = "SELECT {$select} FROM {$table} ";
			$sql .= $where ? "WHERE {$where} " : '';
			$sql .= $group ? "GROUP BY {$group} " : '';
			$sql .= $order ? "ORDER BY {$order} " : '';
			$sql .= ( $offset || $limit ) ? "LIMIT {$offset}, {$limit} " : '';
		}

		/*
		$rs = $this->DB->Query( $sql, $offset, $limit );

		$array = array();

		while ( $rs->NextRecord() )
		{
			if ( $key )
				$array[$rs->Get( $key )] = $rs->GetArray();
			else
				$array[] = $rs->GetArray();
		}

		return $array;
		*/

		$this->DB->query($sql);
		$array = array();
		while ($row = $this->DB->fetch()) {
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

		$table = $this->table[$table] ? $this->table[$table] : $table;
		$where = $this->_parseCondition( $condition, $conditionExt );

		$sql  = "DELETE FROM {$table} ";
		$sql .= $where ? "WHERE {$where} " : '';

		$this->DB->query( $sql );
		return $this->DB->getAffectedRows();
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
		
		$table = $this->table[$table] ? $this->table[$table] : $table;
		$set = $this->_parseData( $data, $dataExt );
		$where = $this->_parseCondition( $condition, $conditionExt );

		if ( !$sql )
		{
			$sql  = "UPDATE {$table} ";
			$sql .= $set ? "SET {$set} " : '';
			$sql .= $where ? "WHERE {$where} " : '';
		}

		$this->DB->query( $sql );
		return $this->DB->getAffectedRows();
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
			$values .= $sign . "'" . addslashes( $val ) . "'";
			$sign = ',';
		}

		$table = $this->table[$table] ? $this->table[$table] : $table;

		$sql  = "INSERT INTO {$table} ";
		$sql .= "( {$fields} ) ";
		$sql .= "VALUES ( {$values} )";

		$this->DB->query( $sql );
		return $this->DB->getInsertId();
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
			$values .= $sign . "'" . addslashes( $val ) . "'";
			$sign = ',';
		}

		$table = $this->table[$table] ? $this->table[$table] : $table;

		$sql  = "Replace INTO {$table} ";
		$sql .= "( {$fields} ) ";
		$sql .= "VALUES ( {$values} )";

		$this->DB->query( $sql );
		return $this->DB->getAffectedRows();
	}

	/**
	* _parseCondition
	* 构造条件 "WHERE"之后的语句
	* $condition 参数,构造条件字符串
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
				$conditionList[] = "$k = '" . addslashes( $v ) . "'";
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
	* 构造 "SET" 之后的语句
	* $dataExt 参数,附加的SET参数
	*/
	function _parseData( $data, $dataExt )
	{
		if ( is_array( $data ) )
		{
			foreach ( $data as $key => $val )
			{
				$dataList[] = "{$key} = '" . addslashes( $val ) . "'";
			}

			$set	= @implode( ',', $dataList );
		}

		if ( $set && $dataExt )
			return  "{$set} , {$dataExt} ";
		elseif ( !$set && $dataExt )
			return $dataExt;
		else
			return $set;
	}

	function Error( $msg )
	{
		exit( $msg );
	}
}
