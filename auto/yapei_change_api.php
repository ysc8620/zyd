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
    $postStr = str_replace(array('<o>','<d>'),'<a>',$postStr);
    $postStr = str_replace(array('</o>','</d>'),'</a>',$postStr);
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $list = json_decode(json_encode($obj), true);

    if(count($list['a']) == 5){
        // 亚赔（让球盘）
        // $match = $curr->findOne(array('match_id'=>$));
        if(isset($list['a'][0]['h'])){
            foreach($list['a'][0]['h'] as $asia){
                $info = explode(',', $asia);
                $match_id = $info[0];
                if(empty($match_id)){continue;}
                if(!in_array($info[1], [3,24,31,8])){
                    continue;
                }
                $data = [
                    'match_id' => intval($info[0]),
                    'company_id' => intval($info[1]),
                    'change_rate' => ($info[2]),
                    'change_home_rate' => ($info[3]),
                    'change_away_rate' => ($info[4]),
                    'is_inclose' => strval($info[5]),
                    'is_walk' => strval($info[6]),
                    'update_time' => time()
                ];
                $data['change_rate'] = -$data['change_rate'];
                $match = M('asia_yapei_change')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->order("id DESC")->find();
                if($match){
                    if($match['change_rate'] != $data['change_rate']
                        || $match['change_home_rate'] != $data['change_home_rate']
                        || $match['change_away_rate'] != $data['change_away_rate'])
                    {
                        M('asia_yapei_change')->add($data);
                    }
                    // M('asia_yapei_change')->where(array('id'=>$match['id']))->save($data);
                }else{
                    M('asia_yapei_change')->add($data);
                }
            }
        }

        // 欧赔（标准盘）
        if($list['a'][1]['h']){
            foreach($list['a'][1]['h'] as $oupei){
                $info = explode(',', $oupei);
                $match_id = $info[0];
                if(empty($match_id)){continue;}
                if(!in_array($info[1], [3,24,31,8])){
                    continue;
                }
                $data = [
                    'match_id' => intval($info[0]),
                    'company_id' => intval($info[1]),
                    'change_home_rate' => ($info[2]),
                    'change_draw_rate' => ($info[3]),
                    'change_away_rate' => ($info[4]),
                    'update_time' => time()
                ];

                $match = M('asia_oupei_change')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->order("id DESC")->find();
                if($match){
                    if($match['change_home_rate'] != $data['change_home_rate']
                        || $match['change_draw_rate'] != $data['change_draw_rate']
                        || $match['change_away_rate'] != $data['change_away_rate'])
                    {
                        M('asia_oupei_change')->add($data);
                    }
                    // M('asia_oupei')->where(array('id'=>$match['id']))->save($data);
                }else{
                    M('asia_oupei_change')->add($data);
                }
            }
        }

        if(isset($list['a'][2]['h'])){
            // 大小球
            foreach($list['a'][2]['h'] as $daxiaoqiu){
                $info = explode(',', $daxiaoqiu);
                $match_id = $info[0];
                if(empty($match_id)){continue;}
                if(!in_array($info[1], [3,24,31,8])){
                    continue;
                }
                $data = [
                    'match_id' => intval($info[0]),
                    'company_id' => intval($info[1]),
                    'change_rate' => ($info[2]),
                    'change_big_rate' => ($info[3]),
                    'change_small_rate' => ($info[4]),
                    'update_time' => time()
                ];

                $match = M('asia_daxiaoqiu_change')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->order("id DESC")->find();
                if($match){
                    if($match['change_rate'] != $data['change_rate']
                        || $match['change_big_rate'] != $data['change_big_rate']
                        || $match['change_small_rate'] != $data['change_small_rate'])
                    {
                        M('asia_daxiaoqiu_change')->add($data);
                    }

//                    M('asia_daxiaoqiu')->where(array('id'=>$match['id']))->save($data);
                }else{
                    M('asia_daxiaoqiu_change')->add($data);
                }
            }
        }

        if(isset($list['a'][3]['h'])){
            // 半场大小球
            foreach($list['a'][3]['h'] as $half){
                $info = explode(',', $half);
                $match_id = $info[0];
                if(empty($match_id)){continue;}
                if(!in_array($info[1], [3,24,31,8])){
                    continue;
                }
                $data = [
                    'match_id' => intval($info[0]),
                    'company_id' => intval($info[1]),
                    'change_rate' => ($info[2]),
                    'change_home_rate' => ($info[3]),
                    'change_away_rate' => ($info[4]),
                    'update_time' => time()
                ];

                $data['change_rate'] = -$data['change_rate'];

                $match = M('asia_half_change')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->order("id DESC")->find();
                if($match){
                    if($match['change_rate'] != $data['change_rate']
                        || $match['change_home_rate'] != $data['change_home_rate']
                        || $match['change_away_rate'] != $data['change_away_rate'])
                    {
                        M('asia_half_change')->add($data);
                    }
                    // M('asia_half_change')->where(array('id'=>$match['id']))->save($data);
                }else{
                    M('asia_half_change')->add($data);
                }
            }
        }

        if(isset($list['a'][4]['h'])){
            // 大小球
            foreach($list['a'][4]['h'] as $half_daxiaoqiu){
                $info = explode(',', $half_daxiaoqiu);
                $match_id = $info[0];
                if(empty($match_id)){continue;}
                if(!in_array($info[1], [3,24,31,8])){
                    continue;
                }
                $data = [
                    'match_id' => intval($info[0]),
                    'company_id' => intval($info[1]),
                    'change_rate' => ($info[2]),
                    'change_big_rate' => ($info[3]),
                    'change_small_rate' => ($info[4]),
                    'update_time' => time()
                ];

                $match = M('asia_half_daxiaoqiu_change')->where(array('match_id'=>$match_id,'company_id'=>$data['company_id']))->order("id DESC")->find();
                if($match){
                    if($match['change_rate'] != $data['change_rate']
                        || $match['change_big_rate'] != $data['change_big_rate']
                        || $match['change_small_rate'] != $data['change_small_rate'])
                    {
                        M('asia_half_daxiaoqiu_change')->add($data);
                    }
                    //M('asia_half_daxiaoqiu_change')->where(array('id'=>$match['id']))->save($data);
                }else{
                    M('asia_half_daxiaoqiu_change')->add($data);
                }
            }
        }

    }else{
        echo "err";
    }

}while(false);