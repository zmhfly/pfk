<?php
/**
 * @author: zmh
 * @date: 2018-07-12
 */
namespace Framework\Config;

/**
 * 配置文件管理基类
 * @package Framework\Config
 */
class Config
{
    public static $config = [];
    /**
     * 默认配置文件路径
     * @var string
     */
    private $defaultPath;
    /**
     * 指定配置文件路径
     * @var string
     */
    private $configPath;
    /**
     * 当前环境
     * @var string
     */
    private $appEnv = "default";

    public function __construct($configPath = '', $appEnv = 'default')
    {
        $this->configPath = $configPath;
        $this->defaultPath = __DIR__."/default.php";
        $this->appEnv = $appEnv;
        $this->loadDefault();
    }

    // 加载配置文件
    public function load($name)
    {
        if (is_file($name)) {
            $includeFile = $name;
        } else if (is_file($configFile = $this->configPath.DS.$this->appEnv.DS.$name.'.php')) {
            $includeFile = $configFile;
        } else if (is_file($configFile = $this->configPath.DS.$name.'.php')) {
            $includeFile = $configFile;
        } else {
            throw new \Exception('Config file '.$name.' no exist');
        }
        $key = $this->getConfigKey($includeFile);
        if (!isset(self::$config[$key])) {
            self::$config[$key] = include $includeFile;
        }
        return self::$config[$key];
    }

    /**
     * 加载默认配置文件
     */
    public function loadDefault()
    {
        $this->load($this->defaultPath);
    }

    // 获取配置项
    public function get($key)
    {
        $keys = explode(".", $key);
        $config = $this->load(array_shift($keys));
        return $this->getDeepValue($config, implode(".", $keys), ".");
    }

    /**
     * 获取单个key 对应的值
     *
     * @param      $key
     * @param null $value
     *
     * @return mixed
     */
    public function getValue($key, $value = null)
    {
        //获取默认配置文件里的值
        // 在系统默认配置中查找
        return self::$config[$this->getConfigKey($this->defaultPath)][$key];
    }

    // 获取所有配置信息
    public function all()
    {

        return self::$config;
    }

    /**
     * 获取加密后的key值
     *
     * @param $key
     *
     * @return bool|string
     */
    private function getConfigKey($key)
    {
        return base64_encode($key);
    }

    /**
     * 递归获取key 对应的值
     *
     * @param        $array
     * @param        $keys
     * @param string $delimiter
     *
     * @return null
     */
    private function getDeepValue($array, $keys, $delimiter = '.')
    {
        $keys = is_array($keys) ? $keys : [$keys];
        $key = array_shift($keys);
        if (sizeof($keys) > 0 && isset($array[$key])) {
            return $this->getDeepValue($array[$key], implode($delimiter, $keys), $delimiter);
        } else {
            return isset($array[$key]) ? $array[$key] : (isset($array[$this->appEnv][$key]) ? $array[$this->appEnv][$key] : null);
        }
    }
}