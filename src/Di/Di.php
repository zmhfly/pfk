<?php
/**
 * @author: zmh
 * @date: 2018-07-12
 */
namespace Framework\Di;

use Framework\Providers\Abstracts\ServiceProviderInterface;

/**
 * 依赖注入类
 * @package Framework\Di
 */
class Di implements \ArrayAccess
{
    private $_bindings = [];//服务列表
    private $_instances = [];//已经实例化的服务
    protected static $_default = null;

    public function __construct()
    {
        if (!self::$_default) {
            self::$_default = $this;
        }
    }

    public function set($name, $class)
    {
        if (!($class instanceof \Closure) && is_object($class)) {
            $this->_instances[$name] = $class;
        } else {
            $this->_bindings[$name] = $class;
        }
    }

    public function __set($name, $class)
    {
        if (!($class instanceof \Closure) && is_object($class)) {
            $this->_instances[$name] = $class;
        } else {
            $this->_bindings[$name] = $class;
        }
    }

    public function get($name, $params = null)
    {
        if (isset($this->_instances[$name])) {
            return $this->_instances[$name];
        }
        // 没有实例话就初始化
        if (!isset($this->_bindings[$name])) {
            throw new \Exception("1 未找到要实例化的类{$name}");
        }
        $concrete = $this->_bindings[$name];//对象具体注册内容
        $obj = null;
        //匿名函数方式
        if ($concrete instanceof \Closure) {
            if ($params) {
                $obj = call_user_func_array($concrete, $params);
            } else {
                $obj = call_user_func($concrete);
            }
        } else if (is_string($concrete)) {//字符串方式
            if (empty($params)) {
                $obj = new $concrete;
            } else {
                //带参数的类实例化，使用反射
                $class = new \ReflectionClass($concrete);
                $obj = $class->newInstanceArgs($params);
            }
        }
        //写入_instances列表，下次直接取回
        if ($obj) {
            $this->_instances[$name] = $obj;
        }
        // 如果实现了DiAwareInterface这个接口，自动注入
        if (is_object($obj)) {
            if ($obj instanceof DiAwareAbstract) {
                $obj->setDI($this);
            }
        }
        return $obj;
    }

    public function __get($name)
    {
        $params = null;
        $keyName = strtolower($name);
        if (isset($this->_instances[$keyName])) {
            return $this->_instances[$keyName];
        }
        if (!isset($this->_bindings[$keyName])) {
//            return null;
            throw new \Exception("2 未找到要实例化的类{$keyName}");
        }
        $concrete = $this->_bindings[$keyName];//对象具体注册内容
        $obj = null;
        //匿名函数方式
        if ($concrete instanceof \Closure) {
            $obj = call_user_func($concrete);
        } else if (is_string($concrete)) {//字符串方式
            if (empty($params)) {
                $obj = new $concrete;
            } else {
                //带参数的类实例化，使用反射
                $class = new \ReflectionClass($concrete);
                $obj = $class->newInstanceArgs($params);
            }
        }
        //写入_instances列表，下次直接取回
        if ($obj) {
            $this->_instances[$keyName] = $obj;
        }
        // 如果实现了DiAwareInterface这个接口，自动注入
        if (is_object($obj)) {
            if ($obj instanceof DiAwareAbstract) {
                $obj->setDI($this);
            }
        }
        return $obj;
    }

    // 直接注册为服务
    public function register($class)
    {
        if (is_object($class)) {
            $name = strtolower((new \ReflectionClass($class))->getName());
            $nameArr = explode("\\", $name);
            $name = end($nameArr);
            $this->_instances[$name] = $class;
            if ($class instanceof DiAwareAbstract) {
                $class->setDI($this);
            }
        }
    }

    /**
     * @return Di|null
     */
    public function getDefault()
    {
        return self::$_default;
    }

    public function offsetExists($offset)
    {
        return isset($this->_instances[$offset]) || isset($this->_bindings[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->_instances[$offset];
    }

    public function offsetSet($offset, $value)
    {
        return $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        unset($this->_bindings[$offset], $this->_instances[$offset]);
    }
}