<?php
/**
 * 获取球队信息
 * User: ShengYue
 * Date: 2016/7/16
 * Time: 13:24
 */

// url http://interface.win007.com/zq/Player_XML.aspx

// 应用入口文件
namespace auto;
// 检测PHP环境
require_once __DIR__ .'/config.php';
echo date("Y-m-d H:i:s")."=team_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test
do{
//    global $mongo;
//    $curr = $mongo->zyd->match;
    $postStr = file_get_contents("http://interface.win007.com/zq/ModifyRecord.aspx");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['match'] as $match){
        $info = [
            'match_id' => $match['ID'],
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s')
        ];
        if($match['type'] == 'modify'){
            $info['time'] = $match['matchtime'];
        }elseif($match['type'] == 'delete'){
            $info['state'] = -10;
        }
        $team = M('match')->where(array('match_id'=>$info['match_id']))->field('id,match_id')->find();
        if($team){
            M('match')->where(array('match_id'=>$info['match_id']))->save($info);
        }
    }
}while(false);