<?php
$_SESSION['test'] = '{test Session,session is ok}';
Common::PageOut('index.html',array('val'=>'hello world'));
?>