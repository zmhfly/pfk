<?php
/**
 * @author: zmh
 * @date: 2018-07-13
 */
namespace Framework\Providers;

use Framework\Config\Config;
use Framework\Container;
use Framework\Providers\Abstracts\ServiceProviderInterface;

class ConfigProvider implements ServiceProviderInterface
{
    public function register(Container $di)
    {
        // 获取当前的环境变量
        $env = getEnv("appEnv") ? getEnv("appEnv") :"development";
        $config = new Config($di->getConfigPath(),$env);
        return $config;
    }
}