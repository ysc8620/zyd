<?php
$config = array(
    'app' => array(
        'zq8bfc58935bf37o2e' => 'b8e586b6eb3530f1c5efad7ea3f1359e',
        'zq9ged2g338js52h3i' => 'b31d023048ba9f8736d219bi00bb6ab0',
    ),
    'BASE_URL' => 'http://api2.zydzuqiu.com/',
    //'配置项'=>'配置值'
    'URL_MODEL'            =>3,    //2是去除index.php
    'DB_FIELDTYPE_CHECK'   =>true,
    'TMPL_STRIP_SPACE'     =>true,
    'OUTPUT_ENCODE'        =>true, // 页面压缩输出

    'MODULE_ALLOW_LIST'    =>    array('Home','Admin'),
    'DEFAULT_MODULE'       =>    'Home',  // 默认模块

    //加密混合值
    'AUTH_CODE' => 'ZhangYuDiV1',
    //数据库配置
    'URL_CASE_INSENSITIVE' => true,
    'URL_HTML_SUFFIX' => 'html',

    //    'SESSION_OPTIONS'=>array(
    //        'type'=> 'db',//session采用数据库保存
    //        'expire'=>604800,//session过期时间，如果不设就是php.ini中设置的默认值
    //        ),
    /////////////////////////////////////////////////////
    //'SESSION_TYPE' => 'Redis',
    //session保存类型

//    'SESSION_PREFIX' => 'sess_', //session前缀
//    'REDIS_HOST' => '192.168.1.106', //REDIS服务器地址
//    'REDIS_PORT' => 6379, //REDIS连接端口号
//    'SESSION_EXPIRE' => 3600, //SESSION过期时间
    // array host port timeout auth
    // 
//    'AD_REDIS'    =>    array('120.24.232.6',6380,5,''), //6380
//
//    'PRO_REDIS'    =>    array('120.24.232.6',6381,5,''), //产品
//    'TOKEN_REDIS'    =>    array('120.24.232.6',6382,5,''), //微信token 及相关
    //////////////////////////////////////////////////
    'TAGLIB_BUILD_IN' => 'cx',//芝麻乐标签库
    //'TAGLIB_PRE_LOAD' => '',//芝麻乐命名范围
);



$db_config = __DIR__ .'/db_config.php';
$db_config = file_exists($db_config) ? include "$db_config" : array();

return array_merge($db_config, $config);