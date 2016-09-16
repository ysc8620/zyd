<?php
/**
 * 获取球队信息
 * User: ShengYue
 * Date: 2016/7/16
 * Time: 13:24
 */

// url http://interface.win007.com/zq/Player_XML.aspx

// 应用入口文件
namespace auto;
// 检测PHP环境
require_once __DIR__ .'/config.php';
echo date("Y-m-d H:i:s")."=yapei_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test
do{
//    global $mongo;
//    $curr = $mongo->zyd->yapei;
//    $curr_league = $mongo->zyd->league;
//    $curr_match = $mongo->zyd->match;
    $postStr = file_get_contents("http://interface.win007.com/zq/odds.aspx");
    $list = explode('$', $postStr);
    /*
    // 联赛信息
    $league_list = explode(';', $list[0]);
    foreach($league_list as $league){
        $info = explode(',', $league);
        $data = [
            'league_id' => $info[0],
            'type' => $info[1],
            'color' => $info[2],
            'cn_name' => $info[3],
            'tw_name' => $info[4],
            'en_name' => $info[5],
            'important' => $info[7]
        ];
        $league_info = $curr_league->findOne(array('league_id'=>$info[0]));
        if(!$league_info){
            $curr->insert($data);
        }
    }

    // 赛程信息
    $match_list = explode(';', $list[1]);
    foreach($match_list as $match){
        $info = explode(',', $match);
        $data = [
            'league_id' => $info[0],
            'type' => $info[1],
            'color' => $info[2],
            'cn_name' => $info[3],
            'tw_name' => $info[4],
            'en_name' => $info[5],
            'important' => $info[7]
        ];
        $league_info = $curr_match->findOne(array('league_id'=>$info[0]));
        if(!$league_info){
            $curr->insert($data);
        }
    }*/
    // 亚赔（让球盘）
    // $match = $curr->findOne(array('match_id'=>$));
    $asia_list = explode(';', $list[2]);
    foreach($asia_list as $asia){
        $info = explode(',', $asia);
        $match_id = $info[0];
        if(empty($match_id)){continue;}
        if(!in_array($info[1], [3,24,31,8])){
            continue;
        }
        $data = [
            'match_id' => intval($info[0]),
            'company_id' => intval($info[1]),
            'begin_rate' => ($info[2]),
            'begin_home_rate' => ($info[3]),
            'begin_away_rate' => ($info[4]),
            'change_rate' => ($info[5]),
            'change_home_rate' => ($info[6]),
            'change_away_rate' => ($info[7]),
            'is_inclose' => strval($info[8]),
            'is_walk' => strval($info[9]),
            'update_time' => time()
        ];
        $data['begin_rate'] = -$data['begin_rate'];
        $data['change_rate'] = -$data['change_rate'];
        $match = M('asia_yapei')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->field('id')->find();
        if($match){
            M('asia_yapei')->where(array('id'=>$match['id']))->save($data);
        }else{
            M('asia_yapei')->add($data);
        }
    }

    // 欧赔（标准盘）
    $oupei_list = explode(';', $list[3]);
    foreach($oupei_list as $oupei){
        $info = explode(',', $oupei);
        $match_id = $info[0];
        if(empty($match_id)){continue;}
        if(!in_array($info[1], [3,24,31,8])){
            continue;
        }
        $data = [
            'match_id' => intval($info[0]),
            'company_id' => intval($info[1]),
            'begin_home_rate' => ($info[2]),
            'begin_draw_rate' => ($info[3]),
            'begin_away_rate' => ($info[4]),
            'change_home_rate' => ($info[5]),
            'change_draw_rate' => ($info[6]),
            'change_away_rate' => ($info[7]),
            'update_time' => time()
        ];

        $match = M('asia_oupei')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->field('id')->find();
        if($match){
            M('asia_oupei')->where(array('id'=>$match['id']))->save($data);
        }else{
            M('asia_oupei')->add($data);
        }
    }

    // 大小球
    $daxiaoqiu_list = explode(';', $list[4]);
    foreach($daxiaoqiu_list as $daxiaoqiu){
        $info = explode(',', $daxiaoqiu);
        $match_id = $info[0];
        if(empty($match_id)){continue;}
        if(!in_array($info[1], [3,24,31,8])){
            continue;
        }
        $data = [
            'match_id' => intval($info[0]),
            'company_id' => intval($info[1]),
            'begin_rate' => ($info[2]),
            'begin_big_rate' => ($info[3]),
            'begin_small_rate' => ($info[4]),
            'change_rate' => ($info[5]),
            'change_big_rate' => ($info[6]),
            'change_small_rate' => ($info[7]),
            'update_time' => time()
        ];

        $match = M('asia_daxiaoqiu')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->field('id')->find();
        if($match){
            M('asia_daxiaoqiu')->where(array('id'=>$match['id']))->save($data);
        }else{
            M('asia_daxiaoqiu')->add($data);
        }
    }

    // 半场大小球
    $half_list = explode(';', $list[4]);
    foreach($half_list as $half){
        $info = explode(',', $half);
        $match_id = $info[0];
        if(empty($match_id)){continue;}
        if(!in_array($info[1], [3,24,31,8])){
            continue;
        }
        $data = [
            'match_id' => intval($info[0]),
            'company_id' => intval($info[1]),
            'begin_rate' => ($info[2]),
            'begin_home_rate' => ($info[3]),
            'begin_away_rate' => ($info[4]),
            'change_rate' => ($info[5]),
            'change_home_rate' => ($info[6]),
            'change_away_rate' => ($info[7]),
            'update_time' => time()
        ];
        $data['begin_rate'] = -$data['begin_rate'];
        $data['change_rate'] = -$data['change_rate'];

        $match = M('asia_half')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->field('id')->find();
        if($match){
            M('asia_half')->where(array('id'=>$match['id']))->save($data);
        }else{
            M('asia_half')->add($data);
        }
    }

    // 大小球
    $half_daxiaoqiu_list = explode(';', $list[4]);
    foreach($half_daxiaoqiu_list as $half_daxiaoqiu){
        $info = explode(',', $half_daxiaoqiu);
        $match_id = $info[0];
        if(empty($match_id)){continue;}
        if(!in_array($info[1], [3,24,31,8])){
            continue;
        }
        $data = [
            'match_id' => intval($info[0]),
            'company_id' => intval($info[1]),
            'begin_rate' => ($info[2]),
            'begin_big_rate' => ($info[3]),
            'begin_small_rate' => ($info[4]),
            'change_rate' => ($info[5]),
            'change_big_rate' => ($info[6]),
            'change_small_rate' => ($info[7]),
            'update_time' => time()
        ];

        $match = M('asia_half_daxiaoqiu')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->field('id')->find();
        if($match){
            M('asia_half_daxiaoqiu')->where(array('id'=>$match['id']))->save($data);
        }else{
            M('asia_half_daxiaoqiu')->add($data);
        }
    }
}while(false);