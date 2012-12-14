<?php
include "msn.class.php";
class msnHttp{
	public function getAddressList($uname,$upass){
		$msn	= new MSN('MSNP15');
		if(!$msn->connect($uname,$upass)){
			die("connect error!");
			}
		
		$rs	= $msn->getMembershipList();
		return $rs;
		//return $this->GetEmail($rs);
		
	}
	public function GetEmail($rs){
		if(!is_array($rs))return array();
		$result=array();
		$key_arr	= array_keys($rs);
		foreach($key_arr as $k=>$a){
			$names=array_keys($rs[$a]);
			foreach($names as $kk=>$aa){
				$result[]	= $aa."@".$a;
			}
		}
		return $result;
	}
}
?>