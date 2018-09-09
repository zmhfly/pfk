<?php
/**
 * @author: zmh
 * @date: 2018-07-12
 */
namespace Framework\Router;

use Framework\Config\Config;

/**
 * 路由解析基类
 * @package Framework\Router
 */
class Router
{
    protected $_config;
    protected $routerData;

    public function __construct(Config $config)
    {
        $this->_config = $config;
    }

    // 解析路由
    public function parse()
    {
        $roterData = [];
        if ($this->_config->getValue("routeType") == 'pathInfo') {
            $roterData = $this->pathInfo();
        }
        $this->routerData = $roterData;
        return $roterData;
    }

    public function pathInfo()
    {
        $data = [
            'm' => ucfirst($this->_config->getValue("defaultModule")),
            'c' => ucfirst($this->_config->getValue("defaultController")),
            'a' => $this->_config->getValue("defaultMethod"),
            'params' => []
        ];
        if (isset($_SERVER['PATH_INFO'])) {
            $uri = parse_url($_SERVER['PATH_INFO'], PHP_URL_PATH);
            $uri = explode("/", $uri);
            $uri = array_filter($uri); //去空数组
            $count = count($uri); //传递过来的参数个数
            switch ($count) {
                case 0:
                    break;
                case 1:
                    $data['m'] = ucfirst($uri[1]);
                    break;
                case 2:
                    $data['c'] = ucfirst($uri[1]);
                    $data['a'] = $uri[2];
                    break;
                default:
                    $data['m'] = ucfirst($uri[1]);
                    $data['c'] = ucfirst($uri[2]);
                    $data['a'] = $uri[3];
                    array_shift($uri);
                    array_shift($uri);
                    array_shift($uri);
                    $data['params'] = [];
                    foreach ($uri as $k => $v) {
                        if ($k % 2 == 0 && isset($uri[$k + 1])) {
                            $data['params'][$v] = $uri[$k + 1];
                            $_REQUEST[$v] = $uri[$k + 1];
                        }
                    }
                    break;
            }
        }
        return $data;
    }

    // 分发路由
    public function dispatcher()
    {
        if (is_array($this->routerData)) {
            $controller = $this->_config->getValue("defaultNameSapce")."\\".$this->routerData['m']."\\".$this->_config->getValue("defaultControllerPath")."\\".$this->routerData['c']."Controller";
            $controller = new $controller();
            $action = $this->routerData['a'];
            if ($controller && method_exists($controller, $action)) {
                call_user_func([
                    $controller,
                    $action
                ], $this->routerData['params']);
            } else {
                throw new \Exception("方法:".$action." 不存在");
            }
        }
    }
}