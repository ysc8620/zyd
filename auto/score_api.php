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
echo date("Y-m-d H:i:s")."=score_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test
do{
    global $mongo;
    $curr = $mongo->zyd->match;
    $postStr = file_get_contents("http://interface.win007.com/zq/today.aspx");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $item){
        $home = explode(',',trim($item['home']));
        $away = explode(',',trim($item['away']));
        $league = explode(',',trim($item['league']));
        $info = [
            'match_id' => $item['ID'],
            'color'     => $item['color'],
            'league_id'  => $item['leagueID'],
            'league_name' => $league[0],
            'league'    => $item['league'],
            'sub_league'    => $item['subLeague'],
            'level'        => $item['level'],
            'time'      => $item['time'],
            'time2'      => $item['time2'],
            'kind'      => $item['kind'],
            'state'     => $item['state'],
            'home'      => $item['home'],
            'home_name' => $home[0],
            'home_id'   => $home[3],
            'away'      => $item['away'],
            'away_name' => $away[0],
            'away_id'   => $away[3],
            'home_score' => $item['homeScore'],
            'away_score' => $item['awayScore'],
            'home_half_score' => $item['bc1'],
            'away_half_score' => $item['bc2'],
            'home_red'   => $item['red1'],
            'away_red'   => $item['red2'],
            'home_order' => $item['order1'],
            'away_order' => $item['order2'],
            'home_yellow' => $item['yellow1'],
            'away_yellow' => $item['yellow2'],
            'explain'    => $item['explain'],
            'is_neutral' => $item['zl'],
            'is_tv' => $item['tv'],
            'is_lineup' => $item['lineup'],
            'explain2' => $item['explain2'],
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s'),
            'last_update_event' => 'score'
        ];
        $team = $curr->findOne(array('match_id'=>$info['match_id']));
        if($team){
            $curr->update(array('match_id'=>$info['match_id']), array('$set'=>$info));
        }else{
            $curr->insert($info);
        }
    }
}while(false);