<?php
/**
 * @author: zmh
 * @date: 2018-07-13
 */
namespace Framework\Providers\Abstracts;
use Framework\Container;

interface ServiceProviderInterface {

    public function register(Container $di);

}