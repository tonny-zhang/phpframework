<?php
$mlHello = Core::ImportMl('Hello');
$data = array(
	'name' => 'test',
	'age' => 21
);
//$ret = $mlHello->addData($data);
$data['id'] = 1;
$data['name'] = 'modify';
//$ret = $mlHello->updateData($data['id'],$data);
//$ret = $mlHello->deleData(1);
$ret = $mlHello->getList();
var_dump($ret);
?>