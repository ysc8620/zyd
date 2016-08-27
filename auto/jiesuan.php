<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2016/8/25
 * Time: 22:36
 */

namespace auto;
// 检测PHP环境
require_once __DIR__ .'/config.php';
echo date("Y-m-d H:i:s")."=match_api=\r\n";

// 计算半场推荐输赢
$match_list = M("match")->where(array('state'=>array('in',[2,3]), 'is_half_count'=>0))->field('id,match_id,home_score,away_score,home_half_score,away_half_score')->select();
foreach($match_list as $match){
    // 获取所有推荐信息
    $tuijian_list = M('tuijian')->where(array('match_id'=>$match['match_id'], 'tuijian_type'=>1, ''))->select();
    foreach($tuijian_list as $tuijian){
        // 大小球
        if($tuijian['type'] == 3){
            if($tuijian['sub_type'] == 1){

            }
        }
    }


    // 半场欧赔

    // 半场让球
}

// 计算全场推荐输赢
$match_list = M("match")->where(array('state'=>"-1", 'is_full_count'=>0))->field('id,match_id,home_score,away_score,home_half_score,away_half_score')->select();
foreach($match_list as $match){
    // 半场大小球

    // 半场欧赔

    // 半场让球
}
// 通过推荐计算
