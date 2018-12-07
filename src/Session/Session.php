<?php
/**
 * @author: zmh
 * @date: 2018-09-04
 */
namespace Framework\Session;

use Framework\Config\Config;
use Framework\Session\drivers\Redis;

class Session
{
    protected $_config;

    public function __construct(Config $config)
    {
        // 获取session 驱动
        $this->_config = $config;
        $session = $this->_config->getValue('session');
        if (!empty($session['type']) && $session['type'] == "redis") {
            $redis = $session['redis'] ?: $this->_config->getValue("redis");
            // 读取session驱动
            $class = false !== strpos($session['type'], '\\') ? $session['type'] : '\\Framework\Session\drivers\\'.ucwords($session['type']);
            // 检查驱动类
            if (!class_exists($class) || !session_set_save_handler(new $class($redis))) {
                throw new \Exception("未找到驱动".$class);
            }
        }
        // 直接启动session
        $this->start();
    }

    /**
     * 启动session
     */
    public function start()
    {
        session_start();
    }

    /**
     * 写入并结束当前session 会话
     */
    public function commit()
    {
        session_commit();
    }

    /**
     * 获取session 值
     *
     * @param $key
     *
     * @return mixed
     */
    public function get($key)
    {
        return $_SESSION[$key];
    }

    /**
     * 获取所有的session 值
     * @return mixed
     */
    public function getAll()
    {
        return $_SESSION;
    }

    /**
     * 设置session
     *
     * @param $key
     * @param $value
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * 销毁session
     * @return bool
     */
    public function destroy()
    {
        return session_destroy();
    }
}