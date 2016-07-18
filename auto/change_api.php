<?php
/**
 * 获取赛程信息
 * User: ShengYue
 * Date: 2016/7/16
 * Time: 13:24
 */

// url http://interface.win007.com/zq/Player_XML.aspx

// 应用入口文件
namespace auto;
// 检测PHP环境
require_once __DIR__ .'/config.php';

//mongodb://admin_miss:miss@localhost:27017/test
do{
    global $mongo;
    $curr = $mongo->zyd->match;
    $postStr = file_get_contents("http://interface.win007.com/zq/change.xml");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['h'] as $item){
        $change = explode('^',trim($item));

        $match = [
            'match_id' => $change[0],
            'state'     => $change[1],
            'home_score' => $change[2],
            'away_score' => $change[3],
            'home_half_score' => $change[4],
            'away_half_score' => $change[5],
            'home_red' => $change[6],
            'away_red' => $change[7],
            'time2'     => $change[9],
            'is_lineup' => $change[11],
            'home_yellow' => $change[12],
            'away_yellow' => $change[13],
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s'),
            'last_update_event' => 'chnage'
        ];
        $match_info = $curr->findOne(array('match_id'=>$match['match_id']));
        if($match_info){
            $curr->update(array('match_id'=>$match['match_id']), array('$set'=>$match));
        }else{
            $curr->insert($match);
        }
    }
}while(false);