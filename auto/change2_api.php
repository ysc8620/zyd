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
echo date("Y-m-d H:i:s")."=change_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test
do{
    global $mongo;
    $curr = $mongo->zyd->match;
    $postStr = file_get_contents("http://interface.win007.com/zq/change2.xml");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['h'] as $item){
        $change = explode('^',trim($item));
        $match = [
            'match_id' => intval($change[0]),
            'state'     => getValue($change[1]),
            'home_score' => intval($change[2]),
            'away_score' => intval($change[3]),
            'home_half_score' => intval($change[4]),
            'away_half_score' => intval($change[5]),
            'home_red' => intval($change[6]),
            'away_red' => intval($change[7]),
            'time2'     => getValue($change[9]),
            'is_lineup' => getValue($change[11]),
            'home_yellow' => intval($change[12]),
            'away_yellow' => intval($change[13]),
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s'),
            'last_update_event' => 'chnage2'
        ];
        $match_info = M('match')->where(array('match_id'=>$match['match_id']))->field('id,match_id')->find();
        if($match_info){
            M('match')->where(array('id'=>$match['id']))->save($match);
        }
    }
}while(false);