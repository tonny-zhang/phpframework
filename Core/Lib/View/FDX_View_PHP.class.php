<?php
/**
 * 原生PHP文件模板视图类
 *
 * @desc 使用PHP原生程序作为模板
 *
 * 支持继承
 */

class FDX_View_PHP
{
    /**
     * 视图分析类名
     *
     * @var string
     */
    protected $_parser_name = 'FDX_View_Php_Parser';

    /**
     * 视图文件所在目录
     *
     * @var string
     */
    protected $view_dir;

    /**
     * 模板变量
     *
     * @var array
     */
    protected $_vars = array();

    /**
     * 当前使用的分析器
     *
     * @var FDX_View_Php_Parser
     */
    protected $_parser;

    /**
     *
     * 返回头部信息
     */
    protected $response_header = true;

    /**
     * 字符编码
     */
    protected $response_charset = 'utf-8';

    /**
     * 构造函数
     *
     * @param array $config
     */
    function __construct(array $config = null)
    {
        if (is_array($config))
        {
            foreach ($config as $key => $value)
            {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * 指定模板变量
     *
     * @param string|array $key
     * @param mixed $data
     */
    function assign($key, $data = null)
    {
        if (is_array($key))
        {
            $this->_vars = array_merge($this->_vars, $key);
        }
        else
        {
            $this->_vars[$key] = $data;
        }
    }

    /**
     * 渲染视图
     *
     * @param string $viewname
     * @param array $vars
     * @param array $config
     */
    function display($viewname, $vars = null ,$return = false)
    {
        if ($this->response_header)
        {
            header('Content-Type: text/html; charset=' . $this->response_charset);
        }

        $view_dir = $this->view_dir;
        $filename = "{$view_dir}{$viewname}";

        if (file_exists($filename))
        {
            if (!is_array($vars))
            {
                $vars = $this->_vars;
            }
            if (is_null($this->_parser))
            {
                $parser_name = $this->_parser_name;
                $this->_parser = new $parser_name($view_dir);
            }
            $output = $this->_parser->assign($vars)->parse($filename);

        }
        else
        {
            $output = '';
        }

	if (!$return)
            echo $output;
	else
	    return $output;
    }
}

/**
 * FDX_View_Php_Parser 类实现了视图的分析
 *
 */

class FDX_View_Php_Parser
{
    /**
     * 视图文件扩展名
     *
     * @var string
     */
    protected $_extname;

    /**
     * 视图堆栈
     *
     * @var array
     */
    private $_stacks = array();

    /**
     * 当前处理的视图
     *
     * @var int
     */
    private $_current;

    /**
     * 视图变量
     *
     * @var array
     */
    protected $_vars;

    /**
     * 视图文件所在目录
     *
     * @var string
     */
    private $_view_dir;

    /**
     * 构造函数
     */
    function __construct($view_dir)
    {
        $this->_view_dir = $view_dir;
    }

    /**
     * 设置分析器已经指定的变量
     *
     * @param array $vars
     *
     */
    function assign(array $vars)
    {
        $this->_vars = $vars;
        return $this;
    }

    /**
     * 返回分析器使用的视图文件的扩展名
     *
     * @return string
     */
    function extname()
    {
        return $this->_extname;
    }

    /**
     * 分析一个视图文件并返回结果
     *
     * @param string $filename
     * @param string $view_id
     * @param array $inherited_stack
     *
     * @return string
     */
    function parse($filename, $view_id = null, array $inherited_stack = null)
    {
        if (!$view_id) $view_id = mt_rand();

        $stack = array(
                'id'            => $view_id,
                'contents'      => '',
                'extends'       => '',
                'blocks_stacks' => array(),
                'blocks'        => array(),
                'blocks_config' => array(),
                'nested_blocks' => array(),
        );
        array_push($this->_stacks, $stack);
        $this->_current = count($this->_stacks) - 1;
        unset($stack);

        ob_start();
        $this->_include($filename);
        $stack = $this->_stacks[$this->_current];
        $stack['contents'] = ob_get_clean();

        // 如果有继承视图，则用继承视图中定义的块内容替换当前视图的块内容
        if (is_array($inherited_stack))
        {
            foreach ($inherited_stack['blocks'] as $block_name => $contents)
            {
                if (isset($stack['blocks_config'][$block_name]))
                {
                    switch (strtolower($stack['blocks_config'][$block_name]))
                    {
                        case 'append':
                            $stack['blocks'][$block_name] .= $contents;
                            break;
                        case 'replace':
                        default:
                            $stack['blocks'][$block_name] = $contents;
                    }
                }
                else
                {
                    $stack['blocks'][$block_name] = $contents;
                }
            }
        }

        // 如果有嵌套 block，则替换内容
        while (list($child, $parent) = array_pop($stack['nested_blocks']))
        {
            $stack['blocks'][$parent] = str_replace("%block_contents_placeholder_{$child}_{$view_id}%",
                    $stack['blocks'][$child], $stack['blocks'][$parent]);
            unset($stack['blocks'][$child]);
        }

        // 保存对当前视图堆栈的修改
        $this->_stacks[$this->_current] = $stack;

        if ($stack['extends'])
        {
            // 如果有当前视图是从某个视图继承的，则载入继承视图
            $filename = "{$this->_view_dir}/{$stack['extends']}.{$this->_extname}";
            return $this->parse($filename, $view_id, $this->_stacks[$this->_current]);
        }
        else
        {
            // 最后一个视图一定是没有 extends 的
            $last = array_pop($this->_stacks);
            foreach ($last['blocks'] as $block_name => $contents)
            {
                $last['contents'] = str_replace("%block_contents_placeholder_{$block_name}_{$last['id']}%",
                        $contents, $last['contents']);
            }
            $this->_stacks = array();

            return $last['contents'];
        }
    }

    /**
     * 视图的继承
     *
     * @param string $tplname
     *
     * @access public
     */
    protected function _extends($tplname)
    {
        $this->_stacks[$this->_current]['extends'] = $tplname;
    }

    /**
     * 开始定义一个区块
     *
     * @param string $block_name
     * @param mixed $config
     *
     * @access public
     */
    protected function _block($block_name, $config = null)
    {
        $stack =& $this->_stacks[$this->_current];
        if (!empty($stack['blocks_stacks']))
        {
            // 如果存在嵌套的 block，则需要记录下嵌套的关系
            $last = $stack['blocks_stacks'][count($stack['blocks_stacks']) - 1];
            $stack['nested_blocks'][] = array($block_name, $last);
        }
        $this->_stacks[$this->_current]['blocks_config'][$block_name] = $config;
        array_push($stack['blocks_stacks'], $block_name);
        ob_start();
    }

    /**
     * 结束一个区块
     *
     * @access public
     */
    protected function _endblock()
    {
        $block_name = array_pop($this->_stacks[$this->_current]['blocks_stacks']);
        $this->_stacks[$this->_current]['blocks'][$block_name] = ob_get_clean();
        echo "%block_contents_placeholder_{$block_name}_{$this->_stacks[$this->_current]['id']}%";
    }

    /**
     * 载入一个视图片段
     *
     * @param string $element_name
     * @param array $vars
     *
     * @access public
     */
    protected function _element($element_name, array $vars = null)
    {
        $filename = "{$this->_view_dir}/_elements/{$element_name}_element.{$this->_extname}";
        $this->_include($filename, $vars);
    }

    /**
     * 载入视图文件
     */
    protected function _include($___filename, array $___vars = null)
    {
        $this->_extname = pathinfo($___filename, PATHINFO_EXTENSION);
        if (!empty($this->_vars)) extract($this->_vars);
        if (is_array($___vars)) extract($___vars);
        include $___filename;
    }
}