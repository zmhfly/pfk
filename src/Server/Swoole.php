<?php
/**
 * @author: zmh
 * @date: 2019-01-12
 */
namespace Framework\Server;

use Framework\Application;
use Framework\Container;
use Framework\Router\Request;
use Framework\Router\Response;
use Framework\Router\Router;
use Swoole\Http\Server;
use Swoole\Http\Request as SwooleHttpRequest;
use Swoole\Http\Response as SwooleHttpResponse;

class Swoole
{
    public $events = [
        'finish',
        'managerStart',
        'managerStop',
        'pipeMessage',
        'receive',
        'request',
        'shutdown',
        'start',
        'task',
        'workerStart',
        'workerStop'
    ];
    protected $host;
    protected $port;
    /**
     * @var Server
     */
    protected $swoole;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var Response
     */
    protected $response;
    /**
     * @var Router
     */
    protected $router;
    public function __construct($config)
    {
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->swoole = new Server($this->host, $this->port);
    }

    public function init()
    {

        $this->swoole->on("start", [
            $this,
            'onStart'
        ]);
        $this->swoole->on('request', [
            $this,
            'onRequest'
        ]);
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return $this
     */
    public function daemon()
    {
        $this->swoole->set(["daemonize" => 1]);
        return $this;
    }

    public function start(Container $container)
    {
        $this->init();
       $this->request = $container->request;
       $this->router = $container->router;
       $this->response= $container->response;
        $this->swoole->start();
    }

    public function onStart()
    {

    }

    public function onRequest(SwooleHttpRequest $request, SwooleHttpResponse $response)
    {
        // 0. 忽略静态资源
        if ($this->isAssetsRequest($request, $response)) {
            return;
        }
        // 1. 初始HTTP状态码
        $statusCode = 200;
        $errorMessage = null;
        try {
            $this->request->runType = "swoole";
            $this->request->setPathInfo($request->server['path_info']);
            $this->request->setPost($request->post);
            $this->request->setGet($request->get);
            $this->request->setRowBody($request->rawContent());
            // 解析路由
            $this->router->setRequest($this->request);
            $router = $this->router->parse();
            //将返回的变量 添加到request
            $this->request->setModule($router['m']);
            $this->request->setController($router['c']);
            $this->request->setAction($router['a']);
            $this->request->setParams($router['params']);

            // 初始化session
            // 前置操作
            // 分发路由
          $returnData =  $this->router->dispatcher();
            // 路由缓存 有需要
            // 后置操作

            //            // 2. 处理HTTP请求
            //            $result = RequestLogic::factory([
            //                'request' => $request,
            //                'response' => $response
            //            ]);
            //            // 3. 请求结果处理
            //            if (!($result instanceof Response)) {
            //                if ($result instanceof Struct) {
            //                    $result = $this->serviceServer->withStruct($result);
            //                } else if (is_array($result)) {
            //                    $result = $this->serviceServer->withData($result);
            //                } else {
            //                    $result = $this->serviceServer->withSuccess();
            //                }
            //            }
        } catch(\Throwable $e) {
            // 4. 捕获错误数据
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            if ($errorCode >= 100 && $errorCode < 1000) {
                $statusCode = $errorCode;
            }
            $returnData = $this->response->withError();
        }
        $response->status($statusCode);
        $response->header('content-type', 'application/json;charset=utf-8');
        $response->end($returnData->getContent());




//        $response->end(json_encode(["rawContent"=>json_decode($request->rawContent()),"swoole"=>json_decode(json_encode($this->request,JSON_UNESCAPED_UNICODE),true)
//                                    ,"server"=>        json_decode(json_encode($_SERVER,JSON_UNESCAPED_UNICODE),true)
//        ]));
    }

    /**
     * 是否为静态资源
     *
     * @param SwooleHttpRequest $request
     *
     * @return bool
     */
    private function isAssetsRequest(SwooleHttpRequest $request, SwooleHttpResponse $response)
    {
        if (isset($request->server['request_uri'])) {
            $requestUri = preg_replace("/[\?]+(.*)$/", '', $request->server['request_uri']);
            if (preg_match("/\.([a-z0-9]+)$/i", $requestUri) > 0) {
                $response->status(400);
                $response->end("HTTP BAD REQUEST");
                return true;
            }
        }
        return false;
    }
}