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
        foreach($)
    }

}while(false);