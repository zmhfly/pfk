<?php
/**
 * @author: zmh
 * @date: 2018-07-12
 */
namespace Framework\Config;

use Framework\Logger\Logger;

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
    private $appEnv = "development";

    public function __construct($configPath = '', $appEnv = 'development')
    {
        $this->configPath = $configPath;
        $this->defaultPath = __DIR__."/default.php";
        $this->appEnv = $appEnv;
        // 加载指定目录的配置文件
        $custom = $this->loadForPath($configPath);
        // 加载配置文件
        $config = $this->loadDefault();
        // 合并配置文件
        // 如果有重复，就用自定义的覆盖默认的配置
        self::$config = array_merge($config, $custom);
    }

    // 加载指定目录下的所有配置文件
    public function loadForPath($path)
    {
        // 加载指定环境变量下和default 下的数据
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path), \RecursiveIteratorIterator::SELF_FIRST);
        $config = [];
        foreach ($iterator as $item) {
            $filename = $item->getFilename();
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $custom = $this->loadForCustom($item->getPathname());
            if (!empty($custom)) {
                $name = str_replace('.php', '', $filename);
                $config[$name] = $custom;
            }
        }
        return $config;
    }

    /**
     * 加载自定义文件
     *
     * @param $includeFile
     *
     * @return array
     */
    public function loadForCustom($includeFile)
    {
        $file = include $includeFile;
        $default = isset($file['default']) ? $file['default'] : [];
        $custom = isset($file[$this->appEnv]) ? $file[$this->appEnv] : [];
        return array_merge($default, $custom);
    }

    // 加载配置文件
    public function load($name)
    {
        if (is_file($name)) {
            $includeFile = $name;
        } else if (is_file($configFile = $this->configPath.DS.$name.'.php')) {
            $includeFile = $configFile;
        } else if (is_file($configFile = $this->defaultPath.DS.$name.'.php')) {
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
        return $this->load($this->defaultPath);
    }

    // 获取配置项
    public function get($key)
    {
        $keys = explode(".", $key);
        $config = self::$config[array_shift($keys)];
        return $this->getDeepValue($config, $keys, ".");
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
        return isset(self::$config[$this->getConfigKey($this->defaultPath)]) ? self::$config[$this->getConfigKey($this->defaultPath)][$key] : "";
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
        if(is_string($array)){
            return $array;
        }
        $keys = is_array($keys) ? $keys : [$keys];
        $key = array_shift($keys);
        if (sizeof($keys) > 0 && isset($array[$key])) {
            return $this->getDeepValue($array[$key], $keys, $delimiter);
        } else {
            try {

                $logger = new Logger([
                    "logPath" => "/Users/zong/TEST_WWW/zmhfly/naiwa/log"
                ]);
                $logger->cc()->info($key);
                $a = isset($array[$key]) ? $array[$key] : (empty($key) ? $array : null);
            } catch(\Exception $e) {
                echo $key;
                echo "\n";
                echo $e->getMessage();
                exit();
            }
            return $a;
        }
    }
}