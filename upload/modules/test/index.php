<?php
if($_POST)
{
    $name = md5(uniqid(rand()));

    $uploader = new Helper_Uploader();
    if(!$uploader->existsFile('imagefile'))
    {
        echo "网络出错";
        exit;
    }

    $file = $uploader->file('imagefile');
    if(!$file->isValid("JPG,JPEG,PNG,GIF,BMP"))
    {
        echo "文件类型不允许";
        exit;
    }

    $time = time();

    $img_dir = Core::getConfig('Img_Upload_Dir');
    $sub_dir = date('ymd',$time);
    $dest_dir = $img_dir.'/test/'.$sub_dir;
    Helper_Filesys::mkdirs($dest_dir);
    $ext = $file->extname() ?: 'jpg';
    $destImage=$dest_dir."/".$name.".".$ext;

    if(isset($_POST['is_compress']) && $_POST['is_compress'])
    {
        $image=new Helper_XImage($file->filepath());
        if(!$image->cropImageFixWidth($destImage, 500, 90)) //500定宽

        {
            echo "图片压缩出现异常";
            exit;
        }
    }
    else
    {
       if( !copy($file->filepath(), $destImage) ){
	    echo "图片移动出现异常";
            exit;
       }
	
	
	/*
	$image=new Helper_XImage($file->filepath());
        if(!$image->convertToJPEG($destImage,100)) //不压缩，只转换格式
        {
            echo "图片转换出现异常";
            exit;
        }
	*/
    }

    // $objCms = Core::ImportMl('Cms');
    // $imageId = $objCms->addCmsInfo($name, $time);
    list($width, $height) = getimagesize($destImage);
    $response = array();
    //$response['id'] = $imageId;
    $response['name'] = $name;
    $response['time'] = $time;
    $response['width'] = $width;
    $response['height'] = $height;
    $domain = Core::getConfig( "Domain" );
    $response['url'] = "http://www.{$domain}/test/{$sub_dir}/{$name}.{$ext}";
    $response['status'] = '200';
    echo json_encode($response);
    exit;
}
