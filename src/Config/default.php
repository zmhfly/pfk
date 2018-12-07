<?php
/**
 * 系统默认配置文件
 * @author: zmh
 * @date: 2018-07-12
 */
return [
    "defaultNameSapce" => "App",
    "appPath" => "app",
    "defaultModule" => "Api",
    "defaultControllerPath" => "Controllers",
    "defaultController" => "Index",
    "defaultMethod" => "index",
    "template" => [
        "layout" => false,
        "suffix"=>"php",
        "templatePathRoot" => "../views",
        "layoutPath"=>"../views/layout.php",
        "layoutItem"=>"__REPLACE__"
    ],
    // 日志目录
    "logPath" => "log",
    // 路由相关
    "routeType" => "pathInfo",
    //session 相关配置
    "session" => [
        "type" => "file",
        "redis" => [
            // redis主机
            'host' => '127.0.0.1',
            // redis端口
            'port' => 6379,
            // 密码
            'password' => '',
            // 操作库
            'select' => 0,
            // 有效期(秒)
            'expire' => 3600,
            // 超时时间(秒)
            'timeout' => 0,
            // 是否长连接
            'persistent' => true,
            // sessionkey前缀
            'sessionName' => '',
        ]
    ],
    // redis 相关配置
    "redis" => [
        // redis主机
        'host' => '127.0.0.1',
        // redis端口
        'port' => 6379,
        // 密码
        'password' => '',
        // 有效期(秒)
        'expire' => 3600,
        // 超时时间(秒)
        'timeout' => 0,
        // 是否长连接
        'persistent' => true,
    ]
];
