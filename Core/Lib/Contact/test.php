<?php
/*
		"163.com"=>array("file"=>"class.163Http.php","class"=>"http163"),
		"gmail.com"=>array("file"=>"class.gmail.php","class"=>"Gmail"),
		"126.com"=>array("file"=>"class.126Http.php","class"=>"http126"),
		"sina.com"=>array("file"=>"class.sinaHttp.php","class"=>"sinaHttp"),
		"yahoo.cn"=>array("file"=>"class.yahooHttp.php","class"=>"yahooHttp"),
		"tom.com"=>array("file"=>"class.tomHttp.php","class"=>"tomHttp"),
		"msn"=>array("file"=>"class.msn.php","class"=>"msnHttp")
*/
error_reporting(7);
define("TIMEOUT",100);
include("GetAddress.php");
print_r(GetAddress::Instance("songchuans@gmail.com","5825645","gmail.com")->GetList());
?>