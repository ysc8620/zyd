<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件
set_time_limit(300);
ini_set('memory_limit','1024M');
header("Content-type:text/html;charset=utf-8");
global $mongo;
/**
 *
 */
function _shutdown_handler(){
    global $mongo;
    try{
        //$mongo->close();
    }catch (\Exception $e){
        print_r($e);
    }
}
register_shutdown_function('_shutdown_handler');

function getValue($value){
    return is_array($value)?"":strval($value);
}

try{
    //$mongo = new \Mongo("mongodb://root:LEsc123456@localhost:27017");
}catch (\Exception $e){
    exit('mongodb连接失败');
}

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',False);

// 定义应用目录
define('APP_PATH', __DIR__ . '/../Application/');

// 引入ThinkPHP入口文件
require __DIR__ .'/../ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单