<?php
/**
 * @author: zmh
 * @date: 2018-08-14
 */
namespace Framework\Providers;

use Framework\Config\Config;
use Framework\Container;
use Framework\Providers\Abstracts\ServiceProviderInterface;
use Framework\Router\Router;

class RouterProvider implements ServiceProviderInterface
{
    public function register(Container $di)
    {
        $config = $di->config;
        return new Router($config);
    }
}