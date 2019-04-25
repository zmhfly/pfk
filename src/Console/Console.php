<?php
/**
 * @author: zmh
 * @date: 2019-01-19
 */
namespace Framework\Console;

class Console
{
    public $lists = [];

    private $input = null;
    private $serviceName = "";
    public function __construct()
    {
        $this->input = new Input();
        $this->serviceName = $this->input->getServiceName();


        // 注册默认服务

        //注册自定义服务
        $this->registerCommands();

        // 检查服务
        if(!$this->checkService()){
            throw new \Exception("未找到对应的服务: ".$this->serviceName);
        }

        if(!$this->checkOptions()){

        }

        $this->runService();
    }

    /**
     * 自动注册
     */
    public function registerCommands()
    {
        try {
            $class = new \App\Commands\DemoCommand();
            $this->lists[$class->signature] = [
                "description" => $class->description,
                "class" => $class
            ];
        } catch(\Exception $e) {
            echo $e->getMessage();
        }
        echo "\n";
    }

    /**
     * 检查服务是否存在
     * @return bool
     */
    private function checkService(){
        if(array_key_exists($this->serviceName,$this->lists)){
            return true;
        }
        // 如果是 h help list 执行帮助
        if(array_key_exists($this->serviceName,["h","help","list"])){
            $this->runHelp();
            exit();
        }
        return false;
    }

    // 检查入参
    private function checkOptions(){

        $options = $this->lists[$this->serviceName]['class']->options;
        $inputOptions = array_keys($this->input->getOptions());
        $diff = array_diff($options,$inputOptions);
        if($diff){
            throw new \Exception("下列参数未定义".json_encode($diff,JSON_UNESCAPED_UNICODE));
        }
        return true;

    }
    private function runService(){
        $this->lists[$this->serviceName]['class']->handle($this->input->getOptions());
    }
    private function runHelp(){
        foreach ($this->lists as $k => $v) {
            echo $k." ".$v["description"]."\n";
        }
    }
}