<?php
class Dispatcher_Site extends FDX_Dispatcher
{
    function BeforeRun()
    {
        $firstModule = current( explode( '.', $this->module ) );
        if ( !$firstModule )
        {
            $this->module = 'index';
        }

        //自动登录
        if(!isLogin() && isset($_COOKIE['username']) && isset($_COOKIE['password']))
        {
            $objUser = Core::ImportMl('User');
            if($objUser->login($_COOKIE['username'],$_COOKIE['password'],1,true))
            {
            }
        }
    }
}
