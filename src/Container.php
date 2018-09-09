<?php
/**
 * @author: zmh
 * @date: 2018-07-12
 */
namespace Framework;

use Framework\Config\Config;
use Framework\Controllers\Controller;
use Framework\Di\Di;
use Framework\Providers\Abstracts\ServiceProviderInterface;
use Framework\Providers\ConfigProvider;
use Framework\Providers\LoggerProvider;
use Framework\Providers\RequestProvider;
use Framework\Providers\ResponseProvider;
use Framework\Providers\RouterProvider;
use Framework\Providers\SessionProvider;
use Framework\Router\Request;
use Framework\Router\Router;
use Framework\Session\Session;

/**
 * Class Container
 * @property Config  $config
 * @property Router  $router
 * @property Request $request
 * @property Session $session
 * @package Framework
 */
class Container extends Di
{
    protected $baseDir;
    /**
     *  框架一级的服务,在容器初始化的时候注册
     * @var array
     */
    protected $_providers = [
        ConfigProvider::class,
        RouterProvider::class,
        RequestProvider::class,
        ResponseProvider::class,
        SessionProvider::class,
        LoggerProvider::class
    ];
    protected $_deafult = [
        Controller::class
    ];

    public function __construct()
    {
        parent::__construct();
        $this->registerServices();
    }

    public function run($baseDir = "")
    {
        $this->setBaseDir($baseDir);
        $this->registerProviders();
        try {
            // 解析路由
            $router = $this->router->parse();
            //将返回的变量 添加到request
            $this->request->setParams($router['params']);
            // 初始化session
            // 前置操作
            // 分发路由
            $this->router->dispatcher();
            // 路由缓存 有需要
            // 后置操作
        } catch(\Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }

    public function registerProviders()
    {
        foreach ($this->_providers as $v) {
            $obj = new $v;
            if ($obj instanceof ServiceProviderInterface) {
                $this->register($obj->register($this));
            }
        }
        // 获取配置文件路径
        $providers = $this->config->get("app.providers");
        if ($providers) {
            foreach ($providers as $v) {
                $obj = new $v;
                if ($obj instanceof ServiceProviderInterface) {
                    $this->register($obj->register($this));
                }
            }
        }
    }

    // 注册服务
    public function registerServices()
    {
        foreach ($this->_deafult as $v) {
            $this->register(new $v);
        }
    }

    public function setBaseDir($dir)
    {
        $this->baseDir = $dir;
    }

    /**
     * 获取根目录
     * @return mixed
     */
    public function getBaseDir(){
        return $this->baseDir;
    }
    /**
     * 设置项目路径
     * @return string
     */
    public function appPath()
    {
        return $this->baseDir.DS."app";
    }

    /**
     * 获取当前环境信息
     * @return string
     */
    public function getEnv()
    {
        $default = 'development';
        // 1. 读环境变量
        $value = getenv('APP_ENV');
        // 2. 使用默认
        $value || $value = $default;
        // 3. 同步属性并返回
        return strtolower($value);
    }

    public function getConfigPath()
    {
        return $this->appPath().DS."configs";
    }

    /**
     * 是否为开发环境
     * @return bool
     */
    public function isDevelopment()
    {
        return $this->getEnv() === "development";
    }

    /**
     * 是否为生产环境
     * @return bool
     */
    public function isProduction()
    {
        return $this->getEnv() === "production";
    }

    /**
     * 是否为测试环境
     * @return bool
     */
    public function isTesting()
    {
        return $this->getEnv() === "testing";
    }
}