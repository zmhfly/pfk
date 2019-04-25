<?php
/**
 * @author: zmh
 * @date: 2019-01-19
 */
namespace Framework\Console;

class Input
{
    private $argv;

    private $serviceName;
    public function __construct()
    {
        $this->getOptions();
    }

    /**
     * 解析命令行参数 $GLOBALS['argv'] 到一个数组<br>
     * 参数形式支持:        <br>
     * -e            <br>
     * -e <value>        <br>
     * --long-param        <br>
     * --long-param=<value><br>
     * --long-param <value><br>
     * <value>
     */
    function getOptions()
    {
        $params = $_SERVER['argv'];
        $tokens = [$params[0],$params[1]];
        print_r($tokens);exit();
        $result = [];
        while (null !== $token = array_shift($params)) {
            $token = trim($token);
            $optionKey = substr($token, 0, 2);
            if ($optionKey == '--' && $token = substr($token, 2)) {
                if (strpos($token, '=') !== false) {
                    list($pname, $value) = explode('=', $token, 2);
                    $result[$pname] = $value;
                } else {
                    $result[$token] = 1;
                }
            } else if ($optionKey[0] == '-' && $token = substr($token, 1)) {
                if (strpos($token, '=') !== false) {
                    list($pname, $value) = explode('=', $token, 2);
                    $result[$pname] = $value;
                } else {
                    $result[$token] = 1;
                }
            } else if ($token && (!in_array($token,$tokens))) {
                $result[$token] = 1;
            }else{
                $result[] = $token;
            }
        }
        $this->serviceName = isset($result) ? $result[0] :"";
        array_shift($result);
        $this->argv = $result;
        return $result;
    }
    public function getServiceName(){
        return $this->serviceName;
    }
}