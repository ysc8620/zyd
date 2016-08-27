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
    $postStr = file_get_contents("http://interface.win007.com/zq/Team_XML.aspx");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['i'] as $item){
        $info = [
            'team_id' => intval($item['id']),
            'league_id' => intval($item['lsID']),
            'cn_name' => getValue($item['g']),
            'tw_name' => getValue($item['b']),
            'en_name' => getValue($item['e']),
            'found' => getValue($item['Found']),
            'area' => getValue($item['Area']),
            'gym' => getValue($item['gym']),
            'capacity' => getValue($item['Capacity']),
            'flag' => getValue($item['Flag']),
            'addr' => getValue($item['addr']),
            'url' => getValue($item['URL']),
            'master' => getValue($item['master']),
            'update_time' => time(),
            'update_date' => date('Y-m-d H:i:s')
        ];
        $team = M('team')->where(array('team_id'=>$info['team_id']))->field('id,team_id')->find();
        if($team){
            M('team')->where(array('id'=>$team['id']))->save($info);
        }else{
            M('team')->add($info);
        }
    }
}while(false);