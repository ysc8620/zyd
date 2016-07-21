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
//    global $mongo;
//    $curr = $mongo->zyd->match;
    $postStr = file_get_contents("http://interface.win007.com/zq/today.aspx");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $item){
        $home = explode(',',trim($item['home']));
        $away = explode(',',trim($item['away']));
        $league = explode(',',trim($item['league']));
        $info = [
            'match_id' => intval($item['ID']),
            'color'     => strval($item['color']),
            'league_id'  =>intval($item['leagueID']),
            'league_name' => strval($league[0]),
            'league'    => strval($item['league']),
            'sub_league'    => strval($item['subLeague']),
            'level'        => strval($item['level']),
            'time'      => strval($item['time']),
            'time2'      => strval($item['time2']),
            'kind'      => intval($item['kind']),
            'state'     => strval($item['state']),
            'home'      => strval($item['home']),
            'home_name' => strval($home[0]),
            'home_id'   => intval($home[3]),
            'away'      => strval($item['away']),
            'away_name' => strval($away[0]),
            'away_id'   => intval($away[3]),
            'home_score' => intval($item['homeScore']),
            'away_score' => intval($item['awayScore']),
            'home_half_score' => intval($item['bc1']),
            'away_half_score' => intval($item['bc2']),
            'home_red'   => intval($item['red1']),
            'away_red'   => intval($item['red2']),
            'home_order' => strval($item['order1']),
            'away_order' => strval($item['order2']),
            'home_yellow' => intval($item['yellow1']),
            'away_yellow' => intval($item['yellow2']),
            'explain'    => strval($item['explain']),
            'is_neutral' => strval($item['zl']),
            'is_tv' => strval($item['tv']),
            'is_lineup' => intval($item['lineup']),
            'explain2' => strval($item['explain2']),
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s'),
            'last_update_event' => 'score'
        ];
        $team = M('match')->where(array('match_id'=>$info['match_id']))->find();
        if($team){
            M('match')->where(array('match_id'=>$info['match_id']))->save($info);
        }else{
            M('match')->add($info);
        }
    }
}while(false);