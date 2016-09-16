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
    $postStr = file_get_contents("http://interface.win007.com/zq/Odds_1x2_half.aspx");

    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $list = json_decode(json_encode($obj), true);
    foreach($list['h'] as $match){
        if(is_array($match['odds']['o'])){
            echo '22';
            //print_r($match['odds']);
            foreach($match['odds']['o'] as $rate){
                $item = explode(',',$rate);

                if(!in_array($item[0], [3,24,31,8])){
                    continue;
                }
                //`match_id`, `company_id`, `begin_home_rate`, `begin_draw_rate`, `begin_away_rate`, `change_home_rate`,
                // `change_draw_rate`, `change_away_rate`, `change_date`, `update_time`
                $data = [
                    'match_id' => $match['id'],
                    'company_id' => $item[0],
                    'begin_home_rate'=>$item[2],
                    'begin_draw_rate'=>$item[3],
                    'begin_away_rate'=>$item[4],
                    'change_home_rate'=>$item[5],
                    'change_draw_rate'=>$item[6],
                    'change_away_rate'=>$item[7],
                    'change_date'=>$item[8],
                    'update_time'=>time()
                ];
                $has = M('asia_half_oupei')->where(array('match_id'=>$data['match_id'],'company_id'=>$data['company_id'],'change_date'=>$data['change_date']))->field('id')->find();
                if(!$has){
                    M('asia_half_oupei')->add($data);
                }
            }
        }else{
            $item = explode(',',$match['odds']['o']);

            //`match_id`, `company_id`, `begin_home_rate`, `begin_draw_rate`, `begin_away_rate`, `change_home_rate`,
            // `change_draw_rate`, `change_away_rate`, `change_date`, `update_time`
            $data = [
                'match_id' => $match['id'],
                'company_id' => $item[0],
                'begin_home_rate'=>$item[2],
                'begin_draw_rate'=>$item[3],
                'begin_away_rate'=>$item[4],
                'change_home_rate'=>$item[5],
                'change_draw_rate'=>$item[6],
                'change_away_rate'=>$item[7],
                'change_date'=>$item[8],
                'update_time'=>time()
            ];
            $has = M('asia_half_oupei')->where(array('match_id'=>$data['match_id'],'company_id'=>$data['company_id'],'change_date'=>$data['change_date']))->find();
            if(!$has){
                M('asia_half_oupei')->add($data);
            }
        }
    }

}while(false);