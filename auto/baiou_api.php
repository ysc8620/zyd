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
    $curr = $mongo->zyd->baiou;
    $postStr = file_get_contents("http://interface.win007.com/zq/1x2.aspx?min=6");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['h'] as $item){
        $info = [
            'match_id' => $item['id'],
            'time' => $item['time'],
            'league' => $item['league'],
            'home' => $item['home'],
            'away' => $item['away'],
            'odds' => []
        ];
        foreach($item['odds']['o'] as $odds){
            $row = explode(',', $odds);
            $info['odds'][$row[0]] = [
                'company_id' => $row[0],
                'company_name' => $row[1],
                'begin_home_win' => $row[2],
                'begin_draw' => $row[3],
                'away_win' => $row[4],
                'home_win' => $row[5],
                'away_win' => $row[6],
                'away_win' => $row[7],
                'change_time' => $row[8],
               // 'info' => $row[9]
            ];
            $baiou = $curr->findOne(array('match_id'=>$info['match_id']));
            if($baiou){
                $curr->update(array('_id'=>$baiou['_id']), array('$set'=>$info));
            }else{
                $curr->insert($info);
            }
        }

    }
}while(false);