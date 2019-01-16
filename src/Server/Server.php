<?php
/**
 * @author: zmh
 * @date: 2019-01-12
 */
namespace Framework\Server;

use Framework\Application;

class Server
{
    /**
     * @var Application
     */
    protected $application;
    /**
     * @var Swoole
     */
    protected $server;
    /**
     * @var
     */
    protected $input;
    public function __construct(Application $application,$input)
    {

        $this->application = $application;
        $this->input = $input;
    }
    public function run(){

    }
    public function initRequest(){
        $this->application->request->runType = "swoole";

        $this->application->request->setPathInfo();
    }
}