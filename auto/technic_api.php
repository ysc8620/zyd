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
echo date("Y-m-d H:i:s")."=technic_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test
do{
//    global $mongo;
//    $curr = $mongo->zyd->match;
    $postStr = file_get_contents("http://interface.win007.com/zq/Technic_XML.aspx?date=".date("Y-m-d"));
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $item){
        $match_id = $item['id'];
        $match_info = M('match')->where(array('match_id'=>$match_id))->find();
        if($match_info){
            $technic = empty($match_info['technic'])?[]:json_decode($match_info['technic'],true);
            $technic['update_time'] = time();
            $technic['update_date'] = date('Y-m-d H:i:s');
            $technic['last_update_event'] = 'technic';
            $info = explode(';', trim($item['TechnicCount']));
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
}while(false);