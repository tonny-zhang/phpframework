<?php
/**
 * 数据层基类，主要包括公用函数的封装:
 * 对外提供简单的直接操作数据库的获取，删除操作；
 * 对继承的类提供关于MC的相关操作。
 *
 */
//include(realpath(dirname((dirname(__FILE__))) . "/Config/Cache.php"));
class Dt_Base {
	protected $db = null;
	protected $dbTable = null;
	protected $mcConfig = array();
	
	public function __construct() {
		
	}
	
	public function getListFromDB($key, $value){
		$value = intval($value);
		$sql = "select * from {$this->dbTable} where $key=$value";
		return $this->db->query($sql);
	}
	
	public function getListFromDBNoCheck($field, $value){
		$value = $this->db->escapeString($value);
		$sql = "select * from {$this->dbTable} where $field='$value'";
		return $this->db->query($sql);
	}
	
	public function getOneFromDB($key, $value){
		$value = intval($value);
		$sql = "select * from {$this->dbTable} where $key=$value";
		$data = $this->db->query($sql);
		return isset($data[0]) ? $data[0] : array();
	}
	
	public function getOneFromDBNoCheck($field, $value){
		$value = $this->db->escapeString($value);
		$sql = "select * from {$this->dbTable} where $field='$value'";
		$data = $this->db->query($sql);
		return isset($data[0]) ? $data[0] : array();
	}	
	
	public function deleteFromDB($key, $value){
		$value = intval($value);
		$sql = "delete from {$this->dbTable} where $key=$value";
		return $this->db->execute($sql);
	}
	
	public function deleteFromDBNoCheck($key, $value){
		$value = $this->db->escapeString($value);
		$sql = "delete from {$this->dbTable} where $key='$value'";
		return $this->db->execute($sql);
	}
	
	public function formatDataFromDB($data, $key){
		if (is_array($data)) {
			$result = array();
			foreach ($data as $v){
				$result[$v[$key]] = $v;
			}
			return $result;
		}
		return array();
	}
	
	public function addOne($data) {
		return $this->db->Add(array('table' => $this->dbTable, 'data' => $data));
	}
	
	public function updateOne($id, $data){
		return $this->db->Update(array('table' => $this->dbTable, 'data' => $data, 'condition' => array('id' => $id)));
	}
	
	protected function getListTemplate($type, $keys, $funcGetFromDB, $dbField = NULL, $useMc = 1, $setMcFlag = 1) {
		//创建MC对象
		$mcCfg = $this->mcConfig[$type];
		$objMc = $this->getMc($type);
		
		if(empty($keys)) {
			return false;
		}
		if(!is_array($keys)) {
			$keys = array($keys);
		}

		//从MC中取数据
		$result = $notInMcKeys = $data = array();
		$formatMcKeys = $this->getMcKeys($mcCfg['key'], $keys);
		//var_dump( 'mcKeys=', $formatMcKeys, 'usermc=', $useMc, "<hr/>" );
		if($useMc && $objMc){
			$data = $objMc->get($formatMcKeys);
		}
		if(empty($data)) {
			$notInMcKeys = $keys;
			foreach ($formatMcKeys as $k => $v){
				$result[$k] = array();
			}
		} else {
			foreach ($formatMcKeys as $k => $v){
				if(isset($data[$v])) {
					$result[$k] = $data[$v];
				} else {
					$notInMcKeys[] = $k;
					$result[$k] = array();
				}
			}
		}

		//从数据库中取数据，并回种缓存
		//var_dump( 'notinmc=', $notInMcKeys, 'setmcflag=', $setMcFlag, 'dbfield=', $dbField, "<hr/>");
		if ($notInMcKeys) {
			$dataFromDb = $this->$funcGetFromDB($notInMcKeys);
			if($dbField && !empty($dataFromDb)){
				//没有批量设置函数
				foreach ($dataFromDb as $notKey => $v){
					$key = !empty($v) ? $v[$dbField] : $notKey;
					
					$result[$key] = $v;
					if($setMcFlag && $objMc && $v){
						$objMc->set($formatMcKeys[$key], $v, 0, $mcCfg['lifetime']);
					}
				}
			} else {
				if(count($keys) === 1) {
					reset($keys);
					$key = current($keys);
					
					$result[$key] = $dataFromDb;
					if($setMcFlag && $objMc && $dataFromDb){
						$objMc->set($formatMcKeys[$key], $dataFromDb, 0, $mcCfg['lifetime']);
					}
				} else {
					new FDX_Exception('wrong use dt/base::getListTemplate function', -1);
				}
			}
		}
		
		return $result;
	}
	
	protected function getListTemplate2($type, $keys, $dbField = NULL, $funcCallback, $param = array(), $useMc = 1, $setMcFlag = 1) {
		//创建MC对象
		$mcCfg = $this->mcConfig[$type];
		$objMc = $this->getMc($type);
		
		if(empty($keys)) {
			return false;
		}
		if(!is_array($keys)) {
			$keys = array($keys);
		}

		//从MC中取数据
		$result = $notInMcKeys = $data = array();
		$formatMcKeys = $this->getMcKeys($mcCfg['key'], $keys);
		//var_dump( 'mcKeys=', $formatMcKeys, 'usermc=', $useMc, "<hr/>" );
		if($useMc && $objMc){
			$data = $objMc->get($formatMcKeys);
		}
		if(empty($data)) {
			$notInMcKeys = $keys;
			foreach ($formatMcKeys as $k => $v){
				$result[$k] = array();
			}
		} else {
			foreach ($formatMcKeys as $k => $v){
				if(isset($data[$v])) {
					$result[$k] = $data[$v];
				} else {
					$notInMcKeys[] = $k;
					$result[$k] = array();
				}
			}
		}

		//从数据库中取数据，并回种缓存
		//var_dump( 'notinmc=', $notInMcKeys, 'setmcflag=', $setMcFlag, 'dbfield=', $dbField, "<hr/>");
		if ($notInMcKeys) {
			$param[0] = $notInMcKeys;
			$dataFromDb = call_user_func_array($funcCallback, $param);
			if($dbField && !empty($dataFromDb)){
				//没有批量设置函数
				foreach ($dataFromDb as $notKey => $v){
					$key = !empty($v) ? $v[$dbField] : $notKey;
					
					$result[$key] = $v;
					if($setMcFlag && $objMc && $v){
						$objMc->set($formatMcKeys[$key], $v, 0, $mcCfg['lifetime']);
					}
				}
			} else {
				if(count($keys) === 1) {
					reset($keys);
					$key = current($keys);
					
					$result[$key] = $dataFromDb;
					if($setMcFlag && $objMc && $dataFromDb){
						$objMc->set($formatMcKeys[$key], $dataFromDb, 0, $mcCfg['lifetime']);
					}
				} else {
					new FDX_Exception('wrong use dt/base::getListTemplate function', -1);
				}
			}
		}
		
		return $result;
	}
	
	public function getCallbackTemplate($type, $key, $funcCallback, $param = array(), $setEmpty = false){
		$objMc = $this->getMc($type);
		if($objMc){
			$mckey = sprintf($this->mcConfig[$type]['key'], $key);
			$data = $objMc->get($mckey);
			//var_dump('data from mc=', $data, 'mckey=', $mckey, "<hr/>");
			if ($data === false){
				$data = call_user_func_array($funcCallback, $param);
				//var_dump( 'data from db=', $data, "<hr/>");
				if($data) {
					$objMc->set($mckey, $data, 0, $this->mcConfig[$type]['lifetime']);
				}
				if(empty($data) && $setEmpty) {
					$objMc->set($mckey, $data, 0, 300);
				}
			}
			return $data;
		}
		return false;
	}
	
	/**
	 * 清除一条MC记录
	 *
	 * @param unknown_type $objMc
	 * @param unknown_type $funcCreateKey
	 * @param unknown_type $format
	 * @param unknown_type $key
	 */
	protected function clearMc($type, $key = '', $objMc = NULL){
		//创建MC对象
		if(!$objMc) {
			$objMc = $this->getMc($type);
		}

		$formatMcKey = $this->getMcKeys($this->mcConfig[$type]['key'], $key);
		//var_dump($type,$key,$formatMcKey);
		if($objMc) {
			$objMc->remove($formatMcKey);
		}

		// 提交到缓存清理队列
		$this->rsyncMc($formatMcKey);
		return true;
	}
	
	protected function rsyncMc($name){
		static $s_objGearManClient = NULL;
		if ( $s_objGearManClient === NULL && class_exists('GearmanClient', false)) {
				$gearmanConfig = Core::GetConfig( 'gearman' );

				$s_objGearManClient = new GearmanClient();
				$s_objGearManClient->addServer($gearmanConfig['host'], $gearmanConfig['port']);
		}
		
		if ($s_objGearManClient) {
			$s_objGearManClient->doBackground('cache_del_all', $name);
		}
	}
	
	protected function setMc($type, $key, $data, $objMc = NULL){
		if(!$objMc){
			$objMc = $this->getMc($type);
		}
		if($objMc){
			if(is_array($key)) return false;
			$formatMcKey = $this->getMcKeys($this->mcConfig[$type]['key'], $key);
			$objMc->set($formatMcKey, $data, 0, $this->mcConfig[$type]['lifetime']);
		}
		return true;
	}
	
	protected function getMc($type){
		static $s_objMc = array();
		$mc = $this->mcConfig[$type]['mc'];
		if(!isset($s_objMc[$mc])){
			if(!$mc){
				return false;
			}
			global $new_cache_config;
			$s_objMc[$mc] = FDX_Cache::getInstance()->getCache($new_cache_config[$mc], $mc);
		}
		return $s_objMc[$mc];
	}

	/**
	 * 获得MC使用的键值
	 *
	 * @param unknown_type $format
	 * @param unknown_type $keys
	 * @return unknown
	 */
	protected function getMcKeys($format, $keys){
		if(is_array($keys)) {
			$ret = array();
			foreach ($keys as $v) {
				$ret[$v] = sprintf($format, $v);
			}
			return $ret;
		} else {
			return sprintf($format, $keys);
		}
	}
}
?>