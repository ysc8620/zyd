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
//    $curr = $mongo->zyd->event;
//    $curr_match = $mongo->zyd->match;
    $postStr = file_get_contents("http://interface.win007.com/zq/detail.aspx");
    $data = explode("\r\n", $postStr);
    $update_time = time();
    $match_ids = [];
    foreach ($data as $item) {
        if(strpos($item,'rq[') !== false){
            preg_match_all("/\"(.*?)\"/i", $item, $res);
            if($res[0]){
                if($res[1][0]){
                    $row = explode('^', $res[1][0]);
                    $match_ids[intval($row[0])] = intval($row[0]);
                    $info = [
                        'match_id' => intval($row[0]),
                        'is_home_away' => intval($row[1]),
                        'event_type' => getValue($row[2]),
                        'time' => getValue($row[3]),
                        'player' => getValue($row[4]),
                        'player_id' => getValue($row[5]),
                        'player_name' => getValue($row[6]),
                        'update_time' => $update_time,
                        'update_date' => date('Y-m-d H:i:s')
                    ];
                    $event = M('event')->where(array('match_id'=>$info['match_id'], 'time'=>$info['time']))->find();
                    if($event){
                        M('event')->where(array('id'=>$event['id']))->save($info);
                    }else{
                        M('event')->add($info);
                    }
                }
            }
        }elseif(strpos($item,'TC[') !== false){
            preg_match_all("/\"(.*?)\"/i", $item, $res);
            if($res[0]){
                if($res[1][0]){
                    $info = explode('^', $res[1][0]);
                    $match_id = $info[0];
//                    $match_info = $curr_match->findOne(array('match_id'=>$match_id));
                    $match_info = M('match')->where(array('match_id'=>$match_id))->find();
                    if($match_info){
                        $technic = empty($match_info['technic'])?[]:json_decode($match_info['technic'],true);
                        $info = explode(';', trim($info[1]));
                        $technic['update_time'] = time();
                        $technic['update_date'] = date('Y-m-d H:i:s');
                        $technic['last_update_event'] = 'event';
                        foreach($info as $row){
                            $list = explode(',', $row);
                            $technic['id'.$list[0]] = [
                                'home' => $list[1],
                                'away' => $list[2]
                            ];
                        }
                        M('match')->where(array('match_id'=>$match_id))->save(array('technic' =>json_encode($technic)));
                    }
                }
            }

        }
    }

    M('event')->where(array('match_id'=>array('in',$match_ids), 'update_time'=>array('neq', $update_time)))->find();
    M()->getLastSql();

}while(false);