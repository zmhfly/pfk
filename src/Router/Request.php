<?php
/**
 * @author: zmh
 * @date: 2018-07-12
 */
namespace Framework\Router;

/**
 * web 访问入参处理
 * @package Framework\Router
 */
class Request
{
    private $pathInfo;
    private $queryString;
    private $params;
    private $get;
    private $post;
    private $_header;

    private $_moudle;
    private $_controller;
    private $_action;

    public function __construct()
    {
        $this->pathInfo = isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']: [];
        $this->queryString = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING']:[];
        $this->get = $_GET;
        $this->post = $_POST;
        $this->getAllHeaders();
    }

    public function setParams($params)
    {
        $this->get = array_merge($this->get,$params);
        $this->post = array_merge($this->post,$params);
        $this->params = $params;
        return $this;
    }

    public function setPathInfo($pathInfo)
    {
        $this->pathInfo = $pathInfo;
        return $this;
    }

    public function setQueryString($queryString)
    {
        $this->queryString = $queryString;
        return $this;
    }

    private function getAllHeaders()
    {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $this->_header[str_replace(' ', '-', str_replace('_', ' ', substr($name, 5)))] = $value;
            }
        }
        return $this;
    }

    public function get(){
        return $this->get;
    }
    public function post(){
        return $this->post;
    }
    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    public function getQueryString()
    {
        return $this->queryString;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setModule($module){

        $this->_moudle = $module;
    }
    public function getModule(){
        return $this->_moudle;

    }

    public function setController($controller){
        $this->_controller = $controller;
    }
    /**
     * 获取当前的控制器
     * @return mixed
     */
    public function getController(){
        return $this->_controller;
    }

    public function setAction($action){
        $this->_action = $action;
    }
    public function getAction(){
        return $this->_action;

    }
    /**
     * @param string $error
     * @param string $raw
     * @param bool   $assoc
     *
     * @return array|\stdClass
     */
    private function generateError($error, $raw, $assoc = false)
    {
        if ($assoc) {
            return [
                '_raw' => $raw,
                '_error' => $error
            ];
        }
        $std = new \stdClass();
        $std->_raw = $raw;
        $std->_error = $error;
        return $std;
    }

    /**
     * 获取原始的入参
     */
    public function getRawBody()
    {
        return file_get_contents("php://input");
    }

    /**
     * 转为JSON字符串
     *
     * @param array|\stdClass $data
     * @param string          $body
     * @param bool            $assoc
     *
     * @return bool
     */
    private function generageRawBody(& $data, $body, $assoc = false)
    {
        $data = json_decode($body, $assoc);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $this->generateError(json_last_error_msg(), $body, $assoc);
            return false;
        }
        return true;
    }

    /**
     * 获取传入的json
     *
     * @param bool $isArray 是否需要数组
     *
     * @return null|array|\stdClass
     */
    public function getJsonRawBody($isArray = false)
    {
        $data = null;
        // 获取原始数据
        $body = $this->getRawBody();
        $body = $body ?: "{}";
        if (!$this->generageRawBody($data, $body, $isArray)) {
            // 1.1 解析JSON出错
            return $data;
        }
        // 返回数组格式的数据
        if ($isArray === true) {
            return $data;
        }
        return $data;
    }

    public function getHeader($name){
        return $this->_header[$name]?:"";
    }
    /**
     * 计算HTTP请求类型
     * @return string
     */
    public function getMethodReplacement()
    {
        $returnMethod = "GET";
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $returnMethod = strtoupper($_SERVER['REQUEST_METHOD']);
        }
        if ("POST" === $returnMethod) {
            $overrideMethod = $this->getHeader("X-HTTP-METHOD-OVERRIDE");
            if (!empty($overrideMethod)) {
                $returnMethod = strtoupper($overrideMethod);
            }
        }

        return $returnMethod;
    }
    /**
     * 获取客户端IP地址
     * @param integer $type 返回类型 0 返回IP地址 1 返回IPV4地址数字
     * @param boolean $adv 是否进行高级模式获取（有可能被伪装）
     * @return mixed
     */
    public function getIp($type = 0, $adv = true)
    {
        $type = $type ? 1 : 0;
        static $ip = null;
        if (null !== $ip) {
            return $ip[$type];
        }
        if ($adv) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $pos = array_search('unknown', $arr);
                if (false !== $pos) {
                    unset($arr[$pos]);
                }
                $ip = trim(current($arr));
            } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (isset($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        // IP地址合法验证
        $long = sprintf("%u", ip2long($ip));
        $ip = $long ? [$ip, $long] : ['0.0.0.0', 0];
        return $ip[$type];
    }

    /**
     * 是否get请求
     * @return bool
     */
    public function isGet(){
        return $this->getMethodReplacement() == "GET";
    }

    /**
     * 是否post 请求
     * @return bool
     */
    public function isPost(){
        return $this->getMethodReplacement() == "POST";
    }
    /**
     * 是否Ajax请求
     * @return bool
     */
    public function isAjax()
    {
        return $this->getHeader("X_REQUESTED_WITH") && strtoupper($this->getHeader('X_REQUESTED_WITH')) == 'XMLHTTPREQUEST';
    }
}