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
//    global $mongo;
//    $curr = $mongo->zyd->match;
    $date = date("Y-m-d");
    echo $date."\r\n";
    $postStr = file_get_contents("http://interface.win007.com/zq/BF_XML.aspx?date={$date}");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $item){
        $home = explode(',',trim($item['h']));
        $away = explode(',',trim($item['i']));
        $league = explode(',', $item['c']);
        $info = [
            'match_id' => intval($item['a']),
            'color'     => getValue($item['b']),
            'league'    => getValue($item['c']),
            'league_id' => intval($league[3]),
            'league_name' => getValue($league[0]),
            'time'      => getValue($item['d']),
            'sub_league'      => getValue($item['e']),
            'state'     => getValue($item['f']),
            'home'      => getValue($item['h']),
            'home_name' => getValue($home[0]),
            'home_id'   => intval($home[3]),
            'away'      => getValue($item['i']),
            'away_name' => getValue($away[0]),
            'away_id'   => intval($away[3]),
            'home_score' => intval($item['j']),
            'away_score' => intval($item['k']),
            'home_half_score' => intval($item['l']),
            'away_half_score' => intval($item['m']),
            'home_red'   => intval($item['n']),
            'away_red'   => intval($item['o']),
            'home_order' => getValue($item['p']),
            'away_order' => getValue($item['q']),
            'explain'    => getValue($item['r']),
            'match_round' => getValue($item['s']),
            'address' => getValue($item['t']),
            'weather_ico' => getValue($item['u']),
            'weather' => getValue($item['v']),
            'temperature' => getValue($item['w']),
            'match_league' => getValue($item['x']),
            'group' =>getValue($item['y']),
            'is_neutral' => getValue($item['z']),
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s'),
            'last_update_event' => 'match'
        ];
        $team = M('match')->where(array('match_id'=>$info['match_id']))->field('id,match_id')->find();
        if($team){
            M('match')->where(array('id'=>$team['id']))->save($info);
        }else{
            M('match')->add($info);
        }
    }

    $date = date("Y-m-d", strtotime("+1 day"));
    echo $date."\r\n";
    $postStr = file_get_contents("http://interface.win007.com/zq/BF_XML.aspx?date={$date}");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $item){
        $home = explode(',',trim($item['h']));
        $away = explode(',',trim($item['i']));
        $league = explode(',', $item['c']);
        $info = [
            'match_id' => intval($item['a']),
            'color'     => getValue($item['b']),
            'league'    => getValue($item['c']),
            'league_id' => intval($league[3]),
            'league_name' => getValue($league[0]),
            'time'      => getValue($item['d']),
            'sub_league'      => getValue($item['e']),
            'state'     => getValue($item['f']),
            'home'      => getValue($item['h']),
            'home_name' => getValue($home[0]),
            'home_id'   => intval($home[3]),
            'away'      => getValue($item['i']),
            'away_name' => getValue($away[0]),
            'away_id'   => intval($away[3]),
            'home_score' => intval($item['j']),
            'away_score' => intval($item['k']),
            'home_half_score' => intval($item['l']),
            'away_half_score' => intval($item['m']),
            'home_red'   => intval($item['n']),
            'away_red'   => intval($item['o']),
            'home_order' => getValue($item['p']),
            'away_order' => getValue($item['q']),
            'explain'    => getValue($item['r']),
            'match_round' => getValue($item['s']),
            'address' => getValue($item['t']),
            'weather_ico' => getValue($item['u']),
            'weather' => getValue($item['v']),
            'temperature' => getValue($item['w']),
            'match_league' => getValue($item['x']),
            'group' =>getValue($item['y']),
            'is_neutral' => getValue($item['z']),
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s'),
            'last_update_event' => 'match'
        ];
        $team = M('match')->where(array('match_id'=>$info['match_id']))->field('id,match_id')->find();
        if($team){
            M('match')->where(array('id'=>$team['id']))->save($info);
        }else{
            M('match')->add($info);
        }
    }

    $date = date("Y-m-d", strtotime("+2 day"));
    echo $date."\r\n";
    $postStr = file_get_contents("http://interface.win007.com/zq/BF_XML.aspx?date={$date}");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $item){
        $home = explode(',',trim($item['h']));
        $away = explode(',',trim($item['i']));
        $league = explode(',', $item['c']);
        $info = [
            'match_id' => intval($item['a']),
            'color'     => getValue($item['b']),
            'league'    => getValue($item['c']),
            'league_id' => intval($league[3]),
            'league_name' => getValue($league[0]),
            'time'      => getValue($item['d']),
            'sub_league'      => getValue($item['e']),
            'state'     => getValue($item['f']),
            'home'      => getValue($item['h']),
            'home_name' => getValue($home[0]),
            'home_id'   => intval($home[3]),
            'away'      => getValue($item['i']),
            'away_name' => getValue($away[0]),
            'away_id'   => intval($away[3]),
            'home_score' => intval($item['j']),
            'away_score' => intval($item['k']),
            'home_half_score' => intval($item['l']),
            'away_half_score' => intval($item['m']),
            'home_red'   => intval($item['n']),
            'away_red'   => intval($item['o']),
            'home_order' => getValue($item['p']),
            'away_order' => getValue($item['q']),
            'explain'    => getValue($item['r']),
            'match_round' => getValue($item['s']),
            'address' => getValue($item['t']),
            'weather_ico' => getValue($item['u']),
            'weather' => getValue($item['v']),
            'temperature' => getValue($item['w']),
            'match_league' => getValue($item['x']),
            'group' =>getValue($item['y']),
            'is_neutral' => getValue($item['z']),
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s'),
            'last_update_event' => 'match'
        ];
        $team = M('match')->where(array('match_id'=>$info['match_id']))->field('id,match_id')->find();
        if($team){
            M('match')->where(array('id'=>$team['id']))->save($info);
        }else{
            M('match')->add($info);
        }
    }


    $date = date("Y-m-d", strtotime("+3 day"));
    echo $date."\r\n";
    $postStr = file_get_contents("http://interface.win007.com/zq/BF_XML.aspx?date={$date}");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $item){
        $home = explode(',',trim($item['h']));
        $away = explode(',',trim($item['i']));
        $league = explode(',', $item['c']);
        $info = [
            'match_id' => intval($item['a']),
            'color'     => getValue($item['b']),
            'league'    => getValue($item['c']),
            'league_id' => intval($league[3]),
            'league_name' => getValue($league[0]),
            'time'      => getValue($item['d']),
            'sub_league'      => getValue($item['e']),
            'state'     => getValue($item['f']),
            'home'      => getValue($item['h']),
            'home_name' => getValue($home[0]),
            'home_id'   => intval($home[3]),
            'away'      => getValue($item['i']),
            'away_name' => getValue($away[0]),
            'away_id'   => intval($away[3]),
            'home_score' => intval($item['j']),
            'away_score' => intval($item['k']),
            'home_half_score' => intval($item['l']),
            'away_half_score' => intval($item['m']),
            'home_red'   => intval($item['n']),
            'away_red'   => intval($item['o']),
            'home_order' => getValue($item['p']),
            'away_order' => getValue($item['q']),
            'explain'    => getValue($item['r']),
            'match_round' => getValue($item['s']),
            'address' => getValue($item['t']),
            'weather_ico' => getValue($item['u']),
            'weather' => getValue($item['v']),
            'temperature' => getValue($item['w']),
            'match_league' => getValue($item['x']),
            'group' =>getValue($item['y']),
            'is_neutral' => getValue($item['z']),
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s'),
            'last_update_event' => 'match'
        ];
        $team = M('match')->where(array('match_id'=>$info['match_id']))->field('id,match_id')->find();
        if($team){
            M('match')->where(array('id'=>$team['id']))->save($info);
        }else{
            M('match')->add($info);
        }
    }
}while(false);