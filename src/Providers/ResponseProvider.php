<?php
/**
 * @author: zmh
 * @date: 2018-08-25
 */
namespace Framework\Providers;

use Framework\Container;
use Framework\Providers\Abstracts\ServiceProviderInterface;
use Framework\Router\Response;

class ResponseProvider implements ServiceProviderInterface
{
    public function register(Container $di)
    {
        return new Response();
    }
}