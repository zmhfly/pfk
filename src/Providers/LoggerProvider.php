<?php
/**
 * @author: zmh
 * @date: 2018-09-09
 */
namespace Framework\Providers;

use Framework\Container;
use Framework\Logger\Logger;
use Framework\Providers\Abstracts\ServiceProviderInterface;

class LoggerProvider implements ServiceProviderInterface
{
    public function register(Container $di)
    {
        // 获取日志存放路径
        $config['logPath'] = $di->getBaseDir().DS.$di->config->getValue("logPath");
        return new Logger($config);
    }
}