<?php
/**
 * @author: zmh
 * @date: 2018-09-30
 */
namespace Framework\Views;

use Framework\Di\DiAwareAbstract;

class View extends DiAwareAbstract
{
    /**
     * @var array
     */
    protected $_config;
    /**
     * @var string
     */
    protected $_controller;
    /**
     * @var string
     */
    protected $_action;
    /**
     * 视图模板路径
     * @var string
     */
    protected $_templatePath;
    /**
     * 是否启用布局
     * @var bool
     */
    protected $_layout = false;
    /**
     * 布局路径
     * @var string
     */
    protected $_layoutPath = "";
    protected $_data = [];

    public function __construct($config)
    {
        $this->_config = $config;
        $this->_layout = $config['layout'];
        $this->_layoutPath = $config['layoutPath'];
    }

    public function setTemplatePath($path)
    {
        $this->_templatePath = $path;
    }

    // 设置layout 布局
    public function layout($layout)
    {
        $this->_layoutPath = is_bool($layout) ? $this->_config['layoutPath'] : $layout;
        $this->_layout = is_bool($layout) ? $layout : false;
    }

    public function data($data = [])
    {

        $this->_data = array_merge($this->_data, $data);
    }

    public function assign($data = [], $path = "")
    {
        $this->_data = array_merge($this->_data, $data);
        $this->display($path);
    }

    public function display($path = "")
    {
        $this->_templatePath = $path ? $path : $this->_templatePath;
        header("Content-type: text/html; charset=utf-8");
        if ($this->_layout) {
            $this->data([
                $this->_config['layoutItem'] => $this->_config["templatePathRoot"].DS.$this->_templatePath.".".$this->_config['suffix']
            ]);
            extract($this->_data);
            include $this->_config['layoutPath'];
        } else {
            extract($this->_data);
            include $this->_config["templatePathRoot"].DS.$this->_templatePath.".".$this->_config['suffix'];
        }
    }
}