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
    $postStr = file_get_contents("http://interface.win007.com/zq/Player_XML.aspx?day=60");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);


    foreach($data['i'] as $item){
        $info = [
            'from_id'   => $item['id'],
            'player_id' => $item['PlayerID'],
            'cn_name'   => $item['Name_J'],
            'tw_name'   => $item['Name_F'],
            'en_name'   => $item['Name_E'],
            'birthday'  => $item['Birthday'],
            'tallness'  => $item['Tallness'],
            'weight'    => $item['Weight'],
            'country'   => $item['Country'],
            'photo'     => $item['Photo'],
            'health'    => $item['Health'],
            'value'     => $item['value'],
            'feet'      => $item['feet'],
            'introduce' => $item['Introduce'],
            'team_id'  => $item['TeamID'],
            'place'    => $item['Place'],
            'number'   => $item['Number']
        ];
        $player = $curr->findOne(array('player_id'=>$item['PlayerID']));
        if($player){
            $curr->update(array('player_id'=>$info['player_id']), array('$set'=>$info));
        }else{
            $curr->insert($info);
        }
    }
}while(false);