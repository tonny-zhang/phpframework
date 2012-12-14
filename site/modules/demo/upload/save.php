<?php

$objTempImage = Core::ImportMl('TempImage');
$result = $objTempImage->uploadImage($_FILES['imagefile']['tmp_name'], $_FILES['imagefile']['name'], 'test');

$return = array();

if ( $result == -1 )
{
	$return['status'] = 404;
	$return['info'] = '上传类型错误';
}
elseif ( $result == -2 )
{
	$return['status'] = 404;
	$return['info'] = '上传文件失败(-2)';
}
elseif ( $result == -3 )
{
	$return['status'] = 404;
	$return['info'] = '上传文件失败(-3)';
}
elseif ( is_array( $result ) )
{
	if ( $result['status'] == '404' || $result['status'] == '200' )
	{
		$return = $result;
	}
	else
	{
		$return['status'] = 404;
		$return['info'] = '上传文件失败(0)';
	}
}
else
{
	$return['status'] = 404;
	$return['info'] = '上传文件失败(1)';
}

echo json_encode( $return );
exit();