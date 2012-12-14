<?php

/**
 * Request 请求操作访问类
 *
 */
class FDX_Request {
    /**
     * @var object 对象单例
     */
    private static $_instance = NULL;

    /**
     * 保证对象不被clone
     */
    private function __clone() {

    }

    /**
     * 构造函数
     */
    private function __construct() {

    }


    /**
     * 获取对象唯一实例
     *
     * @param void
     * @return FDX_Request 返回本对象实例
     */
    public static function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     * 获取GET传递过来的参数
     *
     * @param string $key 需要获取的Key名，空则返回所有数据
     * @return mixed
     */
    public function getQuery($key = '') {
        return FDX_InputFilter::getData(FDX_InputFilter::stripslashes($_GET), $key, FDX_InputFilter::FILTER_TEXT);
    }

    /**
     * 获取POST传递过来的参数
     *
     * @param string $key 需要获取的Key名，空则返回所有数据
     * @return mixed
     */
    public function getPost($key = '') {
        return FDX_InputFilter::getData(FDX_InputFilter::stripslashes($_POST), $key, FDX_InputFilter::FILTER_TEXT);
    }

    /**
     * 获取GET/POST传递过来的参数
     *
     * @param string $key 需要获取的Key名，空则返回所有数据
     * @return mixed
     */
    public function getParam($key) {
        if (isset($_GET[$key])) {
            return $this->getQuery($key);
        }
        if (isset($_POST[$key])) {
            return $this->getPost($key);
        }
        return false;
    }


    /**
     * 获取REQUEST传递过来的参数
     *
     * @param string $key 需要获取的Key名，空则返回所有数据
     * @return mixed
     */
    public function getRequest($key = '') {
        return FDX_InputFilter::getData(FDX_InputFilter::stripslashes($_REQUEST), $key, FDX_InputFilter::FILTER_TEXT);
    }

    /**
     * 获取COOKIE数据
     *
     * @param string $key 需要获取的Key名，空则返回所有数据
     * @return mixed
     */
    public function getCookie($key = '') {
        return FDX_InputFilter::getData(FDX_InputFilter::stripslashes($_COOKIE), $key, FDX_InputFilter::FILTER_UNSAFE_RAW);
    }

    /**
     * 获取SERVER数据
     *
     * @param string $key 需要获取的Key名，空则返回所有数据
     * @return mixed
     */
    public function getServer($key = '') {
        return FDX_InputFilter::getData(FDX_InputFilter::stripslashes($_SERVER), $key, FDX_InputFilter::FILTER_UNSAFE_RAW);
    }

    /**
     * 获取ENV数据
     *
     * @param string $key 需要获取的Key名，空则返回所有数据
     * @return mixed
     */
    public function getEnv($key = '') {
        return FDX_InputFilter::getData(FDX_InputFilter::stripslashes($_ENV), $key, FDX_InputFilter::FILTER_UNSAFE_RAW);
    }

    /**
     * 获取FILES数据
     *
     * @param string $key 需要获取的Key名，空则返回所有数据
     * @return mixed
     */
    public function getFile($key = '') {
        if ($key == '') {
            return $_FILES;
        }
        if (!isset($_FILES[$key])) {
            return '';
        }
        return $_FILES[$key];
    }




    //----------------------------
    //
    //   辅助Request数据操作方法
    //
    //----------------------------

    /**
     * 是否是一个GET请求
     *
     * @param void
     * @return bool
     */
    public function isGet() {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            return false;
        }
        return true;
    }

    /**
     * 是否是一个POST请求
     *
     * @param void
     * @return bool
     */
    public function isPost() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            return false;
        }
        return true;
    }


    /**
     * 读取当前站点的URL地址
     *
     * @return string URL
     */
    public function getUrl() {
        if (preg_match('/^http/', $_SERVER['REQUEST_URI'])) {
            return $_SERVER['REQUEST_URI'];
        }
        $standardPort = '80';
        $protocol = 'http';

        $host = explode(":", $_SERVER['HTTP_HOST']);
        if (count($host) == 1) {
            $host[] = $_SERVER['SERVER_PORT'];
        }
        if ($host[1] == $standardPort || empty($host[1])) {
            unset($host[1]);
        }
        $uriPrefix = $protocol.'://'.implode(':', $host);

        return  $uriPrefix . $_SERVER['REQUEST_URI'];
    }


    /**
     * 获取当前客户端IP地址
     *
     * @return string 当前访问的客户端IP
     */
    public function getIp() {
        return $this->getClientIp();
    }

    /**
     * 获取当前客户端IP地址
     *
     * @return string 当前访问的客户端IP
     */
    public function getClientIp() {
        if (isset ( $_SERVER ['HTTP_CLIENT_IP'] ) and ! empty ( $_SERVER ['HTTP_CLIENT_IP'] )) {
            return ( $_SERVER ['HTTP_CLIENT_IP'] );
        }
        if (isset ( $_SERVER ['HTTP_X_FORWARDED_FOR'] ) and ! empty ( $_SERVER ['HTTP_X_FORWARDED_FOR'] )) {
            $ip = strtok ( $_SERVER ['HTTP_X_FORWARDED_FOR'], ',' );
            do {
                $ip = ip2long ( $ip );
                //-------------------
                // skip private ip ranges
                //-------------------
                // 10.0.0.0 - 10.255.255.255
                // 172.16.0.0 - 172.31.255.255
                // 192.168.0.0 - 192.168.255.255
                // 127.0.0.1, 255.255.255.255, 0.0.0.0
                //-------------------
                if (! (($ip == 0) or ($ip == 0xFFFFFFFF) or ($ip == 0x7F000001) or (($ip >= 0x0A000000) and ($ip <= 0x0AFFFFFF)) or
                        (($ip >= 0xC0A8FFFF) and ($ip <= 0xC0A80000)) or (($ip >= 0xAC1FFFFF) and ($ip <= 0xAC100000)))) {
                    return long2ip ( $ip );
                }
            } while ( $ip = strtok ( ',' ) );
        }
        if (isset ( $_SERVER ['HTTP_PROXY_USER'] ) and ! empty ( $_SERVER ['HTTP_PROXY_USER'] )) {
            return ( $_SERVER ['HTTP_PROXY_USER'] );
        }
        if (isset ( $_SERVER ['REMOTE_ADDR'] ) and ! empty ( $_SERVER ['REMOTE_ADDR'] )) {
            return ( $_SERVER ['REMOTE_ADDR'] );
        } else {
            return "0.0.0.0";
        }
    }


    /**
     * 获取访问来源
     *
     * @return string
     */
    public function getReferer() {
        return $_SERVER['HTTP_REFERER'];
    }

    /**
     * 获取用户访问Host
     *
     * @return string
     */
    public function getHost() {
        return isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
    }

    /**
     * getScriptName
     * Returns current script name.
     *
     * @return string
     */
    public function getScriptName() {
        return isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : (isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : '');
    }

}