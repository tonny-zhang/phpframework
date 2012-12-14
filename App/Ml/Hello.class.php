<?php
class Ml_Hello {
	public function __construct(){
		$this->db = new Dt_Common('default','hello');
	}
	public function addData($data){
		return $this->db->addCommonInfo($data);	
	}
	public function updateData($id,$data){
		return $this->db->updateCommon($id,$data);
	}
	public function deleData($id){
		return $this->db->delCommonInfo($id);
	}
	public function getList(){
		return $this->db->getCommonList();
	}
}
?>