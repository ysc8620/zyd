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

$match_list = M('match')->where(array('is_send_end'=>0,'state'=>"-1"))->field("id,match_id,league_id,league_name,time,home_name,away_name,home_score,away_score")->select();
echo M()->getLastSql();
foreach($match_list as $match){
    // 直接关注比赛
    $user_list = M()->table("t_match_follow as m, t_users as u")->where("m.match_id='{$match['match_id']}' AND m.user_id = u.id")->field('u.id, u.jiguang_id, u.jiguang_alias')->select();
    $jiguang_alias = [];
    $jiguang_id = [];

    // 关注的用户发布竞猜
    $match_name = "{$match['league_name']}";

    $match_title = "您关注的比赛{$match_name} {$match['home_name']} VS {$match['away_name']}已结束，比分是{$match['home_score']}：{$match['away_score']}";

    foreach($user_list as $user){
        if($user['jiguang_id']){
            $jiguang_id[$user['jiguang_id']] = $user['jiguang_id'];
        }

        // 消息通知
        $notice = [
            'notice_type'=>1,
            'from_id'=>$match['match_id'],
            'to_id'=>$user['id'],
            'notice_title'=>'比赛结束',
            'notice_msg'=>$match_title,
            'create_time'=>time()
        ];
        M('notice_info')->add($notice);
    }


    send_tuisong($jiguang_alias, $jiguang_id,'比赛结束',$match_title,0,$match['match_id']);
    M('match')->where(array('id'=>$match['id']))->save(['is_send_end'=>1,'send_end_time'=>time()]);
}
echo "ok\r\n";

return;

$push_payload = $client->push()
    ->setPlatform('all')
    //->addRegistrationId('141fe1da9ead0b3ce9a')
    //    ->addTag("NBA")
    ->addAllAudience()
    ->setNotificationAlert('hello zhuanjia')
    ->iosNotification('Hello test zhuanjia', array(
        'sound' => 'default',
        'badge' => 1,
        #'content-available' => true,
        # 'category' => 'jiguang',
        'extras' => array(
            'type' => '1',
            'from_id'=>'10017'
        ),
    ))
//    ->androidNotification('Hello Android', array(
//        'title' => 'hello jpush',
//        'build_id' => 2,
//        'extras' => array(
//            'type' => '0',
//            'from_id'=>'1300494'
//        ),
//    ))
;
try {
    $response = $push_payload->send();
}catch (\JPush\Exceptions\APIConnectionException $e) {
    // try something here
    print $e;
} catch (\JPush\Exceptions\APIRequestException $e) {
    // try something here
    print $e;
}
print_r($response);

die();