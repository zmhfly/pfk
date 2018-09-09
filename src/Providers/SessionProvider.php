<?php
/**
 * @author: zmh
 * @date: 2018-09-04
 */
namespace Framework\Providers;

use Framework\Container;
use Framework\Providers\Abstracts\ServiceProviderInterface;
use Framework\Session\Session;

class SessionProvider implements ServiceProviderInterface
{
    public function register(Container $di)
    {
        $config = $di->config;
        return new Session($config);
    }
}