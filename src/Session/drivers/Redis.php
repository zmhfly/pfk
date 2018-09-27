<?php
/**
 * @author: zmh
 * @date: 2018-09-04
 */
namespace Framework\Session\drivers;

class Redis extends \SessionHandler
{
    /** @var \Redis */
    protected $_handler = null;
    protected $_config = [
        'host' => '127.0.0.1',
        // redis主机
        'port' => 6379,
        // redis端口
        'password' => '',
        // 密码
        'select' => 0,
        // 操作库
        'expire' => 3600,
        // 有效期(秒)
        'timeout' => 0,
        // 超时时间(秒)
        'persistent' => true,
        // 是否长连接
        'sessionName' => '',
        // sessionkey前缀
    ];

    public function __construct($config = [])
    {
        $this->config = array_merge($this->_config, $config);
    }

    /**
     * 创建session 连接
     *
     * @param string $save_path
     * @param string $sessionName
     *
     * @return bool
     * @throws \Exception
     */
    public function open($save_path, $sessionName)
    {
        // 检测php环境
        if (!extension_loaded('redis')) {
            throw new \Exception('请安装redis拓展');
        }
        $this->_handler = new \Redis;
        // 建立连接
        $func = $this->_config['persistent'] ? 'pconnect' : 'connect';
        $this->_handler->$func($this->_config['host'], $this->_config['port'], $this->_config['timeout']);
        if ('' != $this->_config['password']) {
            $this->_handler->auth($this->_config['password']);
        }
        if (0 != $this->_config['select']) {
            $this->_handler->select($this->_config['select']);
        }
        return true;
    }

    /**
     * 关闭Session
     * @access public
     */
    public function close()
    {
        $this->gc(ini_get('session.gc_maxlifetime'));
        $this->_handler->close();
        $this->handler = null;
        return true;
    }

    /**
     * 读取Session
     * @access public
     *
     * @param string $sessID
     *
     * @return string
     */
    public function read($sessID)
    {
        return (string) $this->_handler->get($this->_config['sessionName'].$sessID);
    }

    /**
     * 写入Session
     * @access public
     *
     * @param string $sessID
     * @param String $sessData
     *
     * @return bool
     */
    public function write($sessID, $sessData)
    {
        if ($this->_config['expire'] > 0) {
            return $this->_handler->setex($this->_config['sessionName'].$sessID, $this->_config['expire'], $sessData);
        } else {
            return $this->_handler->set($this->_config['sessionName'].$sessID, $sessData);
        }
    }

    /**
     * 删除Session
     * @access public
     *
     * @param string $sessID
     *
     * @return bool
     */
    public function destroy($sessID)
    {
        return $this->_handler->delete($this->_config['sessionName'].$sessID) > 0;
    }

    /**
     * Session 垃圾回收
     * @access public
     *
     * @param string $sessMaxLifeTime
     *
     * @return bool
     */
    public function gc($sessMaxLifeTime)
    {
        return true;
    }
}