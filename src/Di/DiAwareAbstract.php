<?php
/**
 * @author: zmh
 * @date: 2018-07-13
 */
namespace Framework\Di;

class DiAwareAbstract
{
    protected static $di;

    /**
     * @param Di $di
     */
    public function setDI(Di $di)
    {
        static::$di = $di->getDefault();
    }

    /**
     * @return Di
     */
    public function getDI()
    {
        return static::$di;
    }

    public function __set($name, $value)
    {
        $this->getDI()->$name = $value;
    }

    public function __get($name)
    {
        return $this->getDI()->$name;
    }
}