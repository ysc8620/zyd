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
require_once 'config.php';

//mongodb://admin_miss:miss@localhost:27017/test
do{
    global $mongo;
    $curr = $mongo->zyd->baiou;
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
        $data = [];
        $data['id'.$info[1]] = [
            'match_id' => $info[0],
            'company_id' => $info[1],
            'begin_rate' => $info[2],
            'begin_home_rate' => $info[3],
            'begin_away_rate' => $info[4],
            'change_rate' => $info[5],
            'change_home_rate' => $info[6],
            'change_away_rate' => $info[7],
            'is_inclose' => $info[8],
            'is_walk' => $info[9]
        ];

        $match = $curr->findOne(array('match_id'=>$match_id));
        if($match){
            $asia_info = array_merge((array)$match['asia'], $data);
            $curr->update(array('_id'=>$match['_id']) ,array('asia'=>$asia_info));
        }else{
            $full_data = [
                'match_id' => $match_id,
                'asia' => $data
            ];
            $curr->insert($full_data);
        }
    }

    // 欧赔（标准盘）
    $oupei_list = explode(';', $list[3]);
    foreach($oupei_list as $oupei){
        $info = explode(',', $oupei);
        $match_id = $info[0];
        $data = [];
        $data['id'.$info[1]] = [
            'match_id' => $info[0],
            'company_id' => $info[1],
            'begin_home_rate' => $info[2],
            'begin_draw_rate' => $info[3],
            'begin_away_rate' => $info[4],
            'change_home_rate' => $info[5],
            'change_draw_rate' => $info[6],
            'change_away_rate' => $info[7]
        ];

        $match = $curr->findOne(array('match_id'=>$match_id));
        if($match){
            $oupei_info = array_merge((array)$match['oupei'], $data);
            $curr->update(array('_id'=>$match['_id']) ,array('oupei'=>$oupei_info));
        }else{
            $full_data = [
                'match_id' => $match_id,
                'oupei' => $data
            ];
            $curr->insert($full_data);
        }
    }

    // 大小球
    $daxiaoqiu_list = explode(';', $list[4]);
    foreach($daxiaoqiu_list as $daxiaoqiu){
        $info = explode(',', $daxiaoqiu);
        $match_id = $info[0];
        $data = [];
        $data['id'.$info[1]] = [
            'match_id' => $info[0],
            'company_id' => $info[1],
            'begin_rate' => $info[2],
            'begin_big_rate' => $info[3],
            'begin_small_rate' => $info[4],
            'change_rate' => $info[5],
            'change_big_rate' => $info[6],
            'change_small_rate' => $info[7]
        ];

        $match = $curr->findOne(array('match_id'=>$match_id));
        if($match){
            $daxia_info = array_merge((array)$match['daxiaoqiu'], $data);
            $curr->update(array('_id'=>$match['_id']) ,array('daxiaoqiu'=>$daxia_info));
        }else{
            $full_data = [
                'match_id' => $match_id,
                'daxiaoqiu' => $data
            ];
            $curr->insert($full_data);
        }
    }

    // 大小球
    $half_list = explode(';', $list[4]);
    foreach($half_list as $half){
        $info = explode(',', $half);
        $match_id = $info[0];
        $data = [];
        $data['id'.$info[1]] = [
            'match_id' => $info[0],
            'company_id' => $info[1],
            'begin_rate' => $info[2],
            'begin_big_rate' => $info[3],
            'begin_small_rate' => $info[4],
            'change_rate' => $info[5],
            'change_big_rate' => $info[6],
            'change_small_rate' => $info[7]
        ];

        $match = $curr->findOne(array('match_id'=>$match_id));
        if($match){
            $half_info = array_merge((array)$match['half'], $data);
            $curr->update(array('_id'=>$match['_id']) ,array('half'=>$half_info));
        }else{
            $full_data = [
                'match_id' => $match_id,
                'half' => $data
            ];
            $curr->insert($full_data);
        }
    }

    // 大小球
    $half_daxiaoqiu_list = explode(';', $list[4]);
    foreach($half_daxiaoqiu_list as $half_daxiaoqiu){
        $info = explode(',', $half_daxiaoqiu);
        $match_id = $info[0];
        $data = [];
        $data['id'.$info[1]] = [
            'match_id' => $info[0],
            'company_id' => $info[1],
            'begin_rate' => $info[2],
            'begin_big_rate' => $info[3],
            'begin_small_rate' => $info[4],
            'change_rate' => $info[5],
            'change_big_rate' => $info[6],
            'change_small_rate' => $info[7]
        ];

        $match = $curr->findOne(array('match_id'=>$match_id));
        if($match){
            $half_daxiaoqiu_info = array_merge((array)$match['half_daxiaoqiu'], $data);
            $curr->update(array('_id'=>$match['_id']) ,array('half_daxiaoqiu'=>$half_daxiaoqiu_info));
        }else{
            $full_data = [
                'match_id' => $match_id,
                'half_daxiaoqiu' => $data
            ];
            $curr->insert($full_data);
        }
    }
}while(false);