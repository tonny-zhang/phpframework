<?php

/*
+---------------------------------------------------+
| Name : NEAT_CATEGORY ������
+---------------------------------------------------+
| C / M : 2004-11-5 / 2005-3-28
+---------------------------------------------------+
| Version : 1.0.4
+---------------------------------------------------+
| Author : walkerlee
+---------------------------------------------------+
| Powered by NEATSTUDIO 2002 - 2004
+---------------------------------------------------+
| Email : neatstudio@yahoo.com.cn
| Homepge : http://www.neatstudio.com
| BBS : http://www.neatstudio.com/bbs/
+---------------------------------------------------+
| Log :
+---------------------------------------------------+
hihiyou �޸İ�(����CMS),ȥ��NBS��NCA [2006-5-13]
+---------------------------------------------------+
*/

class NEATCategory
{
	var $NDB;

	var $table;
	var $tableFids;

	/*
	+---------------------------------------------------+
	| Function Name : setNDB
	+---------------------------------------------------+
	| Created / Modify : 2004-10-27 / 
	+---------------------------------------------------+
	*/

	function SetNDB($NDB)
	{
		$this->NDB = $NDB;
	}

	/*
	+---------------------------------------------------+
	| Function Name : setTable
	+---------------------------------------------------+
	| C / M : 2004-10-22 / 
	+---------------------------------------------------+
	*/

	function SetTable($table)
	{
		$this->table = $table;
	}

	/*
	+---------------------------------------------------+
	| Function Name : setField
	+---------------------------------------------------+
	| C / M : 2004-10-22 / 
	+---------------------------------------------------+
	*/

	function SetField($fields)
	{
		foreach($fields as $k=>$v)
		{
			$this->tableFids[$k] = $v;
		}
	}

	/*
	+---------------------------------------------------+
	| Function Name : GetTree
	+---------------------------------------------------+
	| C / M : 2004-10-22 / 2004-10-24 11:57
	+---------------------------------------------------+
	* 本函数的作用是：给数组$array的值增加一个字段deep,代表当前分类的级数或深度
	*/

	function GetTree($array, $pid, $deep = 0, $name)
	{
		$this->tmpgetArray;

		$deep++;

		if (is_array($array) && !empty($array))
		{
			foreach($array as $key => $val)
			{
				if ($val[$this->tableFids['pid']] == $pid)
				{

					$i = $val[$this->tableFids['id']];

					foreach($val as $k => $v)
					{
						$this->tmpgetArray[$name][$i][$k] = $v;
					}

					$this->tmpgetArray[$name][$i]['deep']	= $deep-1;

					$this->GetTree($array, $val[$this->tableFids['id']], $deep, $name);
				}
			}

			return $this->getarr[$name] = $this->tmpgetArray[$name];
		}
		else
		{
			return null;
		}
	}

	/*
	+---------------------------------------------------+
	| Function Name : getNav
	+---------------------------------------------------+
	| C / M : 2004-10-24 11:58 / 
	+---------------------------------------------------+
	| Log : 
	+---------------------------------------------------+
	  2004-11-30(hihiyou)
	  ���һ���ж�,��$getarrΪ��ʱ,
	  array_reverse����.
	+---------------------------------------------------+
	*/

	function GetNav($array, $id)
	{
		while($array[$id][$this->tableFids['pid']] <> NULL)
		{	
			foreach($array[$id] as $k => $v)
			{
				$getarr[$id][$k] = $v;
			}

			$id = $array[$id][$this->tableFids['pid']];
		}

		if (!empty($getarr))
			return array_reverse($getarr);
		else
			return;
	}

	/*
	+---------------------------------------------------+
	| Function Name : changeOrderID �ı���������
	+---------------------------------------------------+
	| C / M : 2004-10-24 11:58 / 
	+---------------------------------------------------+
	| Note : 
	+---------------------------------------------------+
	$array : get by getTree()
	$id : the category's id which want to change orderid
	$type : the change method

	type = 1 : up
	type = 2 : down
	+---------------------------------------------------+
	| Log : 
	+---------------------------------------------------+
	
	+---------------------------------------------------+
	*/

	function ChangeOrderID($array, $id, $type)
	{
		$cateArray = $this->GetChangeOrderID($array, $id);

		//get pre and next

		$type == 1 ? $targetIndexTemp = $cateArray['info']['index'] - 1 : $targetIndexTemp = $cateArray['info']['index'] + 1;

		$cateArray['list'][$targetIndexTemp] ? $targetIndex = $targetIndexTemp : $targetIndex = $cateArray['info']['index'];

		// Default

		$thisOrderID = $cateArray['list'][$targetIndex]['orderid'];
		$targetOrderID = $cateArray['info']['orderid'];
		
		// set this and target's orderid
		
		if ($cateArray['list'][$targetIndex]['orderid'] == $cateArray['info']['orderid'])
			$type == 1 ? $thisOrderID++ : $targetOrderID++;

		$thisID = $id;
		$targetID = $cateArray['list'][$targetIndex]['id'];

		$thisCoData[$this->tableFids['id']]  = $thisID;
		$thisUpData[$this->tableFids['orderid']] = $thisOrderID;

		$this->UpdateCategory($thisUpData, $thisCoData);

		$targetCoData[$this->tableFids['id']]  = $targetID;
		$targetUpData[$this->tableFids['orderid']] = $targetOrderID;

		$this->UpdateCategory($targetUpData, $targetCoData);
	}

	/*
	+---------------------------------------------------+
	| getChangeOrderID ȡ����������ݵ�orderid
	+---------------------------------------------------+
	| C / M : 2004-10-25 11:58 / 2004-11-5
	+---------------------------------------------------+
	| Note : 
	+---------------------------------------------------+
	$array : get by getTree()
	$id : the node's id
	
	[����]
	
	$cateArray['info']['index']		= key
	$cateArray['info']['id']		= ���
	$cateArray['info']['pid']		= �ϼ�������
	$cateArray['info']['orderid']	= ������

	$cateArray['list'] ��1��ʼ��ӵı�úŵĽڵ����,������źͷ���orderid
	+---------------------------------------------------+
	| Log : 
	+---------------------------------------------------+
	
	+---------------------------------------------------+
	*/

	function GetChangeOrderID($array, $id)
	{	
		foreach($array as $k => $v)
		{	
			!$i[$v[$this->tableFids['pid']]] ? $i[$v[$this->tableFids['pid']]] = 1 : $i[$v[$this->tableFids['pid']]]++;
			
			$cateArrayTemp['list'][$v[$this->tableFids['pid']]][$i[$v[$this->tableFids['pid']]]]['id'] = $v[$this->tableFids['id']];
			$cateArrayTemp['list'][$v[$this->tableFids['pid']]][$i[$v[$this->tableFids['pid']]]]['orderid'] = $v[$this->tableFids['orderid']];

			if ($v[$this->tableFids['id']] == $id)
			{
				$cateArray['info']['index'] = $i[$v[$this->tableFids['pid']]];
				$cateArray['info']['id'] = $v[$this->tableFids['id']];
				$cateArray['info']['pid'] = $v[$this->tableFids['pid']];
				$cateArray['info']['orderid'] = $v[$this->tableFids['orderid']];
			}
		}

		$cateArray['list'] = $cateArrayTemp['list'][$cateArray['info']['pid']];

		return $cateArray;
	}

	/*
	+---------------------------------------------------+
	| getCategory ȡԭʼ��� (����ݿ�ȡ�б�)
	+---------------------------------------------------+
	| C / M : 2004-10-22 / 2004-11-13
	+---------------------------------------------------+
	| Note : 
	+---------------------------------------------------+
	get the list without make tree from database
	+---------------------------------------------------+
	| Log : 
	+---------------------------------------------------+
	2004-11-13 (walker)
	
	$type = '' get by id
	$type = '1' get by pid

	2004-11-8 (walker)
	if $id seted,then get the category which id is set.
	+---------------------------------------------------+
	*/
	
	function GetCategory($id = '', $type = '')
	{
		$sql  = "SELECT * ";
		$sql .= "FROM " . $this->table . " ";
		
		if (!$id) // get all
		{
			if ( $this->hide )
				$sql .= "WHERE " . $this->tableFids['hidden'] . " = 0 ";
			
			$sql .= "ORDER BY " . $this->tableFids['orderid'] . " DESC";

			$this->NDB->query($sql);

			$i = 0;

			while ($array = $this->NDB->fetch())
			{
				foreach ($array as $k => $v)
				{
					$cateArray[$i][$k] = $v;
				}

				$i++;
			}

			/*

			$rs = $this->NDB->Query($sql);
			
			$i = 0;
			
			while($rs->NextRecord())
			{
				
				$array = $rs->GetArray();

				foreach ($array as $k => $v)
				{
					$cateArray[$i][$k] = $v;
				}

				$i++;
			}
			*/
		}
		else // get by id or pid
		{
			!$type ?	$fids = $this->tableFids['id'] : $fids = $this->tableFids['pid'];

			$sql .= "WHERE " . $fids . " = '" . $id . "'";

			$rs = $this->NDB->query($sql);
			$cateArray = $this->NDB->fetch();
		}

		return $cateArray;
	}

	/*
	+---------------------------------------------------+
	| GetUnderside ȡ����µ��������
	+---------------------------------------------------+
	| C / M : 2004-10-25 11:58 / 2004-11-5
	+---------------------------------------------------+
	| Note : 
	+---------------------------------------------------+
	$array : This is the ordered's array (get by getTree())

	$id : The node's id

	$type : the type of the return array
		
	type = 1 only get the category's id list
	type = 2 get the category's all information
	type = 1 ȡ�����¼������ID(��(�Լ������)
	type = 2 ȡ�����¼���������(��(�Լ������)
	type = 3 ȡ��һ���¼������ID(����(�Լ������)
	type = 4 ȡ��һ���¼���������(����(�Լ������)
	+---------------------------------------------------+
	| Log : 
	+---------------------------------------------------+
	 2004-11-5 8:47 (walker)
	 �����ע��
	+---------------------------------------------------+
	*/

	function GetUnderside($array, $id, $type = 1)
	{

		
		$pidArray[] = $id;

		//start treeNode

		if ($type == 1)
			$getarr[0] = $array[$id][$this->tableFids['id']];
		elseif ( $type == 2 )
			$getarr[0] = $array[$id];

		// end treeNode
		
		if ( $type == 1 || $type == 2 )
		{
			foreach($array as $k => $v)
			{
				if (in_array($v[$this->tableFids['pid']], $pidArray))
				{
					$i++;
					$pidArray[] = $v[$this->tableFids['id']];
					
					if ( $type == 1 )
						$getarr[$i] = $v[$this->tableFids['id']];
					elseif ( $type == 2 )
						$getarr[$i] = $v;
				}
			}
		}
		elseif( $type == 3 || $type == 4 )
		{
			foreach($array as $k => $v)
			{
				if ( $v[$this->tableFids['pid']] == $id )
				{
					$i++;

					if ( $type == 3 )
						$getarr[$i] = $v[$this->tableFids['id']];
					elseif ( $type == 4 )
						$getarr[$i] = $v;
				}
			}
		}
		
		return $getarr;
	}

	/*
	+---------------------------------------------------+
	| Function Name : AddCategory
	+---------------------------------------------------+
	| C / M : 2004-10-22 / 
	+---------------------------------------------------+
	*/
	
	function AddCategory($categoryData)
	{
		if ( !is_array( $categoryData ) )
			return false;

		$fields	= @implode( ",", array_keys( $categoryData ) );
		$values	= "'" .  @implode( "','", $categoryData ) . "'";

		$sql  = "INSERT INTO " . $this->table . " ";
		$sql .= "( {$fields} ) ";
		$sql .= "VALUES ( {$values} )";

		$this->NDB->query( $sql );
	}

	/*
	+---------------------------------------------------+
	| Function Name : DelCategory
	+---------------------------------------------------+
	| C / M : 2004-10-22 / 
	+---------------------------------------------------+
	*/
	
	function DelCategory($array, $id)
	{
		$idArray = $this->GetUnderside($array, $id);

		$num = count($idArray);
		
		foreach ($idArray as $k => $v)
		{	
			$i++;

			$idArraySql .= $this->tableFids['id'] . " = " . $v;

			if ($i < $num)
				$idArraySql .= " OR ";
		}
		
		$sql  = "DELETE FROM " . $this->table . " ";
		$sql .= "WHERE " . $idArraySql;
		
		//$sql = $this->NBS->del($categoryData);
		
		$this->NDB->query($sql);

		return $idArray;
	}

	/*
	+---------------------------------------------------+
	| Function Name : UpdateCategory
	+---------------------------------------------------+
	| C / M : 2004-10-22 / 
	+---------------------------------------------------+
	*/
	
	function UpdateCategory($categoryData, $categoryCondition )
	{
		$array = $this->GetCategory();
		$array = $this->GetTree($array, 0, 0, 'category');
		$array = $this->GetUnderside($array, $categoryCondition[$this->tableFids['id']], $type = 1);
		// unset($array[0]);

		if ( in_array( $categoryData[$this->tableFids['pid']], $array ) )
		{
			return false;
		}
		else
		{
			foreach ( $categoryData as $key => $val )
			{
				$dataList[] = "{$key} = '{$val}'";
			}

			$set	= @implode( ',', $dataList );

			
			
			foreach ( $categoryCondition as $k => $v )
			{
				$conditionList[] = "$k = '$v'";
			}

			$where = @implode( " AND ", $conditionList );
			
			$sql  = "UPDATE " . $this->table . " ";
			$sql .= $set ? "SET {$set} " : null;
			$sql .= $where ? "WHERE {$where} " : null;

			$this->NDB->query($sql);

			return true;
		}
	}

	/*
	+---------------------------------------------------+
	| Function Name : getNodeMaxOrderID
	+---------------------------------------------------+
	| C / M : 2004-11-8 / 
	+---------------------------------------------------+
	| Note : 
	+---------------------------------------------------+
	 $pid : ����һ����
	 ���� : ȡ���������orderid ���Ҽ�1.
	+---------------------------------------------------+
	| Log : 
	+---------------------------------------------------+

	+---------------------------------------------------+
	*/

	function GetNodeMaxOrderID($pid)
	{
		$sql  = "SELECT MAX(" . $this->tableFids['orderid'] . ") + 1 AS " . $this->tableFids['orderid'] . " ";
		$sql .= "FROM " . $this->table . " ";
		$sql .= "WHERE " . $this->tableFids['pid'] . " = '" . $pid . "'";


		$this->NDB->query($sql);
		$info = array();
		$info = $this->NDB->fetch();
		
		/*
		$rs = $this->NDB->Query($sql);

		$rs->NextRecord();

		$rs->Get($this->tableFids['orderid']);
		*/

		return $info[$this->tableFids['orderid']];
	}
}
