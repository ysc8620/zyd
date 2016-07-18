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
namespace auto;
// 检测PHP环境
require_once __DIR__ .'/config.php';

//mongodb://admin_miss:miss@localhost:27017/test
use \Org\Util\File;
$lock_time = time();
$lock_file = 'locak_auto_api_data.lock';
File::write_file($lock_file, $lock_time);

do{
    $time = intval(File::read_file($lock_file));
    if($time > $lock_time){break;}

    $postStr = file_get_contents("http://interface.win007.com/zq/today.aspx");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);
    foreach($data as $item){
        #M('match')->where(array('id'=>$item))
        #M('match')->add($item);
    }
}while(false);