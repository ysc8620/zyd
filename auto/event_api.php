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
                        unset($info['update_date']);
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
                    $match_info = M('match')->where(array('match_id'=>$match_id))->field("technic,id,match_id")->find();
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
                        M('match')->where(array('id'=>$match_info['id']))->save(array('technic' =>json_encode($technic)));
                    }
                }
            }

        }
    }

    M('event')->where(array('match_id'=>array('in',$match_ids), 'update_time'=>array('lt', $update_time)))->delete();
    // echo M()->getLastSql();
    $list = M('event')->where(array('event_type'=>1 , 'is_send_tuisong'=>0))->field("id,is_home_away,event_type,match_id")->select();
    //
    foreach($list as $item){
        echo "send {$item['id']}-{$item['match_id']}\r\n";
        $match = M('match')->where(array('match_id'=>$item['match_id']))->field('id,league_id,league_name,home_name,away_name,match_id,home_score,away_score')->find();
        if($match['home_score'] == '0' || $match['away_score'] == '0'){continue;}
        // 判断比分变化情况
        $event_log = M('event_tuisong')->where(array('match_id'=>$item['match_id']))->find();
        if($event_log
            && $event_log['home_score'] == $match['home_score']
            && $event_log['away_score'] == $match['away_score']){
            // 如果当前比分没有变化 则忽略推送
            M('event')->where(array('id'=>$item['id']))->save(['is_send_tuisong'=>1]);
            continue;
        }
        // 推送日志
        $log = [
            'match_id'=>$item['match_id'],
            'home_score'=>$match['home_score'],
            'away_score'=>$match['away_score']
        ];
        // 增加推送日志
        if($event_log){
            M('event_tuisong')->where(array('id'=>$event_log['id']))->save($log);
        }else{
            M('event_tuisong')->add($log);
        }
        // 关注的用户发布竞猜
        $match_name = $match['league_name'];

        // 直接关注比赛
        $user_list = M()->table("t_match_follow as m, t_users as u")->where("m.match_id='{$match['match_id']}' AND u.id = m.user_id")->field('u.id, u.jiguang_id, u.jiguang_alias')->select();
        $jiguang_alias = [];
        $jiguang_id = [];
        $home_str = "";
        $away_str = "";
        if($item['is_home_away']){
            $home_str = "（进球）";
        }else{
            $away_str = "（进球）";
        }
        $match_title = "{$match_name} {$match['home_name']}{$home_str} {$match['home_score']}-{$match['away_score']} {$match['away_name']}{$away_str}";
        foreach($user_list as $user){
            if($user['jiguang_id']){
                $jiguang_id[$user['jiguang_id']] = $user['jiguang_id'];
            }

            // 消息通知
            $notice = [
                'notice_type'=>1,
                'from_id'=>$match['match_id'],
                'to_id'=>$user['id'],
                'notice_title'=>'比赛进球',
                'notice_msg'=>$match_title,
                'create_time'=>time()
            ];
            M('notice_info')->add($notice);
        }






        send_tuisong($jiguang_alias, $jiguang_id,'比赛进球',$match_title,0,$match['match_id']);

        M('event')->where(array('id'=>$item['id']))->save(['is_send_tuisong'=>1]);
    }

}while(false);