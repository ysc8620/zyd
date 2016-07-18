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
echo date("Y-m-d H:i:s")."=event_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test
do{
    global $mongo;
    $curr = $mongo->zyd->event;
    $postStr = file_get_contents("http://interface.win007.com/zq/detail.aspx");
    $data = explode("\r\n", $postStr);
    foreach ($data as $item) {
        if(strpos($item,'rq[') !== false){
            preg_match_all("/\"(.*?)\"/i", $item, $res);
            if($res[0]){
                if($res[1][0]){
                    $row = explode('^', $res[1][0]);
                    $info = [
                        'match_id' => $row[0],
                        'is_home_away' => $row[1],
                        'event_type' => $row[2],
                        'time' => $row[3],
                        'player' => $row[4],
                        'player_id' => $row[5],
                        'player_name' => $row[6],
                    ];
                    $event = $curr->findOne(array('match_id'=>$info['match_id'], 'time'=>$info['time']));
                    if($event){
                        $curr->update(array('_id'=>$event['_id']), array('$set'=>$info));
                    }else{
                        $curr->insert($info);
                    }
                }
            }
        }
    }

}while(false);