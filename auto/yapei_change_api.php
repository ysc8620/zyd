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
    $postStr = file_get_contents("http://interface.win007.com/zq/ch_odds.xml");
    preg_match_all("/(<h>[^(<h>)]*?<\/h>)+/is",$postStr,$list);
    if(count($list[0]) == 5){
        echo "ok";
        // 亚赔（让球盘）
        // $match = $curr->findOne(array('match_id'=>$));
        $asia_list = explode(';', $list[0][0]);
        foreach($asia_list as $asia){
            $info = explode(',', $asia);
            $match_id = $info[0];
            $data = [
                'match_id' => intval($info[0]),
                'company_id' => intval($info[1]),
                'change_rate' => floatval($info[2]),
                'change_home_rate' => floatval($info[3]),
                'change_away_rate' => floatval($info[4]),
                'is_inclose' => strval($info[5]),
                'is_walk' => strval($info[6]),
                'update_time' => time()
            ];

            $match = M('asia_yapei')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->find();
            if($match){
                M('asia_yapei')->where(array('id'=>$match['id']))->save($data);
            }else{
                M('asia_yapei')->add($data);
            }
        }

        // 欧赔（标准盘）
        $oupei_list = explode(';', $list[0][1]);
        foreach($oupei_list as $oupei){
            $info = explode(',', $oupei);
            $match_id = $info[0];
            $data = [
                'match_id' => intval($info[0]),
                'company_id' => intval($info[1]),
                'change_home_rate' => floatval($info[2]),
                'change_draw_rate' => floatval($info[3]),
                'change_away_rate' => floatval($info[4]),
                'update_time' => time()
            ];

            $match = M('asia_oupei')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->find();
            if($match){
                M('asia_oupei')->where(array('id'=>$match['id']))->save($data);
            }else{
                M('asia_oupei')->add($data);
            }
        }

        // 大小球
        $daxiaoqiu_list = explode(';', $list[0][2]);
        foreach($daxiaoqiu_list as $daxiaoqiu){
            $info = explode(',', $daxiaoqiu);
            $match_id = $info[0];
            $data = [
                'match_id' => intval($info[0]),
                'company_id' => intval($info[1]),
                'change_rate' => floatval($info[2]),
                'change_big_rate' => floatval($info[3]),
                'change_small_rate' => floatval($info[4]),
                'update_time' => time()
            ];

            $match = M('asia_daxiaoqiu')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->find();
            if($match){
                M('asia_daxiaoqiu')->where(array('id'=>$match['id']))->save($data);
            }else{
                M('asia_daxiaoqiu')->add($data);
            }
        }

        // 半场大小球
        $half_list = explode(';', $list[0][3]);
        foreach($half_list as $half){
            $info = explode(',', $half);
            $match_id = $info[0];
            $data = [
                'match_id' => intval($info[0]),
                'company_id' => intval($info[1]),
                'change_rate' => floatval($info[2]),
                'change_home_rate' => floatval($info[3]),
                'change_away_rate' => floatval($info[4]),
                'update_time' => time()
            ];

            $match = M('asia_half')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->find();
            if($match){
                M('asia_half')->where(array('id'=>$match['id']))->save($data);
            }else{
                M('asia_half')->add($data);
            }
        }

        // 大小球
        $half_daxiaoqiu_list = explode(';', $list[0][4]);
        foreach($half_daxiaoqiu_list as $half_daxiaoqiu){
            $info = explode(',', $half_daxiaoqiu);
            $match_id = $info[0];
            $data = [
                'match_id' => intval($info[0]),
                'company_id' => intval($info[1]),
                'change_rate' => floatval($info[2]),
                'change_big_rate' => floatval($info[3]),
                'change_small_rate' => floatval($info[4]),
                'update_time' => time()
            ];

            $match = M('asia_half_daxiaoqiu')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->find();
            if($match){
                M('asia_half_daxiaoqiu')->where(array('id'=>$match['id']))->save($data);
            }else{
                M('asia_half_daxiaoqiu')->add($data);
            }
        }
    }else{
        echo "err";
    }

}while(false);