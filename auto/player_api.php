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
require_once __DIR__ .'/config.php';
echo date("Y-m-d H:i:s")."=player_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test
do{
//    global $mongo;
//    $curr = $mongo->zyd->player;
    $postStr = file_get_contents("http://interface.win007.com/zq/Player_XML.aspx?day=1");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);


    foreach($data['i'] as $item){
        $info = [
            'from_id'   => intval($item['id']),
            'player_id' => intval($item['PlayerID']),
            'cn_name'   => getValue($item['Name_J']),
            'tw_name'   => getValue($item['Name_F']),
            'en_name'   => getValue($item['Name_E']),
            'birthday'  => getValue($item['Birthday']),
            'tallness'  => getValue($item['Tallness']),
            'weight'    => getValue($item['Weight']),
            'country'   => getValue($item['Country']),
            'photo'     => getValue($item['Photo']),
            'health'    => getValue($item['Health']),
            'value'     => getValue($item['value']),
            'feet'      => getValue($item['feet']),
            'introduce' => getValue($item['Introduce']),
            'team_id'  => intval($item['TeamID']),
            'place'    => getValue($item['Place']),
            'number'   => getValue($item['Number']),
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s')
        ];
        $player = M('player')->where(array('player_id'=>$info['player_id']))->field('id')->find();
        if($player){
            M('player')->where(array('player_id'=>$info['player_id']))->save($info);
        }else{
            M('player')->add($info);
        }
    }
}while(false);