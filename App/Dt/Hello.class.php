<?php
/**
 * 数据层
 *
 */
class Dt_Hello extends Dt_Common {
	public function __construct() {
		$this->db = FDX_Model::getInstance()->getDb($db);
	}
	
	public function addCommonInfo($data){
		$ret = $this->addOne($data);
		if($ret && ($this->useMcList || $this->useMcTotal)) {
			$this->clearMc('common_list_total', 1);
			$this->clearMc('common_list', 1);
			$this->clearMc('common_list', 2);
		}
		return $ret;
	}
	
	public function delCommonInfo($id, $field = 'id') {
		$ret = $this->deleteFromDB($field, $id);
		if($ret && ($this->useMcInfo || $this->useMcList || $this->useMcTotal)) {
			$this->clearMc('common_info', $id);
			$this->clearMc('common_list_total', 1);
			$this->clearMc('common_list', 1);
			$this->clearMc('common_list', 2);
		}
		return $ret;
	}
	
	public function delCommonInfoNoCheck($field, $value){
		return $this->deleteFromDBNoCheck($field, $value);
	}
	
	public function updateCommon($id, $data, $field = 'id') {
		$ret = $this->db->Update(array('table' => $this->dbTable, 'data' => $data, 'condition' => array($field => (int)$id)));
		if($ret && ($this->useMcList || $this->useMcInfo)) {
			$this->clearMc('common_info', $id);
			$this->clearMc('common_list', 1);
			$this->clearMc('common_list', 2);
		}
		return $ret;
	}
	
	public function getCommonInfo($id, $field = 'id', $useMc = true) {
		if($useMc && $this->useMcInfo) {
			return $this->getCallbackTemplate('common_info', $id, array($this, __FUNCTION__), array($id, $field, false), true);
		}
		return $this->getOneFromDB($field, $id);
	}
	
	public function getCommonInfoBatch($ids, $isCallBack = true) {
		if(empty($ids)) return array();
		
		if($isCallBack || !$this->useMcInfo) {
			$sql = "select * from {$this->dbTable} where id in (".implode(',', $ids).")";
			return $this->db->query($sql);
		}
		return $this->getListTemplate('common_info', $ids, __FUNCTION__, 'id');
	}
	
	public function getCommonInfoNoCheck($field, $value) {
		return $this->getOneFromDBNoCheck($field, $value);
	}
	
	public function getCommonTotal($useMc = true) {
		if($useMc && $this->useMcTotal) {
			return $this->getCallbackTemplate('common_list_total', 1, array($this, __FUNCTION__), array(false), true);
		}
		
		$sql = "select count(*) from {$this->dbTable}";
		$ret = $this->db->query($sql);
		return isset($ret[0]) ? (int)$ret[0]['count(*)'] : 0;
	}
	
	public function getCommonTotalByField($field = 'id', $value = NULL) {
		$sql = "select count(*) from {$this->dbTable}";
		if($value !== NULL) $sql .= " where $field='".$this->db->escapeString($value)."'";

		$ret = $this->db->query($sql);
		return isset($ret[0]) ? (int)$ret[0]['count(*)'] : 0;
	}
	
	// 最广义的，尽量少用
	public function getCommonTotalByCfg($cond = array()) {
		$cfg = array();
		$cfg['table'] = $this->dbTable;
		$cfg['condition'] = $cond;
		$cfg['select'] = 'COUNT(*) AS total';
		$cfg['key'] = 'total';
		return $this->db->Get( $cfg );
	}
	
	public function getCommonList($offset = 0, $limit = 0, $orderField = 'id', $orderFlag = 'desc', $useMc = true){
		if($this->useMcList && $limit && $offset + $limit <= $this->pageSize && $useMc) {
			$mcKey = ($orderFlag == 'desc') ? 1 : 2;
			$list =  $this->getCallbackTemplate('common_list', $mcKey, array($this, __FUNCTION__), array($offset, $limit, $orderField, $orderFlag, false), true);
			return (is_array($list) && $list) ? array_slice($list, $offset, $limit) : array();
		}
		
		$cfg = array();
		$cfg['table'] = $this->dbTable;
		$cfg['order'] = "{$orderField} {$orderFlag}";
		if($offset + $limit <= $this->pageSize && $limit && $this->useMcList) {
			$cfg['offset'] = 0;
			$cfg['limit'] = $this->pageSize;
		} else {
			$cfg['offset'] = $offset;
			$cfg['limit'] = $limit;
		}
		return $this->db->GetList($cfg);
	}
	
	public function getCommonListByField($field = 'id', $value = NULL, $orderField = 'id', $orderFlag = 'desc', $offset = 0, $limit = 0) {
		$sql = "select * from {$this->dbTable}";
		if($value !== NULL) $sql .= " where $field='".$this->db->escapeString($value)."'";
		if($orderField) $sql .= " order by $orderField $orderFlag";
		if($limit) $sql .= " limit $offset, $limit";
		return $this->db->query($sql);
	}
	
	public function getCommonListByFieldIn($field = 'id', $value = NULL, $orderField = 'id', $orderFlag = 'desc') {
		$sql = "select * from {$this->dbTable}";
		if($value !== NULL) $sql .= " where $field in (".implode(',', $value).")";
		if($orderField) $sql .= " order by $orderField $orderFlag";
		return $this->db->query($sql);
	}
	
	// 最广义的，尽量少用
	public function getCommonListByCfg($cond = array(), $offset = 0, $limit = 0, $order = '') {
		return $this->db->GetList(array('table' => $this->dbTable, 'condition' => $cond, 'order' => $order, 'offset' => $offset, 'limit' => $limit));
	}
	
	// 最广义的，尽量少用
	public function getCommonListByCfgEx($cond = array(), $offset = 0, $limit = 0, $order = '') {
		return $this->db->GetList(array('table' => $this->dbTable, 'conditionExt' => $cond, 'order' => $order, 'offset' => $offset, 'limit' => $limit));
	}

	public function formatListKey($list, $key) {
		$result = array();
		if($list && is_array($list)) {
			foreach ($list as $v) {
				$result[$v[$key]] = $v;
			}
			return $result;
		}
		return $result;
	}
}