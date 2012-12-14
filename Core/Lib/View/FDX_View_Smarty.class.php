<?php

/**
 * Smarty文件模板视图类
 *
 * @desc 针对 Smarty Template 的模板View的模板加载
 */
class FDX_View_Smarty {

    /**
     * @var array Smarty对象参数
     */
    public $config = array();
    /**
     * @var bool 是否调试模式
     */
    public $debug = false;

    /**
     * 构造函数
     *
     * @param object $controller 控制器对象
     *
     * @param array $params 需要传递的选项参数
     *
     * 参数说明：
     * $params = array(
     'template_dir'		=> 'view/',					//指定模板文件存放目录，缺省为 view 目录
     'cache_dir'			=> 'cache/smarty/cache',	//指定缓存文件存放目录
     'compile_dir'		=> 'cache/smarty',			//Smarty编译目录
     'config_dir'		=> '',						//Smarty配置文件目录, 缺省为空
     'left_delimiter'	=> '{{',					//模板变量的左边界定符, 缺省为 {{
     'right_delimiter'	=> '}}',					//模板变量的右边界定符，缺省为 }}
     );

     */

    public function __construct($config) {

        $this->config = $config;
        
    }

    /**
     * 设置模板相应的调试模式
     *
     * @param bool $debug 是否调试模式，true or false
     * @return void
     */
    public function setDebug($debug = false) {
        $this->debug = $debug;
    }

    /**
     * 解析处理一个模板文件
     *
     * @param  string $template  模板文件名
     * @param  array  $vars 需要给模板变量赋值的变量
     * @return void
     */
    public function display($template, $vars) {
        //加载Smarty
        load_plugin("Smarty/Smarty");
        $smarty = new Smarty;

        //判断是否传递配置参数
        if ( empty($this->config) || !isset($this->config['compile_dir']) || !isset($this->config['config_dir']) || !isset($this->config['cache_dir'])) {
            throw new FDX_Exception("未设置smarty相关参数");
        }

        //设置Smarty参数
        $smarty->template_dir 	 = !isset($this->config['template_dir']) ? FDX_VIEW_DIR : $this->config['template_dir'];
        $smarty->compile_dir  	 = $this->config['compile_dir'];
        $smarty->config_dir   	 = $this->config['config_dir'];
        $smarty->cache_dir    	 = $this->config['cache_dir'];
        $smarty->left_delimiter  = !isset($this->config['left_delimiter']) ? "{{" : $this->config['left_delimiter'];
        $smarty->right_delimiter = !isset($this->config['right_delimiter']) ? "}}" : $this->config['right_delimiter'];
        $smarty->debugging	 = $this->debug;

        //检查模板文件
        $filePath = FDX_VIEW_DIR.$template;
        if(!is_file($filePath) || !is_readable($filePath)) {
            throw new FDX_Exception("模板文件 ". $filePath ." 不存在或不可读");
        }
        //设置模板变量
        if (!empty($vars)) {
            foreach($vars as $key => $value) {
                $smarty->assign($key, $value);
            }
        }
        $smarty->display($filePath);
    }
}
