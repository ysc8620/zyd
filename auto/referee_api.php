<?php
/**
 * 获取裁判信息
 * User: ShengYue
 * Date: 2016/7/16
 * Time: 13:24
 */

// url http://interface.win007.com/zq/Player_XML.aspx

// 应用入口文件
namespace auto;
// 检测PHP环境
require_once __DIR__ .'/config.php';
echo date("Y-m-d H:i:s")."=referee_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test
do{
//    global $mongo;
//    $curr = $mongo->zyd->referee;
    $postStr = file_get_contents("http://interface.win007.com/zq/Referee.aspx");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['i'] as $item){
        $info = [
            'schedule_id' => intval($item['scheduleID']),
            'type_id' => intval($item['typeID']),
            'referee_id' => intval($item['refereeID']),
            'cn_name' => getValue($item['Name_J']),
            'tw_name' => getValue($item['name_f']),
            'en_name' => getValue($item['Name_E']),
            'birthday' => getValue($item['Birthday']),
            'country' => getValue($item['country']),
            'photo' => getValue($item['Photo']),
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s')
        ];
        $referee = M('referee')->where(array('referee_id'=>$info['referee_id']))->field('id')->find();
        if($referee){
            M('referee')->where(array('referee_id'=>$info['referee_id']))->save($info);
        }else{
            M('referee')->add($info);
        }
    }
}while(false);