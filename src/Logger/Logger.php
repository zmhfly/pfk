<?php
/**
 * @author: zmh
 * @date: 2018-09-09
 */
namespace Framework\Logger;

use Monolog\Logger as mLogger;
use Monolog\Handler\StreamHandler;

class Logger
{
    protected $_config = [
        'logPath' => __DIR__
    ];

    public function __construct($config)
    {
        $this->_config = $config;
    }

    public function __call($name, $args)
    {

        $dir = $this->_config["logPath"].DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR;
        $date = date("Y-m-d", time());
        $logFile = $dir.$date.'.log';
        // 实例化monolog
        $logger = new mLogger($name);
        // 生成一个程序处理对象
        $file_handler = new StreamHandler($logFile);
        // 将程序处理对象放入记录器的处理程序堆栈
        $logger->pushHandler($file_handler);
        return $logger;
    }
}