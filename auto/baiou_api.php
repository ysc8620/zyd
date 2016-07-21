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
echo date("Y-m-d H:i:s")."=baiou_api=\r\n";
//mongodb://admin_miss:miss@localhost:27017/test
do{
    global $mongo;
    $curr = $mongo->zyd->baiou;
    $postStr = file_get_contents("http://interface.win007.com/zq/1x2.aspx?min=6");
    $obj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
    $data = json_decode(json_encode($obj), true);

    foreach($data['h'] as $item){
        $info = [
            'match_id' => intval($item['id']),
            'time' => strval($item['time']),
            'league' => strval($item['league']),
            'home' => strval($item['home']),
            'away' => strval($item['away']),
            'update_time' => time(),
        ];
        foreach($item['odds']['o'] as $odds){
            $row = explode(',', $odds);
            $info1= [
                'company_id' => strval($row[0]),
                'company_name' => strval($row[1]),
                'begin_home_win' => strval($row[2]),
                'begin_draw' => strval($row[3]),
                'begin_away_win' => strval($row[4]),
                'home_win' => strval($row[5]),
                'draw_win' => strval($row[6]),
                'away_win' => strval($row[7]),
                'change_time' => strval($row[8]),
                'update_time' => time(),
               // 'info' => $row[9]
            ];
            $new = array_merge($info, $info1);
            $baiou = M('baiou')->where(array('match_id'=>$info['match_id'],'company_id'=>$new['company_id'], 'change_time'=>$new['change_time']))->find();
            if(!$baiou){
                M('baiou')->add($new);
            }
        }

    }
}while(false);