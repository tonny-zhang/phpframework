<?php

define('MINIFY_MIN_DIR', dirname(__FILE__));


$min_libPath = dirname(__FILE__) . '/lib';
// setup include path
set_include_path($min_libPath . PATH_SEPARATOR . get_include_path());
$css[] = file_get_contents('E:\git_resource\phpframework\site\css\reset.css');
$css[] = file_get_contents('E:\git_resource\phpframework\site\css\ui.css');
include($min_libPath.'/Minify/CSS.php');
//$css = Minify_CSS_Compressor::process($css, array());
foreach ($css as $key => $value) {
	$code[] = Minify_CSS::minify($value, array('preserveComments'=>false));
}
$content = implode('', $code);
$content = gzencode($content,9);
$headers['Vary'] = 'Accept-Encoding';
$headers['Content-Encoding'] = 'gzip';
foreach ($headers as $name => $val) {
                header($name . ': ' . $val);
            }
echo $content;
?>