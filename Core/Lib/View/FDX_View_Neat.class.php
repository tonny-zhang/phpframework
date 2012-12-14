<?php

/**
 * NeatTemplate文件模板视图类
 *
 * @desc 针对 Neat Template 的模板View的模板加载
 */
class FDX_View_Neat {

    /**
     * @var array Smarty对象参数
     */
    public $config = array();
    public $Template = false;

    /**
     * 构造函数
     *
     * @param object $controller 控制器对象
     *
     * @param array $params 需要传递的选项参数
     *
     */

    public function __construct($config) {

        $this->config = $config;

        Core::LoadLib( 'NeatTemplate/NEATTemplate.class.php' );

        $this->Template = new NEATTemplate();
        $this->Template->SetCachePath( $this->config['template_cache_path'] );
    }

    /**
     * 解析处理一个模板文件
     *
     * @param  string $template  模板文件名
     * @param  array  $vars 需要给模板变量赋值的变量
     * @return void
     */
    public function display($template, $vars) {

        $this->Template->ST( $this->config['template_path'] . $template );
        $this->Template->SV( $vars );

        $this->Template->OP();
    }

    public function result($template, $vars){
        $this->Template->ST( $this->config['template_path'] . $template );
        $this->Template->SV( $vars );

        return $this->Template->RS();
    }

    public function compile($template){
        $this->Template->ST( $this->config['template_path'] . $template );

        return $this->Template->Compile();
    }

    public function out($template, $vars, $parent = 'main', $parentVars = array()){
        if ( $parent )
        {
            $result = $this->result($template, $vars);

            $parentVars['module'] = $result;
            $parentVars['menu_list'] = $menuConfigList;
            $parentVars['is_login'] = $loginInfo ? 1 : 0;
            $parentVars['login_user_name'] = $loginInfo['user_name'];

            $this->display($parent . ".html", $parentVars);
        }
        else
        {
            $this->display($template, $vars);
        }
    }
}
