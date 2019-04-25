<?php
/**
 * @author: zmh
 * @date: 2019-04-24
 */
namespace Framework\Db;

class DbConnection
{
    public $DB;
    public function __construct($config)
    {
        if(!$this->DB){
            $connection = $config['connection'];
            new MysqlConnection($connection['host'],$connection['port'],$connection['username'],$connection['password'],$connection['dbname']);
        }
    }
}