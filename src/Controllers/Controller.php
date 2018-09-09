<?php
/**
 * @author: zmh
 * @date: 2018-07-12
 */
namespace Framework\Controllers;

use Framework\Di\DiAwareAbstract;
use Framework\Services\ServiceTrait;

/**
 * 控制器基类
 * @package Framework\Controllers
 */
class Controller extends DiAwareAbstract
{
    use ServiceTrait;
}