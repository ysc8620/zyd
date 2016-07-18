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
echo date("Y-m-d H:i:s")."=match_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test
do{
    global $mongo;
    $curr = $mongo->zyd->match;
    $date = date("Y-m-d");
    $postStr = file_get_contents("http://interface.win007.com/zq/BF_XML.aspx?date={$date}");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $item){
        $home = explode(',',trim($item['h']));
        $away = explode(',',trim($item['i']));
        $league = explode(',', $item['c']);
        $info = [
            'match_id' => $item['a'],
            'color'     => $item['b'],
            'league'    => $item['c'],
            'league_id' => $league[3],
            'league_name' => $league[0],
            'time'      => $item['d'],
            'sub_league'      => $item['e'],
            'state'     => $item['f'],
            'home'      => $item['h'],
            'home_name' => $home[0],
            'home_id'   => $home[3],
            'away'      => $item['i'],
            'away_name' => $away[0],
            'away_id'   => $away[3],
            'home_score' => $item['j'],
            'away_score' => $item['k'],
            'home_half_score' => $item['l'],
            'away_half_score' => $item['m'],
            'home_red'   => $item['n'],
            'away_red'   => $item['o'],
            'home_order' => $item['p'],
            'away_order' => $item['q'],
            'explain'    => $item['r'],
            'match_round' => $item['s'],
            'address' => $item['t'],
            'weather_ico' => $item['u'],
            'weather' => $item['v'],
            'temperature' => $item['w'],
            'match_league' => $item['x'],
            'group' =>$item['y'],
            'is_neutral' => $item['z'],
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s'),
            'last_update_event' => 'match'
        ];
        $team = $curr->findOne(array('match_id'=>$info['match_id']));
        if($team){
            $curr->update(array('match_id'=>$info['match_id']), array('$set'=>$info));
        }else{
            $curr->insert($info);
        }
    }
}while(false);