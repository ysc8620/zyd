<?php
/**
 * 获取球员信息
 * User: ShengYue
 * Date: 2016/7/16
 * Time: 13:24
 */

// url http://interface.win007.com/zq/Player_XML.aspx

// 应用入口文件
namespace auto;
// 检测PHP环境
require_once 'config.php';

//mongodb://admin_miss:miss@localhost:27017/test
do{
    global $mongo;
    $curr = $mongo->zyd->player;
    $postStr = file_get_contents("http://interface.win007.com/zq/Player_XML.aspx?day=1");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);
    print_r($data);
    die();

    foreach($data as $item){
        $player = $curr->findOne(array('player_id'=>$item['PlayerID']));
    }
}while(false);