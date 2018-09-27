<?php
/**
 * @author: zmh
 * @date: 2018-09-09
 */
namespace Framework\Logger;

use Monolog\Formatter\LineFormatter;
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

        // the default date format is "Y-m-d H:i:s"
        $dateFormat = "Y-m-d H:i:s";
        // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
        $output = "[%datetime%] [%level_name%]:%message% %context% %extra%\n";
        // finally, create a formatter
        $formatter = new LineFormatter($output, $dateFormat);

        $dir = $this->_config["logPath"].DIRECTORY_SEPARATOR.$name.DIRECTORY_SEPARATOR;
        $date = date("Y-m-d", time());
        $logFile = $dir.$date.'.log';
//        @mkdir("/Users/zong/TEST_WWW/zmhfly/naiwa/log/aa",0777,true);

        // 实例化monolog
        $logger = new mLogger($name);

        // 生成一个程序处理对象
        $file_handler = new StreamHandler($logFile);
        // 定制 内容格式
        $file_handler->setFormatter($formatter);

        // 将程序处理对象放入记录器的处理程序堆栈
        $logger->pushHandler($file_handler);

        return $logger;
    }
}