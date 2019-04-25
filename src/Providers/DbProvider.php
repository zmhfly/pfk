<?php
/**
 * @author: zmh
 * @date: 2019-04-24
 */
namespace Framework\Providers;

use Framework\Container;
use Framework\Db\DbConnection;
use Framework\Providers\Abstracts\ServiceProviderInterface;

class DbProvider implements ServiceProviderInterface
{
    public function register(Container $di)
    {
        $dbConfig = $di->config->get("database");
        return new DbConnection($dbConfig);
        // TODO: Implement register() method.
    }
}