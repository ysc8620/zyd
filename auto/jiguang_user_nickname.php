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

require __DIR__ . '/../ThinkPHP/Library/Jpush/autoload.php';

use JPush\Client as JPush;

$app_key = "30b1dce198d525524980af61";
$master_secret = "c1281a437204064c2190979f";
$registration_id = "1a1018970aa0c6a908c";
$client = new JPush($app_key, $master_secret);

$user_list = M('users')->where(array('jiguang_id'=>array('neq',''),'jiguang_alias'=>''))->field("id,jiguang_id,jiguang_type")->select();
foreach($user_list as $user){
    echo $user['id']."\r\n";
    $response = $client->device()->updateAlias($registration_id, 'U'.$user['id']);
    if($response['http_code'] == 200){
        M('users')->where(array('id'=>$user['id']))->save(['jiguang_alias'=>'U'.$user['id']]);
    }
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