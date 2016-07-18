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

//mongodb://admin_miss:miss@localhost:27017/test
do{
    global $mongo;
    $curr = $mongo->zyd->referee;
    $postStr = file_get_contents("http://interface.win007.com/zq/Referee.aspx");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['i'] as $item){
        $info = [
            'schedule_id' => $item['scheduleID'],
            'type_id' => $item['typeID'],
            'referee_id' => $item['refereeID'],
            'cn_name' => trim($item['Name_J']),
            'tw_name' => trim($item['name_f']),
            'en_name' => trim($item['Name_E']),
            'birthday' => $item['Birthday'],
            'country' => $item['country'],
            'photo' => $item['Photo'],
        ];
        $referee = $curr->findOne(array('referee_id'=>$info['referee_id']));
        if($referee){
            $curr->update(array('referee_id'=>$info['referee_id']), array('$set'=>$info));
        }else{
            $curr->insert($info);
        }
    }
}while(false);