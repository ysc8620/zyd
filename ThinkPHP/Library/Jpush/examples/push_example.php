<?php
require 'conf.php';

// 简单推送示例
/*
 type=0 推送赛事信息 from_id=赛事id
type=1 推送专家或用户信息 from_id=用户id
type=2 推送竞猜信息 from_id=竞猜id

这几种类型的推送 点击 进入到各自的详情页
*/

$push_payload = $client->push()
    ->setPlatform('android')
   //->addRegistrationId('141fe1da9ead0b3ce9a')
   //    ->addTag("NBA")
   ->addAllAudience()
    ->setNotificationAlert('hello zhuanjia')
//    ->iosNotification('Hello test zhuanjia', array(
//        'sound' => 'default',
//        'badge' => 1,
//        #'content-available' => true,
//       # 'category' => 'jiguang',
//        'extras' => array(
//            'type' => '1',
//            'from_id'=>'10017'
//        ),
//    ))
    ->androidNotification('Hello Android', array(
        'title' => 'hello jpush',
        'build_id' => 2,
        'extras' => array(
            'type' => '2',
            'from_id'=>'2'
        ),
    ))
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
// 完整的推送示例
try {
    $response = $client->push()
//        ->setPlatform(array('ios', 'android'))
//        ->addAlias('alias')
//        ->addTag(array('tag1', 'tag2'))
//        ->addRegistrationId($registration_id)
        ->setPlatform('all')
        ->addAllAudience()
        ->setNotificationAlert('Hi, JPush')
        ->iosNotification('Hello IOS', array(
            'sound' => 'hello jpush',
            'badge' => 2,
            'content-available' => true,
            'category' => 'jiguang',
            'extras' => array(
                'key' => 'value',
                'jiguang'
            ),
        ))
        ->androidNotification('Hello Android', array(
            'title' => 'hello jpush',
            'build_id' => 2,
            'extras' => array(
                'key' => 'value',
                'jiguang'
            ),
        ))
        ->message('message content', array(
            'title' => 'hello jpush',
            'content_type' => 'text',
            'extras' => array(
                'key' => 'value',
                'jiguang'
            ),
        ))
        ->options(array(
            'sendno' => 100,
            'time_to_live' => 100,
            'apns_production' => false,
            'big_push_duration' => 100
        ))
        ->send();
} catch (\JPush\Exceptions\APIConnectionException $e) {
    // try something here
    print $e;
} catch (\JPush\Exceptions\APIRequestException $e) {
    // try something here
    print $e;
}

print_r($response);
