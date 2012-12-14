<?php

/*
 * Fandongxi 异常处理类
 *
*/

class FDX_Exception extends Exception {
    /**
     * 构造函数
     *
     * @param string $message 错误消息
     * @param int $code 错误代码
     */

    function __construct($message, $errcode = 0) {
        parent::__construct($message, $errcode);
    }
    
    /**
     * 输出异常的详细信息和调用堆栈
     *
     */

    static function dump(Exception $ex) {
        $out = "exception '" . get_class($ex) . "'";
        if ($ex->getMessage() != '') {
            $out .= " with message '" . $ex->getMessage() . "'";
        }

        $out .= ' in ' . $ex->getFile() . ':' . $ex->getLine() . "\n\n";
        $out .= $ex->getTraceAsString();

        if (ini_get('html_errors')) {
            echo nl2br(htmlspecialchars($out));
        }
        else {
            echo $out;
        }
    }
}
