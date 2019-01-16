<?php
/**
 * @author: zmh
 * @date: 2018-08-25
 */
namespace Framework\Providers;
use Framework\Container;
use Framework\Providers\Abstracts\ServiceProviderInterface;
use Framework\Router\Request;

class RequestProvider implements ServiceProviderInterface
{
    public function register(Container $di)
    {

        $runType = $di->getRunType();
        return new Request($runType);
    }
}