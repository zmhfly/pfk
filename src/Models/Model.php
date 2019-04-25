<?php
/**
 * @author: zmh
 * @date: 2019-04-23
 */
namespace Framework\Models;

use Framework\Di\DiAwareAbstract;

abstract class Model extends DiAwareAbstract
{
    public function __set($name, $value)
    {
        $this->$name = $value;
    }

    public function __get($name)
    {
        return $this->$name;
    }
    public abstract function getTable();
    private function getDb(){
    }
    public function find(){

    }
    public function findAll(){

    }
}