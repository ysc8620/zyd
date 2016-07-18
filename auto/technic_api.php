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
    global $mongo;
    $curr = $mongo->zyd->match;
    $postStr = file_get_contents("http://interface.win007.com/zq/Technic_XML.aspx?date=".date("Y-m-d"));
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $item){
        $match_id = $item['id'];
        $match_info = $curr->findOne(array('match_id'=>$match_id));
        if($match_info){
            $technic = (array)$match_info['technic'];
            $info = explode(';', trim($item['TechnicCount']));
            foreach($info as $row){
                $list = explode(',', $row);
                $technic['id'.$list[0]] = [
                  'home' => $list[1],
                    'away' => $list[2]
                ];
            }
            $curr->update(array('match_id'=>$match_id), array('$set'=>array('technic' =>$technic)));
        }
    }
}while(false);