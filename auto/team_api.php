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
require_once 'config.php';

//mongodb://admin_miss:miss@localhost:27017/test
do{
    global $mongo;
    $curr = $mongo->zyd->team;
    $postStr = file_get_contents("http://interface.win007.com/zq/Team_XML.aspx");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['i'] as $item){
        $info = [
            'team_id' => $item['id'],
            'league_id' => $item['lsID'],
            'cn_name' => $item['g'],
            'tw_name' => trim($item['b']),
            'en_name' => trim($item['e']),
            'found' => trim($item['Found']),
            'area' => $item['Area'],
            'gym' => $item['gym'],
            'capacity' => $item['Capacity'],
            'flag' => $item['Flag'],
            'addr' => $item['addr'],
            'url' => $item['URL'],
            'master' => $item['master'],
        ];
        $team = $curr->findOne(array('team_id'=>$info['team_id']));
        if($team){
            $curr->update(array('team_id'=>$info['team_id']), array('$set'=>$info));
        }else{
            $curr->insert($info);
        }
    }
}while(false);