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
echo date("Y-m-d H:i:s")."=change_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test
do{
//    global $mongo;
//    $curr = $mongo->zyd->match;
    $postStr = file_get_contents("http://120.55.66.48/zqfan/webapi/live/jingcai.json");
    $data = json_decode($postStr, true);
    foreach($data['data'] as $date){
        foreach($date['matches'] as $match){
            $info = [
                'date' => $date['date'],
                'match_no' => $match['no'],
                'league_name' => $match['leagueName'],
                'match_time' => $match['timeText'],
                'home_name' => $match['homeName'],
                'away_name' => $match['guestName'],
                'home_rate' => $match['homeSp'],
                'tie_rate' => $match['guestSp'],
                'away_rate' => $match['tieSp'],
                'win_rate' => $match['winRate'],
                'draw_rate' => $match['drawRate'],
                'lose_rate' => $match['loseRate'],
                'home_concede' => $match['homeConcede'],
                'update_time' => time()
            ];

            $match_info = M('jingcai')->where(array('date'=>$info['date'], 'match_no'=>$info['match_no']))->field('id')->find();
            if($match_info){
                M('jingcai')->where(array('id'=>$match_info['id']))->save($info);
            }else{
                M('jingcai')->add($info);
            }
        }
    }


}while(false);