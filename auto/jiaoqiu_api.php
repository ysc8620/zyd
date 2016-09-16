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
    $postStr = file_get_contents("http://interface.win007.com/zq/SpecialOdds.aspx?type=other");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $match){
        if(!in_array($match['CompanyID'], [3,24,31,8])){
            continue;
        }
            $info = [
                'schedule_id' => $match['scheduleID'],
                'company_id' => $match['CompanyID'],
                'type' => $match['type'],
                'home_rate' => $match['HomeOdds'],
                'away_rate' => $match['AwayOdds'],
                'goal' => $match['Goal'],
                'modify_time' => $match['ModifyTime'],
                'update_time' => time()
            ];

            $match_info = M('jiaoqiu')->where(array('schedule_id'=>$info['schedule_id'],'company_id'=>$info['company_id']))->order('id DESC')->find();
            if(strtotime($match_info['modify_time']) < strtotime($info['modify_time'])){
                M('jiaoqiu')->add($info);
            }
    }


}while(false);