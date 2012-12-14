<?php
/********************************
 *
 *  描述: Fandongxi 路由分发类
 *
 *  分发请求到相应的处理脚本
 *
 ********************************/

class FDX_Dispatcher
{

    public $moduleRootPath;

    public $module;

    public function  __construct($moduleRootPath = '')
    {
        $this->moduleRootPath = $moduleRootPath;
        $this->module = isset($_GET['mod']) ? $this->SafeModule( $_GET['mod'] ) : '';
    }

    public function ExplodeModule( $module )
    {
        return explode( '.', $module );
    }

    public function SafeModule( $module )
    {
        $module = trim( $module );
        $module = str_replace( array( '/', '\\', '../', '..\\' ), '', $module );
        $module = preg_replace( array( '/\.{2,}/is' ), '', $module );
        return $module;
    }

    public function BeforeRun()
    {

    }

    public function Run( $quoteData = array() )
    {
        $this->BeforeRun();

        $moduleList = $this->ExplodeModule( $this->module );

        $modulePath = $this->moduleRootPath . implode( '/', $moduleList ) . '.php';

        if ( !file_exists( $modulePath ) )
        {
            $modulePath = $this->moduleRootPath . implode( '/', $moduleList ) . '/index.php';
            if ( !file_exists( $modulePath ) )
            {
                header("HTTP/1.0 404 Not Found");
                exit();
            }
        }

        if(!empty ($quoteData))
        {
            @extract( $quoteData );
        }

        require_once $modulePath;
    }
}
