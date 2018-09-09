<?php
/**
 * 系统默认的一些方法
 * @author: zmh
 * @date: 2018-07-13
 */
if (!function_exists('app')) {

    function app()
    {
        return (new \Framework\Container());
    }
}
function aabb(){
    echo 1;
}